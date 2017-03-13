<?php

namespace CultuurNet\UDB3\IISImporter\URL;

use ValueObjects\Identity\UUID;
use ValueObjects\StringLiteral\StringLiteral;
use ValueObjects\Web\Url;

interface UrlFactoryInterface
{
    /**
     * @param StringLiteral $baseUrl
     * @param UUID $cdbid
     * @return Url;
     */
    public function generateUrl(StringLiteral $baseUrl, UUID $cdbid);
}
