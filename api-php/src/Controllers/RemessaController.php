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
		$idEstado = (int)($params['idEstado'] ?? 0);

		$pdo = Database::pdo();
		$query = 
			'SELECT * FROM tb_utils_cidades WHERE id_estado = ?;';

		$stmt = $pdo->prepare($query);
		$stmt->execute([$idEstado]);

		$cidades = $stmt->fetchAll();

		if (!$cidades) {
			json_response(['error' => 'Not Found'], 404);
			return;
		}

		json_response(['data' => $cidades]);
		return;
	}

	public static function getRemessa(array $params): void {
		$idRemessa = (int)($params['idRemessa'] ?? 0);

		$pdo = Database::pdo();

		$query = 
			'SELECT * FROM dp_remessas WHERE id = ?;';

		$stmt = $pdo->prepare($query);
		$stmt->execute([$idRemessa]);

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
		$id_status = !empty($body['id_status']) ? (int)$body['id_status'] : null;
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
			id_status = ?, 
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
			$id_status, $titulo, $n_nota, $entrega, $nova_entrega, $saida, $nova_saida, 
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

	public static function getOrdensRemessa(array $params): void {
		$id = (int)($params['id'] ?? 0);

		$pdo = Database::pdo();

		$query = 
			'SELECT A.* 
				FROM dp_ordens A
				LEFT JOIN dp_remessas B ON A.id_remessa = B.id
				WHERE B.id = ?;';

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
				A.*,
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
		LEFT JOIN dp_categoria_departamento B ON A.id_categoria = B.id_categoria
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

}
