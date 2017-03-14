<?php

namespace CultuurNet\UDB3\IISImporter\AMQP;

use PhpAmqpLib\Message\AMQPMessage;

class AMQPPropertiesFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AMQPPropertiesFactory
     */
    private $amqpPropertiesFactory;

    /**
     * @var array
     */
    private $expectedProperties;

    protected function setUp()
    {
        $this->amqpPropertiesFactory = new AMQPPropertiesFactory();

        $this->expectedProperties = [
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
        ];
    }

    /**
     * @test
     */
    public function it_creates_properties()
    {
        $properties = $this->amqpPropertiesFactory->createProperties(false);

        $this->expectedProperties['content_type'] = 'application/vnd.cultuurnet.udb2-events.event-created+json';

        $this->assertEquals($this->expectedProperties, $properties);
    }

    /**
     * @test
     */
    public function it_creates_properties_for_update()
    {
        $properties = $this->amqpPropertiesFactory->createProperties(true);

        $this->expectedProperties['content_type'] = 'application/vnd.cultuurnet.udb2-events.event-updated+json';

        $this->assertEquals($this->expectedProperties, $properties);
    }
}
