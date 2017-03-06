<?php

namespace CultuurNet\UDB3\IISImporter\Exceptions;

use CultuurNet\UDB3\IISImporter\Event\ParserV3;

class InvalidCdbXmlExceptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ParserV3
     */
    protected $parser;

    public function setUp()
    {
        $this->parser = new ParserV3();
    }

    /**
     * @test
     */
    public function itThrowsTheExpectedException()
    {
        $faultyXml = file_get_contents(__DIR__ . '/../NotACdbxml.xml');
        $this->setExpectedException(InvalidCdbXmlException::class);
        $this->parser->loadDOM($faultyXml);
    }
}
