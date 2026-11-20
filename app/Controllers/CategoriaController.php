<?php
namespace App\Controllers;

use PDO;
use PDOException;

class CategoriaController extends BaseController
{
    public function criar(PDO $db)
    {
        if (
            $this->validation->rules([
                'name' => 'required|min:3'
            ])
        ) {
            $this->response->json([
                'status' => false,
                'erros' => $this->validation->getErros()
            ], 400);
        }
        $name = $this->request->body('name');
        $slug = $this->pkg->slug($name);
        try {
            $sql = "INSERT INTO category (name, slug) VALUES (:name, :slug)";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':name' => $name,
                ':slug' => $slug
            ]);

            $this->response->json([
                'status' => true,
                'message' => 'Categoria cadastrada com sucesso.'
            ]);
        } catch (PDOException $e) {
            if ($e->getCode() == '23000') {
                $this->response->json([
                    'status' => false,
                    'message' => 'Categoria já está cadastrada.'
                ], 409);
            }
            $this->response->json([
                'status' => false,
                'message' => 'O servidor não conseguiu concluir sua solicitação. Tente novamente mais tarde.'
            ], 500);
        }
    }
    public function list(PDO $db)
    {
        try {
            $sql = "SELECT id, name, slug FROM category WHER deleted_at IS NULL ORDER BY name ASC";
            $stmt = $db->query($sql);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $this->response->json([
                'status' => true,
                'data' => $data
            ], 200);
        } catch (PDOException $e) {
            $this->response->json([
                'status' => false,
                'message' => 'O servidor não conseguiu concluir sua solicitação. Tente novamente mais tarde.'
            ], 500);
        }
    }

    /**
     * Summary of delete
     * @param PDO $db
     * @param mixed $id
     * @return void
     */
    public function delete(PDO $db, $id)
    {
        try {
            $sql = 'UPDATE category 
                SET deleted_at = NOW() 
                WHERE id = :id AND deleted_at IS NULL';

            $stmt = $db->prepare($sql);
            $stmt->execute(['id' => $id]);

            // Retorna true apenas se o item existia e ainda não estava deletado
            if ($stmt->rowCount() > 0) {
                $this->response->json([
                    'status' => true,
                    'message' => 'Categoria deletada com sucesso.'
                ], 200);
            } else {
                $this->response->json([
                    'status' => false,
                    'message' => 'Categoria não encontrada ou já deletada.'
                ], 404);
            }
        } catch (PDOException $e) {
            $this->response->json([
                'status' => false,
                'message' => 'O servidor não conseguiu concluir sua solicitação. Tente novamente mais tarde.'
            ], 500);
        }
    }

    public function trash(PDO $db)
    {
        try {
            $sql = "SELECT id, name, slug FROM category WHERE deleted_at IS NOT NULL ORDER BY name ASC";
            $stmt = $db->query($sql);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $this->response->json([
                'status' => true,
                'data' => $data
            ], 200);
        } catch (PDOException $e) {
            dump($e);
            $this->response->json([
                'status' => false,
                'message' => 'O servidor não conseguiu concluir sua solicitação. Tente novamente mais tarde.'
            ], 500);
        }
    }

    public function restore(PDO $db, $id)
    {
        try {
            $sql = 'UPDATE category 
                SET deleted_at = NULL 
                WHERE id = :id AND deleted_at IS NOT NULL';

            $stmt = $db->prepare($sql);
            $stmt->execute(['id' => $id]);

            // Retorna true apenas se o item existia e estava deletado
            if ($stmt->rowCount() > 0) {
                $this->response->json([
                    'status' => true,
                    'message' => 'Categoria restaurada com sucesso.'
                ], 200);
            } else {
                $this->response->json([
                    'status' => false,
                    'message' => 'Categoria não encontrada ou não está deletada.'
                ], 404);
            }
        } catch (PDOException $e) {
            $this->response->json([
                'status' => false,
                'message' => 'O servidor não conseguiu concluir sua solicitação. Tente novamente mais tarde.'
            ], 500);
        }
    }

    public function forceDelete(PDO $db, $id)
    {
        try {
            $sql = 'DELETE FROM category 
                WHERE id = :id AND deleted_at IS NOT NULL';

            $stmt = $db->prepare($sql);
            $stmt->execute(['id' => $id]);

            // Retorna true apenas se o item existia e estava deletado
            if ($stmt->rowCount() > 0) {
                $this->response->json([
                    'status' => true,
                    'message' => 'Categoria deletada permanentemente com sucesso.'
                ], 200);
            } else {
                $this->response->json([
                    'status' => false,
                    'message' => 'Categoria não encontrada ou não está deletada.'
                ], 404);
            }
        } catch (PDOException $e) {
            $this->response->json([
                'status' => false,
                'message' => 'O servidor não conseguiu concluir sua solicitação. Tente novamente mais tarde.'
            ], 500);
        }
    }
}