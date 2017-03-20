<?php

namespace CultuurNet\UDB3\IISImporter\File;

class FileManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FileManager
     */
    private $fileManager;

    protected function setUp()
    {
        $this->fileManager = new FileManager(
            new \SplFileInfo('/vagrant/import')
        );
    }

    /**
     * @test
     */
    public function it_return_the_process_folder()
    {
        $processFolder = $this->fileManager->getProcessFolder();
        $expectedProcessFolder = new \SplFileInfo('/vagrant/import/process');

        $this->assertEquals($expectedProcessFolder, $processFolder);
    }

    /**
     * @test
     */
    public function it_return_the_error_folder()
    {
        $errorFolder = $this->fileManager->getErrorFolder();
        $expectedErrorFolder = new \SplFileInfo('/vagrant/import/error');

        $this->assertEquals($expectedErrorFolder, $errorFolder);
    }

    /**
     * @test
     */
    public function it_return_the_invalid_folder()
    {
        $invalidFolder = $this->fileManager->getInvalidFolder();
        $expectedInvalidFolder = new \SplFileInfo('/vagrant/import/invalid');

        $this->assertEquals($expectedInvalidFolder, $invalidFolder);
    }

    /**
     * @test
     */
    public function it_return_the_success_folder()
    {
        $successFolder = $this->fileManager->getSuccessFolder();
        $expectedSuccessFolder = new \SplFileInfo('/vagrant/import/success');

        $this->assertEquals($expectedSuccessFolder, $successFolder);
    }

    /**
     * @test
     */
    public function it_returns_process_folder_files()
    {
        $fileManager = new FileManager(
            new \SplFileInfo(__DIR__ . '/import')
        );

        $expectedFiles = [
            new \SplFileInfo(__DIR__ . '/import/process/event1.xml'),
            new \SplFileInfo(__DIR__ . '/import/process/event2.xml'),
        ];

        $files = $fileManager->getProcessFolderFiles();

        $this->assertEquals($expectedFiles, $files);
    }

    /**
     * @test
     */
    public function it_can_move_a_file()
    {
        $sourceFolder = new \SplFileInfo(__DIR__ . '/move/source');
        $sourceFile = new \SplFileInfo(__DIR__ . '/move/source/file.xml');

        $destinationFolder = new \SplFileInfo(__DIR__ . '/move/destination');
        $destinationFile = new \SplFileInfo(__DIR__ . '/move/destination/file.xml');

        $this->fileManager->moveFileToFolder($sourceFile, $destinationFolder);
        $this->assertTrue(file_exists($destinationFile->getPathname()));
        $this->assertFalse(file_exists($sourceFile->getPathname()));

        $this->fileManager->moveFileToFolder($destinationFile, $sourceFolder);
        $this->assertTrue(file_exists($sourceFile->getPathname()));
        $this->assertFalse(file_exists($destinationFile->getPathname()));
    }
}
