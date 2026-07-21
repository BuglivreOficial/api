<?php

require dirname(__DIR__) . '/vendor/autoload.php';

use Core\Router\Routing;

// Front controller: toda requisição HTTP entra por este arquivo.
$router = new Routing();

// Carrega o mapa de rotas antes de iniciar o despacho da requisição atual.
require dirname(__DIR__) . '/router/api.php';

$router->despachar();
