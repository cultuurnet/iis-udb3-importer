<?php

namespace CultuurNet\UDB3\IISImporter\Event;

use CultuurNet\UDB3\IISImporter\AMQP\AMQPPublisherInterface;
use CultuurNet\UDB3\IISImporter\Url\UrlFactory;
use CultuurNet\UDB3\IISStore\Stores\RepositoryInterface;
use Lurker\Resource\DirectoryResource;
use Lurker\Resource\ResourceInterface;
use ValueObjects\StringLiteral\StringLiteral;
use Lurker\Event\FilesystemEvent;
use Lurker\ResourceWatcher;
use ValueObjects\Identity;
use ValueObjects\Identity\UUID;

class Watcher implements WatcherInterface
{
    const SUCCESS_FOLDER = 'success';
    const ERROR_FOLDER = 'error';

    /**
     * @var StringLiteral
     */
    protected $trackingId;

    /**
     * @var ResourceWatcher
     */
    protected $resourceWatcher;

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
     * @var ResourceInterface
     */
    protected $resourceFolder;

    /**
     * @param StringLiteral $trackingId
     * @param ParserInterface $parser
     * @param RepositoryInterface $store
     * @param AMQPPublisherInterface $publisher
     */
    public function __construct(
        StringLiteral $trackingId,
        ParserInterface $parser,
        RepositoryInterface $store,
        AMQPPublisherInterface $publisher
    ) {
        $this->trackingId = $trackingId;
        $this->parser = $parser;
        $this->store = $store;
        $this->publisher = $publisher;

        $this->resourceWatcher = new ResourceWatcher();
    }

    /**
     * @inheritdoc
     */
    public function track($resource)
    {
        $directoryResource = new DirectoryResource($resource);
        $this->resourceFolder = $resource;
        $this->checkFolder();
        $this->resourceWatcher->track($this->trackingId->toNative(), $directoryResource);
    }

    /**
     * @inheritdoc
     */
    public function configureListener()
    {
        $this->resourceWatcher->addListener(
            $this->trackingId->toNative(),
            function (FilesystemEvent $filesystemEvent) {
                if ($filesystemEvent->isFileChange() &&
                    ($filesystemEvent->getTypeString() == 'create' ||
                    $filesystemEvent->getTypeString() == 'modify') &&
                    !$this->isSubFolder($filesystemEvent->getResource())
                ) {
                    $xmlString = new StringLiteral(file_get_contents($filesystemEvent->getResource()));
                    $this->consumeFile($xmlString, new StringLiteral($filesystemEvent->getResource()));
                }
            }
        );
    }

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

    public function start()
    {
        $this->resourceWatcher->start();
    }

    /**
     * @param ResourceInterface $resource
     * @return bool
     */
    protected function isSubFolder(ResourceInterface $resource)
    {
        $path = (string) $resource;
        return 0 === strpos($path, $this->resourceFolder . '/' . Watcher::ERROR_FOLDER) ||
            0 === strpos($path, $this->resourceFolder . '/' . Watcher::SUCCESS_FOLDER);
    }

    /**
     * @return void
     */
    protected function checkFolder()
    {
        $files = scandir($this->resourceFolder);
        foreach ($files as $file) {
            $fileLiteral = new StringLiteral($this->resourceFolder.'/'.$file);
            if (is_file($fileLiteral->toNative())) {
                $xmlString = new StringLiteral(file_get_contents($fileLiteral->toNative()));
                $this->consumeFile($xmlString, $fileLiteral);
            }
        }
    }

    /**
     * @param StringLiteral $xmlString
     * @param StringLiteral $fileName
     * @return void
     */
    protected function consumeFile(StringLiteral $xmlString, StringLiteral $fileName)
    {
        if ($this->parser->validate($xmlString->toNative())) {
            $eventList = $this->parser->split($xmlString->toNative());

            foreach ($eventList as $externalId => $singleEvent) {
                $externalIdLiteral = new StringLiteral($externalId);
                $cdbid = $this->store->getEventCdbid($externalIdLiteral);
                $isUpdate = true;
                if (!$cdbid) {
                    $isUpdate = false;
                    $cdbidString = Identity\UUID::generateAsString();
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
                $baseUrl = new StringLiteral('http://test.import.com');
                $author = new StringLiteral('importsUDB3');
                $urlFactory = new UrlFactory($baseUrl);
                $this->publisher->publish($cdbid, $now, $author, $urlFactory->generateUrl($cdbid), $isUpdate);
                $this->store->savePublished($cdbid, $now);
                $this->moveFile($fileName->toNative(), Watcher::SUCCESS_FOLDER);
            }
        } else {
            $this->moveFile($fileName->toNative(), Watcher::ERROR_FOLDER);
        }
    }
}
