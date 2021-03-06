<?php

namespace CultuurNet\UDB3\IISImporter\Processor;

use CultuurNet\CalendarSummary\CalendarFormatterInterface;
use CultuurNet\UDB3\IISImporter\AMQP\AMQPPublisherInterface;
use CultuurNet\UDB3\IISImporter\Calendar\CalendarFactoryInterface;
use CultuurNet\UDB3\IISImporter\CategorizationRules\CategorizationRulesInterface;
use CultuurNet\UDB3\IISImporter\File\FileManagerInterface;
use CultuurNet\UDB3\IISImporter\Identification\IdentificationFactoryInterface;
use CultuurNet\UDB3\IISImporter\Media\MediaManagerInterface;
use CultuurNet\UDB3\IISImporter\Parser\ParserInterface;
use CultuurNet\UDB3\IISImporter\Time\TimeFactoryInterface;
use CultuurNet\UDB3\IISImporter\Url\UrlFactoryInterface;
use CultuurNet\UDB3\IISStore\Stores\RepositoryInterface;
use Monolog\Logger;
use ValueObjects\Identity\UUID;
use ValueObjects\StringLiteral\StringLiteral;
use ValueObjects\Web\Url;

class ProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FileManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $fileManager;

    /**
     * @var ParserInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $parser;

    /**
     * @var RepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $store;

    /**
     * @var AMQPPublisherInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $publisher;

    /**
     * @var UrlFactoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlFactory;

    /**
     * @var StringLiteral
     */
    private $author;

    /**
     * @var MediaManagerInterface
     */
    private $mediaManager;

    /**
     * @var Processor
     */
    private $processor;

    /**
     * @var TimeFactoryInterface
     */
    private $timeFactory;

    /**
     * @var CategorizationRulesInterface
     */
    private $flandersRegionFactory;

    /**
     * @var CalendarFactoryInterface
     */
    private $calendarFactory;

    /**
     * @var Logger;
     */
    private $logger;

    /**
     * @var IdentificationFactoryInterface
     */
    private $identificationFactory;

    protected function setUp()
    {
        $this->fileManager = $this->createMock(FileManagerInterface::class);

        $this->parser = $this->createMock(ParserInterface::class);

        $this->store = $this->createMock(RepositoryInterface::class);

        $this->publisher = $this->createMock(AMQPPublisherInterface::class);

        $this->urlFactory = $this->createMock(UrlFactoryInterface::class);

        $this->author = new StringLiteral('importsUDB3');

        $this->mediaManager = $this->createMock(MediaManagerInterface::class);

        $this->timeFactory = $this->createMock(TimeFactoryInterface::class);

        $this->flandersRegionFactory = $this->createMock(CategorizationRulesInterface::class);

        $this->calendarFactory = $this->createMock(CalendarFactoryInterface::class);

        $this->logger = $this->createMock(Logger::class);

        $this->identificationFactory = $this->createMock(IdentificationFactoryInterface::class);

        $this->processor = new Processor(
            $this->fileManager,
            $this->parser,
            $this->store,
            $this->publisher,
            $this->urlFactory,
            $this->author,
            $this->mediaManager,
            $this->timeFactory,
            $this->flandersRegionFactory,
            $this->calendarFactory,
            $this->logger,
            $this->identificationFactory
        );
    }

    /**
     * @test
     */
    public function it_can_process_a_file()
    {
        $file = new \SplFileInfo(__DIR__ . '/../FileToSplit.xml');
        $xmlString = file_get_contents($file->getPathname());

        $this->mockValidate($xmlString, true);

        $eventList = [
            'EGD201711542' => file_get_contents(__DIR__ . '/../EGD201711542.xml'),
            'EGD201711555' => file_get_contents(__DIR__ . '/../EGD201711555.xml'),
        ];

        $this->parser->expects($this->once())
            ->method('split')
            ->with($xmlString)
            ->willReturn($eventList);

        $this->store->expects($this->exactly(2))
            ->method('getEventCdbid')
            ->withConsecutive(
                ['EGD201711542'],
                ['EGD201711555']
            )
            ->willReturn(
                null,
                new UUID()
            );

        $this->store->expects($this->once())
            ->method('saveRelation');
        $this->store->expects($this->once())
            ->method('saveEventXml');
        $this->store->expects($this->once())
            ->method('saveCreated');

        $this->store->expects($this->once())
            ->method('updateEventXml');
        $this->store->expects($this->once())
            ->method('saveUpdated');

        $this->publisher->expects($this->exactly(2))
            ->method('publish');
        $this->store->expects($this->exactly(2))
            ->method('savePublished');

        $this->urlFactory->expects($this->exactly(2))
            ->method('generateEventUrl')
            ->willReturn(Url::fromNative('http://www.test.be'));

        $this->fileManager->expects($this->once())
            ->method('getSuccessFolder')
            ->willReturn(new \SplFileInfo('/vagrant/import/success'));

        $this->mockMoveFileToFolder($file, '/vagrant/import/success');

        $this->processor->consumeFile($file);
    }

    /**
     * @test
     */
    public function it_moves_file_to_invalid_folder_for_invalid_xml()
    {
        $file = new \SplFileInfo(__DIR__ . '/../FileToSplit.xml');
        $xmlString = file_get_contents($file->getPathname());

        $this->mockValidate($xmlString, false);

        $this->fileManager->expects($this->once())
            ->method('getInvalidFolder')
            ->willReturn(new \SplFileInfo('/vagrant/import/invalid'));

        $this->mockMoveFileToFolder($file, '/vagrant/import/invalid');

        $this->processor->consumeFile($file);
    }

    /**
     * @test
     */
    public function it_moves_file_to_error_folder_when_error()
    {
        $file = new \SplFileInfo(__DIR__ . '/../FileToSplit.xml');
        $xmlString = file_get_contents($file->getPathname());

        $this->mockValidate($xmlString, true);

        $this->parser->expects($this->once())
            ->method('split')
            ->with($xmlString)
            ->willThrowException(new \Exception());

        $this->fileManager->expects($this->once())
            ->method('getErrorFolder')
            ->willReturn(new \SplFileInfo('/vagrant/import/error'));

        $this->mockMoveFileToFolder($file, '/vagrant/import/error');

        $this->processor->consumeFile($file);
    }

    /**
     * @param string $xmlString
     * @param bool $result
     */
    private function mockValidate($xmlString, $result)
    {
        $this->parser->expects($this->once())
            ->method('validate')
            ->with($xmlString)
            ->willReturn($result);
    }

    /**
     * @param \SplFileInfo $file
     * @param string $destinationFolder
     */
    private function mockMoveFileToFolder(\SplFileInfo $file, $destinationFolder)
    {
        $this->fileManager->expects($this->once())
            ->method('moveFileToFolder')
            ->with(
                $file,
                new \SplFileInfo($destinationFolder)
            );
    }
}
