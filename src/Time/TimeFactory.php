<?php

namespace CultuurNet\UDB3\IISImporter\Time;

class TimeFactory implements TimeFactoryInterface
{
    /**
     * @inheritdoc
     */
    public function changeDateToLocalTime($date)
    {
        if ($this->isAlreadyLocalTime($date)) {
            return $date;
        } else {
            // IIS SENDS localdatetime as UTC so this is not needed.
            //
            // $timeInfo = explode('+', $date);
            // $datetime = new \DateTime($timeInfo[0]);
            // $intervalSpec = 'PT' . substr($timeInfo[1], 0, 2) . 'H';
            // $interval = new \DateInterval($intervalSpec);
            // $datetime->add($interval);
            // return $datetime->format("Y-m-d\TH:i:s");
            return  substr($date, 0, strpos($date, '+'));
        }
    }

    /**
     * @inheritdoc
     */
    public function changeTimeStampToLocalTime($date)
    {
        if ($this->isAlreadyLocalTime($date)) {
            return $date;
        } else {
            // IIS SENDS localdatetime as UTC so this is not needed.
            //
            // $timeInfo = explode('+', $date);
            // $datetime = new \DateTime($timeInfo[0]);
            // $intervalSpec = 'PT' . substr($timeInfo[1], 0, 2) . 'H';
            // $interval = new \DateInterval($intervalSpec);
            // $datetime->add($interval);
            // return $datetime->format("H:i:s");
            return  substr($date, 0, strpos($date, '+'));
        }
    }

    /**
     * @inheritdoc
     */
    public function isAlreadyLocalTime($time)
    {
        if (strpos($time, '+') !== false) {
            return false;
        } else {
            return true;
        }
    }
}
