<?php

namespace CultuurNet\UDB3\IISImporter\Event;

interface WatcherInterface
{
    /**
     * Starts to track the folder
     **/
    public function track();

    /**
     * Adds the listener function
     **/
    public function configureListener();

    /**
     * Starts the watcher
     */
    public function start();
}
