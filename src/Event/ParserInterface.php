<?php
/**
 * Created by PhpStorm.
 * User: jonas
 * Date: 13.09.16
 * Time: 14:20
 */

namespace CultuurNet\UDB3\IISImporter;

interface ParserInterface
{
    /**
     * Returns the validity for a given cdbXml
     *
     * @param string $xmlString
     *
     * @return boolean
     **/
    public function validate($xmlString);
}
