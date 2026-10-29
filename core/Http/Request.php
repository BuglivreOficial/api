<?php
namespace Core\Http;

use JsonException;

class Request
{
    public function input(string $key)
    {
        try {
            $json = json_decode(file_get_contents('php://input'), true, flags: JSON_THROW_ON_ERROR);
            return $json[$key];
        } catch (JsonException $th) {
            (new Response())->json([
                'status' => false,
                'message' => 'O servidor não pode ou não irá processar a solicitação devido a algo que é percebido como um erro do cliente (por exemplo, sintaxe de solicitação malformada).' 
            ], 400);
        }
    }

    public function all() {
        try {
            $json = json_decode(file_get_contents('php://input'), true, flags: JSON_THROW_ON_ERROR);
            return $json;
        } catch (JsonException $th) {
            (new Response())->json([
                'status' => false,
                'message' => 'O servidor não pode ou não irá processar a solicitação devido a algo que é percebido como um erro do cliente (por exemplo, sintaxe de solicitação malformada).' 
            ], 400);
        }
    }
}