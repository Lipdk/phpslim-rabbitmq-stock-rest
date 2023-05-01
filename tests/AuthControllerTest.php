<?php
declare(strict_types=1);

namespace Tests;

class AuthControllerTest extends BaseTestCase
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

    public function testAuthEndpoint()
    {
        // Arrange
        $headers = ['HTTP_ACCEPT' => 'application/json', 'Authorization' => $this->getAuthorizationHeader()];
        $request = $this->createRequest('POST', '/auth', $headers);

        // Act
        $response = $this->app->handle($request);
        $code = $response->getStatusCode();

        // Assert.
        $this->assertEquals(200, $code);
    }

    public function testAuthEndpointUnauthorized()
    {
        $invalidAuth = 'Basic ' . base64_encode('email@email.com:secret');
        $headers = ['HTTP_ACCEPT' => 'application/json', 'Authorization' => $invalidAuth];
        $request = $this->createRequest('POST', '/auth', $headers);

        // Act
        $response = $this->app->handle($request);
        $code = $response->getStatusCode();

        // Assert
        $this->assertEquals(401, $code);
    }
}
