<?php

namespace CultuurNet\UDB3\IISImporter\Event;

use CultuurNet\UDB3\IISImporter\Url\UrlFactory;
use CultuurNet\UDB3\IISStore\Stores\RepositoryInterface;
use ValueObjects\DateTime\Date;
use ValueObjects\StringLiteral\StringLiteral;
use Lurker\Event\FilesystemEvent;
use Lurker\ResourceWatcher;
use ValueObjects\Identity;
use ValueObjects\Identity\UUID;

class Watcher implements WatcherInterface
{
    /**
     * @var StringLiteral
     */
    protected $trackingId;

    /**
     * @var ResourceWatcher
     */
    protected $resourceWatcher;

    /**
     * @var ParserInterface
     */
    protected $parser;

    /**
     * @var RepositoryInterface
     */
    protected $store;

    /**
     * @var PublishInterface
     */
    protected $publisher;

    /**
     * @param StringLiteral $trackingId
     */
    public function __construct(StringLiteral $trackingId, PublishInterface $publisher)
    {
        $this->trackingId = $trackingId;
        $this->resourceWatcher = new ResourceWatcher();
        $this->publisher = $publisher;
    }

    public function track($resource)
    {
        $this->resourceWatcher->track($this->trackingId->toNative(), $resource);
    }

    /**
     * @inheritdoc
     */
    public function configureListener(ParserInterface $parser, RepositoryInterface $store)
    {
        $this->parser = $parser;
        $this->store = $store;

        $this->resourceWatcher->addListener(
            $this->trackingId->toNative(),
            function (FilesystemEvent $filesystemEvent) {
                if ($filesystemEvent->getTypeString() == 'create' ||
                    $filesystemEvent->getTypeString() == 'modify') {
                    $xmlString = file_get_contents($filesystemEvent->getResource());

                    if ($this->parser->validate($xmlString)) {
                        $eventList = $this->parser->split($xmlString);

                        foreach ($eventList as $externalId => $singleEvent) {
                            $externalIdLiteral = new StringLiteral($externalId);
                            $cdbid = $this->store->getEventCdbid($externalIdLiteral);
                            $isUpdate = true;
                            if (!$cdbid) {
                                $isUpdate = false;
                                $cdbidString = Identity\UUID::generateAsString();
                                $cdbid = UUID::fromNative($cdbidString);
                            }
                            $singleXml = simplexml_load_string($singleEvent);
                            $singleXml->event[0]['cdbid'] = $cdbid;
                            $singleEvent = new StringLiteral($singleXml->asXML());

                            if ($isUpdate) {
                                $this->store->updateEventXml($cdbid, $singleEvent);
                                $this->store->saveUpdated($cdbid, new \DateTime());
                            } else {
                                $this->store->saveRelation($cdbid, $externalIdLiteral);
                                $this->store->saveEventXml($cdbid, $singleEvent);
                                $this->store->saveCreated($cdbid, new \DateTime());
                            }
                            $now = new \DateTime();
                            $baseUrl = new StringLiteral('http://test.import.com');
                            $author = new StringLiteral('importsUDB3');
                            $urlFactory = new UrlFactory($baseUrl);
                            $this->publisher->publish($cdbid,$now,$author, $urlFactory->generateUrl($cdbid));
                            $this->store->savePublished($cdbid,$now);

                        }
                    } else {
                        echo 'Invalid file uploaded';
                    }
                }
            }
        );
    }

    public function start()
    {
        $this->resourceWatcher->start();
    }
}
