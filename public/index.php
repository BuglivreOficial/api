<?php

require dirname(__DIR__) . '/vendor/autoload.php';

try {
    if (!file_exists(dirname(__DIR__) . '/.env')) {
        throw new Exception("o arquivo de configuração de variáveis de ambiente não existe.");
    }
    $env = parse_ini_file(dirname(__DIR__) . '/.env');
    if ($env === false) {
        throw new Exception("Error a gerar variaveis de ambiente");
    }

    $rota = new \Core\Http\Routing();

    $rota->grupo('/api', function ($rota) {
        require dirname(__DIR__) . '/routes/api.php';
    });

    

    $rota->executar();
} catch (Throwable $e) {
    dump($e);
}