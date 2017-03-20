<?php

namespace CultuurNet\UDB3\IISImporter\Parser;

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

    /**
     * @test
     */
    public function it_can_split_a_file()
    {
        $largeFile = file_get_contents(__DIR__ . '/../FileToSplit.xml');
        $events = $this->parser->split($largeFile);

        $this->assertCount(5, $events);
        $this->assertArrayHasKey('EGD201711542', $events);
        $this->assertArrayHasKey('EGD201711555', $events);
        $this->assertArrayHasKey('EGD201711557', $events);
        $this->assertArrayHasKey('EGD201711574', $events);
        $this->assertArrayHasKey('EGD201711576', $events);
    }
}
