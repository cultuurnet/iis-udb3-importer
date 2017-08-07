<?php

namespace CultuurNet\UDB3\IISImporter\Processor;

use CultuurNet\UDB3\IISImporter\AMQP\AMQPPublisherInterface;
use CultuurNet\UDB3\IISImporter\CategorizationRules\CategorizationRulesInterface;
use CultuurNet\UDB3\IISImporter\File\FileManagerInterface;
use CultuurNet\UDB3\IISImporter\Media\MediaManagerInterface;
use CultuurNet\UDB3\IISImporter\Parser\ParserInterface;
use CultuurNet\UDB3\IISImporter\Time\TimeFactoryInterface;
use CultuurNet\UDB3\IISImporter\Url\UrlFactoryInterface;
use CultuurNet\UDB3\IISStore\Stores\RepositoryInterface;
use Monolog\Logger;
use ValueObjects\StringLiteral\StringLiteral;
use ValueObjects\Identity\UUID;
use ValueObjects\Web\Url;

class Processor implements ProcessorInterface
{
    /**
     * @var FileManagerInterface
     */
    protected $fileManager;

    /**
     * @var ParserInterface
     */
    protected $parser;

    /**
     * @var RepositoryInterface
     */
    protected $store;

    /**
     * @var AMQPPublisherInterface
     */
    protected $publisher;

    /**
     * @var UrlFactoryInterface
     */
    protected $urlFactory;

    /**
     * @var StringLiteral
     */
    protected $author;

    /**
     * @var MediaManagerInterface
     */
    protected $mediaManager;

    /**
     * @var TimeFactoryInterface
     */
    protected $timeFactory;

    /**
     * @var CategorizationRulesInterface
     */
    protected $flandersRegionFactory;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * FileProcessor constructor.
     * @param FileManagerInterface $fileManager
     * @param ParserInterface $parser
     * @param RepositoryInterface $store
     * @param AMQPPublisherInterface $publisher
     * @param UrlFactoryInterface $urlFactory
     * @param StringLiteral $author
     * @param MediaManagerInterface $mediaManager
     * @param TimeFactoryInterface $timeFactory
     * @param CategorizationRulesInterface $flandersRegionFactory
     * @param Logger $logger
     */
    public function __construct(
        FileManagerInterface $fileManager,
        ParserInterface $parser,
        RepositoryInterface $store,
        AMQPPublisherInterface $publisher,
        UrlFactoryInterface $urlFactory,
        StringLiteral $author,
        MediaManagerInterface $mediaManager,
        TimeFactoryInterface $timeFactory,
        CategorizationRulesInterface $flandersRegionFactory,
        Logger $logger
    ) {
        $this->fileManager = $fileManager;
        $this->parser = $parser;
        $this->store = $store;
        $this->publisher = $publisher;
        $this->urlFactory = $urlFactory;
        $this->author = $author;
        $this->mediaManager = $mediaManager;
        $this->timeFactory = $timeFactory;
        $this->flandersRegionFactory = $flandersRegionFactory;
        $this->logger = $logger;
    }


    /**
     * @inheritdoc
     */
    public function consumeFile(\SplFileInfo $file)
    {
        $this->logger->debug('Will consume file: ' . $file->getFilename());
        $xmlString = file_get_contents($file->getPathname());

        if ($this->parser->validate($xmlString)) {
            try {
                $eventList = $this->parser->split($xmlString);

                foreach ($eventList as $externalId => $event) {
                    $this->processEvent(
                        new StringLiteral($externalId),
                        $event
                    );
                }

                $this->fileManager->moveFileToFolder($file, $this->fileManager->getSuccessFolder());
            } catch (\Exception $e) {
                $this->logger->error($file->getFilename() . ' gives error: ' . $e->getMessage());
                $this->fileManager->moveFileToFolder($file, $this->fileManager->getErrorFolder());
            }
        } else {
            $this->logger->warning($file->getFilename() . ' is invalid');
            $this->fileManager->moveFileToFolder($file, $this->fileManager->getInvalidFolder());
        }
    }

    /**
     * @param StringLiteral $externalId
     * @param string $event
     */
    private function processEvent(StringLiteral $externalId, $event)
    {
        $now = new \DateTime();
        $isUpdate = true;

        // Check for existing event.
        $cdbid = $this->store->getEventCdbid($externalId);
        if (!$cdbid) {
            $isUpdate = false;
            $cdbidString = UUID::generateAsString();
            $cdbid = UUID::fromNative($cdbidString);
        }

        $this->logger->debug('externalId: ' . $externalId->toNative() . ' is cdbid: ' . $cdbid->toNative());

        // Add cdbid to the event.
        $singleXml = simplexml_load_string($event);
        $singleXml->event[0]['cdbid'] = $cdbid->toNative();

        // Add wfstatus to autovalidate the event.
        $singleXml->event[0]['wfstatus'] = 'approved';

        // Add private status if event is for schools
        if (isset($singleXml->event[0]->categories[0])) {
            foreach ($singleXml->event[0]->categories[0]->category as $xmlCategory) {
                if ('Scholen' == (string) $xmlCategory) {
                    $singleXml->event[0]['private'] = 'true';
                }
            }
        }

        // Change the dates to local time so they don't error on import
        if ($singleXml->event[0]['creationdate']) {
            $creationDate = (string) $singleXml->event[0]['creationdate'];
            if (!$this->timeFactory->isAlreadyLocalTime($creationDate)) {
                $singleXml->event[0]['creationdate'] = $this->timeFactory->changeDateToLocalTime($creationDate);
            }
        }
        if ($singleXml->event[0]['lastupdated']) {
            $lastUpdated = (string) $singleXml->event[0]['lastupdated'];
            if (!$this->timeFactory->isAlreadyLocalTime($lastUpdated)) {
                $singleXml->event[0]['lastupdated'] = $this->timeFactory->changeDateToLocalTime($lastUpdated);
            }
        }
        if ($singleXml->event[0]['availablefrom']) {
            $availableFrom = (string) $singleXml->event[0]['availablefrom'];
            if (!$this->timeFactory->isAlreadyLocalTime($availableFrom)) {
                $singleXml->event[0]['availablefrom'] = $this->timeFactory->changeDateToLocalTime($availableFrom);
            }
        }
        if ($singleXml->event[0]['availableto']) {
            $availableTo = (string) $singleXml->event[0]['availableto'];
            if (!$this->timeFactory->isAlreadyLocalTime($availableTo)) {
                $singleXml->event[0]['availableto'] = $this->timeFactory->changeDateToLocalTime($availableTo);
            }
        }

        if ($singleXml->event[0]->calendar[0]->timestamps[0]) {
            foreach ($singleXml->event[0]->calendar[0]->timestamps[0]->timestamp as $xmlTimeStamp) {
                if ($xmlTimeStamp->timestart) {
                    $timeStart = (string) $xmlTimeStamp->timestart;

                    if (!$this->timeFactory->isAlreadyLocalTime($timeStart)) {
                        $tempStart = $this->timeFactory->changeTimeStampToLocalTime($timeStart);
                        $xmlTimeStamp->timestart = $tempStart;
                    }
                }

                if ($xmlTimeStamp->timeend) {
                    $timeEnd = (string) $xmlTimeStamp->timeend;

                    if (!$this->timeFactory->isAlreadyLocalTime($timeEnd)) {
                        $tempEnd = $this->timeFactory->changeTimeStampToLocalTime($timeEnd);
                        $xmlTimeStamp->timeend = $tempEnd;
                    }
                }
            }
        }

        if ($singleXml->event[0]->calendar[0]->periods[0]) {
            foreach ($singleXml->event[0]->calendar[0]->periods[0]->period[0] as $period) {
                if ($period->children()) {
                    foreach ($period->children() as $day) {
                        if ($day->openingtime) {
                            if ($day->openingtime['from']) {
                                $from = (string) $day->openingtime['from'];
                                if (!$this->timeFactory->isAlreadyLocalTime($from)) {
                                    $day->openingtime['from'] = $this->timeFactory->changeTimeStampToLocalTime($from);
                                }
                            }

                            if ($day->openingtime['to']) {
                                $to = (string) $day->openingtime['to'];
                                if (!$this->timeFactory->isAlreadyLocalTime($to)) {
                                    $day->openingtime['to'] = $this->timeFactory->changeTimeStampToLocalTime($to);
                                }
                            }
                        }
                    }
                }
            }
        }

        if ($singleXml->event[0]->eventdetails[0]) {
            foreach ($singleXml->event[0]->eventdetails[0]->eventdetail as $eventDetail) {
                if ($eventDetail->media) {
                    foreach ($eventDetail->media[0]->file as $file) {
                        if (isset($file->mediatype) && $file->mediatype == 'culturefeed-page') {
                            if (!isset($file->reltype)) {
                                $file->addChild('reltype', 'organiser');
                            }
                        }
                        if ($file->hlink) {
                            try {
                                $hlink = Url::fromNative($file->hlink);
                                $mediaLink = $this->mediaManager->generateMediaLink($hlink);
                                $file->hlink = (string) $mediaLink;
                            } catch (\Exception $e) {
                                $this->logger->error($file->hlink . ' cannot be found');
                                unset($file);
                            }
                        }
                    }
                }
            }
        }


        if ($singleXml->event[0]->location[0]->address[0]) {
            $physical = $singleXml->event[0]->location[0]->address[0]->physical[0];

            $zipCode = (string) $physical->zipcode[0];
            $city = (string) $physical->city[0];
            $zipCity = new StringLiteral($zipCode . ' ' . $city);
            $category = $this->flandersRegionFactory->getCategoryFromValue($zipCity);

            if ($category) {
                $flandersRegionNode = $singleXml
                    ->event[0]->categories[0]->addChild('category', (string) $category->label);

                $flandersRegionNode->addAttribute('type', (string) $category->type);
                $flandersRegionNode->addAttribute('catid', (string) $category->catId);
            }
        }

        $event = new StringLiteral($singleXml->asXML());

        // Update or create event.
        if ($isUpdate) {
            $this->store->updateEventXml($cdbid, $event);
            $this->store->saveUpdated($cdbid, $now);
        } else {
            $this->store->saveRelation($cdbid, $externalId);
            $this->store->saveEventXml($cdbid, $event);
            $this->store->saveCreated($cdbid, $now);
        }

        // Publish the event.
        $this->publisher->publish(
            $cdbid,
            $now,
            $this->author,
            $this->urlFactory->generateEventUrl($cdbid),
            $isUpdate
        );
        $this->store->savePublished($cdbid, $now);
    }
}
