<?php
namespace App\Controllers;

use Core\Helpers\Validation;
use Core\Http\Response;
use Core\Http\Request;

class CategoriaController
{
    private Response $resposta;
    private Request $request;
    private Validation $validation;
    public function __construct()
    {
        $this->resposta = new Response();
        $this->request = new Request();
        $this->validation = new Validation();
    }
    public function criar()
    {
        if (
            $this->validation->rules([
                'category' => 'required|min:3'
            ])
        ) {
            $this->resposta->json([
                'status' => false,
                'erros' => $this->validation->getErros()
            ], 400);
        }
        ;
        $categoria = $this->request->body('categoria');
        $this->resposta->json([
            'status' => true
        ]);
    }
}