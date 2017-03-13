<?php

namespace CultuurNet\UDB3\IISImporter\AMQP;

use ValueObjects\Identity\UUID;
use PhpAmqpLib\Channel\AMQPChannel;
use ValueObjects\StringLiteral\StringLiteral;
use ValueObjects\Web\Url;

class AMQPPublisher implements AMQPPublisherInterface
{
    /**
     * @var AMQPChannel
     */
    private $channel;

    /**
     * @var StringLiteral
     */
    private $exchange;

    /**
     * @var AMQPMessageFactoryInterface
     */
    private $messageFactory;

    /**
     * @param AMQPChannel $channel
     * @param StringLiteral $exchange
     * @param AMQPMessageFactoryInterface $messageFactory
     */
    public function __construct(
        AMQPChannel $channel,
        StringLiteral $exchange,
        AMQPMessageFactoryInterface $messageFactory
    ) {
        $this->channel = $channel;
        $this->exchange = $exchange;
        $this->messageFactory = $messageFactory;
    }

    /**
     * @inheritdoc
     */
    public function publish(UUID $cdbid, \DateTime $dateTime, StringLiteral $author, Url $url, $isUpdate)
    {
        $this->channel->basic_publish(
            $this->messageFactory->createMessage($cdbid, $dateTime, $author, $url, $isUpdate),
            $this->exchange
        );
    }
}
