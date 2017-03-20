<?php

namespace CultuurNet\UDB3\IISImporter\Event;

use CultuurNet\UDB3\IISImporter\File\FileProcessor;
use CultuurNet\UDB3\IISImporter\File\FileProcessorInterface;
use Lurker\Resource\ResourceInterface;
use ValueObjects\StringLiteral\StringLiteral;
use Lurker\Event\FilesystemEvent;
use Lurker\ResourceWatcher;

class Watcher implements WatcherInterface
{
    /**
     * @var StringLiteral
     */
    protected $trackingId;

    /**
     * @var FileProcessorInterface
     */
    protected $fileProcessor;

    /**
     * @var ResourceWatcher
     */
    protected $resourceWatcher;

    /**
     * @param StringLiteral $trackingId
     * @param FileProcessorInterface $fileProcessor
     */
    public function __construct(
        StringLiteral $trackingId,
        FileProcessorInterface $fileProcessor
    ) {
        $this->trackingId = $trackingId;
        $this->fileProcessor = $fileProcessor;

        $this->resourceWatcher = new ResourceWatcher();
    }

    /**
     * @inheritdoc
     */
    public function track()
    {
        $directoryResource = $this->fileProcessor->getResource();
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
                    $this->fileProcessor->consumeFile(
                        $xmlString,
                        new StringLiteral($filesystemEvent->getResource())
                    );
                }
            }
        );
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
        return 0 === strpos($path, $this->fileProcessor->getResource() . '/' . FileProcessor::ERROR_FOLDER) ||
            0 === strpos($path, $this->fileProcessor->getResource() . '/' . FileProcessor::SUCCESS_FOLDER) ||
            0 === strpos($path, $this->fileProcessor->getResource() . '/' . FileProcessor::INVALID_FOLDER);
    }

    /**
     * @return void
     */
    protected function checkFolder()
    {
        $files = scandir($this->fileProcessor->getResource());
        foreach ($files as $file) {
            $fileLiteral = new StringLiteral($this->fileProcessor->getResource() . '/' . $file);
            if (is_file($fileLiteral->toNative())) {
                $xmlString = new StringLiteral(file_get_contents($fileLiteral->toNative()));
                $this->fileProcessor->consumeFile($xmlString, $fileLiteral);
            }
        }
    }
}
