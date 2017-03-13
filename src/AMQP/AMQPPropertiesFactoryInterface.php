<?php

namespace CultuurNet\UDB3\IISImporter\AMQP;

interface AMQPPropertiesFactoryInterface
{
    /**
     * @param bool $isUpdate
     * @return array
     */
    public function createProperties($isUpdate);
}
