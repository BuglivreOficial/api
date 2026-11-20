<?php
namespace Core\Helpers;

use Exception;

class Slave
{
    public function inProduction()
    {
        return !$_SERVER['REMOTE_ADDR'] == '127.0.0.1';
    }
    public function envProduction()
    {
        if (!file_exists(DIR . '/.env')) {
            throw new Exception("o arquivo de configuração de variáveis de ambiente não existe.");
        }
        $vars = parse_ini_file(DIR . '/.env');
        if ($vars === false) {
            throw new Exception("Error a gerar variaveis de ambiente");
        }
        foreach ($vars as $key => $value) {
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
    public function envDevelopment()
    {
        if (!file_exists(DIR . '/.env.development')) {
            throw new Exception("o arquivo de configuração de variáveis de ambiente não existe.");
        }
        $vars = parse_ini_file(DIR . '/.env.development');
        if ($vars === false) {
            throw new Exception("Error a gerar variaveis de ambiente");
        }
        foreach ($vars as $key => $value) {
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
}