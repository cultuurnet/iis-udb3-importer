<?php

namespace CultuurNet\UDB3\IISImporter\Processor;

use CultuurNet\UDB3\IISImporter\AMQP\AMQPPublisherInterface;
use CultuurNet\UDB3\IISImporter\File\FileManagerInterface;
use CultuurNet\UDB3\IISImporter\Parser\ParserInterface;
use CultuurNet\UDB3\IISImporter\Url\UrlFactoryInterface;
use CultuurNet\UDB3\IISStore\Stores\RepositoryInterface;
use ValueObjects\StringLiteral\StringLiteral;
use ValueObjects\Identity\UUID;

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
     * FileProcessor constructor.
     * @param FileManagerInterface $fileManager
     * @param ParserInterface $parser
     * @param RepositoryInterface $store
     * @param AMQPPublisherInterface $publisher
     * @param UrlFactoryInterface $urlFactory
     * @param StringLiteral $author
     */
    public function __construct(
        FileManagerInterface $fileManager,
        ParserInterface $parser,
        RepositoryInterface $store,
        AMQPPublisherInterface $publisher,
        UrlFactoryInterface $urlFactory,
        StringLiteral $author
    ) {
        $this->fileManager = $fileManager;
        $this->parser = $parser;
        $this->store = $store;
        $this->publisher = $publisher;
        $this->urlFactory = $urlFactory;
        $this->author = $author;
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
            $this->urlFactory->generateUrl($cdbid),
            $isUpdate
        );
        $this->store->savePublished($cdbid, $now);
    }
}
