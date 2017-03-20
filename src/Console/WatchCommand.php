<?php

namespace CultuurNet\UDB3\IISImporter\Console;

use CultuurNet\UDB3\IISImporter\Watcher\WatcherInterface;
use Knp\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class WatchCommand extends Command
{
    /**
     * @var WatcherInterface
     */
    protected $watcher;

    /**
     * WatchCommand constructor.
     * @param WatcherInterface $watcher
     */
    public function __construct(WatcherInterface $watcher) {
        $this->watcher = $watcher;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('importer')
            ->setDescription('Start the importer by watching the folder.');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->watcher->start();
    }
}
