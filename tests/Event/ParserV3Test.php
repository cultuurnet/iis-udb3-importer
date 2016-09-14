<?php

/**
 * Created by PhpStorm.
 * User: jonas
 * Date: 14.09.16
 * Time: 10:02
 */

namespace CultuurNet\UDB3\IISImporter\Event;

use phpDocumentor\Reflection\Types\This;

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
        $invalidXmlString = 'Bla';
        $this->assertFalse($this->parser->validate($invalidXmlString));
    }

    public function testNotCdbxml()
    {

    }

    public function testDeprecatedCdbxml()
    {
        $deprecated = file_get_contents(__DIR__ . '/../deprecated.xml');
        $this->assertFalse($this->parser->validate($deprecated));

    }

    public function testCorrectCdbxml()
    {

    }
}
