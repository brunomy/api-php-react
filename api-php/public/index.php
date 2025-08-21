<?php
declare(strict_types=1);

require __DIR__ . '/../bootstrap.php';

use App\Router;
use App\Controllers\UserController;
use App\Controllers\OrderController;
use App\Controllers\ConfController;

cors();

$router = new Router();
/*
  $router->get('/api/health', fn() => json_response(['status' => 'ok']));
  $router->get('/api/users', fn() => UserController::index());
  $router->get('/api/users/{id}', fn($params) => UserController::show($params));
  $router->post('/api/users', fn() => UserController::store());
*/

$router->get('/api/ordens', fn() => OrderController::index());
$router->get('/api/ordem/{id}', fn($params) => OrderController::show($params));



//CONFIGURAÇÂO
$router->get('/api/configuracao/{id}', fn($params) => ConfController::getCategoriasDepartamento($params));

$router->put('/api/configuracao/criarEtapa', fn($params) => ConfController::createEtapa($params));
$router->get('/api/configuracao/buscarEtapas/{idDepartamento}/{idCategoria}', fn($params) => ConfController::getEtapas($params));
$router->delete('/api/configuracao/deletarEtapa/{idEtapa}', fn($params) => ConfController::deleteEtapa($params));

$router->put('/api/configuracao/criarAtividade', fn($params) => ConfController::createAtividade($params));
$router->get('/api/configuracao/buscarAtividades/{idConfEtapa}', fn($params) => ConfController::getAtividades($params));
$router->delete('/api/configuracao/deletarAtividade/{idAtividade}', fn($params) => ConfController::deleteAtividade($params));

$router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $_SERVER['REQUEST_URI'] ?? '/');
