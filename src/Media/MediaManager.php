<?php

namespace CultuurNet\UDB3\IISImporter\Media;

use ValueObjects\Web\Url;

class MediaManager implements MediaManagerInterface
{

    /**
     * @inheritdoc
     */
    public function generateMediaLink(Url $url)
    {
        //TODO: temporary development return
        return $url;
    }
}
