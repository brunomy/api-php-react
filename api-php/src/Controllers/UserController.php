<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Database;

final class UserController {
    public static function login(array $params): void {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];

        $usuario = trim((string)($data['user'] ?? ''));
        $senha   = trim((string)($data['password'] ?? ''));

        if ($usuario === '' || $senha === '') {
            self::json_response(['error' => 'Invalid payload'], 422);
            return;
        }

        $pdo = Database::pdo();
        $stmt = $pdo->prepare("SELECT * FROM dp_users WHERE usuario = :usuario AND stats = 1 AND deleted_at IS NULL LIMIT 1");
        $stmt->execute(['usuario' => $usuario]);
        $userDb = $stmt->fetch();

        if (!$userDb || !password_verify($senha, $userDb['senha'])) {
            self::json_response(['error' => 'Usuário ou senha inválidos'], 401);
            return;
        }

        // Aqui você pode usar JWT, por enquanto só devolve um token fake
        $token = base64_encode(random_bytes(32));

        self::json_response([
            'success' => true,
            'user' => [
                'id' => $userDb['id'],
                'nome' => $userDb['nome'],
                'permissao' => $userDb['permissao'],
            ],
            'token' => $token
        ]);
    }

    public static function getDepartamentos(array $params): void {
		$idUser = (int)($params['idUser'] ?? 0);

		$pdo = Database::pdo();
        $query = 
            'SELECT A.*
                FROM dp_departamentos A
                LEFT JOIN dp_user_departamento B ON A.id = B.id_departamento
                WHERE B.id_user = ? AND A.stats = 1 AND A.deleted_at IS NULL
                ORDER BY A.ordem ASC';

		$stmt = $pdo->prepare($query);
		$stmt->execute([$idUser]);

		$departamentos = $stmt->fetchAll();
		
		if (!$departamentos) {
			json_response(['error' => 'Not Found'], 404);
			return;
		}

		json_response(['data' => $departamentos]);
		return;
	}

    private static function json_response(array $data, int $status = 200): void {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
    }
}


