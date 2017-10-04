<?php

namespace CultuurNet\UDB3\IISImporter\Calendar;

use CultureFeed_Cdb_Data_Calendar;
use ValueObjects\StringLiteral\StringLiteral;

interface CalendarFactoryInterface
{
    /**
     * @param \SimpleXMLElement $calendarNode
     * @return CultureFeed_Cdb_Data_Calendar
     */
    public function generateCalendar($calendarNode);

    /**
     * @param CultureFeed_Cdb_Data_Calendar $calendar
     * @return string
     */
    public function formatCalendar($calendar);

    /**
     * @param \SimpleXMLElement $calendarNode
     * @return StringLiteral
     */
    public function format($calendarNode);
}
