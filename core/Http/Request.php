<?php
namespace Core\Http;

class Request {
    public function body(string $key) {
        $json = json_decode(file_get_contents('php://input'), true);
        return $json[$key] ?? null;
    }
}