<?php

use CultuurNet\UDB3\IISImporter\Event\ParserV3;
use CultuurNet\UDB3\IISImporter\Event\PublishAMQP;
use CultuurNet\UDB3\IISImporter\Event\Watcher;
use CultuurNet\UDB3\IISStore\Stores\Doctrine\StoreLoggingDBALRepository;
use CultuurNet\UDB3\IISStore\Stores\Doctrine\StoreRelationDBALRepository;
use CultuurNet\UDB3\IISStore\Stores\Doctrine\StoreXmlDBALRepository;
use CultuurNet\UDB3\IISStore\Stores\StoreRepository;
use DerAlex\Silex\YamlConfigServiceProvider;
use Doctrine\DBAL\DriverManager;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use Silex\Application;
use ValueObjects\StringLiteral\StringLiteral;

$app = new Application();

if (!isset($appConfigLocation)) {
    $appConfigLocation =  __DIR__;
}
$app->register(new YamlConfigServiceProvider($appConfigLocation . '/config.yml'));

/**
 * Turn debug on or off.
 */
$app['debug'] = $app['config']['debug'] === true;

/**
 * Load additional bootstrap files.
 */
foreach ($app['config']['bootstrap'] as $identifier => $enabled) {
    if (true === $enabled) {
        require __DIR__ . "/bootstrap/{$identifier}.php";
    }
}

$app['dbal_connection'] = $app->share(
    function (Application $app) {
        return DriverManager::getConnection(
            $app['config']['database'],
            null
        );
    }
);

$app['iis.dbal_store.xml'] = $app->share(
    function (Application $app) {
        return new StoreXmlDBALRepository(
            $app['dbal_connection'],
            new StringLiteral('xml')
        );
    }
);

$app['iis.dbal_store.log'] = $app->share(
    function (Application $app) {
        return new StoreLoggingDBALRepository(
            $app['dbal_connection'],
            new StringLiteral('log')
        );
    }
);

$app['iis.dbal_store.relation'] = $app->share(
    function (Application $app) {
        return new StoreRelationDBALRepository(
            $app['dbal_connection'],
            new StringLiteral('relation')
        );
    }
);

$app['iis.dbal_store'] = $app->share(
    function (Application $app) {
        return new StoreRepository(
            $app['iis.dbal_store.log'],
            $app['iis.dbal_store.relation'],
            $app['iis.dbal_store.xml']
        );
    }
);

$app['iis.parser'] = $app->share(
    function () {
        return new ParserV3();
    }
);

$app['iis.watcher'] = $app->share(
    function () {
        $trackingId = new StringLiteral('import_files');
        return new Watcher($trackingId);
    }
);

$app['iis.amqp_connection'] = $app->share(
    function(Application $app) {
        $connection = new AMQPStreamConnection(
            $app['config']['amqp.host'],
            $app['config']['amqp.port'],
            $app['config']['amqp.user'],
            $app['config']['amqp.password'],
            $app['config']['amqp.vhost']
        );

        return $connection;
    }
);

$app['iis.publisher'] = $app->share(
    function (Application $app) {
        $channel = new AMQPChannel($app['iis.amqp_connection']);
        return new PublishAMQP($channel, $app['config']['publish']['exchange']);
    }
);

return $app;
