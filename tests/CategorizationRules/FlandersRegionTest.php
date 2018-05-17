<?php

namespace CultuurNet\UDB3\IISImporter\CategorizationRules;

use CultuurNet\UDB3\IISImporter\Download\DownloaderInterface;
use CultuurNet\UDB3\IISImporter\Url\UrlFactoryInterface;
use League\Flysystem\Adapter\AbstractAdapter;
use ValueObjects\StringLiteral\StringLiteral;
use ValueObjects\Web\Url;

class FlandersRegionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Url
     */
    private $taxonomyUrl;

    /**
     * @var Url
     */
    private $nameSpaceUrl;

    /**
     * @var CategoryRules
     */
    private $flandersRegion;

    protected function setUp()
    {
        $this->taxonomyUrl = Url::fromNative('http://taxonomy.uitdatabank.be/api/domain/flandersregion/classification');
        $this->nameSpaceUrl = Url::fromNative('http://www.cultuurdatabank.com/XMLSchema/CdbXSD/3.2/FINAL');

        $this->flandersRegion = new CategoryRules($this->taxonomyUrl, $this->nameSpaceUrl);
    }

    /**
     * @test
     */
    public function test_non_flemish_address()
    {
        $value = new StringLiteral('4000 Luik');
        $this->assertNull($this->flandersRegion->getFlandersRegion($value));
    }

    /**
     * @test
     */
    public function test_flemish_address()
    {
        $value = new StringLiteral('9700 Oudenaarde');
        $expected = new Category(
            new StringLiteral('reg.1464'),
            new StringLiteral('flandersregion'),
            new StringLiteral('9700 Oudenaarde')
        );

        $this->assertEquals($expected, $this->flandersRegion->getFlandersRegion($value));
    }

    /**
     * @test
     */
    public function test_borough()
    {
        $value = new StringLiteral('2018 Antwerpen');
        $expected = new Category(
            new StringLiteral('reg.476'),
            new StringLiteral('flandersregion'),
            new StringLiteral('2018 Antwerpen 18 (Antwerpen)')
        );

        $this->assertEquals($expected, $this->flandersRegion->getFlandersRegion($value));

    }
}
