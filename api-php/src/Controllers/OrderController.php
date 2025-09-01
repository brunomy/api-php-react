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
						A.*, C.titulo AS titulo_remessa, C.entrega, C.nova_entrega, C.saida, C.nova_saida, C.nome, D.cidade, E.uf,
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
				LEFT JOIN tb_utils_cidades D ON C.id_cidade = D.id
				LEFT JOIN tb_utils_estados E ON D.id_estado = E.id
				
				LEFT JOIN dp_requisitos r ON r.id_ordem = A.id
				WHERE A.deleted_at IS NULL AND B.id_departamento = ?
				GROUP BY A.id
				ORDER BY A.created_at ASC;";

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

    $pdo = Database::pdo();
    $query = 
    $query = 
        "SELECT 
            A.*, C.titulo AS titulo_remessa, C.entrega, C.nova_entrega, C.saida, C.nova_saida, C.nome, D.cidade, E.uf,
            COALESCE(JSON_ARRAYAGG(
                CASE 
                    WHEN r.id IS NOT NULL THEN
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
                    ELSE NULL
                END
            ), JSON_ARRAY()) AS requisitos,
            (
                SELECT COUNT(*) 
                FROM dp_atividades at 
                WHERE at.id_ordem = A.id AND at.deleted_at IS NULL
            ) AS atividades,
            (
                SELECT COUNT(*) 
                FROM dp_atividades at 
                WHERE at.id_ordem = A.id AND at.status = 4 AND at.deleted_at IS NULL
            ) AS atividades_finalizadas,
            (
                SELECT COUNT(*) 
                FROM dp_checklists ch 
                INNER JOIN dp_atividades at ON ch.id_atividade = at.id 
                WHERE at.id_ordem = A.id AND ch.status IN (0, 1)
            ) AS checklists,
            (
                SELECT COUNT(*) 
                FROM dp_checklists ch 
                INNER JOIN dp_atividades at ON ch.id_atividade = at.id 
                WHERE at.id_ordem = A.id AND ch.status = 1
            ) AS checklists_finalizados,
            (
                SELECT COUNT(*) 
                FROM dp_volumes v 
                INNER JOIN dp_atividades at ON v.id_atividade = at.id 
                WHERE at.id_ordem = A.id
            ) AS volumes,
            (
                SELECT COUNT(*) 
                FROM dp_volumes v 
                INNER JOIN dp_atividades at ON v.id_atividade = at.id 
                WHERE at.id_ordem = A.id AND v.id_embalagem IS NOT NULL
            ) AS volumes_embalados
        FROM dp_ordens A
        LEFT JOIN dp_categoria_departamento B ON A.id_categoria = B.id_categoria
        LEFT JOIN dp_remessas C ON A.id_remessa = C.id
        LEFT JOIN tb_utils_cidades D ON C.id_cidade = D.id
        LEFT JOIN tb_utils_estados E ON D.id_estado = E.id
        LEFT JOIN dp_requisitos r ON r.id_ordem = A.id
        WHERE A.deleted_at IS NULL AND A.id = ?
        GROUP BY A.id";

    $stmt = $pdo->prepare($query);
    $stmt->execute([$id]);

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
		$body = read_json_body();

		$pdo = Database::pdo();

		// Atualizar status da ordem
		$query = 'UPDATE dp_ordens SET id_status = 1 WHERE id = ? AND deleted_at IS NULL';
		$stmt = $pdo->prepare($query);
		$stmt->execute([$id]);

		// Atualizar status das atividades
		$query = 'UPDATE dp_atividades SET id_status = 1 WHERE id_ordem = ? AND deleted_at IS NULL';
		$stmt = $pdo->prepare($query);
		$stmt->execute([$id]);

		json_response([
				'message' => 'Ordem enviada para produção',
				'data' => [
						'id' => $id
				]
		], 200);
	}
}
