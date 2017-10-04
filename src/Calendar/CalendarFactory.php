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
     * @inheritdoc
     */
    public function generateCalendar($calendarNode)
    {
        // TODO: Implement generateCalendar() method.
    }

    /**
     * @inheritdoc
     */
    public function formatCalendar($calendar)
    {
        // TODO: Implement formatCalendar() method.
    }

    /**
     * @inheritdoc
     */
    public function format($calendarNode)
    {
        $calendarSummary = $this->calendarFormatter->format($this->calendar, $this->formatType);
        return new StringLiteral($calendarSummary);
    }
}
