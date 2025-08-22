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
			LEFT JOIN dp_categoria_departamento B ON A.id = B.id_categoria
			LEFT JOIN dp_conf_etapas C ON A.id = C.id_categoria
			LEFT JOIN dp_conf_atividades D ON C.id = D.id_conf_etapa
			LEFT JOIN dp_conf_checklists E ON D.id = E.id_conf_atividade
			LEFT JOIN dp_conf_volumes F ON D.id = F.id_conf_atividade
			WHERE 
					A.stats = 1
					AND A.deleted_at IS NULL
					AND C.deleted_at IS NULL
					AND D.deleted_at IS NULL
					AND E.deleted_at IS NULL
					AND F.deleted_at IS NULL
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

		$query2 = 
			'SELECT 
				id, nome
			FROM tb_produtos_categorias
			WHERE 
				id = ?;';

		$stmt = $pdo->prepare($query);
		$stmt->execute([$idDepartamento, $idCategoria]);

		$etapas = $stmt->fetchAll();

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
			'SELECT * 
					FROM dp_conf_atividades
					WHERE id_conf_etapa = ? && deleted_at IS NULL
					ORDER BY id ASC';

		$stmt = $pdo->prepare($query);
		$stmt->execute([$id_conf_etapa]);

		$atividades = $stmt->fetchAll();
		
		if (!$atividades) {
			json_response(['error' => 'Not Found'], 404);
			return;
		}

		json_response(['data' => $atividades]);
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
}
