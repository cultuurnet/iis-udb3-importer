<?php

namespace CultuurNet\UDB3\IISImporter\Console;

use CultuurNet\UDB3\IISImporter\Event\ParserInterface;
use CultuurNet\UDB3\IISImporter\Event\PublishInterface;
use CultuurNet\UDB3\IISImporter\Event\WatcherInterface;
use Knp\Command\Command;
use CultuurNet\UDB3\IISImporter\Event;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use CultuurNet\UDB3\IISStore\Stores\RepositoryInterface;

class WatchCommand extends Command
{
    /**
     * @var Event\ParserInterface
     */
    protected $parser;

    /**
     * @var RepositoryInterface
     */
    protected $store;

    /**
     * @var WatcherInterface
     */
    protected $watcher;

    /**
     * @var PublishInterface
     */
    protected $publisher;

    /**
     * WatchCommand constructor.
     * @param ParserInterface $parser
     * @param RepositoryInterface $store
     * @param WatcherInterface $watcher
     * @param PublishInterface $publisher
     */
    public function __construct(
        ParserInterface $parser,
        RepositoryInterface $store,
        WatcherInterface $watcher,
        PublishInterface $publisher
    ) {
        $this->parser = $parser;
        $this->store = $store;
        $this->watcher = $watcher;
        $this->publisher = $publisher;
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
        $app = $this->getSilexApplication();

        $this->watcher->track($app['config']['input_folder']);
        $this->watcher->configureListener($this->parser, $this->store);
        $this->watcher->start();
    }
}
