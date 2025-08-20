<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Database;

final class UserController {
    public static function index(): void {
        $pdo = Database::pdo();
        $stmt = $pdo->query('SELECT id, name, email FROM users ORDER BY id DESC LIMIT 100');
        json_response(['data' => $stmt->fetchAll()]);
        return; // opcional
    }

    public static function show(array $params): void {
        $id = (int)($params['id'] ?? 0);
        $pdo = Database::pdo();
        $stmt = $pdo->prepare('SELECT id, name, email FROM users WHERE id = ?');
        $stmt->execute([$id]);
        $user = $stmt->fetch();

        if (!$user) {
            json_response(['error' => 'Not Found'], 404);
            return; // <— sem valor
        }

        json_response(['data' => $user]);
        return;
    }

    public static function store(): void {
        $body = read_json_body();
        $name  = trim((string)($body['name']  ?? ''));
        $email = trim((string)($body['email'] ?? ''));

        if ($name === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            json_response(['error' => 'Invalid payload'], 422);
            return; // <— sem valor
        }

        $pdo = Database::pdo();
        $stmt = $pdo->prepare('INSERT INTO users (name, email) VALUES (?, ?)');
        $stmt->execute([$name, $email]);

        json_response([
            'data' => [
                'id'    => (int)$pdo->lastInsertId(),
                'name'  => $name,
                'email' => $email,
            ]
        ], 201);
        return;
    }
}
