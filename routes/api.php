<?php

use App\Controllers\CategoriaController;

/**
 * @var \Core\Http\Routing $route
 */

$route->get('/category', [CategoriaController::class, 'list']);
$route->post('/categoria/criar', [CategoriaController::class, 'criar']);

// Rotas responsáveis por deletar, listar, restaurar e deletar permanentemente categorias
$route->get('/category/trash', [CategoriaController::class, 'trash']);
$route->delete('/category/{id:int}', [CategoriaController::class, 'delete']);
$route->patch('/category/{id:int}/restore', [CategoriaController::class, 'restore']);
$route->delete('/category/{id:int}/force-delete', [CategoriaController::class, 'forceDelete']);