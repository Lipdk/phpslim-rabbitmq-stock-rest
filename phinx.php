<?php

require __DIR__ . '/vendor/autoload.php';
$dotenv = new Symfony\Component\Dotenv\Dotenv();
$dotenv->load(__DIR__ . '/.env');
$ENV = $_ENV['ENV'] ?? 'dev';
$containerBuilder = new DI\ContainerBuilder();
$dependencies = require __DIR__ . '/app/services.php';
$dependencies($containerBuilder);
$container = $containerBuilder->build();

return
[
    'paths' => [
        'migrations' => '%%PHINX_CONFIG_DIR%%/db/migrations',
        'seeds' => '%%PHINX_CONFIG_DIR%%/db/seeds'
    ],
    // TODO: Modify to use environment variables
    'environments' => [
        'default_migration_table' => 'phinxlog',
        'default_environment' => 'development',
        'production' => [
            'adapter' => 'mysql',
            'host' => 'db',
            'name' => 'db',
            'user' => 'db',
            'pass' => 'db',
            'port' => '3306',
            'charset' => 'utf8',
        ],
        'development' => [
            'adapter' => 'mysql',
            'host' => 'db',
            'name' => 'db',
            'user' => 'db',
            'pass' => 'db',
            'port' => '3306',
            'charset' => 'utf8',
        ]
    ],
    'version_order' => 'creation'
];
