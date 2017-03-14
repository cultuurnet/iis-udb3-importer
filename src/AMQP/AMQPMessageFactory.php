<?php

namespace CultuurNet\UDB3\IISImporter\AMQP;

use PhpAmqpLib\Message\AMQPMessage;
use ValueObjects\Identity\UUID;
use ValueObjects\StringLiteral\StringLiteral;
use ValueObjects\Web\Url;

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
    public function __construct(
        AMQPBodyFactoryInterface $amqpBody,
        AMQPPropertiesFactoryInterface $amqpProperties
    ) {
        $this->amqpBody = $amqpBody;
        $this->amqpProperties = $amqpProperties;
    }

    /**
     * @inheritdoc
     */
    public function createMessage(
        UUID $cdbid,
        \DateTime $dateTime,
        StringLiteral $author,
        Url $url,
        $isUpdate
    ) {
        return new AMQPMessage(
            $this->amqpBody->createBody($cdbid, $dateTime, $author, $url),
            $this->amqpProperties->createProperties($isUpdate)
        );
    }
}
