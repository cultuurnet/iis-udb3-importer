<?php

namespace CultuurNet\UDB3\IISImporter\Event;

use CultuurNet\UDB3\IISImporter\AMQP\AMQPMessageFactoryInterface;
use CultuurNet\UDB3\IISImporter\Url\UrlFactoryInterface;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use ValueObjects\DateTime\Date;
use ValueObjects\DateTime\DateTime;
use ValueObjects\Identity\UUID;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Channel\AMQPChannel;
use ValueObjects\StringLiteral\StringLiteral;

class PublishAMQP implements PublishInterface
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
    public function publish(UUID $cdbid, \DateTime $dateTime, StringLiteral $author, Url $url)
    {
        $this->channel->basic_publish(
            $this->messageFactory->createMessage($cdbid, $dateTime, $author, $url),
            $this->exchange
        );
    }
}
