<?php

namespace CultuurNet\UDB3\IISImporter\AMQP;

use ValueObjects\Identity\UUID;
use ValueObjects\StringLiteral\StringLiteral;
use ValueObjects\Web\Url;

interface AMQPBodyFactoryInterface
{
    /**
     * @param UUID $cdbid
     * @param \DateTime $dateTime
     * @param StringLiteral $author
     * @param Url $url
     * @return string
     */
    public function createBody(
        UUID $cdbid,
        \Datetime $dateTime,
        StringLiteral $author,
        Url $url
    );
}
