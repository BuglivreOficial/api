<?php

try {
    require dirname(__DIR__) . '/vendor/autoload.php';

    $route = new \Core\Routing\Router();

    $route->group('/api', function ($route) {
        require dirname(__DIR__) . '/routes/api.php';
    });
    require dirname(__DIR__) . '/routes/web.php';

    $route->start();
} catch (\Core\Routing\MethodNotAllowedException $e) {
    dump($e);
} catch (\Core\Routing\NotFoundException $e) {
    dump($e);
} catch (\Throwable $e) {
    dump($e);
}