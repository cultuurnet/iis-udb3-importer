<?php

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
