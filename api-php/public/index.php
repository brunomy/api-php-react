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
$router->get('/api/getUsersDepartamento/{idDepartamento}', fn($params) => UserController::getUsersDepartamento($params));

$router->get('/api/getUserEquipes/{idUser}/{idDepartamento}', fn($params) => UserController::getUserEquipes($params));
$router->post('/api/criarEquipe', fn($params) => UserController::createEquipe($params));
$router->put('/api/updateEquipe/{id}', fn($params) => UserController::updateEquipe($params));
$router->delete('/api/deletarEquipe/{idEquipe}', fn($params) => UserController::deleteEquipe($params));

$router->get('/api/getFuncionarios/{idEquipe}', fn($params) => UserController::getFuncionarios($params));
$router->post('/api/criarFuncionario', fn($params) => UserController::createFuncionario($params));
$router->delete('/api/deletarFuncionario/{idFuncionario}', fn($params) => UserController::deleteFuncionario($params));



//CONFIGURAÇÂO
$router->get('/api/configuracao/{id}', fn($params) => ConfController::getCategoriasDepartamento($params));

$router->get('/api/configuracao/buscarEtapas/{idDepartamento}/categoria/{idCategoria}/etapas', fn($params) => ConfController::getEtapas($params));
$router->post('/api/configuracao/criarEtapa', fn($params) => ConfController::createEtapa($params));
$router->put('/api/configuracao/etapa/{id}', fn($params) => ConfController::updateEtapa($params));
$router->delete('/api/configuracao/deletarEtapa/{idEtapa}', fn($params) => ConfController::deleteEtapa($params));

$router->get('/api/configuracao/buscarAtividades/{idConfEtapa}', fn($params) => ConfController::getAtividades($params));
$router->post('/api/configuracao/criarAtividade', fn($params) => ConfController::createAtividade($params));
$router->put('/api/configuracao/atividade/{id}', fn($params) => ConfController::updateAtividade($params));
$router->delete('/api/configuracao/deletarAtividade/{idAtividade}', fn($params) => ConfController::deleteAtividade($params));

$router->get('/api/configuracao/buscarChecklistVolumes/{idConfAtividade}', fn($params) => ConfController::getChecklistsVolumes($params));
$router->post('/api/configuracao/criarChecklist', fn($params) => ConfController::createChecklist($params));
$router->delete('/api/configuracao/deletarChecklist/{idChecklist}', fn($params) => ConfController::deleteChecklist($params));
$router->post('/api/configuracao/criarVolume', fn($params) => ConfController::createVolume($params));
$router->delete('/api/configuracao/deletarVolume/{idVolume}', fn($params) => ConfController::deleteVolume($params));






$router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $_SERVER['REQUEST_URI'] ?? '/');
