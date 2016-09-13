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
        'console.version'           => '1.0.0',
        'console.project_directory' => __DIR__.'/..'
    ]
);

/** @var \Knp\Console\Application $consoleApp */
$consoleApp = $app['console'];

$consoleApp->add(new \CultuurNet\UDB3\IISImporter\Console\WatchCommand());


$consoleApp->run();
