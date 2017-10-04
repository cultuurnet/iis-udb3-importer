<?php

namespace CultuurNet\UDB3\IISImporter\Calendar;

use CultureFeed_Cdb_Data_Calendar;
use CultuurNet\CalendarSummary\CalendarFormatterInterface;
use ValueObjects\StringLiteral\StringLiteral;

class CalendarFactory implements CalendarFactoryInterface
{
    /**
     * @var CalendarFormatterInterface
     */
    protected $calendarFormatter;

    /**
     * @var CultureFeed_Cdb_Data_Calendar
     */
    protected $calendar;

    /**
     * @var string
     */
    protected $formatType;

    /**
     * @param StringLiteral $calendarNode
     * @return CultureFeed_Cdb_Data_Calendar
     */
    public function generateCalendar(StringLiteral $calendarNode)
    {
        // TODO: Implement generateCalendar() method.
    }

    public function format()
    {
        $calendarSummary = $this->calendarFormatter->format($this->calendar, $this->formatType);
        return new StringLiteral($calendarSummary);
    }
}
