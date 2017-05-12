<?php

namespace CultuurNet\UDB3\IISImporter\Url;

use ValueObjects\Identity\UUID;
use ValueObjects\StringLiteral\StringLiteral;
use ValueObjects\Web\Url;

interface UrlFactoryInterface
{
    /**
     * @param UUID $cdbid
     * @return Url
     */
    public function generateEventUrl(UUID $cdbid);

    /**
     * @param StringLiteral $path
     * @return Url
     */
    public function generateMediaUrl(StringLiteral $path);
}
