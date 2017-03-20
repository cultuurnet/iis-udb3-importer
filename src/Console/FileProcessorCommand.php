<?php

namespace CultuurNet\UDB3\IISImporter\Console;

use CultuurNet\UDB3\IISImporter\File\FileProcessorInterface;
use Knp\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use ValueObjects\StringLiteral\StringLiteral;

class FileProcessorCommand extends Command
{
    /**
     * @var FileProcessorInterface
     */
    private $fileProcessor;

    /**
     * FileProcessorCommand constructor.
     * @param FileProcessorInterface $fileProcessor
     */
    public function __construct(FileProcessorInterface $fileProcessor)
    {
        $this->fileProcessor = $fileProcessor;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('fileProcessor')
            ->setDescription('Process a file.')
            ->addArgument(
                'fullPath',
                InputArgument::REQUIRED,
                'Full path of the file to process'
            );
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $fullPath = new StringLiteral($input->getArgument('fullPath'));
        $this->fileProcessor->consumeFile($fullPath);
    }
}
