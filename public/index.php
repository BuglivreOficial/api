<?php

try {
    require dirname(__DIR__) . '/vendor/autoload.php';

    $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
    $dotenv->load();

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
} catch (\Dotenv\Exception\InvalidPathException $e) {
    dump($e->getMessage());
} catch (\Throwable $e) {
    dump($e);
}