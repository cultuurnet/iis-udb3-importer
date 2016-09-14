<?php

/**
 * Created by PhpStorm.
 * User: jonas
 * Date: 14.09.16
 * Time: 10:02
 */

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
}
