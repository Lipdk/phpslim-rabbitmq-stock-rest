<?php

declare(strict_types=1);

namespace Tests;

use App\Utilities\Config;
use DI\ContainerBuilder;
use Exception;
use Firebase\JWT\JWT;
use PHPUnit\Framework\TestCase as PHPUnit_TestCase;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Psr7\Factory\StreamFactory;
use Slim\Psr7\Headers;
use Slim\Psr7\Request as SlimRequest;
use Slim\Psr7\Uri;
use Symfony\Component\Dotenv\Dotenv;

class BaseTestCase extends PHPUnit_TestCase
{
    /**
     * @return App
     * @throws Exception
     */
    protected function getAppInstance(): App
    {
        parent::setUp();

        $containerBuilder = new ContainerBuilder();

        $dotenv = new Dotenv();
        $dotenv->load(__DIR__ . '/../.env');

        $dependencies = require __DIR__ . '/../app/services.php';
        $dependencies($containerBuilder);

        $container = $containerBuilder->build();
        AppFactory::setContainer($container);

        // Eloquent ORM
        \App\Models\Bootstrap::load($container);

        $app = AppFactory::create();

        $routes = require __DIR__ . '/../app/routes.php';
        $routes($app);

        return $app;
    }

    /**
     * @return String
     */
    protected function getAuthorizationHeader(): String
    {
        $adminTestingEmail = $_ENV["ADMIN_EMAIL"];
        $adminTestingPassword = $_ENV["ADMIN_PASSWORD"];


        return 'Basic ' . base64_encode("$adminTestingEmail:$adminTestingPassword");
    }

    protected function getAuthorizationTokenHeader(): String
    {
        $expire = (new \DateTime('now'))->modify('+1 hour')->format('Y-m-d H:i:s');
        $token = JWT::encode([
            'expired_at' => $expire,
            'email' => $_ENV["ADMIN_EMAIL"],
        ], Config::getJwtKeyMaterial());

        return "Bearer $token";
    }

    /**
     * @param string $method
     * @param string $path
     * @param array  $headers
     * @param array  $cookies
     * @param array  $serverParams
     * @return Request
     */
    protected function createRequest(
        string $method,
        string $path,
        array $headers = ['HTTP_ACCEPT' => 'application/json'],
        string $query = '',
        array $cookies = [],
        array $serverParams = []
    ): Request {
        $uri = new Uri('', 'localhost', 80, $path, $query);
        $handle = fopen('php://temp', 'w+');
        $stream = (new StreamFactory())->createStreamFromResource($handle);

        $h = new Headers();
        foreach ($headers as $name => $value) {
            $h->addHeader($name, $value);
        }

        return new SlimRequest($method, $uri, $h, $cookies, $serverParams, $stream);
    }
}
