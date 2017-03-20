<?php

namespace CultuurNet\UDB3\IISImporter\Event;

use CultuurNet\UDB3\IISImporter\File\FileProcessorInterface;
use Symfony\Component\Finder\Finder;
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
        $this->checkFolder();
        $this->resourceWatcher->track(
            $this->trackingId->toNative(),
            $this->fileProcessor->getPath()
        );
    }

    /**
     * Adds the listener function
     */
    public function configureListener()
    {
        $this->resourceWatcher->addListener(
            $this->trackingId->toNative(),
            function (FilesystemEvent $filesystemEvent) {
                if ($filesystemEvent->isFileChange() &&
                    ($filesystemEvent->getTypeString() == 'create' ||
                    $filesystemEvent->getTypeString() == 'modify') &&
                    !$this->fileProcessor->isSubFolder($filesystemEvent->getResource())
                ) {
                    $this->fileProcessor->consumeFile(
                        new StringLiteral((string) $filesystemEvent->getResource())
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
     * @return void
     */
    protected function checkFolder()
    {
        $finder = new Finder();
        $finder->files()->in($this->fileProcessor->getPath());

        foreach ($finder as $file) {
            $fileLiteral = new StringLiteral($file->getPathname());
            if (is_file($fileLiteral->toNative())) {
                $this->fileProcessor->consumeFile($fileLiteral);
            }
        }
    }
}
