<?php

namespace CultuurNet\UDB3\IISImporter\AMQP;

use CultuurNet\UDB3\IISImporter\URL\UrlFactoryInterface;
use ValueObjects\Identity\UUID;
use ValueObjects\StringLiteral\StringLiteral;
use ValueObjects\DateTime\DateTime;

interface AMQPMessageFactoryInterface
{
    /**
     * @param UUID $cdbid
     * @param DateTime $dateTime
     * @param StringLiteral $author
     * @param UrlFactoryInterface $urlFactory
     * @param bool $isUpdate
     * @return void
     */
    public function createMessage(UUID $cdbid, DateTime $dateTime, StringLiteral $author, UrlFactoryInterface $urlFactory, $isUpdate = false);
}
