<?php

namespace CultuurNet\UDB3\IISImporter\File;

use Symfony\Component\Finder\Finder;

class FileManager implements FileManagerInterface
{
    const PROCESS_FOLDER = 'process';
    const SUCCESS_FOLDER = 'success';
    const ERROR_FOLDER = 'error';
    const INVALID_FOLDER = 'invalid';

    /**
     * @var \SplFileInfo
     */
    private $rootFolder;

    /**
     * FileManager constructor.
     * @param \SplFileInfo $rootFolder
     */
    public function __construct(\SplFileInfo $rootFolder)
    {
        $this->rootFolder = $rootFolder;
    }

    /**
     * @inheritdoc
     */
    public function getProcessFolder()
    {
        return $this->createSplFileInfo(self::PROCESS_FOLDER);
    }

    /**
     * @inheritdoc
     */
    public function getErrorFolder()
    {
        return $this->createSplFileInfo(self::ERROR_FOLDER);
    }

    /**
     * @inheritdoc
     */
    public function getInvalidFolder()
    {
        return $this->createSplFileInfo(self::INVALID_FOLDER);
    }

    /**
     * @inheritdoc
     */
    public function getSuccessFolder()
    {
        return $this->createSplFileInfo(self::SUCCESS_FOLDER);
    }

    /**
     * @inheritdoc
     */
    public function getProcessFolderFiles()
    {
        $files = [];

        $finder = new Finder();
        $finder->files()->in($this->getProcessFolder()->getPathname());

        foreach ($finder as $file) {
            if (is_file($file)) {
                $files[] = new \SplFileInfo($file);
            }
        }

        return $files;
    }

    /**
     * @param string $folderName
     * @return \SplFileInfo
     */
    private function createSplFileInfo($folderName)
    {
        return new \SplFileInfo($this->rootFolder . '/' . $folderName);
    }
}
