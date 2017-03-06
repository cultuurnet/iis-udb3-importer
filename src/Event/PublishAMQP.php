<?php

namespace CultuurNet\UDB3\IISImporter\Event;

use ValueObjects\Identity\UUID;

class PublishAMQP implements PublishInterface
{
    /**
     * @inheritdoc
     */
    public function publish(UUID $cdbid)
    {
        // TODO: Implement publish() method.
    }
}
