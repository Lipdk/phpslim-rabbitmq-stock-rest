<?php
declare(strict_types=1);

namespace App\Middlewares;

use App\Utilities\Config;
use Slim\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Utilities\JsonRenderer;

final class JwtAuth
{
    private JsonRenderer $renderer;
    private Response $response;

    public function __construct(JsonRenderer $renderer, Response $response)
    {
        $this->renderer = $renderer;
        $this->response = $response;
    }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        try {
            if ($request->hasHeader('Authorization')) {
                $header = rtrim(ltrim($request->getHeader('Authorization')[0]));
                preg_match("/Bearer\s(\S+)/", $header, $matches);
                $token = $matches[1] ?? '';

                if (empty($token)) {
                    return $this->renderer->json($this->response, ['error' => 'Invalid bearer token'])
                        ->withHeader('Content-Type', 'application/json')
                        ->withStatus(401);
                }

                $key = new Key(Config::getJwtKeyMaterial(), Config::getJwtAlgorithm());
                $tokenData = JWT::decode($token, $key);
                $now = (new \DateTime('now'))->format('Y-m-d H:i:s');

                if ($tokenData->expired_at < $now) {
                    return $this->renderer->json($this->response, ['error' => 'Token expired'])
                        ->withHeader('Content-Type', 'application/json')
                        ->withStatus(401);
                }

                $request = $request->withHeader('email', $tokenData->email);
            } else {
                return $this->renderer->json($this->response, ['error' => 'Unauthorized access'])
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(401);
            }
        } catch (\Exception $e) {
            return $this->renderer->json($this->response, ['error' => $e->getMessage()])
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(500);
        }

        return $handler->handle($request);
    }
}