<?php

use App\Controllers\CategoriaController;

/**
 * @var \Core\Http\Routing $rota
 */

$rota->post('/categoria/criar', [CategoriaController::class, 'criar']);
$rota->get('/categoria/listar', function() {
    echo 'kkkkkk';
});