#!/usr/bin/env php
<?php

use CultuurNet\UDB3\IISImporter\Console\AMQPPublishCommand;
use CultuurNet\UDB3\IISImporter\Console\FileProcessorCommand;
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

$consoleApp->add(
    new WatchCommand(
        $app['iis.watcher']
    )
);

$consoleApp->add(
    new AMQPPublishCommand(
        $app['iis.url_factory'],
        $app['iis.amqp_publisher']
    )
);

$consoleApp->add(
    new FileProcessorCommand(
        $app['iis.file_processor']
    )
);

$consoleApp->run();
