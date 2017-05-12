<?php

namespace CultuurNet\UDB3\IISImporter\Download;

use ValueObjects\Web\Url;

interface DownloaderInterface
{
    /**
     * @param Url $url
     * @return resource|false The path resource or false on failure
     */
    public function fetchStreamFromFTP(Url $url);
}
