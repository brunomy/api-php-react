<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Database;

final class OrderController {
    public static function getOrdensDepartamento(array $params): void {
		$idDepartamento = (int)($params['idDepartamento'] ?? 0);

		$pdo = Database::pdo();
        $query = 
            'SELECT A.*, C.titulo AS titulo_remessa, C.entrega, C.nova_entrega, C.saida, C.nova_saida, C.nome, D.cidade, E.uf
                FROM dp_ordens A
                LEFT JOIN dp_categoria_departamento B ON A.id_categoria = B.id_categoria
                LEFT JOIN dp_remessas C ON A.id_remessa = C.id
								LEFT JOIN tb_utils_cidades D ON C.id_cidade = D.id
								LEFT JOIN tb_utils_estados E ON D.id_estado = E.id
                WHERE B.id_departamento = ?';

		$stmt = $pdo->prepare($query);
		$stmt->execute([$idDepartamento]);

		$ordens = $stmt->fetchAll();
		
		if (!$ordens) {
			json_response(['error' => 'Not Found'], 404);
			return;
		}

		json_response(['data' => $ordens]);
		return;
	}

}
