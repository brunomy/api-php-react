<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Database;
use PDO;
use DateTime;
use Exception;

final class AtividadeController {
	public static function createAtividade(array $params): void {
    $body = read_json_body();

    $id = (int)($body['id'] ?? 0);
    $id_ordem = (int)($body['id_ordem'] ?? 0);
    $id_conf_etapa = (int)($body['id_conf_etapa'] ?? 0);
    $id_departamento = (int)($body['id_departamento'] ?? 0);
    $etapa = trim((string)($body['etapa'] ?? ''));
    $id_conf_atividade = (int)($body['id_conf_atividade'] ?? 0);
    $atividade = trim((string)($body['atividade'] ?? ''));
    $id_equipe = (int)($body['id_equipe'] ?? null);
    $id_status = (int)($body['id_status'] ?? 0);
    $data = trim((string)($body['data'] ?? ''));

    $pdo = Database::pdo();
		
    $query = 'SELECT id FROM dp_etapas WHERE id_ordem = ? AND id_conf_etapa = ?';

    $stmt = $pdo->prepare($query);
    $stmt->execute([$id_ordem, $id_conf_etapa]);
    $find_etapa = $stmt->fetch(PDO::FETCH_ASSOC);

    if($find_etapa && isset($find_etapa['id'])) {
        $id_etapa = (int)$find_etapa['id'];
    } else {
        $query = 'INSERT INTO dp_etapas (id_ordem, id_conf_etapa, etapa) VALUES (?, ?, ?)';
        $stmt = $pdo->prepare($query);
        $stmt->execute([$id_ordem, $id_conf_etapa, $etapa]);
        $id_etapa = (int)$pdo->lastInsertId();
    }

		if(!$id) {
			$query = 'INSERT INTO dp_atividades (id_conf_atividade, id_departamento, id_ordem, id_etapa, id_equipe, id_status, etapa, atividade, data, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())';
			$stmt = $pdo->prepare($query);
			$stmt->execute([
					$id_conf_atividade, 
					$id_departamento,
					$id_ordem, 
					$id_etapa, 
					$id_equipe, 
					$id_status, 
					$etapa, 
					$atividade, 
					$data,
					0
			]);

    	$id_atividade = (int)$pdo->lastInsertId();
		} else {
			$query = 'UPDATE dp_atividades SET id_equipe = ?, data = ? WHERE id = ?';

			$stmt = $pdo->prepare($query);
			$stmt->execute([
					$id_equipe, 
					$data,
					$id
			]);

			$id_atividade = $id;
		}

		// Buscar todos os conf_checklists para esta atividade
		$query = 'SELECT id, titulo FROM dp_conf_checklists WHERE id_conf_atividade = ? AND deleted_at IS NULL';
		$stmt = $pdo->prepare($query);
		$stmt->execute([$id_conf_atividade]);
		$conf_checklists = $stmt->fetchAll();

		// Para cada conf_checklist, inserir um registro em dp_checklists
		foreach ($conf_checklists as $conf_checklist) {
			$id_conf_checklist = $conf_checklist['id'];
			$checklist = $conf_checklist['titulo'];

			// Verificar se o checklist já existe para evitar duplicatas
			$query_check = 'SELECT id FROM dp_checklists WHERE id_atividade = ? AND id_conf_checklist = ?';
			$stmt_check = $pdo->prepare($query_check);
			$stmt_check->execute([$id_atividade, $id_conf_checklist]);
			$exists = $stmt_check->fetch();

			// Se não existir, criar o checklist
			if (!$exists) {
				$query = 'INSERT INTO dp_checklists (id_atividade, id_conf_checklist, etapa, atividade, checklist, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())';
				$stmt = $pdo->prepare($query);
				$stmt->execute([
						$id_atividade,
						$id_conf_checklist,
						$etapa,
						$atividade,
						$checklist,
						0
				]);
			}
		}

		// Buscar id_remessa da ordem
		$query = 'SELECT id_remessa FROM dp_ordens WHERE id = ? AND deleted_at IS NULL';
		$stmt = $pdo->prepare($query);
		$stmt->execute([$id_ordem]);
		$ordem_data = $stmt->fetch(PDO::FETCH_ASSOC);

		if (!$ordem_data) {
			json_response(['error' => 'Ordem não encontrada'], 404);
			return;
		}

		$id_remessa = (int)$ordem_data['id_remessa'];

		// Buscar todos os conf_volumes para esta atividade
		$query = 'SELECT id, titulo FROM dp_conf_volumes WHERE id_conf_atividade = ? AND deleted_at IS NULL';
		$stmt = $pdo->prepare($query);
		$stmt->execute([$id_conf_atividade]);
		$conf_volumes = $stmt->fetchAll();

		// Para cada conf_volume, inserir um registro em dp_volumes
		foreach ($conf_volumes as $conf_volume) {
			$id_conf_volume = $conf_volume['id'];
			$volume = $conf_volume['titulo'];


			// Verificar se o volume já existe para evitar duplicatas
			$query_check = 'SELECT id FROM dp_volumes WHERE id_atividade = ? AND id_conf_volume = ?';
			$stmt_check = $pdo->prepare($query_check);
			$stmt_check->execute([$id_atividade, $id_conf_volume]);
			$exists = $stmt_check->fetch();

			if ($exists) {
				error_log("Debug - Volume já existe para atividade: " . $id_atividade . " e conf_volume: " . $id_conf_volume);
				continue;
			}

			// Se não existir, criar o volume
			try {
				$query = 'INSERT INTO dp_volumes (id_conf_volume, id_atividade, id_embalagem, id_remessa, etapa, atividade, volume, comprimento, largura, altura, peso, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())';
				$stmt = $pdo->prepare($query);
				$result = $stmt->execute([
					$id_conf_volume,
					$id_atividade,
					null,
					$id_remessa,
					$etapa,
					$atividade,
					$volume,
					0.0,
					0.0,
					0.0,
					0.0
				]);
			} catch (Exception $e) {
				error_log("Debug - Erro ao inserir volume: " . $e->getMessage());
			}
		}

		json_response(['data' => $id_atividade]);
		return;
	}

	public static function updateAtividade(array $params): void {
		$id = (int)($params['id'] ?? 0);
		$body = read_json_body();

    $id_equipe = (int)($body['id_equipe'] ?? null);
    $id_status = (int)($body['id_status'] ?? null);
    $data = trim((string)($body['data'] ?? ''));

		if ($id <= 0 || $id_equipe === null || $id_status === null || $data === '') {
			json_response(['error' => 'Payload inválido'], 422);
			return;
		}

		$pdo = Database::pdo();

		$query = 'UPDATE dp_atividades SET id_equipe = ?, id_status = ?, data = ? WHERE id = ? AND deleted_at IS NULL';
		$stmt = $pdo->prepare($query);
		$stmt->execute([$id_equipe, $id_status, $data, $id]);

		json_response([
			'message' => 'Atividade atualizada com sucesso',
			'data' => [
					'id' => $id,
					'id_equipe' => $id_equipe,
					'id_status' => $id_status,
					'data' => $data,
			]
		], 200);
	}

	public static function deleteAtividade(array $params): void {
		$id = (int)($params['id'] ?? 0);

		$pdo = Database::pdo();

		$stmt = $pdo->prepare('SELECT * FROM dp_atividades WHERE id = ? AND deleted_at IS NULL');
		$stmt->execute([$id]);
		$atividade = $stmt->fetch();

		if (!$atividade) {
			json_response(['error' => 'Not Found'], 404);
			return;
		}

		$stmt = $pdo->prepare('DELETE FROM dp_atividades WHERE id = ?');
		$stmt->execute([$id]);

		json_response(['message' => 'Atividade deleted successfully']);
		return;
	}

	public static function getAtividadesOrdem(array $params): void {
		$id_departamento = (int)($params['id_departamento'] ?? 0);
		$id = (int)($params['id'] ?? 0);

		$pdo = Database::pdo();
		$query = 
			'SELECT A.*, B.nome AS nome_equipe
			FROM dp_atividades A
			LEFT JOIN dp_equipes B ON A.id_equipe = B.id
			WHERE A.id_ordem = ? AND A.id_departamento = ? AND A.deleted_at IS NULL ORDER BY data ASC';

		$stmt = $pdo->prepare($query);
		$stmt->execute([$id, $id_departamento]);
		$atividades = $stmt->fetchAll();

		if (!$atividades) {
			json_response(['Not Found']);
			return;
		}

		json_response(['data' => $atividades]);
		return;
	}

	public static function getAtividadesProducao(array $params): void {
    $id = (int)($params['id'] ?? 0);

    $pdo = Database::pdo();

    //Buscar atividades do domingo desta semana para trás
    $query = 
			'SELECT A.*, B.nome AS nome_equipe
        FROM dp_atividades A
        LEFT JOIN dp_equipes B ON A.id_equipe = B.id
        WHERE A.id_departamento = ? 
        AND A.id_status > 0 
        AND A.deleted_at IS NULL 
        AND A.data <= DATE_SUB(DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) + 1 DAY), INTERVAL 1 DAY)
        ORDER BY A.data ASC';

    $stmt = $pdo->prepare($query);
    $stmt->execute([$id]);
    $atividades_atrasadas = $stmt->fetchAll();

		//Buscar atividades desta semana
		$query = 
			'SELECT A.*, B.nome AS nome_equipe
        FROM dp_atividades A
        LEFT JOIN dp_equipes B ON A.id_equipe = B.id
        WHERE A.id_departamento = ? 
        AND A.id_status > 0 
        AND A.deleted_at IS NULL 
        AND A.data >= DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) + 1 DAY)
        AND A.data <= DATE_ADD(DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) + 1 DAY), INTERVAL 6 DAY)
        ORDER BY A.data ASC';

    $stmt = $pdo->prepare($query);
    $stmt->execute([$id]);
    $atividades_da_semana = $stmt->fetchAll();

		//Atividades da próxima semana em diante
		$query = 
			'SELECT A.*, B.nome AS nome_equipe
        FROM dp_atividades A
        LEFT JOIN dp_equipes B ON A.id_equipe = B.id
        WHERE A.id_departamento = ? 
        AND A.id_status > 0 
        AND A.deleted_at IS NULL 
        AND A.data > DATE_ADD(DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) + 1 DAY), INTERVAL 6 DAY)
        ORDER BY A.data ASC';

    $stmt = $pdo->prepare($query);
    $stmt->execute([$id]);
    $atividades_futuras = $stmt->fetchAll();

		json_response(['data' => [
			"atrasadas" => $atividades_atrasadas,
			"da_semana" => $atividades_da_semana,
			"futuras" => $atividades_futuras
		]]);
    return;
	}
}
