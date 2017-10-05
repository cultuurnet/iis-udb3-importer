<?php

namespace CultuurNet\UDB3\IISImporter\Calendar;

use CultureFeed_Cdb_Data_Calendar;
use CultureFeed_Cdb_Data_Calendar_OpeningTime;
use CultureFeed_Cdb_Data_Calendar_Permanent;
use CultureFeed_Cdb_Data_Calendar_SchemeDay;
use CultureFeed_Cdb_Data_Calendar_Timestamp;
use CultureFeed_Cdb_Data_Calendar_TimestampList;
use CultureFeed_Cdb_Data_Calendar_Weekscheme;
use CultuurNet\CalendarSummary\CalendarFormatterInterface;
use CultuurNet\CalendarSummary\Period\LargePeriodPlainTextFormatter;
use CultuurNet\CalendarSummary\Permanent\LargePermanentPlainTextFormatter;
use CultuurNet\CalendarSummary\Timestamps\LargeTimestampsPlainTextFormatter;
use CultuurNet\UDB3\IISImporter\Exceptions\CalendarException;
use ValueObjects\StringLiteral\StringLiteral;

class CalendarFactory implements CalendarFactoryInterface
{
    const LONG_FORMAT = 'lg';

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
        if (isset($calendarNode->timestamps)) {
            $this->calendarFormatter = new LargeTimestampsPlainTextFormatter();

            $this->calendar = new CultureFeed_Cdb_Data_Calendar_TimestampList();
            $this->calendarFormatter = new LargeTimestampsPlainTextFormatter();
            foreach ($calendarNode->timestamps[0]->timestamp as $xmlTimeStamp) {
                $date = isset($xmlTimeStamp->date)?(string) $xmlTimeStamp->date:null;
                $timeStart = isset($xmlTimeStamp->timestart)?(string) $xmlTimeStamp->timestart:null;
                $timeEnd = isset($xmlTimeStamp->timeend)?(string) $xmlTimeStamp->timeend:null;

                $timestamp = new CultureFeed_Cdb_Data_Calendar_Timestamp($date, $timeStart, $timeEnd);
                $this->calendar->add($timestamp);
            }
        } elseif (isset($calendarNode->permanentopeningtimes)) {
            $this->calendarFormatter = new LargePermanentPlainTextFormatter();

            $this->calendar = new CultureFeed_Cdb_Data_Calendar_Permanent();
            if (isset($calendarNode->permanentopeningtimes[0]->permanent[0]->weekscheme)) {
                $weekNode = $calendarNode->permanentopeningtimes[0]->permanent[0]->weekscheme;
                $weekScheme = new CultureFeed_Cdb_Data_Calendar_Weekscheme();

                if (isset($weekNode->monday)) {
                    $this->generateDay($weekNode->monday, $weekScheme, CultureFeed_Cdb_Data_Calendar_SchemeDay::MONDAY);
                }
                if (isset($weekNode->tuesday)) {
                    $this->generateDay($weekNode->tuesday, $weekScheme, CultureFeed_Cdb_Data_Calendar_SchemeDay::TUESDAY);
                }
                if (isset($weekNode->wednesday)) {
                    $this->generateDay($weekNode->wednesday, $weekScheme, CultureFeed_Cdb_Data_Calendar_SchemeDay::WEDNESDAY);
                }
                if (isset($weekNode->thursday)) {
                    $this->generateDay($weekNode->thursday, $weekScheme, CultureFeed_Cdb_Data_Calendar_SchemeDay::THURSDAY);
                }
                if (isset($weekNode->friday)) {
                    $this->generateDay($weekNode->friday, $weekScheme, CultureFeed_Cdb_Data_Calendar_SchemeDay::FRIDAY);
                }
                if (isset($weekNode->saturday)) {
                    $this->generateDay($weekNode->saturday, $weekScheme, CultureFeed_Cdb_Data_Calendar_SchemeDay::SATURDAY);
                }
                if (isset($weekNode->sunday)) {
                    $this->generateDay($weekNode->sunday, $weekScheme, CultureFeed_Cdb_Data_Calendar_SchemeDay::SUNDAY);
                }
                $this->calendar->setWeekScheme($weekScheme);
            }
        } elseif (isset($calendarNode->period)) {
            $this->calendarFormatter = new LargePeriodPlainTextFormatter();

        } else {
            throw new CalendarException('This calendar node is not supported');
        }
        return $this->calendar;
    }

    /**
     * @inheritdoc
     */
    public function formatCalendar($calendar)
    {

    }

    /**
     * @inheritdoc
     */
    public function format($calendarNode)
    {
        $this->generateCalendar($calendarNode);
        return new StringLiteral($this->calendarFormatter->format($this->calendar, self::LONG_FORMAT));
    }

    /**
     * @param \SimpleXMLElement $dayNode
     * @param CultureFeed_Cdb_Data_Calendar_Weekscheme $weekScheme
     * @param $weekDay
     */
    private function generateDay(\SimpleXMLElement $dayNode, CultureFeed_Cdb_Data_Calendar_Weekscheme $weekScheme, $weekDay)
    {
        if ($dayNode['opentype'] == 'open') {
            $day = new CultureFeed_Cdb_Data_Calendar_SchemeDay($weekDay, CultureFeed_Cdb_Data_Calendar_SchemeDay::SCHEMEDAY_OPEN_TYPE_OPEN);
            if (isset($dayNode->openingtime)) {
                $openingTime = new CultureFeed_Cdb_Data_Calendar_OpeningTime(
                    $dayNode->openingtime['from'],
                    $dayNode->openingtime['to']
                );
                $day->addOpeningTime($openingTime);
            }
            $weekScheme->setDay($weekDay, $day);
        }
    }
}
