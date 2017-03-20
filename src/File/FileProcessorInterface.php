<?php

namespace CultuurNet\UDB3\IISImporter\File;

use Lurker\Resource\ResourceInterface;
use ValueObjects\StringLiteral\StringLiteral;

interface FileProcessorInterface
{
    /**
     * @param StringLiteral $fileName
     * @return void
     */
    public function consumeFile(StringLiteral $fileName);

    /**
     * @return ResourceInterface
     */
    public function getResource();

    /**
     * @param ResourceInterface $resource
     * @return bool
     */
    public function isSubFolder(ResourceInterface $resource);

    /**
     * Move file to a folder
     *
     * @param string $file to file to move
     * @param string $folder the destination folder
     */
    public function moveFile($file, $folder);

}
