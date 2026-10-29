<?php

use App\Controllers\CategoriaController;
use App\Controllers\TesteController;

/**
 * @var \Core\Routing\Router $route
 */

$route->post('/category', [CategoriaController::class, 'create']);