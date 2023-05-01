<?php
declare(strict_types=1);

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class HelloController
{
    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function index(Request $request, Response $response): Response
    {
        $response->getBody()->write("Up and running!");
        return $response;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function hello(Request $request, Response $response, array $args): Response
    {
        $name = $args['name'];
        $body = "Hello, $name";

        $response->getBody()->write($body);

        return $response;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function bye(Request $request, Response $response, array $args): Response
    {
        $name = $args['name'];
        $body = "Bye, $name";

        $response->getBody()->write($body);

        return $response;
    }
}
