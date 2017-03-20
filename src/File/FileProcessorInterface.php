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
     * @return string
     */
    public function getPath();

    /**
     * @param ResourceInterface $resource
     * @return bool
     */
    public function isSubFolder(ResourceInterface $resource);
}
