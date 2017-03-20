<?php

namespace CultuurNet\UDB3\IISImporter\File;

use CultuurNet\UDB3\IISImporter\AMQP\AMQPPublisherInterface;
use CultuurNet\UDB3\IISImporter\Event\ParserInterface;
use CultuurNet\UDB3\IISImporter\Url\UrlFactory;
use CultuurNet\UDB3\IISStore\Stores\RepositoryInterface;
use ValueObjects\StringLiteral\StringLiteral;
use ValueObjects\Identity\UUID;

class FileProcessor implements FileProcessorInterface
{
    const PROCESS_FOLDER = 'process';
    const SUCCESS_FOLDER = 'success';
    const ERROR_FOLDER = 'error';
    const INVALID_FOLDER = 'invalid';

    /**
     * @var \SplFileInfo
     */
    protected $rootFolder;

    /**
     * @var ParserInterface
     */
    protected $parser;

    /**
     * @var RepositoryInterface
     */
    protected $store;

    /**
     * @var AMQPPublisherInterface
     */
    protected $publisher;

    /**
     * @var UrlFactory
     */
    protected $urlFactory;

    /**
     * @var StringLiteral
     */
    protected $author;

    /**
     * FileProcessor constructor.
     * @param \SplFileInfo $rootFolder
     * @param ParserInterface $parser
     * @param RepositoryInterface $store
     * @param AMQPPublisherInterface $publisher
     * @param UrlFactory $urlFactory
     * @param StringLiteral $author
     */
    public function __construct(
        \SplFileInfo $rootFolder,
        ParserInterface $parser,
        RepositoryInterface $store,
        AMQPPublisherInterface $publisher,
        UrlFactory $urlFactory,
        StringLiteral $author
    ) {
        $this->rootFolder = $rootFolder;
        $this->parser = $parser;
        $this->store = $store;
        $this->publisher = $publisher;
        $this->urlFactory = $urlFactory;
        $this->author = $author;
    }


    /**
     * @inheritdoc
     */
    public function consumeFile(StringLiteral $fileName)
    {
        $fullPath = $this->getProcessFolder() . '/' . $fileName->toNative();
        $xmlString = new StringLiteral(file_get_contents($fullPath));

        if ($this->parser->validate($xmlString->toNative())) {
            try {
                $eventList = $this->parser->split($xmlString->toNative());

                foreach ($eventList as $externalId => $singleEvent) {
                    $externalIdLiteral = new StringLiteral($externalId);
                    $cdbid = $this->store->getEventCdbid($externalIdLiteral);
                    $isUpdate = true;
                    if (!$cdbid) {
                        $isUpdate = false;
                        $cdbidString = UUID::generateAsString();
                        $cdbid = UUID::fromNative($cdbidString);
                    }
                    $singleXml = simplexml_load_string($singleEvent);
                    $singleXml->event[0]['cdbid'] = $cdbid->toNative();
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
                    $this->publisher->publish($cdbid, $now, $this->author, $this->urlFactory->generateUrl($cdbid), $isUpdate);
                    $this->store->savePublished($cdbid, $now);
                    $this->moveFile($fileName, FileProcessor::SUCCESS_FOLDER);
                }
            } catch (\Exception $e) {
                $this->moveFile($fileName, FileProcessor::ERROR_FOLDER);
            }
        } else {
            $this->moveFile($fileName, FileProcessor::INVALID_FOLDER);
        }
    }

    /**
     * @inheritdoc
     */
    public function getProcessFolder()
    {
        return $this->rootFolder->getPathname() . '/' . self::PROCESS_FOLDER;
    }

    /**
     * Move file to a folder
     *
     * @param StringLiteral $fileName
     * @param string $folder the destination folder
     */
    private function moveFile(StringLiteral $fileName, $folder)
    {
        $destinationPath = $this->rootFolder->getPathname() . '/' . $folder;
        if (!file_exists($destinationPath) && !is_dir($destinationPath)) {
            mkdir($destinationPath);
        }
        $destination = $destinationPath . '/' . $fileName;
        $source = $this->getProcessFolder() . '/' . $fileName;

        rename($source, $destination);
    }
}
