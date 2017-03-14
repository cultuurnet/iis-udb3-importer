<?php

namespace CultuurNet\UDB3\IISImporter\AMQP;

use ValueObjects\Identity\UUID;
use ValueObjects\StringLiteral\StringLiteral;
use ValueObjects\Web\Url;

class AMQPBodyFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AMQPBodyFactory
     */
    private $amqpBodyFactory;

    protected function setUp()
    {
        $this->amqpBodyFactory = new AMQPBodyFactory();
    }

    /**
     * @test
     */
    public function it_creates_a_body()
    {
        $uuid = new UUID();
        $dateTime = new \DateTime();
        $author = new StringLiteral('importsUDB3');
        $url = Url::fromNative('http://iis-udb.dev/events/' . $uuid->toNative());

        $body = $this->amqpBodyFactory->createBody(
            $uuid,
            $dateTime,
            $author,
            $url
        );

        $expectedBody = json_encode(
            [
                'eventId' => $uuid->toNative(),
                'time' => $dateTime->format(\DateTime::ATOM),
                'author' => $author->toNative(),
                'url' => (string) $url
            ]
        );

        $this->assertEquals($expectedBody, $body);
    }
}
