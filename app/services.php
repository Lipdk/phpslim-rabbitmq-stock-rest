<?php
declare(strict_types=1);

use DI\ContainerBuilder;

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
    ]);

    $containerBuilder->addDefinitions([
        'settings' => [
            'displayErrorDetails' => $_ENV['ENV'] == 'dev',
//            'logger' => [
//                'name' => 'slim-app',
//                'path' => isset($_ENV['docker']) ? 'php://stdout'
//                    : __DIR__ . '/../logs/app.log',
//                'level' => Logger::DEBUG,
//            ],

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
    ]);
};
