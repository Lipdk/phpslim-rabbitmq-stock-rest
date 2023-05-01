<?php

declare(strict_types=1);

use App\Controllers\Auth;
use App\Controllers\HelloController;
use App\Controllers\StockController;
use App\Controllers\UserController;
use App\Middlewares\JwtAuth;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app)
{
    // Unprotected Routes
    $app->group('', function (Group $group) use ($app) {
        $app->get('/', HelloController::class . ':index');
        $app->get('/hello/{name}', HelloController::class . ':hello');

        // Auth route
        $app->post("/auth", Auth::class);

        // Register user
        $app->post("/user/create", UserController::class . ':create');
    });

    // Protected Routes
    $app->group('', function (Group $group) use ($app) {
        // Stock Information
        $app->get('/stock', StockController::class . ':quote')->add(JwtAuth::class);

        // Get History of Stock Requests
        $app->get('/history', StockController::class . ':history')->add(JwtAuth::class);

        $app->get('/bye/{name}', HelloController::class . ':bye')->add(JwtAuth::class);
    });
};
