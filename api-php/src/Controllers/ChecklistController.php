<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Database;
use PDO;
use DateTime;
use Exception;

final class ChecklistController {
	public static function getChecklistOrdem(array $params): void {
		$id = (int)($params['id'] ?? 0);

		$pdo = Database::pdo();
		$query = 
			'SELECT A.*, B.id_status as status_atividade, C.nome AS nome_equipe
			FROM dp_checklists A
			LEFT JOIN dp_atividades B ON A.id_atividade = B.id
			LEFT JOIN dp_equipes C ON B.id_equipe = C.id
			WHERE B.id_ordem = ? ORDER BY B.data ASC';

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
