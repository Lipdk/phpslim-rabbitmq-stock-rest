<?php
declare(strict_types=1);

use DI\ContainerBuilder;
use App\Models\User;

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
//        \Tuupola\Middleware\JwtAuthentication::class => function()
//        {
//            return new Tuupola\Middleware\JwtAuthentication([
//                "ignore" => ['hello'],
//                "header" => "Authorization",
//                "secret" => 'secret',
//                "algorithm"=>["HS512"],
//                "attribute" => "jwt",
//                "error" => function ($response, $arguments) {
//                    $data["status"] = "false";
//                    $data["message"] = $arguments["message"];
//                    $response->getBody()->write(
//                        json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)
//                    );
//                    return $response->withHeader("Content-Type", "application/json")->withStatus(401);
//                }
//            ]);
//        }
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
