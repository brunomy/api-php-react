<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Database;

final class OrderController {
	public static function getOrdensDepartamento(array $params): void {
		$id = (int)($params['id'] ?? 0);

		$pdo = Database::pdo();
        $query = 
            "SELECT 
                A.*, B.id_status, B.data_producao , D.titulo AS titulo_remessa, D.entrega, D.nova_entrega, D.saida, D.nova_saida, D.nome, E.cidade, F.uf,
                (
                    SELECT MAX(at.data)
                    FROM dp_atividades at 
                    WHERE at.id_ordem = A.id AND at.deleted_at IS NULL
                ) AS maior_data_atividade,
                (
                    SELECT COUNT(*) 
                    FROM dp_atividades at 
                    WHERE at.id_ordem = A.id AND at.id_departamento = B.id_departamento AND at.id_status = 4 AND at.deleted_at IS NULL
                ) AS atividades_finalizadas,
                (
                    SELECT COUNT(*) 
                    FROM dp_atividades at 
                    WHERE at.id_ordem = A.id AND at.id_departamento = B.id_departamento AND at.deleted_at IS NULL
                ) AS atividades,
                COALESCE((
                    SELECT JSON_ARRAYAGG(
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
                    )
                    FROM dp_requisitos r
                    WHERE r.id_ordem = A.id
                ), JSON_ARRAY()) AS requisitos
            FROM dp_ordens A
            LEFT JOIN dp_ordem_departamento B ON A.id = B.id_ordem
            LEFT JOIN dp_categoria_departamento C ON A.id_categoria = C.id_categoria
            LEFT JOIN dp_remessas D ON A.id_remessa = D.id
            LEFT JOIN tb_utils_cidades E ON D.id_cidade = E.id
            LEFT JOIN tb_utils_estados F ON E.id_estado = F.id
            WHERE A.deleted_at IS NULL AND B.id_departamento = ?
            GROUP BY A.id
            ORDER BY A.created_at ASC";

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

	public static function getOrdem(array $params): void {
        $id = (int)($params['id'] ?? 0);
        $id_departamento = (int)($params['id_departamento'] ?? 0);

        $pdo = Database::pdo();
        $query = 
        "SELECT 
            A.*, B.id_status, B.data_producao, D.titulo AS titulo_remessa, D.entrega, D.nova_entrega, D.saida, D.nova_saida, D.nome, E.cidade, F.uf,
            (
                SELECT COUNT(*) 
                FROM dp_atividades at 
                WHERE at.id_ordem = A.id AND B.id_departamento = at.id_departamento AND at.deleted_at IS NULL
            ) AS atividades,
            (
                SELECT MAX(at.data)
                FROM dp_atividades at 
                WHERE at.id_ordem = A.id AND B.id_departamento = at.id_departamento AND at.deleted_at IS NULL
            ) AS maior_data_atividade,
            (
                SELECT COUNT(*) 
                FROM dp_atividades at 
                WHERE at.id_ordem = A.id AND B.id_departamento = at.id_departamento AND at.id_status = 4 AND at.deleted_at IS NULL
            ) AS atividades_finalizadas,
            (
                SELECT COUNT(*) 
                FROM dp_checklists ch 
                INNER JOIN dp_atividades at ON ch.id_atividade = at.id 
                WHERE at.id_ordem = A.id AND B.id_departamento = at.id_departamento AND ch.status IN (0, 1)
            ) AS checklists,
            (
                SELECT COUNT(*) 
                FROM dp_checklists ch 
                INNER JOIN dp_atividades at ON ch.id_atividade = at.id 
                WHERE at.id_ordem = A.id AND B.id_departamento = at.id_departamento AND ch.status = 1
            ) AS checklists_finalizados,
            (
                SELECT COUNT(*) 
                FROM dp_volumes v 
                INNER JOIN dp_atividades at ON v.id_atividade = at.id 
                WHERE at.id_ordem = A.id AND B.id_departamento = at.id_departamento 
            ) AS volumes,
            (
                SELECT COUNT(*) 
                FROM dp_volumes v 
                INNER JOIN dp_atividades at ON v.id_atividade = at.id 
                WHERE at.id_ordem = A.id AND B.id_departamento = at.id_departamento AND v.id_embalagem IS NOT NULL
            ) AS volumes_embalados,
            COALESCE((
                SELECT JSON_ARRAYAGG(
                    JSON_OBJECT(
                        'id', r.id,
                        'nome', r.nome,
                        'ordem', r.ordem,
                        'status', r.status,
                        'updated_at', r.updated_at,
                        'dependencias', (
                            SELECT JSON_ARRAYAGG(
                                JSON_OBJECT(
                                    'id', d.id,
                                    'nome', d.nome,
                                    'cor', d.cor,
                                    'status', d.status,
                                    'updated_at', d.updated_at
                                )
                            )
                            FROM dp_dependencias d
                            WHERE d.id_requisito = r.id
                        )
                    )
                )
                FROM dp_requisitos r
                WHERE r.id_ordem = A.id
            ), JSON_ARRAY()) AS requisitos
        FROM dp_ordens A
        LEFT JOIN dp_ordem_departamento B ON A.id = B.id_ordem
        LEFT JOIN dp_categoria_departamento C ON A.id_categoria = C.id_categoria
        LEFT JOIN dp_remessas D ON A.id_remessa = D.id
        LEFT JOIN tb_utils_cidades E ON D.id_cidade = E.id
        LEFT JOIN tb_utils_estados F ON E.id_estado = F.id
        WHERE A.deleted_at IS NULL AND A.id = ? AND B.id_departamento = ?";

        $stmt = $pdo->prepare($query);
        $stmt->execute([$id, $id_departamento]);

        $ordem = $stmt->fetch();

        if (!$ordem) {
            json_response(['error' => 'Not Found'], 404);
            return;
        }

        json_response(['data' => $ordem]);
        return;
	}

	public static function getProduto(array $params): void {
        $id = (int)($params['ordem_id'] ?? 0);

        $pdo = Database::pdo();
        $query = 
            "SELECT 
                A.nome_produto,
                A.nome_categoria,
                A.observacao,
                A.anexo,
                A.foto_final,
                A.agrupavel,
                A.quantidade,
                B.id,
                COALESCE(JSON_ARRAYAGG(
                    CASE 
                        WHEN C.nome_conjunto IS NOT NULL THEN
                            JSON_OBJECT(
                                'nome_conjunto', C.nome_conjunto,
                                'nome_atributo', C.nome_atributo,
                                'texto', C.texto,
                                'arquivo', C.arquivo,
                                'cor', C.cor
                            )
                        ELSE NULL
                    END
                ), JSON_ARRAY()) AS atributos
            FROM dp_ordens A
            LEFT JOIN tb_carrinho_produtos_historico B ON A.id_carrinho_produto = B.id
            LEFT JOIN tb_carrinho_atributos_historico C ON B.id = C.id_carrinho_produto_historico
            WHERE A.id = ? AND A.deleted_at IS NULL
            GROUP BY A.id, B.id";

        $stmt = $pdo->prepare($query);
        $stmt->execute([$id]);

        $produto = $stmt->fetch();

        if (!$produto) {
            json_response(['error' => 'Not Found'], 404);
            return;
        }

        json_response(['data' => $produto]);
        return;
	}

	public static function enviarProducao(array $params): void {
		$id = (int)($params['id'] ?? 0);
		$id_departamento = (int)($params['id_departamento'] ?? 0);
		$body = read_json_body();

		$pdo = Database::pdo();

		// Atualizar status da ordem
		$query = 'UPDATE dp_ordem_departamento SET id_status = 1, data_producao = NOW() WHERE id_departamento = ? AND id_ordem = ?';
		$stmt = $pdo->prepare($query);
		$stmt->execute([$id_departamento, $id]);

		// Atualizar status das atividades
		$query = 'UPDATE dp_atividades SET id_status = 1 WHERE id_departamento = ? AND id_ordem = ? AND deleted_at IS NULL';
		$stmt = $pdo->prepare($query);
		$stmt->execute([$id_departamento, $id]);

		json_response([
				'message' => 'Ordem enviada para produção',
				'data' => [
						'id' => $id
				]
		], 200);
	}

	public static function concluirDependencia(array $params): void {
		$id = (int)($params['id'] ?? 0);
		$body = read_json_body();

		$pdo = Database::pdo();

		// Atualizar status da ordem
		$query = 'UPDATE dp_dependencias SET status = 1, updated_at = NOW() WHERE id = ?';
		$stmt = $pdo->prepare($query);
		$stmt->execute([$id]);

		json_response([
			'message' => 'Dependência concluída',
			'data' => [
				'id' => $id
			]
		], 200);
	}

	public static function concluirRequisito(array $params): void {
		$id = (int)($params['id'] ?? 0);
		$body = read_json_body();

		$pdo = Database::pdo();

		// Atualizar status da ordem
		$query = 'UPDATE dp_requisitos SET status = 1, updated_at = NOW() WHERE id = ?';
		$stmt = $pdo->prepare($query);
		$stmt->execute([$id]);

		json_response([
			'message' => 'Requisito concluído',
			'data' => [
				'id' => $id
			]
		], 200);
	}

    public static function getHistorico(array $params): void {
        $id = (int)($params['id'] ?? 0);
        $id_departamento = (int)($params['id_departamento'] ?? 0);

        $pdo = Database::pdo();
        $query = 
        "SELECT A.created_at, A.descricao, B.nome AS nome_funcionario, C.nome AS nome_equipe, D.nome AS nome_usuario
            FROM dp_historico A
            LEFT JOIN dp_funcionarios B ON A.id_funcionario = B.id
            LEFT JOIN dp_equipes C ON B.id_equipe = C.id
            LEFT JOIN dp_users D ON A.id_user = D.id
            WHERE A.id_ordem = ? AND A.id_departamento = ? ORDER BY created_at ASC";

        $stmt = $pdo->prepare($query);
        $stmt->execute([$id, $id_departamento]);

        $historico = $stmt->fetchAll();

        if (!$historico) {
            json_response(['error' => 'Not Found'], 404);
            return;
        }

        json_response(['data' => $historico]);
        return;
	}
}
