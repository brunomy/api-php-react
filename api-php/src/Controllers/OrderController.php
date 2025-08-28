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
				WHERE A.deleted_at IS NULL AND A.id = ?;";

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
}
