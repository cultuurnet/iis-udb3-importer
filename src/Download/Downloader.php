<?php

namespace CultuurNet\UDB3\IISImporter\Download;

use League\Flysystem\Adapter\Ftp;
use League\Flysystem\Filesystem;
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
}
