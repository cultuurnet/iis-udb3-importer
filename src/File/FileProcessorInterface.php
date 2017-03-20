<?php

namespace CultuurNet\UDB3\IISImporter\File;

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
     * @param StringLiteral $xmlString
     * @param StringLiteral $fileName
     * @return void
     */
    public function consumeFile(StringLiteral $xmlString, StringLiteral $fileName);
}
