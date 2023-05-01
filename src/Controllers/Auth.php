<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;
use App\Utilities\JsonRenderer;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class Auth
{
    protected JsonRenderer $renderer;
    protected User $user;

    public function __construct(JsonRenderer $renderer, User $user)
    {
        $this->renderer = $renderer;
        $this->user = $user;
    }

    public function __invoke(Request $request, Response $response): Response
    {
        try {
            if ($request->hasHeader('Authorization')) {
                $header = rtrim(ltrim($request->getHeader('Authorization')[0]));
                preg_match("/Basic\s(\S+)/", $header, $matches);
                $auth = explode(':', base64_decode($matches[1]));
                $email = $auth[0] ?? '';
                $password = $auth[1] ?? '';

                if (empty($email) || empty($password)) {
                    return $this->renderer->json($response, ['error' => 'Invalid authentication data'])
                        ->withHeader('Content-Type', 'application/json')
                        ->withStatus(401);
                }

                $user = $this->user->auth($email, $password);

                if (!$user instanceof User) {
                    return $this->renderer->json($response, ['error' => 'Failed to authenticate'])
                        ->withHeader('Content-Type', 'application/json')
                        ->withStatus(401);
                }

                $token = $this->user->generateJwtToken($user->email);

                return $this->renderer->json($response, ['success' => ['token' => $token]])
                    ->withHeader("Content-Type", "application/json")
                    ->withStatus(200);
            } else {
                return $this->renderer->json($response, ['error' => 'Unauthorized access'])
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(401);
            }
        } catch (\Exception $e) {
            return $this->renderer->json($response, ['error' => $e->getMessage()])
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(500);
        }
    }
}