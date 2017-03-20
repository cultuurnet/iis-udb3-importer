<?php

namespace CultuurNet\UDB3\IISImporter\File;

interface FileManagerInterface
{
    /**
     * @return \SplFileInfo
     */
    public function getProcessFolder();

    /**
     * @return \SplFileInfo
     */
    public function getErrorFolder();

    /**
     * @return \SplFileInfo
     */
    public function getInvalidFolder();

    /**
     * @return \SplFileInfo
     */
    public function getSuccessFolder();

    /**
     * @return \SplFileInfo[]
     */
    public function getProcessFolderFiles();
}
