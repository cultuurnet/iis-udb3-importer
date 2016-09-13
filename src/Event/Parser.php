<?php
/**
 * Created by PhpStorm.
 * User: jonas
 * Date: 13.09.16
 * Time: 15:48
 */

namespace CultuurNet\UDB3\IISImporter\Event;

class Parser implements ParserInterface
{
    /**
     * @param string $xml_string
     * @return boolean
     */
    public function validate($xmlString)
    {
        return true;
    }
}
