<?php

namespace Core\Http;

class Response
{
    public static function enviarJson(mixed $dados, int $status = 200, array $headers = []): never
    {
        // Centraliza a saída da API para manter respostas JSON consistentes.
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');

        foreach ($headers as $name => $value) {
            header($name . ': ' . $value);
        }

        echo json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
