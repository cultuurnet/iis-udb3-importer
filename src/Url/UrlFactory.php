<?php

namespace CultuurNet\UDB3\IISImporter\Url;

use ValueObjects\Identity\UUID;
use ValueObjects\StringLiteral\StringLiteral;
use ValueObjects\Web\Url;

class UrlFactory implements UrlFactoryInterface
{
    /**
     * @var StringLiteral
     */
    private $baseUrl;

    /**
     * UrlFactory constructor.
     * @param StringLiteral $baseUrl
     */
    public function __construct(StringLiteral $baseUrl)
    {
        $this->baseUrl = $baseUrl;
    }

    /**
     * @inheritdoc
     */
    public function generateUrl(UUID $cdbid)
    {
         return Url::fromNative($this->baseUrl->toNative() . '/' . $cdbid->toNative());
    }
}
