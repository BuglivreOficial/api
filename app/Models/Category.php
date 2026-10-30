<?php
namespace App\Models;

use InvalidArgumentException;
use PDO;
use PDOException;

class Category
{
    private const NAME_MAX_LENGTH = 255;
    private const MYSQL_DUPLICATE_KEY = 1062;

    private PDO $db;

    public function __construct(PDO $pdo)
    {
        $this->db = $pdo;
    }

    /**
     * Cria uma categoria. Retorna false se já existir uma ativa com o mesmo nome.
     *
     * @throws InvalidArgumentException se o nome for inválido
     */
    public function create(string $name): bool
    {
        $name = $this->normalizeName($name);

        return $this->execute(
            "INSERT INTO category (name) VALUES (:name)",
            [':name' => $name]
        );
    }

    /**
     * Retorna todas as categorias ativas (não deletadas).
     */
    public function getAll(): array
    {
        $stmt = $this->db->query(
            "SELECT id, name, created_at, updated_at
               FROM category
              WHERE deleted_at IS NULL
           ORDER BY name ASC"
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca uma categoria ativa pelo seu ID.
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT id, name, created_at, updated_at
               FROM category
              WHERE id = :id AND deleted_at IS NULL"
        );
        $stmt->execute([':id' => $id]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Atualiza o nome de uma categoria ativa. Retorna false se o ID não existir
     * ou se o novo nome já estiver em uso por outra categoria ativa.
     *
     * @throws InvalidArgumentException se o nome for inválido
     */
    public function update(int $id, string $newName): bool
    {
        $newName = $this->normalizeName($newName);

        return $this->execute(
            "UPDATE category SET name = :name WHERE id = :id AND deleted_at IS NULL",
            [':name' => $newName, ':id' => $id]
        );
    }

    /**
     * Exclusão lógica: grava a data/hora em deleted_at.
     * Retorna false se o ID não existir ou já estiver excluído.
     */
    public function delete(int $id): bool
    {
        return $this->execute(
            "UPDATE category SET deleted_at = NOW() WHERE id = :id AND deleted_at IS NULL",
            [':id' => $id]
        );
    }

    /**
     * Executa uma escrita e informa se exatamente uma linha foi afetada.
     * Violação de unicidade vira false; qualquer outro erro é propagado.
     */
    private function execute(string $sql, array $params): bool
    {
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            return $stmt->rowCount() === 1;
        } catch (PDOException $e) {
            if (($e->errorInfo[1] ?? null) === self::MYSQL_DUPLICATE_KEY) {
                return false;
            }

            throw $e;
        }
    }

    /**
     * Remove espaços das pontas e valida o tamanho e o conteúdo do nome.
     */
    private function normalizeName(string $name): string
    {
        $name = trim($name);

        if ($name === '') {
            throw new InvalidArgumentException('O nome da categoria não pode ser vazio.');
        }

        if (mb_strlen($name) > self::NAME_MAX_LENGTH) {
            throw new InvalidArgumentException('O nome da categoria excede ' . self::NAME_MAX_LENGTH . ' caracteres.');
        }

        return $name;
    }
}