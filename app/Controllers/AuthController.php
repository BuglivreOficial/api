<?php

namespace App\Controllers;

class AuthController
{
    // Handlers simples para demonstrar o retorno JSON por verbo HTTP.
    public function buscar(string $id): array
    {
        return [
            'metodo' => 'GET',
            'recurso' => 'auth',
            'id' => $id,
        ];
    }

    public function criar(): array
    {
        return [
            'metodo' => 'POST',
            'recurso' => 'auth',
            'mensagem' => 'Recurso criado com sucesso.',
        ];
    }

    public function atualizar(string $id): array
    {
        return [
            'metodo' => 'PUT',
            'recurso' => 'auth',
            'id' => $id,
            'mensagem' => 'Recurso atualizado com sucesso.',
        ];
    }

    public function atualizarParcialmente(string $id): array
    {
        return [
            'metodo' => 'PATCH',
            'recurso' => 'auth',
            'id' => $id,
            'mensagem' => 'Recurso alterado parcialmente com sucesso.',
        ];
    }

    public function remover(string $id): array
    {
        return [
            'metodo' => 'DELETE',
            'recurso' => 'auth',
            'id' => $id,
            'mensagem' => 'Recurso removido com sucesso.',
        ];
    }
}
