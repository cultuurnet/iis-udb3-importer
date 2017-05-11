<?php

namespace CultuurNet\UDB3\IISImporter\Media;

use ValueObjects\Web\Url;

interface MediaManagerInterface
{
    /**
     * @param Url $url
     * @return Url
     */
    public function generateMediaLink(Url $url);
}
