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
		$id_departamento = (int)($params['id_departamento'] ?? 0);

		$pdo = Database::pdo();
		$query = 
			'SELECT A.*, B.id_status as status_atividade, C.nome AS nome_equipe, D.id_conf_etapa as id_conf_etapa
			FROM dp_checklists A
			LEFT JOIN dp_atividades B ON A.id_atividade = B.id
			LEFT JOIN dp_equipes C ON B.id_equipe = C.id
			LEFT JOIN dp_etapas D ON B.id_etapa = D.id
			WHERE B.id_ordem = ? AND B.id_departamento = ? ORDER BY D.id_conf_etapa ASC';

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

	public static function getOrdensChecklist(array $params): void {
    $id = (int)($params['id'] ?? 0);

    $pdo = Database::pdo();
    $query = 
        'SELECT A.id, A.id_pedido, A.nome_produto,
				B.id_status, C.titulo as nome_remessa,
        (
            SELECT COUNT(ch.id) 
            FROM dp_checklists ch 
            INNER JOIN dp_atividades at ON ch.id_atividade = at.id 
            WHERE at.id_ordem = A.id AND at.id_departamento = ? AND ch.status >= 0
        ) AS checklists,
				(
					SELECT COUNT(ch.id) 
					FROM dp_checklists ch 
					INNER JOIN dp_atividades at ON ch.id_atividade = at.id 
					WHERE at.id_ordem = A.id AND at.id_departamento = ? AND ch.status = 1
        ) AS checklists_concluidos,
				(
					SELECT COUNT(ch.id) 
					FROM dp_checklists ch 
					INNER JOIN dp_atividades at ON ch.id_atividade = at.id 
					WHERE at.id_ordem = A.id AND at.id_departamento = ? AND ch.status = 0 AND at.id_status = 4
        ) AS disponiveis
        FROM dp_ordens A
        LEFT JOIN dp_ordem_departamento B ON A.id = B.id_ordem
				LEFT JOIN dp_remessas C ON A.id_remessa = C.id
        WHERE B.id_departamento = ? AND B.id_status IN (1,2,3)
        AND A.deleted_at IS NULL
        ORDER BY disponiveis DESC';

    $stmt = $pdo->prepare($query);
    $stmt->execute([$id, $id, $id, $id]);
    $ordens = $stmt->fetchAll();

    if (!$ordens) {
        json_response(['data' => []], 404);
        return;
    }

    json_response(['data' => $ordens]);
    return;
	}

	public static function updateChecklist(array $params): void {
		$id = (int)($params['id'] ?? 0);
		$body = read_json_body();

		$id_user = (int)($body['id_user'] ?? 0);
		$status = (int)($body['status'] ?? 0);
		$observacao = trim((string)($body['observacao'] ?? ''));

		$pdo = Database::pdo();

		if ($status === null) {
			json_response(['error' => 'Invalid payload'], 422);
			return;
		}

		$query = 
		'SELECT A.id_atividade, A.id_conf_checklist, A.etapa, A.atividade, A.checklist, B.id_ordem, B.id_departamento, B.atividade as nome_atividade
		FROM dp_checklists A
		LEFT JOIN dp_atividades B ON A.id_atividade = B.id
		WHERE A.id = ?';

		$stmt = $pdo->prepare($query);
		$stmt->execute([$id]);
		$checklist = $stmt->fetch();

		if (!$checklist) {
				json_response(['error' => 'Checklist não encontrado'], 404);
				return;
		}

		if($status === 1) {
			$query = 'UPDATE dp_checklists SET status = 1, observacao = ?, updated_at = NOW() WHERE id = ?';
			$stmt = $pdo->prepare($query);
			$stmt->execute([$observacao, $id]);

			$descricao = 'Realizou o checklist: ' . $checklist['checklist'];
			$descricao = mb_substr($descricao, 0, 255);

			$query = 'INSERT INTO dp_historico (id_departamento, id_user, id_ordem, id_checklist, descricao, created_at) VALUES (?, ?, ?, ?, ?, NOW())';
			$stmt = $pdo->prepare($query);
			$stmt->execute([
					$checklist['id_departamento'],
					$id_user,
					$checklist['id_ordem'],
					$id,
					$descricao
			]);
		} else {
        $query = 'UPDATE dp_checklists SET status = -1, observacao = ?, updated_at = NOW() WHERE id = ?';
        $stmt = $pdo->prepare($query);
        $stmt->execute([$observacao, $id]);

				$descricao = 'Recusou o checklist: ' . $checklist['checklist'];
				$descricao = mb_substr($descricao, 0, 255);

				$query = 'INSERT INTO dp_historico (id_departamento, id_user, id_ordem, id_checklist, descricao, created_at) VALUES (?, ?, ?, ?, ?, NOW())';
				$stmt = $pdo->prepare($query);
				$stmt->execute([
						$checklist['id_departamento'],
						$id_user,
						$checklist['id_ordem'],
						$id,
						$descricao
				]);

        $query = 'INSERT INTO dp_checklists (id_atividade, id_conf_checklist, etapa, atividade, checklist, observacao, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, 0, NOW(), NOW())';
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            $checklist['id_atividade'],
            $checklist['id_conf_checklist'],
            $checklist['etapa'],
            $checklist['atividade'],
            $checklist['checklist'],
            '',
        ]);

				$query = 'UPDATE dp_atividades SET id_status = 1 WHERE id = ?';
        $stmt = $pdo->prepare($query);
        $stmt->execute([$checklist['id_atividade']]);

				$descricao = 'Retornou para produção e deletou os volumes da atividade: ' . $checklist['nome_atividade'];
				$descricao = mb_substr($descricao, 0, 255);

				$query = 'INSERT INTO dp_historico (id_departamento, id_user, id_ordem, id_checklist, descricao, created_at) VALUES (?, ?, ?, ?, ?, NOW())';
				$stmt = $pdo->prepare($query);
				$stmt->execute([
						$checklist['id_departamento'],
						$id_user,
						$checklist['id_ordem'],
						$id,
						$descricao
				]);

				$query = 'UPDATE dp_volumes SET status = 0, comprimento = 0, largura = 0, altura = 0, peso = 0 WHERE id_atividade = ?';
        $stmt = $pdo->prepare($query);
        $stmt->execute([$checklist['id_atividade']]);
		}

		json_response([
			'message' => 'Checklist realizado',
		], 200);
	}
}
