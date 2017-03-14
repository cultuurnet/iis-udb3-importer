<?php

namespace CultuurNet\UDB3\IISImporter\AMQP;

use PhpAmqpLib\Message\AMQPMessage;
use ValueObjects\Identity\UUID;
use ValueObjects\StringLiteral\StringLiteral;
use ValueObjects\Web\Url;

interface AMQPMessageFactoryInterface
{
    /**
     * @param UUID $cdbid
     * @param \DateTime $dateTime
     * @param StringLiteral $author
     * @param Url $url
     * @param bool $isUpdate
     * @return AMQPMessage
     */
    public function createMessage(
        UUID $cdbid,
        \DateTime $dateTime,
        StringLiteral $author,
        Url $url,
        $isUpdate
    );
}
