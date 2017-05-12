<?php

namespace CultuurNet\UDB3\IISImporter\Url;

use ValueObjects\Identity\UUID;
use ValueObjects\StringLiteral\StringLiteral;
use ValueObjects\Web\Url;

class UrlFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var StringLiteral
     */
    private $baseUrl;

    /**
     * @var UrlFactory
     */
    private $urlFactory;

    protected function setUp()
    {
        $this->baseUrl = new StringLiteral('http://iis-udb.dev/events');

        $this->urlFactory = new UrlFactory($this->baseUrl);
    }

    /**
     * @test
     */
    public function it_generates_an_url()
    {
        $cdbid = new UUID();
        $url = $this->urlFactory->generateEventUrl($cdbid);

        $expectedUrl = Url::fromNative(
            $this->baseUrl . '/' . $cdbid->toNative()
        );

        $this->assertEquals($expectedUrl, $url);
    }
}
