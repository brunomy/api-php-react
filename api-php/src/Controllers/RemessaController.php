<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Database;

final class RemessaController {
	public static function getEstados(): void {
		$pdo = Database::pdo();
		$query = 
			'SELECT * FROM tb_utils_estados;';

		$stmt = $pdo->prepare($query);
		$stmt->execute();

		$estados = $stmt->fetchAll();

		if (!$estados) {
			json_response(['error' => 'Not Found'], 404);
			return;
		}

		json_response(['data' => $estados]);
		return;
	}

	public static function getCidades(array $params): void {
		$id = (int)($params['id'] ?? 0);

		$pdo = Database::pdo();
		$query = 
			'SELECT * FROM tb_utils_cidades WHERE id_estado = ?;';

		$stmt = $pdo->prepare($query);
		$stmt->execute([$id]);

		$cidades = $stmt->fetchAll();

		if (!$cidades) {
			json_response(['error' => 'Not Found'], 404);
			return;
		}

		json_response(['data' => $cidades]);
		return;
	}

	public static function getRemessa(array $params): void {
		$id = (int)($params['id'] ?? 0);

		$pdo = Database::pdo();

		$query = 
			'SELECT * FROM dp_remessas WHERE id = ?;';

		$stmt = $pdo->prepare($query);
		$stmt->execute([$id]);

		$remessa = $stmt->fetch();

		if (!$remessa) {
			json_response(['error' => 'Not Found'], 404);
			return;
		}

		json_response(['data' => $remessa]);
		return;
	}

	public static function updateRemessa(array $params): void {
		$id = (int)($params['id'] ?? 0);
		$body = read_json_body();

		// Validações básicas
		$titulo = trim((string)($body['titulo'] ?? ''));
		$entrega = (string)($body['entrega'] ?? '');
		$nome = trim((string)($body['nome'] ?? ''));
		$telefone = trim((string)($body['telefone'] ?? ''));
		$cep = trim((string)($body['cep'] ?? ''));
		$id_estado = (int)($body['id_estado'] ?? 0);
		$id_cidade = (int)($body['id_cidade'] ?? 0);
		$endereco = trim((string)($body['endereco'] ?? ''));
		$numero = trim((string)($body['numero'] ?? ''));
		$bairro = trim((string)($body['bairro'] ?? ''));

		$pdo = Database::pdo();

		// Campos opcionais
		$n_nota = !empty($body['n_nota']) ? trim((string)$body['n_nota']) : null;
		$nova_entrega = !empty($body['nova_entrega']) ? (string)$body['nova_entrega'] : null;
		$saida = !empty($body['saida']) ? (string)$body['saida'] : null;
		$nova_saida = !empty($body['nova_saida']) ? (string)$body['nova_saida'] : null;
		$coleta = !empty($body['coleta']) ? (string)$body['coleta'] : null;
		$cpf_cnpj = !empty($body['cpf_cnpj']) ? trim((string)$body['cpf_cnpj']) : null;
		$complemento = !empty($body['complemento']) ? trim((string)$body['complemento']) : '';

		// Tratativas especiais: se nova_entrega == entrega, nova_entrega = null
		if ($nova_entrega !== null && $nova_entrega === $entrega) {
			$nova_entrega = null;
		}

		// Tratativas especiais: se nova_saida == saida, nova_saida = null
		if ($nova_saida !== null && $saida !== null && $nova_saida === $saida) {
			$nova_saida = null;
		}

		$query = 'UPDATE dp_remessas SET 
			titulo = ?, 
			n_nota = ?, 
			entrega = ?, 
			nova_entrega = ?, 
			saida = ?, 
			nova_saida = ?, 
			coleta = ?, 
			nome = ?, 
			telefone = ?, 
			cpf_cnpj = ?, 
			cep = ?, 
			id_estado = ?, 
			id_cidade = ?, 
			endereco = ?, 
			numero = ?, 
			bairro = ?, 
			complemento = ?,
			updated_at = NOW()
		WHERE id = ? AND deleted_at IS NULL';

		$stmt = $pdo->prepare($query);
		$stmt->execute([
			$titulo, $n_nota, $entrega, $nova_entrega, $saida, $nova_saida, 
			$coleta, $nome, $telefone, $cpf_cnpj, $cep, $id_estado, $id_cidade, 
			$endereco, $numero, $bairro, $complemento, $id
		]);

		if ($stmt->rowCount() === 0) {
			json_response(['error' => 'Remessa não encontrada ou não foi possível atualizar'], 404);
			return;
		}

		json_response([
			'message' => 'Remessa atualizada com sucesso',
			'data' => [
				'id' => $id,
				'titulo' => $titulo,
				'nome' => $nome,
			]
		], 200);
	}

	public static function createRemessa(): void {
    $body = read_json_body();

    $id_ordem = (int)($body['id_ordem'] ?? null);
    $id_pedido = (int)($body['id_pedido_ordem'] ?? null);

    $id_old_remessa = (int)($body['id'] ?? null);
    $id_status = (int)($body['id_status'] ?? null);
    $entrega = trim((string)($body['entrega'] ?? ''));
    $saida = trim((string)($body['saida'] ?? ''));
    $nova_entrega = !empty($body['nova_entrega']) ? trim((string)$body['nova_entrega']) : null;
    $nova_saida = !empty($body['nova_saida']) ? trim((string)$body['nova_saida']) : null;
    $nome = trim((string)($body['nome'] ?? ''));
    $telefone = trim((string)($body['telefone'] ?? ''));
    $cpf_cnpj = trim((string)($body['cpf_cnpj'] ?? ''));
    $cep = trim((string)($body['cep'] ?? ''));
    $id_estado = (int)($body['id_estado'] ?? null);
    $id_cidade = (int)($body['id_cidade'] ?? null);
    $endereco = trim((string)($body['endereco'] ?? ''));
    $numero = trim((string)($body['numero'] ?? ''));
    $bairro = trim((string)($body['bairro'] ?? ''));
    $complemento = trim((string)($body['complemento'] ?? ''));

		if ($id_ordem == null || $entrega === '' || $nome === '' || $telefone === '' || $cep === '' || $id_estado <= 0 || $id_cidade <= 0 || $endereco === '' || $bairro === '') {
				json_response(['error' => 'Dados inválidos'], 422);
				return;
		}

    $pdo = Database::pdo();

    try {
			$pdo->beginTransaction();

			$query = 
			'SELECT titulo FROM dp_remessas 
				WHERE deleted_at IS NULL AND id_pedido = ? 
				ORDER BY titulo DESC 
				LIMIT 1';

			$stmt = $pdo->prepare($query);
			$stmt->execute([$id_pedido]);

			$last_title = $stmt->fetch();

			if(!$last_title){
				$title = $id_pedido . '-1';
			} else {
				$parts = explode('-', $last_title['titulo']);
				$number = (int)end($parts);
				$title = $id_pedido . '-' . ($number + 1);
			}
			
			//adicionar nova remessa
			$query = 'INSERT INTO dp_remessas (id_pedido, id_status, titulo, entrega, nova_entrega, saida, nova_saida, nome, telefone, cpf_cnpj, cep, id_estado, id_cidade, endereco, numero, bairro, complemento, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())';
			$stmt = $pdo->prepare($query);
			$stmt->execute([$id_pedido, $id_status, $title, $entrega, $nova_entrega, $saida, $nova_saida, $nome, $telefone, $cpf_cnpj, $cep, $id_estado, $id_cidade, $endereco, $numero, $bairro, $complemento]);

			$id_new_remessa = (int)$pdo->lastInsertId();

			//Atualizar a ordem para a nova remessa
			$query = 'UPDATE dp_ordens SET id_remessa = ? WHERE id = ?';
			$stmt = $pdo->prepare($query);
			$stmt->execute([$id_new_remessa, $id_ordem]);

			if ($stmt->rowCount() === 0) {
				throw new Exception('Não foi possível atualizar a ordem');
			}

			//deleta todas as embalagens da remessa antiga
			$query = 'DELETE FROM dp_embalagens WHERE id_remessa = ?';
			$stmt_delete = $pdo->prepare($query);
			$stmt_delete->execute([$id_old_remessa]);

			//verificar se existem ordens com a remessa antiga
			$query = 'SELECT * FROM dp_ordens WHERE id_remessa = ?;';

			$stmt = $pdo->prepare($query);
			$stmt->execute([$id_old_remessa]);

			$ordem = $stmt->fetch();

			if (!$ordem) {
				//deleta a remessa antiga
				$query = 'UPDATE dp_remessas SET deleted_at = NOW() WHERE id = ? AND deleted_at IS NULL';
				$stmt = $pdo->prepare($query);
				$stmt->execute([$id_old_remessa]);
			}

			$pdo->commit();

			json_response([
				'message' => 'Remessa criada com sucesso',
				'id' => $id_new_remessa,
				'titulo' => $title
			], 201);

    } catch (Exception $e) {
			$pdo->rollBack();
			json_response(['error' => 'Erro ao criar embalagem: ' . $e->getMessage()], 500);
    }
	}

	public static function mudarRemessaOrdem(array $params): void {
    $id = (int)($params['id'] ?? 0);
    $body = read_json_body();

    $old_remessa = (int)($body['old_remessa'] ?? 0);
    $new_remessa = (int)($body['new_remessa'] ?? 0);

    // Validações básicas
    if ($id <= 0 || $old_remessa <= 0 || $new_remessa <= 0) {
			json_response(['error' => 'Dados inválidos'], 422);
			return;
    }

    if ($old_remessa === $new_remessa) {
			json_response(['error' => 'A nova remessa deve ser diferente da atual'], 422);
			return;
    }

    $pdo = Database::pdo();

    try {
			$pdo->beginTransaction();

			// 1. Verificar se a ordem existe e está na remessa antiga
			$query = 'SELECT id_remessa FROM dp_ordens WHERE id = ? AND deleted_at IS NULL';
			$stmt = $pdo->prepare($query);
			$stmt->execute([$id]);
			$ordem = $stmt->fetch();

			if (!$ordem) {
				throw new Exception('Ordem não encontrada');
			}

			if ((int)$ordem['id_remessa'] !== $old_remessa) {
				throw new Exception('A ordem não pertence à remessa informada');
			}

			// 2. Verificar se a nova remessa existe
			$query = 'SELECT id FROM dp_remessas WHERE id = ? AND id_status != 4 AND deleted_at IS NULL';
			$stmt = $pdo->prepare($query);
			$stmt->execute([$new_remessa]);
			$nova_remessa = $stmt->fetch();

			if (!$nova_remessa) {
				throw new Exception('Nova remessa não encontrada');
			}

			// 3. Atualizar a ordem para a nova remessa
			$query = 'UPDATE dp_ordens SET id_remessa = ? WHERE id = ?';
			$stmt = $pdo->prepare($query);
			$stmt->execute([$new_remessa, $id]);

			if ($stmt->rowCount() === 0) {
				throw new Exception('Não foi possível atualizar a ordem');
			}

			// deleta todas as embalagens da remessa antiga
			$query = 'DELETE FROM dp_embalagens WHERE id_remessa = ?';
			$stmt_delete = $pdo->prepare($query);
			$stmt_delete->execute([$old_remessa]);

			// 5. Verificar se a remessa antiga ficou sem ordens
			$query = 'SELECT COUNT(*) as total FROM dp_ordens WHERE id_remessa = ? AND deleted_at IS NULL';
			$stmt = $pdo->prepare($query);
			$stmt->execute([$old_remessa]);
			$resultado = $stmt->fetch();

			// 6. Se a remessa antiga não tem mais ordens, deletar (soft delete)
			if ((int)$resultado['total'] === 0) {
				$query = 'UPDATE dp_remessas SET deleted_at = NOW() WHERE id = ? AND deleted_at IS NULL';
				$stmt = $pdo->prepare($query);
				$stmt->execute([$old_remessa]);
				$remessa_deletada = true;
			} else {
				$remessa_deletada = false;
			}

			$pdo->commit();

			json_response([
				'message' => 'Ordem movida com sucesso',
				'data' => [
						'ordem_id' => $id,
						'old_remessa' => $old_remessa,
						'new_remessa' => $new_remessa,
						'remessa_antiga_deletada' => $remessa_deletada
				]
			], 200);

    } catch (Exception $e) {
			$pdo->rollBack();
			json_response(['error' => 'Erro ao mover ordem: ' . $e->getMessage()], 500);
    }
	}

	public static function getOrdensRemessa(array $params): void {
		$id = (int)($params['id'] ?? 0);

		$pdo = Database::pdo();

		$query = 
			'SELECT A.*, 
        COALESCE((
            SELECT JSON_ARRAYAGG(
                JSON_OBJECT(
                    "id_departamento", D.id,
                    "nome_departamento", D.nome,
                    "id_status", B.id_status,
                    "data_producao", B.data_producao
                )
            )
            FROM dp_ordem_departamento B
            INNER JOIN dp_departamentos D ON B.id_departamento = D.id
            WHERE B.id_ordem = A.id
        ), JSON_ARRAY()) AS departamentos
        FROM dp_ordens A
        INNER JOIN dp_remessas C ON A.id_remessa = C.id
        WHERE C.id = ? AND A.deleted_at IS NULL
        GROUP BY A.id
        ORDER BY A.id ASC';

		$stmt = $pdo->prepare($query);
		$stmt->execute([$id]);

		$ordens = $stmt->fetchAll();

		if (!$ordens) {
			json_response(['error' => 'Not Found'], 404);
			return;
		}

		json_response(['data' => $ordens]);
		return;
	}

	public static function getOrdensRemessaDepartamento(array $params): void {
		$id_departamento = (int)($params['id_departamento'] ?? 0);
		$id = (int)($params['id'] ?? 0);

		$pdo = Database::pdo();

		$query = 
		"SELECT 
				A.*, B.id_status,
				COALESCE(JSON_ARRAYAGG(
						JSON_OBJECT(
								'requisito_id', r.id,
								'nome', r.nome,
								'ordem', r.ordem,
								'status', r.status,
								'dependencias', (
										SELECT JSON_ARRAYAGG(
												JSON_OBJECT(
														'id', d.id,
														'nome', d.nome,
														'cor', d.cor,
														'status', d.status
												)
										)
										FROM dp_dependencias d
										WHERE d.id_requisito = r.id
								)
						)
				), JSON_ARRAY()) AS requisitos
		FROM dp_ordens A
		LEFT JOIN dp_ordem_departamento B ON A.id = B.id_ordem
		LEFT JOIN dp_remessas C ON A.id_remessa = C.id
		LEFT JOIN dp_requisitos r ON r.id_ordem = A.id
		WHERE B.id_departamento = ? 
			AND C.id = ?
		GROUP BY A.id
		ORDER BY A.id ASC";

		$stmt = $pdo->prepare($query);
		$stmt->execute([$id_departamento, $id]);

		$ordens = $stmt->fetchAll();

		if (!$ordens) {
			json_response(['error' => 'Not Found'], 404);
			return;
		}

		json_response(['data' => $ordens]);
		return;
	}

	public static function getRemessas(): void {
		$pdo = Database::pdo();

    $query =
		'SELECT
			A.id, A.titulo, A.id_status, A.saida, A.nova_saida, A.entrega, A.nova_entrega, B.uf, C.cidade,
			COALESCE((
					SELECT JSON_ARRAYAGG(id_pedido)
					FROM (
							SELECT DISTINCT o.id_pedido
							FROM dp_ordens o
							WHERE o.id_remessa = A.id
					) AS distinct_pedidos
			), JSON_ARRAY()) AS pedidos,
			COUNT(DISTINCT e.id) AS embalagens,
			COUNT(DISTINCT v.id) AS volumes,
			COUNT(DISTINCT CASE WHEN v.id_embalagem IS NOT NULL THEN v.id END) AS volumes_embalados,
			COUNT(DISTINCT CASE
					WHEN a.id_status = 4 AND (ch.status = 1 OR ch.id IS NULL)
					THEN v.id
			END) AS volumes_disponiveis
    FROM dp_remessas A
    LEFT JOIN tb_utils_estados B ON A.id_estado = B.id
    LEFT JOIN tb_utils_cidades C ON A.id_cidade = C.id
    LEFT JOIN dp_ordens o ON o.id_remessa = A.id
    LEFT JOIN dp_atividades a ON a.id_ordem = o.id
    LEFT JOIN dp_volumes v ON v.id_atividade = a.id
    LEFT JOIN dp_embalagens e ON e.id_remessa = A.id
    LEFT JOIN dp_checklists ch ON ch.id_atividade = v.id_atividade
    WHERE A.id_status IN (0, 1, 2, 3) AND A.deleted_at IS NULL
    GROUP BY A.id, A.titulo, A.id_status, A.saida, A.nova_saida, A.entrega, A.nova_entrega, B.uf, C.cidade
    ORDER BY volumes_disponiveis DESC;';

		$stmt = $pdo->prepare($query);
		$stmt->execute();

		$remessas = $stmt->fetchAll();

		if (!$remessas) {
			json_response(['error' => 'Not Found'], 404);
			return;
		}

		json_response(['data' => $remessas]);
		return;
	}

	public static function getRemessasEmAndamento(): void {
		$pdo = Database::pdo();

    $query = 
		'SELECT id, titulo
			FROM dp_remessas
			WHERE id_status != 4 AND deleted_at IS NULL ORDER BY titulo ASC;';

		$stmt = $pdo->prepare($query);
		$stmt->execute();

		$remessas = $stmt->fetchAll();

		if (!$remessas) {
			json_response(['error' => 'Not Found'], 404);
			return;
		}

		json_response(['data' => $remessas]);
		return;
	}

	public static function getVolumes(array $params): void {
		$id = (int)($params['id'] ?? 0);

		$pdo = Database::pdo();

		$query = 
			'SELECT 
				A.*, B.id_status as atividade_status, C.id_pedido,
				(SELECT COUNT(*) FROM dp_checklists WHERE id_atividade = B.id AND status != -1) AS total_checklists,
				(SELECT COUNT(*) FROM dp_checklists WHERE id_atividade = B.id AND status = 1) AS checklists_concluidos
				FROM dp_volumes A
				LEFT JOIN dp_atividades B ON A.id_atividade = B.id
				LEFT JOIN dp_ordens C ON B.id_ordem = C.id
				WHERE C.id_remessa = ?;';

		$stmt = $pdo->prepare($query);
		$stmt->execute([$id]);

		$volumes = $stmt->fetchAll();

		json_response(['data' => $volumes]);
		return;
	}

	public static function createEmbalagem(array $params): void {
    $body = read_json_body();
    
    $id_remessa = (int)($body['id_remessa'] ?? null);
    $descricao = trim((string)($body['descricao'] ?? ''));
    $comprimento = (float)($body['comprimento'] ?? 0);
    $largura = (float)($body['largura'] ?? 0);
    $altura = (float)($body['altura'] ?? 0);
    $peso = (float)($body['peso'] ?? 0);
    $volumes = $body['volumes'] ?? [];

    if ($id_remessa <= 0 || $descricao === '' || $comprimento <= 0 || $largura <= 0 || $altura <= 0 || $peso <= 0 || empty($volumes)) {
			json_response(['error' => 'Dados inválidos'], 422);
			return;
    }

    if (!is_array($volumes)) {
			json_response(['error' => 'Volumes deve ser um array'], 422);
			return;
    }

    $pdo = Database::pdo();

    try {
			$pdo->beginTransaction();

			$query = 'INSERT INTO dp_embalagens (id_remessa, descricao, comprimento, largura, altura, peso, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())';
			$stmt = $pdo->prepare($query);
			$stmt->execute([$id_remessa, $descricao, $comprimento, $largura, $altura, $peso]);

			$id_embalagem = (int)$pdo->lastInsertId();

			$placeholders = str_repeat('?,', count($volumes) - 1) . '?';
			$query_volumes = "UPDATE dp_volumes SET id_embalagem = ? WHERE id IN ($placeholders)";
			$stmt_volumes = $pdo->prepare($query_volumes);
			
			$params_volumes = array_merge([$id_embalagem], $volumes);
			$stmt_volumes->execute($params_volumes);

			if ($stmt_volumes->rowCount() === 0) {
				throw new Exception('Nenhum volume foi encontrado para atualizar');
			}

			$query_remessa = 'UPDATE dp_remessas SET id_status = 2, updated_at = NOW() WHERE id = ? AND deleted_at IS NULL';
			$stmt_remessa = $pdo->prepare($query_remessa);
			$stmt_remessa->execute([$id_remessa]);

			if ($stmt_remessa->rowCount() === 0) {
				throw new Exception('Remessa não encontrada ou não foi possível atualizar');
			}

			$pdo->commit();

			json_response([
				'message' => 'Embalagem criada com sucesso',
				'data' => [
					'id' => $id_embalagem,
					'id_remessa' => $id_remessa,
					'descricao' => $descricao,
					'comprimento' => $comprimento,
					'largura' => $largura,
					'altura' => $altura,
					'peso' => $peso,
					'volumes_atualizados' => $stmt_volumes->rowCount(),
					'remessa_status_atualizado' => true
				]
			], 201);

    } catch (Exception $e) {
			$pdo->rollBack();
			json_response(['error' => 'Erro ao criar embalagem: ' . $e->getMessage()], 500);
    }
	}

	public static function getEmbalagens(array $params): void {
		$id = (int)($params['id'] ?? 0);

		$pdo = Database::pdo();

    $query = 
			'SELECT A.*, 
			COALESCE((
					SELECT JSON_ARRAYAGG(vol.volume) 
					FROM dp_volumes vol 
					WHERE vol.id_embalagem = A.id
			), JSON_ARRAY()) AS volumes
			FROM dp_embalagens A
			WHERE A.id_remessa = ?;';

		$stmt = $pdo->prepare($query);
		$stmt->execute([$id]);

		$embalagens = $stmt->fetchAll();

		json_response(['data' => $embalagens]);
		return;
	}

	public static function deleteEmbalagem(array $params): void {
    $id = (int)($params['id'] ?? 0);

    $pdo = Database::pdo();

    try {
			$pdo->beginTransaction();

			$stmt = $pdo->prepare('SELECT * FROM dp_embalagens WHERE id = ?');
			$stmt->execute([$id]);
			$embalagem = $stmt->fetch();

			if (!$embalagem) {
					json_response(['error' => 'Embalagem não encontrada'], 404);
					return;
			}

			$query = 'DELETE FROM dp_embalagens WHERE id = ?';
			$stmt_delete = $pdo->prepare($query);
			$stmt_delete->execute([$id]);

			if ($stmt_delete->rowCount() === 0) {
					throw new Exception('Não foi possível deletar a embalagem');
			}

			$pdo->commit();

		$query = 
			'SELECT A.*, 
			COALESCE((
					SELECT JSON_ARRAYAGG(vol.volume) 
					FROM dp_volumes vol 
					WHERE vol.id_embalagem = A.id
			), JSON_ARRAY()) AS volumes
			FROM dp_embalagens A
			WHERE A.id_remessa = ?;';

		$stmt = $pdo->prepare($query);
		$stmt->execute([$id]);

		$embalagens = $stmt->fetchAll();

			json_response([
					'message' => 'Embalagem deletada com sucesso',
					'data' => $embalagens
			], 200);

    } catch (Exception $e) {
        $pdo->rollBack();
        json_response(['error' => 'Erro ao deletar embalagem: ' . $e->getMessage()], 500);
    }
	}

	public static function finalizarRemessa(array $params): void {
		$id = (int)($params['id'] ?? 0);

		if ($id <= 0) {
			json_response([
					'error' => 'ID inválido',
			], 422);
			return;
		}

		$pdo = Database::pdo();

		try {
				$pdo->beginTransaction();

				$query_check = 'SELECT id, id_status FROM dp_remessas WHERE id = ? AND deleted_at IS NULL';
				$stmt_check = $pdo->prepare($query_check);
				$stmt_check->execute([$id]);
				$remessa = $stmt_check->fetch();

				if (!$remessa) {
						throw new Exception('Remessa não encontrada');
				}

				if ((int)$remessa['id_status'] === 4) {
						throw new Exception('Remessa já está finalizada');
				}

				$query = 'UPDATE dp_remessas SET id_status = 4, updated_at = NOW() WHERE id = ? AND deleted_at IS NULL';
				$stmt = $pdo->prepare($query);
				$stmt->execute([$id]);

				if ($stmt->rowCount() === 0) {
						throw new Exception('Remessa não encontrada ou não foi possível atualizar');
				}

				$pdo->commit();

				json_response([
						'message' => 'Remessa finalizada com sucesso',
						'data' => [
								'id' => $id,
								'id_status' => 4
						]
				], 200);

		} catch (Exception $e) {
				$pdo->rollBack();
				json_response(['error' => 'Erro ao finalizar remessa: ' . $e->getMessage()], 500);
		}
	}
}