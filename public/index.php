<?php
require dirname(__DIR__) . '/vendor/autoload.php';

use Core\Helpers\Slave;
use Core\Http\Response;


define('DIR', dirname(__DIR__));

try {

    $slave = new Slave();

    if ($slave->inProduction()) {
        $slave->envProduction();
    } else {
        $slave->envDevelopment();
    }

    $route = new \Core\Http\Routing();

    $db = new \Core\Database\Mysql()->make();

    $route->group('/api', function ($route) {
        require dirname(__DIR__) . '/routes/api.php';
    });

    $route->start($db);
} catch (\Core\Exceptions\MysqlException $e) {
    (new Response())->json([
        'status' => false,
        'message' => 'O servidor não conseguiu concluir sua solicitação. Tente novamente mais tarde.'
    ], 500);
} catch (Throwable $e) {
    dump('index.php', $e);
}