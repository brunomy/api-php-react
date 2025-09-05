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

    $query = 
        'SELECT A.*, B.nome AS nome_equipe, C.nome_categoria, D.titulo as titulo_remessa,
				(SELECT COUNT(id) FROM dp_volumes WHERE id_atividade = A.id) AS volumes,
				(SELECT COUNT(id) FROM dp_volumes WHERE id_atividade = A.id AND status = 0) AS volumes_pendentes,
				(SELECT COUNT(id) FROM dp_checklists WHERE id_atividade = A.id AND status = 0) AS checklist_pendente,
        CASE 
            WHEN A.data <= DATE_SUB(DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) + 1 DAY), INTERVAL 1 DAY) THEN "atrasadas"
						WHEN A.data = CURDATE() THEN "hoje"
            WHEN A.data >= DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) + 1 DAY) 
                 AND A.data <= DATE_ADD(DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) + 1 DAY), INTERVAL 6 DAY) THEN "semana"
            WHEN A.data > DATE_ADD(DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) + 1 DAY), INTERVAL 6 DAY) THEN "futuras"
        END as categoria
        FROM dp_atividades A
        LEFT JOIN dp_equipes B ON A.id_equipe = B.id
        LEFT JOIN dp_ordens C ON A.id_ordem = C.id
        LEFT JOIN dp_remessas D ON C.id_remessa = D.id
        WHERE A.id_departamento = ? 
        AND A.id_status > 0 
        AND A.deleted_at IS NULL 
        ORDER BY A.data ASC';

    $stmt = $pdo->prepare($query);
    $stmt->execute([$id]);
    $todas_atividades = $stmt->fetchAll();

    // Organizar as atividades por categoria
    $atividades_organizadas = [
			"atrasadas" => [],
			"hoje" => [],
			"semana" => [],
			"futuras" => []
    ];

    foreach ($todas_atividades as $atividade) {
        $categoria = $atividade['categoria'];
        
        // Aplicar filtro específico para atrasadas (status < 4)
        if ($categoria === 'atrasadas' && $atividade['id_status'] >= 4 && $atividade['volumes_pendentes'] == 0 && $atividade['checklist_pendente'] == 0) {
            continue;
        }
        
        // Remover a coluna categoria do resultado final
        unset($atividade['categoria']);
        
        $atividades_organizadas[$categoria][] = $atividade;
    }

    json_response(['data' => $atividades_organizadas]);
    return;
	}

	public static function iniciarAtividade(array $params): void {
		$id = (int)($params['id'] ?? 0);
		$body = read_json_body();

		$id_user = (int)($body['id_user'] ?? null);
		$id_departamento = (int)($body['id_departamento'] ?? null);
		$id_ordem = (int)($body['id_ordem'] ?? null);
		$id_equipe = (int)($body['id_equipe'] ?? null);
		$codigo = (string)($body['codigo'] ?? null);
		$titulo = trim((string)($body['titulo'] ?? ''));
		$inicio = (string)($body['inicio'] ?? null);
		$tempo = (string)($body['tempo'] ?? null);

		if ($id <= 0 || $id_user === null || $id_ordem === null || $id_equipe === null || $codigo === null || $titulo === '') {
				json_response(['error' => 'Dado inválido'], 422);
				return;
		}

		$funcionario = self::validarFuncionario($id_user, $id_departamento, $id_equipe, $codigo);

		if (!$funcionario) {
				json_response(['error' => 'Código inválido'], 200);
				return;
		}

		$pdo = Database::pdo();

		// Atualizar status da atividade
		$query = 'UPDATE dp_atividades SET id_status = 2, tempo = ?, inicio = IF(inicio IS NULL, NOW(), inicio), pausa = IF(pausa IS NOT NULL, NOW(), pausa) WHERE id = ? AND deleted_at IS NULL';
		$stmt = $pdo->prepare($query);
		$stmt->execute([$tempo, $id]);

		// Atualizar status da ordem
		$query = 'UPDATE dp_ordem_departamento SET id_status = 2 WHERE id_ordem = ? AND id_departamento = ?';
		$stmt = $pdo->prepare($query);
		$stmt->execute([$id_ordem, $id_departamento]);

		if($inicio) {
			$descricao = 'Retomou a atividade: ' . $titulo;
		} else {
			$descricao = 'Iniciou a atividade: ' . $titulo;
		}
		// Limitar drasticamente o tamanho da descrição
		$descricao = mb_substr($descricao, 0, 255);

		// Inserir dado no histórico
		$query = 'INSERT INTO dp_historico (id_departamento, id_ordem, id_equipe, id_funcionario, id_atividade, descricao, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())';
		$stmt = $pdo->prepare($query);
		$stmt->execute([
				$id_departamento,
				$id_ordem, 
				$funcionario['id_equipe'], 
				$funcionario['id'], 
				$id, 
				$descricao
		]);

		json_response([
				'message' => 'Atividade iniciada com sucesso'
		], 200);
	}

	public static function pararAtividade(array $params): void {
    $id = (int)($params['id'] ?? 0);
    $body = read_json_body();

    $id_user = (int)($body['id_user'] ?? null);
    $id_departamento = (int)($body['id_departamento'] ?? null);
    $id_ordem = (int)($body['id_ordem'] ?? null);
    $id_equipe = (int)($body['id_equipe'] ?? null);
    $codigo = (string)($body['codigo'] ?? null);
    $titulo = trim((string)($body['titulo'] ?? ''));
    $tempo_atual = (int)($body['tempo'] ?? 0);
    $inicio = trim((string)($body['inicio'] ?? ''));
    $pausa_anterior = trim((string)($body['pausa'] ?? ''));

    if ($id <= 0 || $id_user === null || $id_ordem === null || $id_equipe === null || $codigo === null || $titulo === '') {
        json_response(['error' => 'Dado inválido'], 422);
        return;
    }

    $funcionario = self::validarFuncionario($id_user, $id_departamento, $id_equipe, $codigo);

    if (!$funcionario) {
        json_response(['error' => 'Código inválido'], 200);
        return;
    }

    $pdo = Database::pdo();

    $tempo_adicional = 0;
    
    if (empty($pausa_anterior) && !empty($inicio)) {
        $query_tempo = 'SELECT TIMESTAMPDIFF(SECOND, ?, NOW()) as segundos';
        $stmt_tempo = $pdo->prepare($query_tempo);
        $stmt_tempo->execute([$inicio]);
        $result = $stmt_tempo->fetch();
        $tempo_adicional = (int)($result['segundos'] ?? 0);
    } elseif (!empty($pausa_anterior)) {
        $query_tempo = 'SELECT TIMESTAMPDIFF(SECOND, ?, NOW()) as segundos';
        $stmt_tempo = $pdo->prepare($query_tempo);
        $stmt_tempo->execute([$pausa_anterior]);
        $result = $stmt_tempo->fetch();
        $tempo_adicional = (int)($result['segundos'] ?? 0);
    }

    $tempo_total = $tempo_atual + $tempo_adicional;

    $query = 'UPDATE dp_atividades SET 
                id_status = 3, 
                pausa = NOW(),
                tempo = ?
              WHERE id = ? AND deleted_at IS NULL';
    $stmt = $pdo->prepare($query);
    $stmt->execute([$tempo_total, $id]);


		//verificar se existem atividades em andamento
		$query = 
			'SELECT id
				FROM dp_atividades
			WHERE id_ordem = ? AND id_departamento = ? AND deleted_at IS NULL AND id_status = 2';

		$stmt = $pdo->prepare($query);
		$stmt->execute([$id_ordem, $id_departamento]);
		$atividadesEmAndamento = $stmt->fetch();

		if(!$atividadesEmAndamento){
			$query = 'UPDATE dp_ordem_departamento SET id_status = 3 WHERE id_ordem = ? AND id_departamento = ?';
			$stmt = $pdo->prepare($query);
			$stmt->execute([$id_ordem, $id_departamento]);
		}


    $descricao = 'Parou a atividade: ' . $titulo;
    $descricao = mb_substr($descricao, 0, 255);

    $query = 'INSERT INTO dp_historico (id_departamento, id_ordem, id_equipe, id_funcionario, id_atividade, descricao, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())';
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        $id_departamento,
        $id_ordem, 
        $funcionario['id_equipe'], 
        $funcionario['id'], 
        $id, 
        $descricao
    ]);

    json_response([
        'message' => 'Atividade pausada com sucesso',
    ], 200);
	}

	public static function finalizarAtividade(array $params): void {
    $id = (int)($params['id'] ?? 0);
    $body = read_json_body();

    $id_user = (int)($body['id_user'] ?? null);
    $id_departamento = (int)($body['id_departamento'] ?? null);
    $id_ordem = (int)($body['id_ordem'] ?? null);
    $id_equipe = (int)($body['id_equipe'] ?? null);
    $codigo = (string)($body['codigo'] ?? null);
    $titulo = trim((string)($body['titulo'] ?? ''));
    $tempo_atual = (int)($body['tempo'] ?? 0);
    $inicio = trim((string)($body['inicio'] ?? ''));
    $pausa_anterior = trim((string)($body['pausa'] ?? ''));
    $id_status = trim((string)($body['id_status'] ?? ''));

    if ($id <= 0 || $id_user === null || $id_ordem === null || $id_equipe === null || $codigo === null || $titulo === '') {
        json_response(['error' => 'Dado inválido'], 422);
        return;
    }

    $funcionario = self::validarFuncionario($id_user, $id_departamento, $id_equipe, $codigo);

    if (!$funcionario) {
        json_response(['error' => 'Código inválido'], 200);
        return;
    }

    $pdo = Database::pdo();

		//Se a atividade estiver em andamento, precisa recalcular o tempo
		if($id_status == 2){
			$tempo_adicional = 0;
			
			if (empty($pausa_anterior) && !empty($inicio)) {
					// Atividade estava rodando desde o início - calcular desde o início
					$query_tempo = 'SELECT TIMESTAMPDIFF(SECOND, ?, NOW()) as segundos';
					$stmt_tempo = $pdo->prepare($query_tempo);
					$stmt_tempo->execute([$inicio]);
					$result = $stmt_tempo->fetch();
					$tempo_adicional = (int)($result['segundos'] ?? 0);
			} elseif (!empty($pausa_anterior)) {
					// Atividade foi retomada - calcular desde a última pausa
					$query_tempo = 'SELECT TIMESTAMPDIFF(SECOND, ?, NOW()) as segundos';
					$stmt_tempo = $pdo->prepare($query_tempo);
					$stmt_tempo->execute([$pausa_anterior]);
					$result = $stmt_tempo->fetch();
					$tempo_adicional = (int)($result['segundos'] ?? 0);
			}
	
			$tempo_total = $tempo_atual + $tempo_adicional;
	
			// Finalizar atividade: status = 4, fim = NOW(), tempo calculado
			$query = 'UPDATE dp_atividades SET 
									id_status = 4, 
									fim = NOW(),
									tempo = ?
								WHERE id = ? AND deleted_at IS NULL';
			$stmt = $pdo->prepare($query);
			$stmt->execute([$tempo_total, $id]);
		} else {
			$query = 'UPDATE dp_atividades SET 
									id_status = 4, 
									fim = NOW()
								WHERE id = ? AND deleted_at IS NULL';
			$stmt = $pdo->prepare($query);
			$stmt->execute([$id]);
		}


    $descricao = 'Finalizou a atividade: ' . $titulo;
    $descricao = mb_substr($descricao, 0, 255);

    // Inserir dado no histórico
    $query = 'INSERT INTO dp_historico (id_departamento, id_ordem, id_equipe, id_funcionario, id_atividade, descricao, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())';
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        $id_departamento,
        $id_ordem, 
        $funcionario['id_equipe'], 
        $funcionario['id'], 
        $id, 
        $descricao
    ]);

    json_response([
        'message' => 'Atividade finalizada com sucesso',
    ], 200);
	}

	public static function getVolumesAtividade(array $params): void {
		$id_departamento = (int)($params['id_departamento'] ?? 0);
		$id = (int)($params['id'] ?? 0);

		$pdo = Database::pdo();
		$query = 
			'SELECT * FROM dp_volumes WHERE id_atividade = ?';

		$stmt = $pdo->prepare($query);
		$stmt->execute([$id]);
		$volumes = $stmt->fetchAll();

		if (!$volumes) {
			json_response(['Not Found']);
			return;
		}

		json_response(['data' => $volumes]);
		return;
	}

	public static function updateVolume(array $params): void {
		$id = (int)($params['id'] ?? 0);
		$body = read_json_body();

		$id_user = (int)($body['id_user'] ?? null);
		$id_departamento = (int)($body['id_departamento'] ?? null);
		$id_ordem = (int)($body['id_ordem'] ?? null);
		$id_atividade = (int)($body['id_atividade'] ?? null);
		$id_equipe = (int)($body['id_equipe'] ?? null);
		$codigo = (string)($body['codigo'] ?? null);
		$titulo = (string)($body['titulo'] ?? '');
		$comprimento = (float)($body['comprimento'] ?? '');
		$largura = (float)($body['largura'] ?? '');
		$altura = (float)($body['altura'] ?? '');
		$peso = (float)($body['peso'] ?? '');
		$status = (int)($body['status'] ?? '');

		if ($id <= 0 || $id_user === null || $id_departamento === null || $id_equipe === null || $codigo === null || $titulo === '' || $comprimento === 0 || $largura === 0 || $altura === 0 || $peso === 0) {
			json_response(['error' => 'Payload inválido'], 422);
			return;
		}

		$funcionario = self::validarFuncionario($id_user, $id_departamento, $id_equipe, $codigo);

		if (!$funcionario) {
				json_response(['error' => 'Código inválido'], 200);
				return;
		}

		$pdo = Database::pdo();

		// Atualizar dados do volume
		$query = 
			'UPDATE dp_volumes 
				SET status = ?, comprimento = ?, largura = ?, altura = ?, peso = ? WHERE id = ?';
		$stmt = $pdo->prepare($query);
		$stmt->execute([$status, $comprimento, $largura, $altura, $peso, $id]);

		if($status === 1){
			$descricao = 'Criou o volume: ' . $titulo;
		} else {
			$descricao = 'Deletou o volume: ' . $titulo;
		}
		$descricao = mb_substr($descricao, 0, 255);

		// Inserir dado no histórico
		$query = 'INSERT INTO dp_historico (id_departamento, id_ordem, id_equipe, id_funcionario, id_atividade, descricao, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())';
		$stmt = $pdo->prepare($query);
		$stmt->execute([
				$id_departamento,
				$id_ordem, 
				$funcionario['id_equipe'], 
				$funcionario['id'], 
				$id_atividade, 
				$descricao
		]);

		json_response([
				'message' => 'Volume criado com sucesso'
		], 200);
	}



	private static function validarFuncionario(int $id_user, int $id_departamento, int $id_equipe, string $codigo): array|false {
		$pdo = Database::pdo();
		
		$query = 
				'SELECT A.*, B.id as id_equipe
					FROM dp_funcionarios A
					LEFT JOIN dp_equipes B ON A.id_equipe = B.id
					WHERE B.id_user = ? AND B.id_departamento = ? AND B.deleted_at IS NULL 
					AND A.deleted_at IS NULL AND A.id_equipe = ? AND (A.senha = ? OR A.codigo = ?)';

		$stmt = $pdo->prepare($query);
		$stmt->execute([$id_user, $id_departamento, $id_equipe, $codigo, $codigo]);
		
		return $stmt->fetch();
	}
}
