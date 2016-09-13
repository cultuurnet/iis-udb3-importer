<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\IISImporter\Console;

use Lurker\Event\FilesystemEvent;
use Lurker\ResourceWatcher;
use Knp\Command\Command;
use CultuurNet\UDB3\IISImporter\Event;

class WatchCommand extends Command
{
    /**
     * @var $ParserRepository
     */
    protected $parser;

    /**
     * WatchCommand constructor.
     * @param Event\ParserInterface $parserRepository
     */
    public function __construct(Event\ParserInterface $parserRepository)
    {
        $this->parser = $parserRepository;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('importer')
            ->setDescription('Start the importer by watching the folder.');
    }

    protected function execute() //(InputInterface $input, OutputInterface $output)
    {
        $app = $this->getSilexApplication();

        $watcher = new ResourceWatcher();
        $watcher->track('import_files', $app['config']['input_folder']);

        $watcher->addListener(
            'import_files',
            function (FilesystemEvent $filesystemEvent) {
                if ($filesystemEvent->getTypeString() == 'create'||$filesystemEvent->getTypeString() == 'modify') {
                    $xmlString = file_get_contents($filesystemEvent->getResource());
                    echo $xmlString;
                }
            }
        );

        $watcher->start();

        //parent::execute($input, $output); // TODO: Change the autogenerated stub
        //parent::execute($input, $output); // TODO: Change the autogenerated stub
    }
}
