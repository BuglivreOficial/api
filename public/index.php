<?php

use Core\Helpers\Slave;

require dirname(__DIR__) . '/vendor/autoload.php';

define('DIR', dirname(__DIR__));

try {

    $slave = new Slave();

    if ($slave->inProduction()) {
        $slave->envProduction();
    } else {
        $slave->envDevelopment();
    }

    dump($_ENV['DB_HOST']);

    $rota = new \Core\Http\Routing();

    $db = new \Core\Database\Mysql()->make();

    $rota->grupo('/api', function ($rota) {
        require dirname(__DIR__) . '/routes/api.php';
    });

    $rota->executar();
} catch (Throwable $e) {
    dump($e, $_SERVER);
}