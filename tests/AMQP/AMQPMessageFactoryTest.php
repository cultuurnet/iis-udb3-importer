<?php

namespace CultuurNet\UDB3\IISImporter\AMQP;

use PhpAmqpLib\Message\AMQPMessage;
use ValueObjects\Identity\UUID;
use ValueObjects\StringLiteral\StringLiteral;
use ValueObjects\Web\Url;

class AMQPMessageFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AMQPBodyFactoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $amqpBodyFactory;

    /**
     * @var AMQPPropertiesFactoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $amqpPropertiesFactory;

    /**
     * @var AMQPMessageFactory
     */
    private $amqpMessageFactory;

    protected function setUp()
    {
        $this->amqpBodyFactory = $this->createMock(
            AMQPBodyFactoryInterface::class
        );

        $this->amqpPropertiesFactory = $this->createMock(
            AMQPPropertiesFactory::class
        );

        $this->amqpMessageFactory = new AMQPMessageFactory(
            $this->amqpBodyFactory,
            $this->amqpPropertiesFactory
        );
    }

    /**
     * @test
     */
    public function is_creates_a_message()
    {
        $uuid = new UUID();
        $dateTime = new \DateTime();
        $author = new StringLiteral('importsUDB3');
        $url = Url::fromNative('http://iis-udb.dev/events/' . $uuid->toNative());
        $isUpdate = false;

        $body = [
            'eventId' => $uuid->toNative(),
            'time' => $dateTime->format(\DateTime::ATOM),
            'author' => $author->toNative(),
            'url' => (string) $url,
        ];

        $properties = [
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
            'content_type' => 'application/vnd.cultuurnet.udb2-events.event-created+json',
        ];

        $this->amqpBodyFactory->expects($this->once())
            ->method('createBody')
            ->with(
                $uuid,
                $dateTime,
                $author,
                $url
            )
            ->willReturn(
                json_encode($body)
            );

        $this->amqpPropertiesFactory->expects($this->once())
            ->method('createProperties')
            ->with($isUpdate)
            ->willReturn($properties);

        $expectedMessage = new AMQPMessage(
            json_encode($body),
            $properties
        );

        $message = $this->amqpMessageFactory->createMessage(
            $uuid,
            $dateTime,
            $author,
            $url,
            $isUpdate
        );

        $this->assertEquals($expectedMessage, $message);
    }
}
