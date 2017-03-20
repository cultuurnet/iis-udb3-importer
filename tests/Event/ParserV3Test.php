<?php

namespace CultuurNet\UDB3\IISImporter\Event;

class ParserV3Test extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ParserV3
     */
    protected $parser;

    public function setUp()
    {
        $this->parser = new ParserV3();
    }

    public function testInvalidXml()
    {
        $invalidXmlString = file_get_contents(__DIR__ . '/../NotReallyXml.xml');
        $this->assertFalse($this->parser->validate($invalidXmlString));
    }

    public function testNotCdbxml()
    {
        $genericXml = file_get_contents(__DIR__ . '/../NotACdbxml.xml');
        $this->assertFalse($this->parser->validate($genericXml));
    }

    public function testDeprecatedCdbxml()
    {
        $deprecated = file_get_contents(__DIR__ . '/../deprecated.xml');
        $this->assertFalse($this->parser->validate($deprecated));
    }

    public function testCorrectCdbxml()
    {
        $valid = file_get_contents(__DIR__ . '/../CorrectVersion3.xml');
        $this->assertTrue($this->parser->validate($valid));
    }

    public function testEmptyFile()
    {
        $valid = file_get_contents(__DIR__ . '/../Empty.xml');
        $this->assertFalse($this->parser->validate($valid));
    }

    public function testBinaryFile()
    {
        $valid = file_get_contents(__DIR__ . '/../Binary.xml');
        $this->assertFalse($this->parser->validate($valid));
    }
}
