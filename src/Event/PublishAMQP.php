<?php

namespace CultuurNet\UDB3\IISImporter\Event;

use PhpAmqpLib\Connection\AMQPStreamConnection;
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
     * @inheritdoc
     */
    public function publish(UUID $cdbid)
    {
        $this->channel->basic_publish(
            $this->createAMQPMessage($cdbid),
            $this->exchange
        );
    }

    /**
     * @param UUID $cdbid
     * @return AMQPMessage
     */
    private function createAMQPMessage(UUID $cdbid)
    {
        //TODO check exact format;
        return new AMQPMessage($cdbid);
    }

    /**
     * @param AMQPChannel $channel
     * @param StringLiteral $exchange
     */
    public function __construct(
        AMQPChannel $channel,
        StringLiteral $exchange
    ) {
        $this->channel = $channel;
        $this->exchange = $exchange;
    }
}
