<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;
use App\Utilities\JsonRenderer;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class UserController
{
    protected JsonRenderer $renderer;
    protected User $user;

    public function __construct(JsonRenderer $renderer, User $user)
    {
        $this->renderer = $renderer;
        $this->user = $user;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function create(Request $request, Response $response, array $args): Response
    {
        $body = $request->getParsedBody();

        try {
            $user = $this->user->store($body);
            $token = $this->user->generateJwtToken($body['email']);

            if ($user instanceof User) {
                return $this->renderer->json($response, [
                    'success' => 'User created successfully',
                    'token' => $token
                ])
                ->withHeader("Content-Type", "application/json")
                ->withStatus(201);
            }
        } catch (\Exception $e) {
            return $this->renderer->json($response, ['error' => $e->getMessage()])
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(500);
        }

        return $this->renderer->json($response, ['error' => 'An error occurred while creating the user'])
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(401);
    }
}
