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

    public static function getUsersDepartamento(array $params): void {
		$idDepartamento = (int)($params['idDepartamento'] ?? 0);

		$pdo = Database::pdo();

        $query = 
            'SELECT 
                A.id, 
                A.nome, 
                A.descricao, 
                A.email, 
                A.telefone, 
                A.permissao, 
                A.usuario,
                COUNT(DISTINCT C.id) AS equipes_count,
                COUNT(DISTINCT D.id) AS funcionarios_count
            FROM dp_users A
            LEFT JOIN dp_user_departamento B ON A.id = B.id_user
            LEFT JOIN dp_equipes C ON A.id = C.id_user AND B.id_departamento = C.id_departamento AND C.deleted_at IS NULL
            LEFT JOIN dp_funcionarios D ON C.id = D.id_equipe AND D.deleted_at IS NULL
            WHERE B.id_departamento = ? 
            AND A.stats = 1 
            AND A.deleted_at IS NULL 
            AND A.permissao != "gerente"
            GROUP BY A.id, A.nome, A.descricao, A.email, A.telefone, A.permissao, A.usuario
            ORDER BY A.nome ASC;';

		$stmt = $pdo->prepare($query);
		$stmt->execute([$idDepartamento]);

		$users = $stmt->fetchAll();

		if (!$users) {
			json_response(['error' => 'Not Found'], 404);
			return;
		}

		json_response(['data' => $users]);
		return;
	}

    public static function getUserEquipes(array $params): void {
		$idUser = (int)($params['idUser'] ?? 0);
		$idDepartamento = (int)($params['idDepartamento'] ?? 0);

		$pdo = Database::pdo();

        $query = 
            'SELECT 
                A.*,
                COUNT(DISTINCT B.id) AS funcionarios_count
            FROM dp_equipes A
            LEFT JOIN dp_funcionarios B ON A.id = B.id_equipe AND B.deleted_at IS NULL
            WHERE A.id_user = ? AND A.id_departamento = ? AND A.deleted_at IS NULL
            GROUP BY A.id
            ORDER BY A.nome ASC;';

		$stmt = $pdo->prepare($query);
		$stmt->execute([$idUser, $idDepartamento]);

		$equipes = $stmt->fetchAll();

        $query = 
            'SELECT 
                id, nome
            FROM dp_users 
            WHERE id = ? AND stats = 1 AND deleted_at IS NULL
            ORDER BY nome ASC;';

		$stmt = $pdo->prepare($query);
		$stmt->execute([$idUser]);

		$user = $stmt->fetch();

		if (!$equipes) {
    		json_response(['data' => ['user' => $user, 'equipes' => []]]);
			return;
		}

		json_response(['data' => ['user' => $user, 'equipes' => $equipes]]);
		return;
	}

    public static function createEquipe(array $params): void {
		$body = read_json_body();
		$id_user  = trim((string)($body['id_user']  ?? ''));
		$id_departamento  = trim((string)($body['id_departamento']  ?? ''));
		$nome  = trim((string)($body['nome']  ?? ''));
		$descricao  = trim((string)($body['descricao']  ?? ''));

		if ($nome === '' || $descricao === '' || $id_user === '' || $id_departamento === '') {
			self::json_response(['error' => 'Invalid payload'], 422);
			return;
		}

		$pdo = Database::pdo();

		$query = 'INSERT INTO dp_equipes (id_user, id_departamento, nome, descricao) VALUES (?, ?, ?, ?)';

		$stmt = $pdo->prepare($query);
		$stmt->execute([$id_user, $id_departamento, $nome, $descricao]);

		self::json_response([
			'data' => [
				'id'    => (int)$pdo->lastInsertId(),
				'id_user'  => $id_user,
				'id_departamento' => $id_departamento,
				'nome' => $nome,
				'descricao' => $descricao,
			]
		], 201);
		return;
	}

    public static function updateEquipe(array $params): void {
		$id = (int)($params['id'] ?? 0);
		$body = read_json_body();

		$nome = trim((string)($body['titulo'] ?? ''));

		if ($id <= 0 || $nome === '') {
				json_response(['error' => 'Payload inválido'], 422);
				return;
		}

		$pdo = Database::pdo();

		$query = 'UPDATE dp_equipes SET nome = ? WHERE id = ? AND deleted_at IS NULL';
		$stmt = $pdo->prepare($query);
		$stmt->execute([$nome, $id]);

		json_response([
				'message' => 'Equipe atualizada com sucesso',
				'data' => [
						'id' => $id,
						'nome' => $nome,
				]
		], 200);
	}

    public static function deleteEquipe(array $params): void {
		$id = (int)($params['idEquipe'] ?? 0);

		$pdo = Database::pdo();

		// Verifica se a equipe existe
		$stmt = $pdo->prepare('SELECT * FROM dp_equipes WHERE id = ? AND deleted_at IS NULL');
		$stmt->execute([$id]);
		$equipe = $stmt->fetch();

		if (!$equipe) {
			json_response(['error' => 'Not Found'], 404);
			return;
		}

		// Marca a equipe como deletada
		$stmt = $pdo->prepare('UPDATE dp_equipes SET deleted_at = NOW() WHERE id = ?');
		$stmt->execute([$id]);

		json_response(['message' => 'Equipe deleted successfully']);
		return;
	}

    public static function getFuncionarios(array $params): void {
		$idEquipe = (int)($params['idEquipe'] ?? 0);

		$pdo = Database::pdo();

        $query = 
            'SELECT 
                *
            FROM dp_funcionarios
            WHERE id_equipe = ? AND deleted_at IS NULL
            ORDER BY nome ASC;';

		$stmt = $pdo->prepare($query);
		$stmt->execute([$idEquipe]);

		$funcionarios = $stmt->fetchAll();

        $query = 
            'SELECT 
                A.nome, A.id
            FROM dp_users A
            LEFT JOIN dp_equipes B ON A.id = B.id_user
            WHERE B.id = ?;';

		$stmt = $pdo->prepare($query);
		$stmt->execute([$idEquipe]);

		$user = $stmt->fetch();

        $query = 
            'SELECT 
                nome, id
            FROM dp_equipes
            WHERE id = ?;';

		$stmt = $pdo->prepare($query);
		$stmt->execute([$idEquipe]);

		$equipe = $stmt->fetch();

		if (!$funcionarios) {
    		json_response(['data' => [], 'user' => $user, 'equipe' => $equipe]);
			return;
		}

		json_response(['data' => $funcionarios, 'user' => $user, 'equipe' => $equipe]);
		return;
	}

    public static function createFuncionario(array $params): void {
		$body = read_json_body();
		$id_equipe  = trim((string)($body['id_equipe']  ?? ''));
		$nome  = trim((string)($body['nome']  ?? ''));
		$funcao  = trim((string)($body['funcao']  ?? ''));
		$usuario  = trim((string)($body['usuario']  ?? ''));
		$senha  = trim((string)($body['senha']  ?? ''));
		$codigo  = trim((string)($body['codigo']  ?? ''));

		if ($nome === '' || $funcao === '' || $senha === '' || $codigo === '') {
			json_response(['error' => 'Invalid payload'], 422);
			return;
		}

		$pdo = Database::pdo();

		$query = 'INSERT INTO dp_funcionarios (id_equipe, nome, funcao, usuario, senha, codigo) VALUES (?, ?, ?, ?, ?, ?)';

		$stmt = $pdo->prepare($query);
		$stmt->execute([$id_equipe, $nome, $funcao, $usuario, $senha, $codigo]);

		json_response([
			'data' => [
				'id'    => (int)$pdo->lastInsertId(),
				'id_equipe'  => $id_equipe,
				'nome' => $nome,
				'funcao' => $funcao,
				'usuario' => $usuario,
				'senha' => $senha,
				'codigo' => $codigo,
			]
		], 201);
		return;
	}

    public static function deleteFuncionario(array $params): void {
		$id = (int)($params['idFuncionario'] ?? 0);

		$pdo = Database::pdo();

		// Verifica se o funcionário existe
		$stmt = $pdo->prepare('SELECT * FROM dp_funcionarios WHERE id = ? AND deleted_at IS NULL');
		$stmt->execute([$id]);
		$funcionario = $stmt->fetch();

		if (!$funcionario) {
			json_response(['error' => 'Not Found'], 404);
			return;
		}

		// Marca o funcionário como deletado
		$stmt = $pdo->prepare('UPDATE dp_funcionarios SET deleted_at = NOW() WHERE id = ?');
		$stmt->execute([$id]);

		json_response(['message' => 'Funcionário deleted successfully']);
		return;
	}

    private static function json_response(array $data, int $status = 200): void {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
    }
}
