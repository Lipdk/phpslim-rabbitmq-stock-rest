<?php
declare(strict_types=1);

namespace Tests;

/**
 * Class HelloTest
 * @package Tests
 */
class HelloTest extends BaseTestCase
{
    /**
     * @var \Slim\App
     */
    protected $app;

    /**
     * @throws \Exception
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->app = $this->getAppInstance();
    }

    public function testIndex()
    {
        // Arrange
        $request = $this->createRequest('GET', '/');

        // Act
        $response = $this->app->handle($request);
        $body = (string) $response->getBody();

        // Assert
        $this->assertEquals("Up and running!", $body);
    }

    public function testHelloEndpoint()
    {
        // Arrange
        $request = $this->createRequest('GET', '/hello/Test');

        // Act
        $response = $this->app->handle($request);
        $body = (string) $response->getBody();

        // Assert
        $this->assertEquals("Hello, Test", $body);
    }

    public function testByeEndpointThrowsUnauthorized()
    {
        // Arrange
        $request = $this->createRequest('GET', '/bye/MyName');

        // Act
        $response = $this->app->handle($request);
        $code = $response->getStatusCode();

        // Assert
        $this->assertEquals(401, $code);
    }

    public function testByeEndpointWithBasicAuth()
    {
        // Arrange
        $headers = ['HTTP_ACCEPT' => 'application/json', 'Authorization' => $this->getAuthorizationTokenHeader()];
        $request = $this->createRequest('GET', '/bye/My Name', $headers);

        // Act
        $response = $this->app->handle($request);
        $body = (string) $response->getBody();

        // Assert
        $this->assertEquals("Bye, My Name", $body);
    }
}
