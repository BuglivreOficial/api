<?php
namespace Core\Http;

class Response
{
    public function json(array $dados, int $status = 200) {
        header('Content-Type: application/json');
        http_response_code($status);
        echo json_encode($dados);
        exit;
    }
}