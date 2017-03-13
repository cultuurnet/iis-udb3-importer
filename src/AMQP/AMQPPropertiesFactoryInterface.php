<?php

namespace CultuurNet\UDB3\IISImporter\AMQP;

interface AMQPPropertiesFactoryInterface
{
    /**
     * @bool $isUpdate
     * @return array
     */
    public function createProperties($isUpdate);
}
