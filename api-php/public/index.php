<?php
declare(strict_types=1);

require __DIR__ . '/../bootstrap.php';

use App\Router;
use App\Controllers\UserController;
use App\Controllers\OrderController;
use App\Controllers\ConfController;

cors();

$router = new Router();

$router->get('/api/ordens', fn() => OrderController::index());
$router->get('/api/ordem/{id}', fn($params) => OrderController::show($params));


//USER
$router->post('/api/login', fn($params) => UserController::login($params));
$router->get('/api/getDepartamentos/{idUser}', fn($params) => UserController::getDepartamentos($params));



//CONFIGURAÇÂO
$router->get('/api/configuracao/{id}', fn($params) => ConfController::getCategoriasDepartamento($params));

$router->get('/api/configuracao/buscarEtapas/{idDepartamento}/categoria/{idCategoria}/etapas', fn($params) => ConfController::getEtapas($params));
$router->post('/api/configuracao/criarEtapa', fn($params) => ConfController::createEtapa($params));
$router->put('/api/configuracao/etapa/{id}', fn($params) => ConfController::updateEtapa($params));
$router->delete('/api/configuracao/deletarEtapa/{idEtapa}', fn($params) => ConfController::deleteEtapa($params));

$router->get('/api/configuracao/buscarAtividades/{idConfEtapa}', fn($params) => ConfController::getAtividades($params));
$router->post('/api/configuracao/criarAtividade', fn($params) => ConfController::createAtividade($params));
$router->delete('/api/configuracao/deletarAtividade/{idAtividade}', fn($params) => ConfController::deleteAtividade($params));







$router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $_SERVER['REQUEST_URI'] ?? '/');
