<?php

namespace CultuurNet\UDB3\IISImporter\AMQP;

use CultuurNet\UDB3\IISImporter\URL\UrlFactoryInterface;
use ValueObjects\Identity\UUID;
use ValueObjects\DateTime\DateTime;
use ValueObjects\StringLiteral\StringLiteral;

interface AMQPBodyFactoryInterface
{
    /**
     * @param  UUID $cdbid
     * @param DateTime $dateTime
     * @param StringLiteral $author
     * @param UrlFactoryInterface $urlFactory
     * @return
     */
    public function createBody(UUID $cdbid, Datetime $dateTime, StringLiteral $author, UrlFactoryInterface $urlFactory);
}
