<?php

namespace CultuurNet\UDB3\IISImporter\AMQP;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use ValueObjects\Identity\UUID;
use ValueObjects\StringLiteral\StringLiteral;
use ValueObjects\Web\Url;

class AMQPPublisherTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AMQPChannel|\PHPUnit_Framework_MockObject_MockObject
     */
    private $channel;

    /**
     * @var StringLiteral
     */
    private $exchange;

    /**
     * @var AMQPMessageFactoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageFactory;

    /**
     * @var AMQPPublisher
     */
    private $amqpPublisher;

    protected function setUp()
    {
        $this->channel = $this->createMock(AMQPChannel::class);

        $this->exchange = new StringLiteral('udb3.vagrant.x.entry');

        $this->messageFactory = $this->createMock(
            AMQPMessageFactoryInterface::class
        );

        $this->amqpPublisher = new AMQPPublisher(
            $this->channel,
            $this->exchange,
            $this->messageFactory
        );
    }

    /**
     * @test
     */
    public function it_publishes_a_message()
    {
        $uuid = new UUID();
        $dateTime = new \DateTime();
        $author = new StringLiteral('importsUDB3');
        $url = Url::fromNative('http://iis-udb.dev/events/' . $uuid->toNative());
        $isUpdate = false;

        $message = new AMQPMessage('body', []);

        $this->messageFactory->expects($this->once())
            ->method('createMessage')
            ->with(
                $uuid,
                $dateTime,
                $author,
                $url,
                $isUpdate
            )
            ->willReturn($message);

        $this->channel->expects($this->once())
            ->method('basic_publish')
            ->with(
                $message,
                $this->exchange
            );

        $this->amqpPublisher->publish(
            $uuid,
            $dateTime,
            $author,
            $url,
            $isUpdate
        );
    }
}
