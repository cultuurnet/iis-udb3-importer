<?php

namespace CultuurNet\UDB3\IISImporter\Exceptions;

class UnexpectedNamespaceException extends InvalidCdbXmlException
{
    /**
     * @param string $namespace
     * @param string[] $validNamespaces
     */
    public function __construct($namespace, $validNamespaces)
    {
        parent::__construct(
            'Unexpected namespace "' . $namespace . '", expected one of: ' . implode(', ', $validNamespaces)
        );
    }
}
