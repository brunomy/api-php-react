<?php
declare(strict_types=1);

namespace App;

use PDO;
use PDOException;

final class Database {
    public static function pdo(): PDO {
        static $pdo = null;
        if ($pdo) return $pdo;

        $dsn  = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4',
            $_ENV['DB_HOST'] ?? '127.0.0.1',
            $_ENV['DB_NAME'] ?? 'test'
        );

        $user = $_ENV['DB_USER'] ?? 'root';
        $pass = $_ENV['DB_PASS'] ?? '';

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];

        try {
            $pdo = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            json_response(['error' => 'DB connection failed', 'detail' => $e->getMessage()], 500);
            exit;
        }
        return $pdo;
    }
}
