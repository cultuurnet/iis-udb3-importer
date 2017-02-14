<?php

namespace CultuurNet\UDB3\IISImporter\Console;

use CultuurNet\UDB3\IISImporter\Event\ParserInterface;
use Lurker\Event\FilesystemEvent;
use Lurker\ResourceWatcher;
use Knp\Command\Command;
use CultuurNet\UDB3\IISImporter\Event;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use CultuurNet\UDB3\IISStore\Stores\RepositoryInterface;
use ValueObjects\Identity;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String as StringLiteral;
use ValueObjects\String\String;

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
                            $externalIdLiteral = new StringLiteral($externalId);
                            $cdbid = $storeRepository->getEventCdbid($externalIdLiteral);
                            $isUpdate = true;
                            if (!$cdbid) {
                                $isUpdate = false;
                                $cdbidString = Identity\UUID::generateAsString();
                                $cdbid = UUID::fromNative($cdbidString);
                                $singleXml = simplexml_load_string($singleEvent);
                                $singleXml->event[0]['cdbid'] = $cdbid;
                                $singleEvent = new String($singleXml->asXML());
                                $storeRepository->storeRelations($cdbid, $externalIdLiteral);
                            }

                            $storeRepository->storeEventXml($cdbid, $singleEvent, $isUpdate);
                            $storeRepository->storeStatus($cdbid, null, null, null);

                        }
                    } else {
                        echo 'Invalid file uploaded';
                    }
                }
            }
        );
        echo 'start looking for files';

        $watcher->start();
    }
}
