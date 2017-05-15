<?php

namespace CultuurNet\UDB3\IISImporter\Processor;

use CultuurNet\UDB3\IISImporter\AMQP\AMQPPublisherInterface;
use CultuurNet\UDB3\IISImporter\File\FileManagerInterface;
use CultuurNet\UDB3\IISImporter\Media\MediaManagerInterface;
use CultuurNet\UDB3\IISImporter\Parser\ParserInterface;
use CultuurNet\UDB3\IISImporter\Url\UrlFactoryInterface;
use CultuurNet\UDB3\IISStore\Stores\RepositoryInterface;
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
     * FileProcessor constructor.
     * @param FileManagerInterface $fileManager
     * @param ParserInterface $parser
     * @param RepositoryInterface $store
     * @param AMQPPublisherInterface $publisher
     * @param UrlFactoryInterface $urlFactory
     * @param StringLiteral $author
     * @param MediaManagerInterface $mediaManager
     */
    public function __construct(
        FileManagerInterface $fileManager,
        ParserInterface $parser,
        RepositoryInterface $store,
        AMQPPublisherInterface $publisher,
        UrlFactoryInterface $urlFactory,
        StringLiteral $author,
        MediaManagerInterface $mediaManager
    ) {
        $this->fileManager = $fileManager;
        $this->parser = $parser;
        $this->store = $store;
        $this->publisher = $publisher;
        $this->urlFactory = $urlFactory;
        $this->author = $author;
        $this->mediaManager = $mediaManager;
    }


    /**
     * @inheritdoc
     */
    public function consumeFile(\SplFileInfo $file)
    {
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
                $this->fileManager->moveFileToFolder($file, $this->fileManager->getErrorFolder());
            }
        } else {
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

        // Add cdbid to the event.
        $singleXml = simplexml_load_string($event);
        $singleXml->event[0]['cdbid'] = $cdbid->toNative();

        // Change the dates to local time so they don't error on import
        if ($singleXml->event[0]['creationdate']) {
            $creationDate = (string) $singleXml->event[0]['creationdate'];
            $singleXml->event[0]['creationdate'] = $this->changeDateToLocalTime($creationDate);
        }
        if ($singleXml->event[0]['lastupdated']) {
            $lastUpdated = (string) $singleXml->event[0]['lastupdated'];
            $singleXml->event[0]['lastupdated'] = $this->changeDateToLocalTime($lastUpdated);
        }
        if ($singleXml->event[0]['availablefrom']) {
            $availableFrom = (string) $singleXml->event[0]['availablefrom'];
            $singleXml->event[0]['availablefrom'] = $this->changeDateToLocalTime($availableFrom);
        }
        if ($singleXml->event[0]['availableto']) {
            $availableTo = (string) $singleXml->event[0]['availableto'];
            $singleXml->event[0]['availableto'] = $this->changeDateToLocalTime($availableTo);
        }

        if ($singleXml->event[0]->calendar[0]->timestamps[0]) {
            foreach ($singleXml->event[0]->calendar[0]->timestamps[0]->timestamp as $xmlTimeStamp) {
                if ($xmlTimeStamp->timestart) {
                    $timeStart = (string) $xmlTimeStamp->timestart;
                    $tempStart = $this->changeTimeStampToLocalTime($timeStart);
                    $xmlTimeStamp->timestart = $tempStart;
                }

                if ($xmlTimeStamp->timeend) {
                    $timeEnd = (string) $xmlTimeStamp->timeend;
                    $tempEnd = $this->changeTimeStampToLocalTime($timeEnd);
                    $xmlTimeStamp->timeend = $tempEnd;
                }
            }
        }

        if ($singleXml->event[0]->eventdetails[0]) {
            foreach ($singleXml->event[0]->eventdetails[0]->eventdetail as $eventDetail) {
                if ($eventDetail->media) {
                    foreach ($eventDetail->media[0]->file as $file) {
                        if ($file->hlink) {
                            $hlink = Url::fromNative($file->hlink);
                            $mediaLink = $this->mediaManager->generateMediaLink($hlink);
                            $file->hlink = (string) $mediaLink;
                        }
                    }
                }
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

    /**
     * @param string $date
     * @return string
     */
    private function changeDateToLocalTime($date)
    {
        $time = strtotime($date);
        return date("Y-m-d\TH:i:s", $time);
    }

    /**
     * @param string $date
     * @return string
     */
    private function changeTimeStampToLocalTime($date)
    {
        $time = strtotime($date);
        return date("H:i:s", $time);
    }
}
