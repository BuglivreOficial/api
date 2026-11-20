<?php
namespace Core\Database;

use Core\Exceptions\MysqlException;
use PDO;
use PDOException;

class Mysql
{
    public function make()
    {
        $host = $_ENV['DB_HOST'] ?? 'localhost';
        $port = $_ENV['DB_PORT'] ?? '3306';
        $database = $_ENV['DB_DATABASE'] ?? 'buglivreoficial';
        $user = $_ENV['DB_USER'] ?? 'root';
        $pass = $_ENV['DB_PASS'] ?? '';
        try {
            $pdo = new PDO("mysql:host={$host};dbname={$database};charset=utf8", $user, $pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        } catch (PDOException $e) {
            throw new MysqlException($e);
            
        }
    }
}