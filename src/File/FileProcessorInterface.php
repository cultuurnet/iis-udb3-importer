<?php

namespace CultuurNet\UDB3\IISImporter\File;

use Lurker\Resource\ResourceInterface;
use ValueObjects\StringLiteral\StringLiteral;

interface FileProcessorInterface
{
    /**
     * Move file to a folder
     *
     * @param string $file to file to move
     * @param string $folder the destination folder
     */
    public function moveFile($file, $folder);

    /**
     * @param StringLiteral $fileName
     * @return void
     */
    public function consumeFile(StringLiteral $fileName);

    /**
     * @return ResourceInterface
     */
    public function getResource();
}
