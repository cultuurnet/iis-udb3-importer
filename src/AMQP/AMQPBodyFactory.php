<?php

namespace CultuurNet\UDB3\IISImporter\AMQP;

use ValueObjects\Identity\UUID;
use ValueObjects\StringLiteral\StringLiteral;
use ValueObjects\Web\Url;

class AMQPBodyFactory implements AMQPBodyFactoryInterface
{
    /**
     * @inheritdoc
     */
    public function createBody(
        UUID $cdbid,
        \DateTime $dateTime,
        StringLiteral $author,
        Url $url
    ) {
        $bodyArray = [
            'eventId' => $cdbid->toNative(),
            'time' => $dateTime->format(\DateTime::ATOM),
            'author' => $author->toNative(),
            'url' => (string) $url,
        ];

        return json_encode($bodyArray);
    }
}
