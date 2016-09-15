#!/usr/bin/env php
<?php

use Knp\Provider\ConsoleServiceProvider;
use CultuurNet\UDB3\IISImporter\Console\WatchCommand;

require_once __DIR__ . '/../vendor/autoload.php';

/** @var \Silex\Application $app */
$app = require __DIR__ . '/../bootstrap.php';

$app->register(
    new ConsoleServiceProvider(),
    [
        'console.name'              => 'Importer',
        'console.version'           => '0.0.1',
        'console.project_directory' => __DIR__.'/..'
    ]
);

/** @var \Knp\Console\Application $consoleApp */
$consoleApp = $app['console'];

$parser = new \CultuurNet\UDB3\IISImporter\Event\ParserV3();
$storeRepository = new \CultuurNet\UDB3\IISImporter\Event\StoreRepository();

$consoleApp->add(new WatchCommand($parser, $storeRepository));

$consoleApp->run();
