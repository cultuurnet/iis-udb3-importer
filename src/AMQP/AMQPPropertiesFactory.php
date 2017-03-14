<?php

namespace CultuurNet\UDB3\IISImporter\AMQP;

use PhpAmqpLib\Message\AMQPMessage;

class AMQPPropertiesFactory implements AMQPPropertiesFactoryInterface
{
    const CONTENT_TYPE_CREATE = 'application/vnd.cultuurnet.udb2-events.event-created+json';
    const CONTENT_TYPE_UPDATED = 'application/vnd.cultuurnet.udb2-events.event-updated+json';

    /**
     * @inheritdoc
     */
    public function createProperties($isUpdate)
    {
        $properties['delivery_mode'] = AMQPMessage::DELIVERY_MODE_PERSISTENT;

        if ($isUpdate) {
            $properties['content_type'] = AMQPPropertiesFactory::CONTENT_TYPE_UPDATED;
        } else {
            $properties['content_type'] = AMQPPropertiesFactory::CONTENT_TYPE_CREATE;
        }

        return $properties;
    }
}
