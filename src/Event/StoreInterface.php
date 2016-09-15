<?php
/**
 * Created by PhpStorm.
 * User: jonas
 * Date: 15.09.16
 * Time: 11:09
 */

namespace CultuurNet\UDB3\IISImporter\Event;

interface StoreInterface
{
    public function save($eventXml);
}
