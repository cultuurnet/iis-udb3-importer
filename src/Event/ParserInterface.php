<?php

namespace CultuurNet\UDB3\IISImporter\Event;

use ValueObjects\Identity\UUID;
use ValueObjects\StringLiteral\StringLiteral;

interface ParserInterface
{
    /**
     * Returns the validity for a given cdbXml
     *
     * @param string $xmlString
     * @return boolean
     **/
    public function validate($xmlString);

    /**
     * @param string $xmlString
     * @return string[]
     */
    public function split($xmlString);
}
