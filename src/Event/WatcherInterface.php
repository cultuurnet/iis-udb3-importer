<?php

namespace CultuurNet\UDB3\IISImporter\Event;

use CultuurNet\UDB3\IISStore\Stores\RepositoryInterface;

interface WatcherInterface
{
    /**
     * Sets the folder to track
     *
     * @param string $resource resource to track
     **/
    public function track($resource);

    /**
     * Adds the listener function
     *
     * @param ParserInterface $parser
     * @param RepositoryInterface $store
     **/
    public function configureListener(ParserInterface $parser, RepositoryInterface $store);

    /**
     * Starts the watcher
     */
    public function start();
}
