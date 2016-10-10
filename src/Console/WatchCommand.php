<?php

namespace CultuurNet\UDB3\IISImporter\Console;

use CultuurNet\UDB3\IISImporter\Event\ParserInterface;
use Lurker\Event\FilesystemEvent;
use Lurker\ResourceWatcher;
use Knp\Command\Command;
use CultuurNet\UDB3\IISImporter\Event;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use CultuurNet\UDB3\IISStore\ReadModel\Index\RepositoryInterface;
use ValueObjects\Identity;

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
     * WatchCommand constructor.
     * @param ParserInterface $parser
     * @param RepositoryInterface $store
     */
    public function __construct(ParserInterface $parser, RepositoryInterface $store)
    {
        $this->parser = $parser;
        $this->store = $store;
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

        $watcher = new ResourceWatcher();
        $watcher->track('import_files', $app['config']['input_folder']);

        $watcher->addListener(
            'import_files',
            function (FilesystemEvent $filesystemEvent) {
                if ($filesystemEvent->getTypeString() == 'create' ||
                    $filesystemEvent->getTypeString() == 'modify') {
                    $xmlString = file_get_contents($filesystemEvent->getResource());

                    $parser = $this->parser;
                    if ($parser->validate($xmlString)) {
                        $eventList = $parser->split($xmlString);

                        $storeRepository = $this->store;

                        foreach ($eventList as $externalId => $singleEvent) {
                            $cdbid = $storeRepository->getEventCdbid($externalId);
                            if (!$cdbid) {
                                $cdbid = Identity\UUID::generateAsString();
                                $singleXml = simplexml_load_string($singleEvent);
                                $singleXml->event[0]['cdbid'] = $cdbid;
                                $singleEvent = $singleXml->asXML();
                            }

                            $storeRepository->storeRelations($cdbid, $externalId);
                            $storeRepository->storeEventXml($cdbid, $singleEvent);
                            $storeRepository->storeStatus($cdbid, null, null, null);

                        }
                    } else {
                        echo 'Invalid file uploaded';
                    }
                }
            }
        );

        $watcher->start();
    }
}
