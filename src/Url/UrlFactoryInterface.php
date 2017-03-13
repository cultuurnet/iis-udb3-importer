<?php

namespace CultuurNet\UDB3\IISImporter\Url;

use ValueObjects\Identity\UUID;
use ValueObjects\StringLiteral\StringLiteral;
use ValueObjects\Web\Url;

interface UrlFactoryInterface
{
    /**
     * @param UUID $cdbid
     * @return Url;
     */
    public function generateUrl(UUID $cdbid);
}
