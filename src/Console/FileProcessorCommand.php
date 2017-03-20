<?php

namespace CultuurNet\UDB3\IISImporter\Console;

use CultuurNet\UDB3\IISImporter\Processor\ProcessorInterface;
use Knp\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FileProcessorCommand extends Command
{
    /**
     * @var ProcessorInterface
     */
    private $processor;

    /**
     * FileProcessorCommand constructor.
     * @param ProcessorInterface $processor
     */
    public function __construct(ProcessorInterface $processor)
    {
        $this->processor = $processor;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('fileProcessor')
            ->setDescription('Process a file.')
            ->addArgument(
                'fileName',
                InputArgument::REQUIRED,
                'Full path of the file to process'
            );
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $file = new \SplFileInfo($input->getArgument('fileName'));
        $this->processor->consumeFile($file);
    }
}
