<?php

namespace CultuurNet\UDB3\IISImporter\AMQP;

use CultuurNet\UDB3\IISImporter\URL\UrlFactoryInterface;
use ValueObjects\DateTime\DateTime;
use ValueObjects\Identity\UUID;
use ValueObjects\StringLiteral\StringLiteral;

class AMQPMessageFactory implements AMQPMessageFactoryInterface
{
    /**
     * AMQPBodyFactoryInterface
     */
    private $amqpBody;

    /**
     * AMQPPropertiesFactoryInterface
     */
    private $amqpProperties;

    /**
     * AMQPMessageFactory constructor.
     * @param AMQPBodyFactoryInterface $amqpBody
     * @param AMQPPropertiesFactoryInterface $amqpProperties
     */
    public function __construct(AMQPBodyFactoryInterface $amqpBody, AMQPPropertiesFactoryInterface $amqpProperties)
    {
        $this->amqpBody = $amqpBody;
        $this->amqpProperties = $amqpProperties;
    }

    /**
     * @inheritdoc
     */
    public function createMessage(UUID $cdbid, DateTime $dateTime, StringLiteral $author, UrlFactoryInterface $urlFactory, $isUpdate = false)
    {
        $this->amqpBody->createBody();
        $this->amqpProperties->createProperties($isUpdate);
    }
}
