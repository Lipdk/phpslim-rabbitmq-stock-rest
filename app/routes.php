<?php

declare(strict_types=1);

use App\Controllers\HelloController;
use App\Controllers\StockController;
use Slim\App;
use App\Models\Bootstrap;
use App\Models\User;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

return function (App $app) {
//    $container = $app->getContainer();
//    Bootstrap::load($container);

    // unprotected routes
    $app->get('/hello/{name}', HelloController::class . ':hello');

    // protected routes
    $app->get('/bye/{name}', HelloController::class . ':bye');

    $app->get('/stock', StockController::class . ':quote');

    $app->get('/users', function (Request $request, Response $response) {
        $users = User::all()->toArray();
        $response->getbody()->write(json_encode($users));
        return $response;
    });

//    $app->group('/users', function (Group $group) use ($container) {
//        $group->get('', ListUsersAction::class);
//        $group->get('/{id}', ViewUserAction::class);
//    });
};
