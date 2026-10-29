<?php
namespace Core\Http;

class Response
{
    public function json(array $data, int $status_code = 200) {
        $data['details'] = [
            'id_request' => "teste ainda",
            'created_at' => date('Y-m-d H:i:s')
        ];
        header('Content-Type: application/json');
        http_response_code($status_code);
        echo json_encode($data);
        exit;
    }
}