<?php

namespace CultuurNet\UDB3\IISImporter\Media;

use CultuurNet\UDB3\IISImporter\Download\DownloaderInterface;
use CultuurNet\UDB3\IISImporter\Url\UrlFactoryInterface;
use League\Flysystem\Adapter\AbstractAdapter;
use League\Flysystem\Filesystem;
use ValueObjects\StringLiteral\StringLiteral;
use ValueObjects\Web\Url;

class MediaManager implements MediaManagerInterface
{
    /**
     * @var UrlFactoryInterface
     */
    protected $urlFactory;

    /**
     * @var DownloaderInterface
     */
    protected $downloader;

    /**
     * @var AbstractAdapter
     */
    protected $adaptor;

    /**
     * MediaManager constructor.
     * @param UrlFactoryInterface $urlFactory
     * @param DownloaderInterface $downloader
     * @param AbstractAdapter $adaptor
     */
    public function __construct(
        UrlFactoryInterface $urlFactory,
        DownloaderInterface $downloader,
        AbstractAdapter $adaptor
    ) {
        $this->urlFactory = $urlFactory;
        $this->downloader = $downloader;
        $this->adaptor = $adaptor;
    }

    /**
     * @inheritdoc
     */
    public function generateMediaLink(Url $url)
    {
        if ($url->getScheme() == 'ftp') {
            $putStream = $this->downloader->fetchStreamFromFTP($url);
            $filesystem = new Filesystem($this->adaptor);
            $destination = new StringLiteral(substr($url->getPath()->toNative(), 1));
            $filesystem->putStream($destination->toNative(), $putStream);
            if (is_resource($putStream)) {
                fclose($putStream);
            }
            $url = $this->urlFactory->generateMediaUrl($destination);
        }
        return $url;
    }
}
