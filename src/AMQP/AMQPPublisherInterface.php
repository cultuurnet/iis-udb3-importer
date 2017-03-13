<?php

namespace CultuurNet\UDB3\IISImporter\AMQP;

use ValueObjects\Identity\UUID;
use ValueObjects\StringLiteral\StringLiteral;
use ValueObjects\Web\Url;

interface AMQPPublisherInterface
{
    /**
     * @param UUID $cdbid
     * @param \DateTime $datetime
     * @param StringLiteral $author
     * @param Url $url
     * @param bool $isUpdate
     * @return void
     */
    public function publish(UUID $cdbid, \DateTime $datetime, StringLiteral $author, Url $url, $isUpdate);
}
