<?php

namespace CultuurNet\UDB3\IISImporter\Media;

use CultuurNet\UDB3\IISImporter\Download\DownloaderInterface;
use CultuurNet\UDB3\IISImporter\Url\UrlFactoryInterface;
use League\Flysystem\Adapter\AbstractAdapter;
use ValueObjects\Web\Url;

class MediaManagerTest extends \PHPUnit_Framework_TestCase
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
    protected $adapter;

    /**
     * @var MediaManager
     */
    protected $mediaManager;

    public function setUp()
    {
        $this->urlFactory = $this->createMock(UrlFactoryInterface::class);
        $this->adapter = $this->createMock(AbstractAdapter::class);
        $this->downloader = $this->createMock(DownloaderInterface::class);

        $this->mediaManager = new MediaManager(
            $this->urlFactory,
            $this->downloader,
            $this->adapter
        );
    }
}
