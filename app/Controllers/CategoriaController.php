<?php
namespace App\Controllers;

use App\Models\Category;
use Core\Database\Mysql;
use Core\Http\Request;
use Core\Http\Response;
use Core\Http\Validation;

class CategoriaController
{
    private Validation $validation;
    private Response $response;

    public function __construct()
    {
        $this->validation = new Validation();
        $this->response = new Response();
     
    }
    public function create(Request $request)
    {
        if (
            !$this->validation->validate([
                'name' => [
                    'rules' => 'required|min:3|max:255',
                    'label' => 'nome'
                ]
            ], $request->all())
        ) {
            $this->response->json([
                'status' => false,
                'errors' => $this->validation->getErrors()
            ], 400);
        }
        $name = $request->input('name');

        try {
            $model = new Category(Mysql::getInstance()->getConnection());
            if(!$model->create($name)) {
                $this->response->json([
                    'status' => false,
                    'errors' => [
                        'name' => 'Categoria já existe'
                    ]
                ], 409);
            }
            $this->response->json([
                'status' => true,
                'message' => 'Categoria criada com sucesso!'
            ]);
        } catch (\Exception $e) {
        }
    }
}