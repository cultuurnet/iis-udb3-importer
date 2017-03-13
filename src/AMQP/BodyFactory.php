<?php

namespace CultuurNet\UDB3\IISImporter\AMQP;

use ValueObjects\DateTime\DateTime;
use ValueObjects\Identity\UUID;
use ValueObjects\StringLiteral\StringLiteral;
use ValueObjects\Web\Url;

class BodyFactory implements AMQPBodyFactoryInterface
{
    /**
     * @inheritdoc
     */
    public function createBody(UUID $cdbid, Datetime $dateTime, StringLiteral $author, Url $url)
    {
        $bodyArray = [
            'eventid' => $cdbid->toNative(),
            'time' => $dateTime,
            'author' => $author,
            'url' => (string)$url,
        ];
        return json_encode($bodyArray);
    }
}
