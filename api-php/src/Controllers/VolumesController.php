<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Database;

final class VolumesController {
	public static function getVolumesOrdem(array $params): void {
		$id = (int)($params['id'] ?? 0);
		$id_departamento = (int)($params['id_departamento'] ?? 0);

		$pdo = Database::pdo();
        $query = 
            "SELECT 
                A.*, 
                B.id_status as atividade_status,
                (
                    SELECT COUNT(*) 
                    FROM dp_checklists ch 
                    WHERE ch.id_atividade = A.id_atividade AND ch.status >= 0
                ) AS checklist,
                (
                    SELECT COUNT(*) 
                    FROM dp_checklists ch 
                    WHERE ch.id_atividade = A.id_atividade AND ch.status = 1
                ) AS checklist_finalizado
            FROM dp_volumes A
            LEFT JOIN dp_atividades B ON A.id_atividade = B.id  
            WHERE B.id_ordem = ? AND B.id_departamento = ?
            ORDER BY B.data ASC";

		$stmt = $pdo->prepare($query);
		$stmt->execute([$id, $id_departamento]);

		$ordens = $stmt->fetchAll();

		if (!$ordens) {
			json_response(['error' => 'Not Found'], 404);
			return;
		}

		json_response(['data' => $ordens]);
		return;
	}

}
