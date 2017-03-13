<?php

namespace CultuurNet\UDB3\IISImporter\AMQP;

use CultuurNet\UDB3\IISImporter\URL\UrlFactoryInterface;
use ValueObjects\DateTime\DateTime;
use ValueObjects\Identity\UUID;
use ValueObjects\StringLiteral\StringLiteral;

class BodyFactory implements AMQPBodyFactoryInterface
{
    /**
     * @inheritdoc
     */
    public function createBody(UUID $cdbid, Datetime $dateTime, StringLiteral $author, UrlFactoryInterface $urlFactory)
    {
        // TODO: Implement createBody() method.
    }
}
