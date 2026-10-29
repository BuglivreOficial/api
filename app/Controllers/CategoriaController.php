<?php
namespace App\Controllers;

use Core\Http\Request;
use Core\Http\Validation;

class CategoriaController {
    public function create(Request $request) { 
        (new Validation())->validate([
            'name' => 'required|min:3|max:150',
            "password" => [
                'rules' => 'required|email',
                'label' => 'senha'
            ]
        ], $request->all());
        $name = $request->input('name');
    }
}