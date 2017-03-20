<?php

namespace CultuurNet\UDB3\IISImporter\File;

use ValueObjects\StringLiteral\StringLiteral;

interface FileProcessorInterface
{
    /**
     * @param StringLiteral $fileName
     * @return void
     */
    public function consumeFile(StringLiteral $fileName);

    /**
     * @return string
     */
    public function getProcessFolder();
}
