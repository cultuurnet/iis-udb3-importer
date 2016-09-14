<?php
/**
 * Created by PhpStorm.
 * User: jonas
 * Date: 14/09/16
 * Time: 10:45
 */

namespace CultuurNet\UDB3\IISImporter\Exceptions;

class SchemaValidationException extends InvalidCdbXmlException
{
    public function __construct($namespace)
    {
        parent::__construct(
            'The XML document does not validate with ' . $namespace
        );
    }
}
