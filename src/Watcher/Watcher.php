<?php

namespace CultuurNet\UDB3\IISImporter\Watcher;

use CultuurNet\UDB3\IISImporter\File\FileManagerInterface;
use CultuurNet\UDB3\IISImporter\Processor\ProcessorInterface;
use ValueObjects\StringLiteral\StringLiteral;
use Lurker\Event\FilesystemEvent;
use Lurker\ResourceWatcher;

class Watcher implements WatcherInterface
{
    /**
     * @var StringLiteral
     */
    private $trackingId;

    /**
     * @var FileManagerInterface
     */
    private $fileManager;

    /**
     * @var ProcessorInterface
     */
    private $processor;

    /**
     * @var ResourceWatcher
     */
    private $resourceWatcher;

    /**
     * @param StringLiteral $trackingId
     * @param FileManagerInterface $fileManager
     * @param ProcessorInterface $processor
     */
    public function __construct(
        StringLiteral $trackingId,
        FileManagerInterface $fileManager,
        ProcessorInterface $processor
    ) {
        $this->trackingId = $trackingId;
        $this->fileManager = $fileManager;
        $this->processor = $processor;

        $this->resourceWatcher = new ResourceWatcher();

        $this->track();

        $this->addListener();
    }

    public function start()
    {
        $this->processFolder();
        $this->resourceWatcher->start();
    }

    private function track()
    {
        $this->resourceWatcher->track(
            $this->trackingId->toNative(),
            $this->fileManager->getProcessFolder()
        );
    }

    private function addListener()
    {
        $this->resourceWatcher->addListener(
            $this->trackingId->toNative(),
            function (FilesystemEvent $filesystemEvent) {
                $this->processFileSystemEvent($filesystemEvent);
            }
        );
    }

    /**
     * @param FilesystemEvent $filesystemEvent
     */
    private function processFileSystemEvent(FilesystemEvent $filesystemEvent)
    {
        if ($filesystemEvent->isFileChange() &&
            ($filesystemEvent->getTypeString() == 'create' ||
                $filesystemEvent->getTypeString() == 'modify')
        ) {
            $splInfo = new \SplFileInfo((string) $filesystemEvent->getResource());
            $this->processor->consumeFile($splInfo);
        }
    }

    private function processFolder()
    {
        $files = $this->fileManager->getProcessFolderFiles();
        foreach ($files as $file) {
            $this->processor->consumeFile($file);
        }
    }
}
