<?php

namespace CultuurNet\UDB3\IISImporter\Event;

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
     **/
    public function configureListener();

    /**
     * Starts the watcher
     */
    public function start();

    /**
     * Move file to a folder
     *
     * @param string $file to file to move
     * @param string $folder the destination folder
     */
    public function moveFile($file, $folder);
}
