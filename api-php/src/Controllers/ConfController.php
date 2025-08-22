<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Database;

final class ConfController {
	public static function getCategoriasDepartamento(array $params): void {
		$idDepartamento = (int)($params['id'] ?? 0);
		$pdo = Database::pdo();

		$query = 
			'SELECT 
					A.id,
					A.nome,
					COUNT(DISTINCT C.id) AS etapas_count,
					COUNT(DISTINCT D.id) AS atividades_count,
					COUNT(DISTINCT E.id) AS checklists_count,
					COUNT(DISTINCT F.id) AS volumes_count
			FROM tb_produtos_categorias A
			LEFT JOIN dp_categoria_departamento B 
					ON A.id = B.id_categoria
			LEFT JOIN dp_conf_etapas C 
					ON A.id = C.id_categoria 
					AND C.deleted_at IS NULL
			LEFT JOIN dp_conf_atividades D 
					ON C.id = D.id_conf_etapa
					AND D.deleted_at IS NULL
			LEFT JOIN dp_conf_checklists E 
					ON D.id = E.id_conf_atividade
					AND E.deleted_at IS NULL
			LEFT JOIN dp_conf_volumes F 
					ON D.id = F.id_conf_atividade
					AND F.deleted_at IS NULL
			WHERE 
					A.stats = 1
					AND A.deleted_at IS NULL
					AND B.id_departamento = ?
			GROUP BY A.id, A.nome
			ORDER BY A.nome ASC;';

		$stmt = $pdo->prepare($query);
		$stmt->execute([$idDepartamento]);

		$categorias = $stmt->fetchAll();
		
		if (!$categorias) {
			json_response(['error' => 'Not Found'], 404);
			return;
		}

		json_response(['data' => $categorias]);
		return;
	}

	//Etapas
	public static function getEtapas(array $params): void {
		$idDepartamento = (int)($params['idDepartamento'] ?? 0);
		$idCategoria = (int)($params['idCategoria'] ?? 0);

		$pdo = Database::pdo();

		$query = 
			'SELECT 
					A.id,
					A.titulo,
					COUNT(DISTINCT B.id) AS atividades_count,
					COUNT(DISTINCT C.id) AS checklists_count,
					COUNT(DISTINCT D.id) AS volumes_count
			FROM dp_conf_etapas A
			LEFT JOIN dp_conf_atividades B ON A.id = B.id_conf_etapa
			LEFT JOIN dp_conf_checklists C ON B.id = C.id_conf_atividade
			LEFT JOIN dp_conf_volumes D ON B.id = D.id_conf_atividade
			WHERE 
					A.deleted_at IS NULL
					AND B.deleted_at IS NULL
					AND C.deleted_at IS NULL
					AND D.deleted_at IS NULL
					AND A.id_departamento = ?
					AND A.id_categoria = ?
			GROUP BY A.id, A.titulo
			ORDER BY A.id ASC;';

		$stmt = $pdo->prepare($query);
		$stmt->execute([$idDepartamento, $idCategoria]);
		$etapas = $stmt->fetchAll();

		$query2 = 
			'SELECT 
				id, nome
			FROM tb_produtos_categorias
			WHERE 
				id = ?;';

		$stmt = $pdo->prepare($query2);
		$stmt->execute([$idCategoria]);
		$categoria = $stmt->fetch();

		if (!$etapas) {
			json_response(['data' => [
				'categoria' => $categoria,
				'etapas' => [],
			]]);
			return;
		}

		json_response(['data' => [
			'categoria' => $categoria,
			'etapas' => $etapas,
		]]);
		return;
	}

	public static function createEtapa(array $params): void {
		$body = read_json_body();
		$id_categoria  = trim((string)($body['id_categoria']  ?? ''));
		$id_departamento  = trim((string)($body['id_departamento']  ?? ''));
		$titulo  = trim((string)($body['titulo']  ?? ''));

		if ($id_categoria === '' || $id_departamento === '' || $titulo === '') {
			json_response(['error' => 'Invalid payload'], 422);
			return;
		}

		$pdo = Database::pdo();

		$query = 'INSERT INTO dp_conf_etapas (id_categoria, id_departamento, titulo) VALUES (?, ?, ?)';

		$stmt = $pdo->prepare($query);
		$stmt->execute([$id_categoria, $id_departamento, $titulo]);

		json_response([
			'data' => [
				'id'    => (int)$pdo->lastInsertId(),
				'id_categoria'  => $id_categoria,
				'id_departamento' => $id_departamento,
				'titulo' => $titulo,
			]
		], 201);
		return;
	}

	public static function deleteEtapa(array $params): void {
		$id = (int)($params['idEtapa'] ?? 0);

		$pdo = Database::pdo();

		// Verifica se a etapa existe
		$stmt = $pdo->prepare('SELECT * FROM dp_conf_etapas WHERE id = ? AND deleted_at IS NULL');
		$stmt->execute([$id]);
		$etapa = $stmt->fetch();

		if (!$etapa) {
			json_response(['error' => 'Not Found'], 404);
			return;
		}

		// Marca a etapa como deletada
		$stmt = $pdo->prepare('UPDATE dp_conf_etapas SET deleted_at = NOW() WHERE id = ?');
		$stmt->execute([$id]);

		json_response(['message' => 'Etapa deleted successfully']);
		return;
	}

	public static function updateEtapa(array $params): void {
		$id = (int)($params['id'] ?? 0);
		$body = read_json_body();

		$titulo = trim((string)($body['titulo'] ?? ''));

		if ($id <= 0 || $titulo === '') {
				json_response(['error' => 'Payload inválido'], 422);
				return;
		}

		$pdo = Database::pdo();

		$query = 'UPDATE dp_conf_etapas SET titulo = ? WHERE id = ? AND deleted_at IS NULL';
		$stmt = $pdo->prepare($query);
		$stmt->execute([$titulo, $id]);

		json_response([
				'message' => 'Etapa atualizada com sucesso',
				'data' => [
						'id' => $id,
						'titulo' => $titulo,
				]
		], 200);
	}

	//Atividades
	public static function getAtividades(array $params): void {
		$id_conf_etapa = (int)($params['idConfEtapa'] ?? 0);

		$pdo = Database::pdo();

		$query = 
			'SELECT 
					A.id,
					A.titulo,
					COUNT(DISTINCT B.id) AS checklists_count,
					COUNT(DISTINCT C.id) AS volumes_count
			FROM dp_conf_atividades A
			LEFT JOIN dp_conf_checklists B ON A.id = B.id_conf_atividade
			LEFT JOIN dp_conf_volumes C ON A.id = C.id_conf_atividade
			WHERE 
					A.deleted_at IS NULL
					AND B.deleted_at IS NULL
					AND C.deleted_at IS NULL
					AND A.id_conf_etapa = ?
			GROUP BY A.id, A.titulo
			ORDER BY A.id ASC;';

		$stmt = $pdo->prepare($query);
		$stmt->execute([$id_conf_etapa]);
		$atividades = $stmt->fetchAll();

		$query2 = 
			'SELECT 
				id, titulo
			FROM dp_conf_etapas
			WHERE 
				id = ?;';

		$stmt = $pdo->prepare($query2);
		$stmt->execute([$id_conf_etapa]);
		$etapa = $stmt->fetch();

		$query3 = 
			'SELECT 
				A.id, A.nome
			FROM tb_produtos_categorias A
			LEFT JOIN dp_conf_etapas B ON A.id = B.id_categoria
			WHERE 
				B.id = ?;';

		$stmt = $pdo->prepare($query3);
		$stmt->execute([$id_conf_etapa]);
		$categoria = $stmt->fetch();

		if (!$atividades) {
			json_response(['data' => [
				'categoria' => $categoria,
				'etapa' => $etapa,
				'atividades' => [],
			]]);
			return;
		}

		json_response(['data' => [
			'categoria' => $categoria,
			'etapa' => $etapa,
			'atividades' => $atividades,
		]]);
		return;
	}

	public static function createAtividade(array $params): void {
		$body = read_json_body();
		$id_conf_etapa  = trim((string)($body['id_conf_etapa']  ?? ''));
		$titulo  = trim((string)($body['titulo']  ?? ''));

		if ($id_conf_etapa === '' || $titulo === '') {
			json_response(['error' => 'Invalid payload'], 422);
			return;
		}

		$pdo = Database::pdo();

		$query = 'INSERT INTO dp_conf_atividades (id_conf_etapa, titulo) VALUES (?, ?)';

		$stmt = $pdo->prepare($query);
		$stmt->execute([$id_conf_etapa, $titulo]);

		json_response([
			'data' => [
				'id'    => (int)$pdo->lastInsertId(),
				'id_conf_etapa'  => $id_conf_etapa,
				'titulo' => $titulo,
			]
		], 201);
		return;
	}

	public static function deleteAtividade(array $params): void {
		$id = (int)($params['idAtividade'] ?? 0);

		$pdo = Database::pdo();

		// Verifica se a atividade existe
		$stmt = $pdo->prepare('SELECT * FROM dp_conf_atividades WHERE id = ? AND deleted_at IS NULL');
		$stmt->execute([$id]);
		$atividade = $stmt->fetch();

		if (!$atividade) {
			json_response(['error' => 'Not Found'], 404);
			return;
		}

		// Marca a atividade como deletada
		$stmt = $pdo->prepare('UPDATE dp_conf_atividades SET deleted_at = NOW() WHERE id = ?');
		$stmt->execute([$id]);

		json_response(['message' => 'Atividade deleted successfully']);
		return;
	}

	public static function updateAtividade(array $params): void {
		$id = (int)($params['id'] ?? 0);
		$body = read_json_body();

		$titulo = trim((string)($body['titulo'] ?? ''));

		if ($id <= 0 || $titulo === '') {
				json_response(['error' => 'Payload inválido'], 422);
				return;
		}

		$pdo = Database::pdo();

		$query = 'UPDATE dp_conf_atividades SET titulo = ? WHERE id = ? AND deleted_at IS NULL';
		$stmt = $pdo->prepare($query);
		$stmt->execute([$titulo, $id]);

		json_response([
				'message' => 'Atividade atualizada com sucesso',
				'data' => [
						'id' => $id,
						'titulo' => $titulo,
				]
		], 200);
	}

	//Checklists e Volumes
	public static function getChecklistsVolumes(array $params): void {
		$id_conf_atividade = (int)($params['idConfAtividade'] ?? 0);

		$pdo = Database::pdo();

		$query = 
			'SELECT 
				id, titulo
			FROM dp_conf_checklists
			WHERE 
				deleted_at IS NULL
				AND id_conf_atividade = ?
			GROUP BY id, titulo
			ORDER BY id ASC;';

		$stmt = $pdo->prepare($query);
		$stmt->execute([$id_conf_atividade]);
		$checklists = $stmt->fetchAll();

		$query2 = 
			'SELECT 
				id, titulo
			FROM dp_conf_volumes
			WHERE 
				deleted_at IS NULL
				AND id_conf_atividade = ?
			GROUP BY id, titulo
			ORDER BY id ASC;';

		$stmt = $pdo->prepare($query2);
		$stmt->execute([$id_conf_atividade]);
		$volumes = $stmt->fetchAll();

		$query3 = 
			'SELECT 
				A.id, A.nome
			FROM tb_produtos_categorias A
			LEFT JOIN dp_conf_etapas B ON A.id = B.id_categoria
			LEFT JOIN dp_conf_atividades C ON B.id = C.id_conf_etapa
			WHERE 
				C.id = ?;';

		$stmt = $pdo->prepare($query3);
		$stmt->execute([$id_conf_atividade]);
		$categoria = $stmt->fetch();

		$query4 = 
			'SELECT 
				A.id, A.titulo
			FROM dp_conf_etapas A
			LEFT JOIN dp_conf_atividades B ON A.id = B.id_conf_etapa
			WHERE 
				B.id = ?;';

		$stmt = $pdo->prepare($query4);
		$stmt->execute([$id_conf_atividade]);
		$etapa = $stmt->fetch();

		$query5 = 
			'SELECT 
				id, titulo
			FROM dp_conf_atividades 
			WHERE 
				id = ?;';

		$stmt = $pdo->prepare($query5);
		$stmt->execute([$id_conf_atividade]);
		$atividade = $stmt->fetch();

		json_response(['data' => [
			'categoria' => $categoria,
			'etapa' => $etapa,
			'atividade' => $atividade,
			'checklists' => $checklists,
			'volumes' => $volumes,
		]]);
		return;
	}

	public static function createChecklist(array $params): void {
		$body = read_json_body();
		$id_conf_atividade  = trim((string)($body['id_conf_atividade']  ?? ''));
		$titulo  = trim((string)($body['titulo']  ?? ''));

		if ($id_conf_atividade === '' || $titulo === '') {
			json_response(['error' => 'Invalid payload'], 422);
			return;
		}

		$pdo = Database::pdo();

		$query = 'INSERT INTO dp_conf_checklists (id_conf_atividade, titulo) VALUES (?, ?)';

		$stmt = $pdo->prepare($query);
		$stmt->execute([$id_conf_atividade, $titulo]);

		json_response([
			'data' => [
				'id'    => (int)$pdo->lastInsertId(),
				'id_conf_atividade'  => $id_conf_atividade,
				'titulo' => $titulo,
			]
		], 201);
		return;
	}

	public static function deleteChecklist(array $params): void {
		$id = (int)($params['idChecklist'] ?? 0);

		$pdo = Database::pdo();

		// Verifica se o checklist existe
		$stmt = $pdo->prepare('SELECT * FROM dp_conf_checklists WHERE id = ? AND deleted_at IS NULL');
		$stmt->execute([$id]);
		$checklist = $stmt->fetch();

		if (!$checklist) {
			json_response(['error' => 'Not Found'], 404);
			return;
		}

		// Marca o checklist como deletado
		$stmt = $pdo->prepare('UPDATE dp_conf_checklists SET deleted_at = NOW() WHERE id = ?');
		$stmt->execute([$id]);

		json_response(['message' => 'Checklist deleted successfully']);
		return;
	}

	public static function createVolume(array $params): void {
		$body = read_json_body();
		$id_conf_atividade  = trim((string)($body['id_conf_atividade']  ?? ''));
		$titulo  = trim((string)($body['titulo']  ?? ''));

		if ($id_conf_atividade === '' || $titulo === '') {
			json_response(['error' => 'Invalid payload'], 422);
			return;
		}

		$pdo = Database::pdo();

		$query = 'INSERT INTO dp_conf_volumes (id_conf_atividade, titulo) VALUES (?, ?)';

		$stmt = $pdo->prepare($query);
		$stmt->execute([$id_conf_atividade, $titulo]);

		json_response([
			'data' => [
				'id'    => (int)$pdo->lastInsertId(),
				'id_conf_atividade'  => $id_conf_atividade,
				'titulo' => $titulo,
			]
		], 201);
		return;
	}

	public static function deleteVolume(array $params): void {
		$id = (int)($params['idVolume'] ?? 0);

		$pdo = Database::pdo();

		// Verifica se o volume existe
		$stmt = $pdo->prepare('SELECT * FROM dp_conf_volumes WHERE id = ? AND deleted_at IS NULL');
		$stmt->execute([$id]);
		$volume = $stmt->fetch();

		if (!$volume) {
			json_response(['error' => 'Not Found'], 404);
			return;
		}

		// Marca o volume como deletado
		$stmt = $pdo->prepare('UPDATE dp_conf_volumes SET deleted_at = NOW() WHERE id = ?');
		$stmt->execute([$id]);

		json_response(['message' => 'Volume deleted successfully']);
		return;
	}
	
}
