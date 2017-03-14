<?php

namespace CultuurNet\UDB3\IISImporter\Console;

use CultuurNet\UDB3\IISImporter\AMQP\AMQPPublisher;
use CultuurNet\UDB3\IISImporter\Url\UrlFactoryInterface;
use Knp\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use ValueObjects\Identity\UUID;
use ValueObjects\StringLiteral\StringLiteral;

class AMQPPublishCommand extends Command
{
    /**
     * @var UrlFactoryInterface
     */
    private $urlFactory;

    /**
     * @var AMQPPublisher
     */
    private $amqpPublisher;

    /**
     * AMQPPublishCommand constructor.
     * @param UrlFactoryInterface $urlFactory
     * @param AMQPPublisher $amqpPublisher
     */
    public function __construct(
        UrlFactoryInterface $urlFactory,
        AMQPPublisher $amqpPublisher
    ) {
        parent::__construct();
        $this->urlFactory = $urlFactory;
        $this->amqpPublisher = $amqpPublisher;
    }


    protected function configure()
    {
        $this
            ->setName('publisher')
            ->setDescription('Publish an event with the given uuid.')
            ->addArgument(
                'cdbid',
                InputArgument::REQUIRED,
                'Cdbid of the event to publish.'
            )
            ->addArgument(
                'isUpdate',
                InputArgument::OPTIONAL,
                'The event needs to be updated instead of created.'
            );
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $app = $this->getSilexApplication();

        $uuid = new UUID($input->getArgument('cdbid'));
        $author = new StringLiteral($app['config']['amqp']['message']['author']);
        $isUpdate = (bool) $input->getArgument('isUpdate');
        $url = $this->urlFactory->generateUrl($uuid);

        $this->amqpPublisher->publish(
            $uuid,
            new \DateTime(),
            $author,
            $url,
            $isUpdate
        );
    }
}
