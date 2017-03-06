<?php

namespace CultuurNet\UDB3\IISImporter\Event;

use ValueObjects\Identity\UUID;

interface PublishInterface
{
    /**
     * @param UUID $cdbid
     */
    public function publish(UUID $cdbid);
}
