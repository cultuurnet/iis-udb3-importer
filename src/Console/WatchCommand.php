<?php

namespace CultuurNet\UDB3\IISImporter\Console;

use CultuurNet\UDB3\IISImporter\Event\ParserInterface;
use CultuurNet\UDB3\IISImporter\Event\WatcherInterface;
use Lurker\Event\FilesystemEvent;
use Lurker\ResourceWatcher;
use Knp\Command\Command;
use CultuurNet\UDB3\IISImporter\Event;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use CultuurNet\UDB3\IISStore\Stores\RepositoryInterface;
use ValueObjects\Identity;
use ValueObjects\Identity\UUID;
use ValueObjects\StringLiteral\StringLiteral;

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
    protected $aWatcher;

    /**
     * WatchCommand constructor.
     * @param ParserInterface $parser
     * @param RepositoryInterface $store
     * @param WatcherInterface $aWatcher
     */
    public function __construct(ParserInterface $parser, RepositoryInterface $store, WatcherInterface $aWatcher)
    {
        $this->parser = $parser;
        $this->store = $store;
        $this->aWatcher = $aWatcher;
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

        $this->aWatcher->track($app['config']['input_folder']);
        $this->aWatcher->configureListener($this->parser, $this->store);

        $this->aWatcher->start();
    }
}
