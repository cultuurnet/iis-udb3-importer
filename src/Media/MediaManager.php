<?php

namespace CultuurNet\UDB3\IISImporter\Media;

use League\Flysystem\Adapter\AbstractAdapter;
use League\Flysystem\Filesystem;
use ValueObjects\Web\Url;

class MediaManager implements MediaManagerInterface
{

    /**
     * @var AbstractAdapter
     */
    protected $adaptor;

    /**
     * MediaManager constructor.
     * @param AbstractAdapter $adapter
     */
    public function __construct(AbstractAdapter $adapter)
    {
        $this->adaptor = $adapter;
    }

    /**
     * @inheritdoc
     */
    public function generateMediaLink(Url $url)
    {
        //TODO: temporary development return
        $filesystem = new Filesystem($this->adaptor);

        $ftpConnection = ftp_connect($url->getDomain());
        ftp_login($ftpConnection, $url->getUser(), $url->getPassword());

        $putStream = tmpfile();
        ftp_get($ftpConnection, $putStream, $url->getPath(), FTP_BINARY);
        fwrite($putStream, $url);
        rewind($putStream);

        $filesystem->putStream('somewhere/todo.txt', $putStream);
        if (is_resource($putStream)) {
            fclose($putStream);
        }

        return $url;
    }
}
