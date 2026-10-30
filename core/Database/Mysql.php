<?php 

namespace Core\Database;

use PDO;
use PDOException;
use Exception;

class Mysql 
{
    private static ?self $instance = null;
    private ?PDO $connection = null;

    // Construtor privado para o Singleton
    private function __construct() 
    {
        // Busca as credenciais de variáveis de ambiente com fallbacks seguros
        $host = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: '127.0.0.1';
        $port = $_ENV['DB_PORT'] ?? getenv('DB_PORT') ?: '3306';
        $dbname = $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: 'meu_banco';
        $user = $_ENV['DB_USER'] ?? getenv('DB_USER') ?: 'root';
        $pass = $_ENV['DB_PASS'] ?? getenv('DB_PASS') ?: '';
        $charset = $_ENV['DB_CHARSET'] ?? getenv('DB_CHARSET') ?: 'utf8mb4';

        $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset={$charset}";

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Transforma erros SQL em Exceções PHP
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Retorna dados como array associativo
            PDO::ATTR_EMULATE_PREPARES   => false,                  // Desativa emulação para maior segurança contra SQL Injection
        ];

        try {
            $this->connection = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            throw new Exception("Erro na conexão com o banco de dados: " . $e->getMessage());
        }
    }

    private function __clone() {}

    public function __wakeup() 
    {
        throw new Exception("Cannot unserialize a singleton.");
    }

    // Retorna a instância da classe Singleton
    public static function getInstance(): self 
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    // Retorna o objeto PDO ativo para executar as queries
    public function getConnection(): PDO 
    {
        return $this->connection;
    }
}
