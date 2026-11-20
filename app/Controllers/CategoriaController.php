<?php
namespace App\Controllers;

class CategoriaController extends BaseController
{
    public function criar()
    {
        if (
            $this->validation->rules([
                'category' => 'required|min:3'
            ])
        ) {
            $this->response->json([
                'status' => false,
                'erros' => $this->validation->getErros()
            ], 400);
        }
        $categoria = $this->request->body('categoria');
        $this->response->json([
            'status' => true
        ]);
    }
}