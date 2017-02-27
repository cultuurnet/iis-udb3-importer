#!/usr/bin/env php
<?php

use CultuurNet\UDB3\IISImporter\Event\ParserV3;
use CultuurNet\UDB3\IISStore\Stores\StoreRepository;
use Knp\Provider\ConsoleServiceProvider;
use CultuurNet\UDB3\IISImporter\Console\WatchCommand;
use CultuurNet\UDB3\IISStore\Stores\Doctrine\StoreLoggingDBALRepository;
use CultuurNet\UDB3\IISStore\Stores\Doctrine\StoreRelationDBALRepository;
use CultuurNet\UDB3\IISStore\Stores\Doctrine\StoreXmlDBALRepository;
use ValueObjects\StringLiteral\StringLiteral;

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

$config = new \Doctrine\DBAL\Configuration();
$connectionParams = array(
    'dbname' => $app['config']['database']['dbname'],
    'user' => $app['config']['database']['user'],
    'password' => $app['config']['database']['password'],
    'host' => $app['config']['database']['host'],
    'driver' => $app['config']['database']['driver'],
);
$connection = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);

$table_name = new StringLiteral('TODO');

$logging_table = new StringLiteral('logging');
$relation_table = new StringLiteral('relation');
$xml_table = new StringLiteral('xml');


$loggingRepository = new StoreLoggingDBALRepository($connection, $logging_table);
$relationsRepository = new StoreRelationDBALRepository($connection, $relation_table);
$xmlRepository = new StoreXmlDBALRepository($connection, $xml_table);

$store = new StoreRepository($loggingRepository, $relationsRepository, $xmlRepository);
$consoleApp->add(new WatchCommand($parser, $store));

$consoleApp->run();
