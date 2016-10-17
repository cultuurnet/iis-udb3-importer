#!/usr/bin/env php
<?php

use CultuurNet\UDB3\IISImporter\Event\ParserV3;
use CultuurNet\UDB3\IISStore\Stores\StoreRepository;
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

$parser = new ParserV3();
$store = new StoreRepository();
$consoleApp->add(new WatchCommand($parser, $store));

$consoleApp->run();
