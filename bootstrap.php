<?php

use Aws\S3\S3Client;
use CultuurNet\CalendarSummary\CalendarPlainTextFormatter;
use CultuurNet\UDB3\IISImporter\AMQP\AMQPBodyFactory;
use CultuurNet\UDB3\IISImporter\AMQP\AMQPMessageFactory;
use CultuurNet\UDB3\IISImporter\AMQP\AMQPPropertiesFactory;
use CultuurNet\UDB3\IISImporter\AMQP\AMQPPublisher;
use CultuurNet\UDB3\IISImporter\Calendar\CalendarFactory;
use CultuurNet\UDB3\IISImporter\CategorizationRules\CategoryRules;
use CultuurNet\UDB3\IISImporter\Download\Downloader;
use CultuurNet\UDB3\IISImporter\File\FileManager;
use CultuurNet\UDB3\IISImporter\Media\MediaManager;
use CultuurNet\UDB3\IISImporter\Parser\ParserV3;
use CultuurNet\UDB3\IISImporter\Processor\Processor;
use CultuurNet\UDB3\IISImporter\Time\TimeFactory;
use CultuurNet\UDB3\IISImporter\Url\UrlFactory;
use CultuurNet\UDB3\IISImporter\Watcher\Watcher;
use CultuurNet\UDB3\IISStore\Stores\Doctrine\StoreLoggingDBALRepository;
use CultuurNet\UDB3\IISStore\Stores\Doctrine\StoreRelationDBALRepository;
use CultuurNet\UDB3\IISStore\Stores\Doctrine\StoreXmlDBALRepository;
use CultuurNet\UDB3\IISStore\Stores\StoreRepository;
use DerAlex\Silex\YamlConfigServiceProvider;
use Doctrine\DBAL\DriverManager;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Channel\AMQPChannel;
use Silex\Application;
use ValueObjects\StringLiteral\StringLiteral;
use ValueObjects\Web\Url;

$app = new Application();

if (!isset($appConfigLocation)) {
    $appConfigLocation =  __DIR__;
}
$app->register(new YamlConfigServiceProvider($appConfigLocation . '/config.yml'));

/**
 * Turn debug on or off.
 */
$app['debug'] = $app['config']['debug'] === true;

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

$app['iis.amqp_connection'] = $app->share(
    function(Application $app) {
        $connection = new AMQPStreamConnection(
            $app['config']['amqp']['host'],
            $app['config']['amqp']['port'],
            $app['config']['amqp']['user'],
            $app['config']['amqp']['password'],
            $app['config']['amqp']['vhost']
        );

        return $connection;
    }
);

$app['iis.amqp_url_factory'] = $app->share(
    function (Application $app) {
        return new UrlFactory(
            new StringLiteral(
                $app['config']['amqp']['message']['base_url']
            )
        );
    }
);

$app['iis.author'] = $app->share(
    function (Application $app) {
        return new StringLiteral(
            $app['config']['amqp']['message']['author']
        );
    }
);

$app['iis.amqp_publisher'] = $app->share(
    function (Application $app) {
        $channel = new AMQPChannel($app['iis.amqp_connection']);
        return new AMQPPublisher(
            $channel,
            new StringLiteral($app['config']['amqp']['publish']['exchange']),
            new AMQPMessageFactory(
                new AMQPBodyFactory(),
                new AMQPPropertiesFactory()
            ));
    }
);

$app['iis.file_manager'] = $app->share(
    function (Application $app) {
        return new FileManager(
            new \SplFileInfo($app['config']['import_folder'])
        );
    }
);

$app['iis.cloud_client'] = $app->share(
    function (Application $app) {
        return new S3Client([
            'credentials' => [
                'key'    => $app['config']['aws']['credentials']['key'],
                'secret' => $app['config']['aws']['credentials']['secret']
            ],
            'region' => $app['config']['aws']['region'],
            'version' => $app['config']['aws']['version'],
        ]);
    }
);

$app['iis.media_url_factory'] = $app->share(
    function (Application $app) {
        return new UrlFactory(
            new StringLiteral(
                $app['config']['aws']['media_url']
            )
        );
    }
);

$app['iis.downloader'] = $app->share(
    function () {
        return new Downloader();
    }
);

$app['iis.adapter'] = $app->share(
    function (Application $app) {
        return new AwsS3Adapter(
            $app['iis.cloud_client'],
            $app['config']['aws']['bucket']
        );
    }
);

$app['iis.media_manager'] = $app->share(
    function (Application $app) {
        return new MediaManager(
            $app['iis.media_url_factory'],
            $app['iis.downloader'],
            $app['iis.adapter']
        );
    }
);

$app['iis.time_factory'] = $app->share(
    function () {
        return new TimeFactory();
    }
);

$app['iis.flanders_region_url'] = $app->share(
    function (Application $app) {
        return Url::fromNative(
            $app['config']['category']['flanders_region']
        );
    }
);

$app['iis.taxonomy_namespace'] = $app->share(
    function (Application $app) {
        return Url::fromNative(
            $app['config']['category']['taxonomy_namespace']
        );
    }
);

$app['iis.category_factory'] = $app->share(
    function (Application $app) {
        return new CategoryRules(
            $app['iis.flanders_region_url'],
            $app['iis.taxonomy_namespace']
        );
    }
);

$app['iis.handler'] = $app->share(
    function (Application $app) {
        return new RotatingFileHandler(
            $app['config']['logging_folder'] . '/importer.log',
            400,
            Logger::DEBUG
        );
    }
);

$app['iis.logger'] = $app->share(
    function (Application $app) {
        return new Logger('importer', array($app['iis.handler']));
    }
);

$app['iis.calendar_formatter'] = $app->share(
    function () {
        return new CalendarPlainTextFormatter();
    }
);

$app['iis.calendar_factory'] = $app->share(
    function () {
        return new CalendarFactory();
    }
);

$app['iis.file_processor'] = $app->share(
    function (Application $app) {
        return new Processor(
            $app['iis.file_manager'],
            $app['iis.parser'],
            $app['iis.dbal_store'],
            $app['iis.amqp_publisher'],
            $app['iis.amqp_url_factory'],
            $app['iis.author'],
            $app['iis.media_manager'],
            $app['iis.time_factory'],
            $app['iis.category_factory'],
            $app['iis.calendar_factory'],
            $app['iis.logger']);
    }
);

$app['iis.watcher'] = $app->share(
    function (Application $app) {
        $trackingId = new StringLiteral('import_files');
        return new Watcher(
            $trackingId,
            $app['iis.file_manager'],
            $app['iis.file_processor']);
    }
);

return $app;
