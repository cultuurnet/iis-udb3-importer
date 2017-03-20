<?php

namespace CultuurNet\UDB3\IISImporter\Processor;

interface ProcessorInterface
{
    /**
     * @param \SplFileInfo $file
     * @return void
     */
    public function consumeFile(\SplFileInfo $file);
}
