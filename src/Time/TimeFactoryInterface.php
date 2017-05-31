<?php

namespace CultuurNet\UDB3\IISImporter\Time;

use ValueObjects\Identity\UUID;
use ValueObjects\StringLiteral\StringLiteral;
use ValueObjects\Web\Url;

interface TimeFactoryInterface
{
    /**
     * @param string $date
     * @return string
     */
    public function changeDateToLocalTime($date);

    /**
     * @param string $date
     * @return string
     */
    public function changeTimeStampToLocalTime($date);

    /**
     * @param string $time
     * @return bool
     */
    public function isAlreadyLocalTime($time);
}
