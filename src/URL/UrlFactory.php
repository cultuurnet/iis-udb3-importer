<?php

namespace CultuurNet\UDB3\IISImporter\URL;

use ValueObjects\Identity\UUID;
use ValueObjects\StringLiteral\StringLiteral;
use ValueObjects\Web\Url;

class UrlFactory implements UrlFactoryInterface
{
    /**
     * @inheritdoc
     */
    public function generateUrl(StringLiteral $baseUrl, UUID $cdbid)
    {
        new Url($baseUrl->toNative() . '/' . $cdbid->toNative());
    }
}
