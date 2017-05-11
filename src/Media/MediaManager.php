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

        $filesystem->put('path/to/file.txt', 'contents');

        return $url;
    }
}
