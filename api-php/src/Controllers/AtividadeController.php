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
			$query = 'INSERT INTO dp_atividades (id_conf_atividade, id_ordem, id_etapa, id_equipe, id_status, etapa, atividade, data, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())';
			$stmt = $pdo->prepare($query);
			$stmt->execute([
					$id_conf_atividade, 
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
		$id = (int)($params['id'] ?? 0);

		$pdo = Database::pdo();
		$query = 'SELECT * FROM dp_atividades WHERE id_ordem = ? AND deleted_at IS NULL';

		$stmt = $pdo->prepare($query);
		$stmt->execute([$id]);
		$atividades = $stmt->fetchAll();

		if (!$atividades) {
			json_response(['Not Found']);
			return;
		}

		json_response(['data' => $atividades]);
		return;
	}

}
