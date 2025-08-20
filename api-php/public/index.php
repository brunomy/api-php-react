<?php
declare(strict_types=1);

require __DIR__ . '/../bootstrap.php';

use App\Router;
use App\Controllers\UserController;

cors();

$router = new Router();

$router->get('/api/health', fn() => json_response(['status' => 'ok']));
$router->get('/api/users', fn() => UserController::index());
$router->get('/api/users/{id}', fn($params) => UserController::show($params));
$router->post('/api/users', fn() => UserController::store());

$router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $_SERVER['REQUEST_URI'] ?? '/');
