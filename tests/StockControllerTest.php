<?php
declare(strict_types=1);

namespace Tests;

use App\Models\User;
class StockControllerTest extends BaseTestCase
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

    public function testStockEndpoint()
    {
        // Arrange
        $headers = ['HTTP_ACCEPT' => 'application/json', 'Authorization' => $this->getAuthorizationTokenHeader()];
        $request = $this->createRequest('GET', '/stock', $headers, 'q=aapl.us');

        // Act
        $response = $this->app->handle($request);
        $code = $response->getStatusCode();
        $body = json_decode((string)$response->getBody());

        // Assert
        $this->assertEquals(200, $code);
        $this->assertEquals('APPLE', $body->name);
    }

    public function testStockEndpointWithoutAuth()
    {
        // Arrange
        $request = $this->createRequest('GET', '/stock', []);

        // Act
        $response = $this->app->handle($request);
        $code = $response->getStatusCode();

        // Assert
        $this->assertEquals(401, $code);
    }

    public function testStockEndpointWithoutInput()
    {
        // Arrange
        $headers = ['HTTP_ACCEPT' => 'application/json', 'Authorization' => $this->getAuthorizationTokenHeader()];
        $request = $this->createRequest('GET', '/stock', $headers);

        // Act
        $response = $this->app->handle($request);
        $code = $response->getStatusCode();
        $body = json_decode((string)$response->getBody());

        // Assert
        $this->assertEquals(422, $code);
        $this->assertEquals('Missing Stock Code', $body->error);
    }

    public function testStockEndpointWithWrongStockCode()
    {
        // Arrange
        $headers = ['HTTP_ACCEPT' => 'application/json', 'Authorization' => $this->getAuthorizationTokenHeader()];
        $request = $this->createRequest('GET', '/stock', $headers, 'q=odd');

        // Act
        $response = $this->app->handle($request);
        $code = $response->getStatusCode();

        // Assert
        $this->assertEquals(404, $code);
    }

    public function testHistoryEndpoint()
    {
        // Arrange/Act
        // -- query a stock
        $headers = ['HTTP_ACCEPT' => 'application/json', 'Authorization' => $this->getAuthorizationTokenHeader()];
        $request = $this->createRequest('GET', '/stock', $headers, 'q=aapl.us');
        $this->app->handle($request);
        // -- history
        $request = $this->createRequest('GET', '/history', $headers);
        $response = $this->app->handle($request);
        $code = $response->getStatusCode();
        $body = json_decode((string)$response->getBody(), true);

        // Assert
        $this->assertEquals(200, $code);
        $this->assertGreaterThan(0, count($body));
    }

    public function testHistoryWithoutAuth()
    {
        // Arrange
        $request = $this->createRequest('GET', '/history', []);

        // Act
        $response = $this->app->handle($request);
        $code = $response->getStatusCode();

        // Assert
        $this->assertEquals(401, $code);
    }
}
