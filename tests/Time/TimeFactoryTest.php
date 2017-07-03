<?php

namespace CultuurNet\UDB3\IISImporter\Time;

class TimeFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TimeFactory
     */
    private $timeFactory;

    protected function setUp()
    {
        $this->timeFactory = new TimeFactory();
    }

    /**
     * @test
     */
    public function it_checks_an_utc_date()
    {
        $utcDate = '2017-03-30T09:56:03+01:00';
        $this->assertFalse($this->timeFactory->isAlreadyLocalTime($utcDate));
    }

    /**
     * @test
     */
    public function it_checks_an_local_date()
    {
        $localDate = '2017-03-23T00:00:00';
        $this->assertTrue($this->timeFactory->isAlreadyLocalTime($localDate));
    }

    /**
     * @test
     */
    public function it_checks_an_utc_timestamp()
    {
        $utcTime = '00:00:00+02:00';
        $this->assertFalse($this->timeFactory->isAlreadyLocalTime($utcTime));
    }

    /**
     * @test
     */
    public function it_checks_an_local_timestamp()
    {
        $localTime = '19:15:00';
        $this->assertTrue($this->timeFactory->isAlreadyLocalTime($localTime));
    }

    /**
     * @test
     */
    public function it_generates_a_local_datetime_from_utc()
    {
        $utcDate = '2017-03-30T09:56:03+01:00';
        $localDate = '2017-03-30T09:56:03';

        $this->assertEquals($localDate, $this->timeFactory->changeDateToLocalTime($utcDate));
    }

    /**
     * @test
     */
    public function it_keeps_a_local_datetime()
    {
        $utcDate = '2017-03-30T10:56:03';
        $localDate = '2017-03-30T10:56:03';

        $this->assertEquals($localDate, $this->timeFactory->changeDateToLocalTime($utcDate));
    }

    /**
     * @test
     */
    public function it_generates_a_local_timestamp_from_utc()
    {
        $utcTime = '00:00:00+02:00';
        $localTime = '00:00:00';

        $this->assertEquals($localTime, $this->timeFactory->changeTimeStampToLocalTime($utcTime));
    }

    /**
     * @test
     */
    public function it_keeps_a_local_timestamp()
    {
        $utcTime = '02:00:00';
        $localTime = '02:00:00';

        $this->assertEquals($localTime, $this->timeFactory->changeTimeStampToLocalTime($utcTime));
    }
}
