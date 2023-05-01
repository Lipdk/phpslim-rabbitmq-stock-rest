<?php
declare(strict_types=1);

namespace Tests;
use App\Models\User;

class UserControllerTest extends BaseTestCase
{
    /**
     * @var \Slim\App
     */
    protected $app;

    protected $firstUser = [
        'name' => 'First Last Name',
        'password' => 'secret',
        'email' => 'email@domain.io',
        'username' => 'testuser'
    ];

    /**
     * @throws \Exception
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->app = $this->getAppInstance();
    }

    public function testUserCreateEndpoint()
    {
        // Arrange
        User::where('username', $this->firstUser['username'])->delete();

        $request = $this->createRequest('POST', '/user/create')->withParsedBody($this->firstUser);

        // Act
        $response = $this->app->handle($request);
        $code = $response->getStatusCode();
        $body = json_decode((string)$response->getBody());

        // Assert.
        $this->assertEquals(200, $code);
        $this->assertEquals('User created successfully', $body->success);
    }

    public function testUserCreateInvalidData()
    {
        $userPayload = [
            'name' => 'First Last Name',
            'username' => 'testuser'
        ];

        $request = $this->createRequest('POST', '/user/create')->withParsedBody($userPayload);

        // Act
        $response = $this->app->handle($request);
        $code = $response->getStatusCode();
        $body = json_decode((string)$response->getBody());

        // Assert.
        $this->assertEquals(422, $code);
        $this->assertEquals('All fields are required', $body->error);
    }

    public function testUserCreateAlreadyExists()
    {
        User::where('username', $this->firstUser['username'])->delete();
        User::insert($this->firstUser);
        $request = $this->createRequest('POST', '/user/create')->withParsedBody($this->firstUser);

        // Act
        $response = $this->app->handle($request);
        $code = $response->getStatusCode();
        $body = json_decode((string)$response->getBody());

        // Assert.
        $this->assertEquals(422, $code);
        $this->assertEquals('User already exists', $body->error);
    }
}
