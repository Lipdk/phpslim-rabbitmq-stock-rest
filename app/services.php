<?php
declare(strict_types=1);

use DI\ContainerBuilder;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        Swift_Mailer::class => function() {
            $host = $_ENV['MAILER_HOST'] ?? 'smtp.mailtrap.io';
            $port = intval($_ENV['MAILER_PORT']) ?? 465;
            $username = $_ENV['MAILER_USERNAME'] ?? 'test';
            $password = $_ENV['MAILER_PASSWORD'] ?? 'test';
            $encryption = $_ENV['MAILER_ENCRYPTION'] ?? 'tls';

            $transport = (new Swift_SmtpTransport($host, $port, $encryption))
                ->setUsername($username)
                ->setPassword($password);

            return new Swift_Mailer($transport);
        },

        'settings' => [
            'displayErrorDetails' => $_ENV['ENV'] == 'dev',
            'db' => [
                'driver' => 'mysql',
                'host' => $_ENV['DB_HOST'] ?? 'db',
                'database' => $_ENV['DB_DB'] ?? 'db',
                'username' => $_ENV['DB_USERNAME'] ?? 'db',
                'password' => $_ENV['DB_PASSWORD'] ?? 'db',
                'charset'   => 'utf8',
                'collation' => 'utf8_unicode_ci',
                'prefix'    => '',
            ],
        ],

        AMQPChannel::class => function () {
            $connection = new AMQPStreamConnection(
                $_ENV['RMQ_HOST'],
                $_ENV['RMQ_PORT'],
                $_ENV['RMQ_USERNAME'],
                $_ENV['RMQ_PASSWORD'],
                $_ENV['RMQ_VHOST']
            );
            return $connection->channel();
        }
    ]);
};
