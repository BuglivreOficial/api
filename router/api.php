<?php

use App\Controllers\AuthController;
use Core\Router\Routing;

// @var Routing $router

// Rotas de exemplo para validar o fluxo do roteador e os verbos HTTP suportados.
$router->get('/get/{id}', [AuthController::class, 'buscar']);
$router->post('/post', [AuthController::class, 'criar']);
$router->put('/put/{id}', [AuthController::class, 'atualizar']);
$router->patch('/patch/{id}', [AuthController::class, 'atualizarParcialmente']);
$router->delete('/delete/{id}', [AuthController::class, 'remover']);
