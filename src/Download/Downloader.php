<?php

namespace CultuurNet\UDB3\IISImporter\Download;

use League\Flysystem\Adapter\Ftp;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Twistor\Flysystem\Http\HttpAdapter;
use ValueObjects\Web\Url;

class Downloader implements DownloaderInterface
{
    /**
     * @inheritdoc
     */
    public function fetchStreamFromFTP(Url $url)
    {
        $ftpAdapter = new Ftp(
            [
            'host' => $url->getDomain()->toNative(),
            'username' => $url->getUser()->toNative(),
            'password' => $url->getPassword()->ToNative(),
            ]
        );
        $ftpSystem = new Filesystem($ftpAdapter);
        $stream = $ftpSystem->readStream($url->getPath()->toNative());

        return $stream;
    }

    /**
     * @inheritdoc
     */
    public function fetchStreamFromHttp(Url $url)
    {
        $httpAdapter = new HttpAdapter($url->getScheme() . "://" . $url->getDomain() . $url->getQueryString());

        $httpSystem = new Filesystem($httpAdapter);
        $stream = $httpSystem->readStream($url->getPath()->toNative());

        return $stream;
    }
}
