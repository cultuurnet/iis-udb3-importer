<?php
/**
 * Created by PhpStorm.
 * User: jonas
 * Date: 14/09/16
 * Time: 10:44
 */

namespace CultuurNet\UDB3\IISImporter\Exceptions;

class UnexpectedRootElementException extends InvalidCdbXmlException
{
    public function __construct($localName, $expectedLocalName)
    {
        parent::__construct(
            'Unexpected root element "' . $localName . '", expected ' . $expectedLocalName
        );
    }
}
