<?php

namespace CultuurNet\UDB3\IISImporter\Event;

use CultuurNet\BroadwayAMQP\AMQPPublisher;
use \CultuurNet\UDB3\IISImporter\Exceptions;
use ValueObjects\StringLiteral\StringLiteral;

class PublishAMQP implements PublishInterface
{
    /**
     * @inheritdoc
     */
    public function publish(StringLiteral $cdbid)
    {
        // TODO: Implement publish() method.
    }
}
