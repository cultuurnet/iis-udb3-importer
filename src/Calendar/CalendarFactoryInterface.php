<?php

namespace CultuurNet\UDB3\IISImporter\Calendar;

use CultureFeed_Cdb_Data_Calendar;
use ValueObjects\StringLiteral\StringLiteral;

interface CalendarFactoryInterface
{
    /**
     * @param StringLiteral $calendarNode
     * @return CultureFeed_Cdb_Data_Calendar
     */
    public function generateCalendar(StringLiteral $calendarNode);

    /**
     * @param StringLiteral $calendarNode
     * @return StringLiteral
     */
    public function format();
}
