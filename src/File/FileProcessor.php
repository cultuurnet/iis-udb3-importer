<?php

namespace CultuurNet\UDB3\IISImporter\File;

use CultuurNet\UDB3\IISImporter\AMQP\AMQPPublisherInterface;
use CultuurNet\UDB3\IISImporter\Event\ParserInterface;
use CultuurNet\UDB3\IISImporter\Url\UrlFactory;
use CultuurNet\UDB3\IISStore\Stores\RepositoryInterface;
use Lurker\Resource\DirectoryResource;
use Lurker\Resource\ResourceInterface;
use ValueObjects\StringLiteral\StringLiteral;
use ValueObjects\Identity\UUID;

class FileProcessor implements FileProcessorInterface
{
    const SUCCESS_FOLDER = 'success';
    const ERROR_FOLDER = 'error';
    const INVALID_FOLDER = 'invalid';

    /**
     * @var ResourceInterface
     */
    protected $resourceFolder;

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
     * @var UrlFactory
     */
    protected $urlFactory;

    /**
     * @var StringLiteral
     */
    protected $author;

    /**
     * @inheritdoc
     */
    public function moveFile($file, $folder)
    {
        $path = $this->resourceFolder . '/' . $folder;
        if (!file_exists($path) && !is_dir($path)) {
            mkdir($path);
        }
        $destination = str_replace($this->resourceFolder, $path, $file);
        rename($file, $destination);
    }

    /**
     * @inheritdoc
     */
    public function consumeFile(StringLiteral $xmlString, StringLiteral $fileName)
    {
        if ($this->parser->validate($xmlString->toNative())) {
            try {
                $eventList = $this->parser->split($xmlString->toNative());

                foreach ($eventList as $externalId => $singleEvent) {
                    $externalIdLiteral = new StringLiteral($externalId);
                    $cdbid = $this->store->getEventCdbid($externalIdLiteral);
                    $isUpdate = true;
                    if (!$cdbid) {
                        $isUpdate = false;
                        $cdbidString = UUID::generateAsString();
                        $cdbid = UUID::fromNative($cdbidString);
                    }
                    $singleXml = simplexml_load_string($singleEvent);
                    $singleXml->event[0]['cdbid'] = $cdbid->toNative();
                    $singleEvent = new StringLiteral($singleXml->asXML());

                    if ($isUpdate) {
                        $this->store->updateEventXml($cdbid, $singleEvent);
                        $this->store->saveUpdated($cdbid, new \DateTime());
                    } else {
                        $this->store->saveRelation($cdbid, $externalIdLiteral);
                        $this->store->saveEventXml($cdbid, $singleEvent);
                        $this->store->saveCreated($cdbid, new \DateTime());
                    }

                    $now = new \DateTime();
                    $this->publisher->publish($cdbid, $now, $this->author, $this->urlFactory->generateUrl($cdbid), $isUpdate);
                    $this->store->savePublished($cdbid, $now);
                    $this->moveFile($fileName->toNative(), FileProcessor::SUCCESS_FOLDER);
                }
            } catch (\Exception $e) {
                $this->moveFile($fileName->toNative(), FileProcessor::ERROR_FOLDER);
            }
        } else {
            $this->moveFile($fileName->toNative(), FileProcessor::INVALID_FOLDER);
        }
    }

    public function __construct(
        DirectoryResource $resource,
        ParserInterface $parser,
        RepositoryInterface $store,
        AMQPPublisherInterface $publisher,
        UrlFactory $urlFactory,
        StringLiteral $author
    ) {
        $this->resourceFolder = $resource;
        $this->parser = $parser;
        $this->store = $store;
        $this->publisher = $publisher;
        $this->urlFactory = $urlFactory;
        $this->author = $author;
    }
}
