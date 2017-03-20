<?php

namespace CultuurNet\UDB3\IISImporter\Exceptions;

use CultuurNet\UDB3\IISImporter\Parser\ParserV3;

class UnexpectedNamespaceExceptionTest extends \PHPUnit_Framework_TestCase
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
        $this->expectException(UnexpectedNamespaceException::class);
        $this->parser->loadDOM($faultyXml);
    }
}
