<?php 
    
/*
    Este script cria o xml para o google shooping do site realpoker
    Precisa ser executado todos os dias para manter o xml atualizado,
    Se estiver rodando pelo forge, use o recurso schedule do próprio forge
    Caso contrário, configure CRONTAB 
*/

require_once dirname(__DIR__).'/public/sistema/System/Core/Loader.php';

use System\Core\Bootstrap;
$sistema = new Bootstrap();


$filename = dirname(__DIR__)."/public/data/produtos.xml";

$query = $sistema->DB_fetch_array("SELECT A.id, A.id_produto, A.nome, A.resumo, B.qtd_minima, (IFNULL(SUM(G.custo),0)+B.custo+B.frete_embutido) valor, CONCAT(C.seo_url_breadcrumbs,C.seo_url) seo_url, A.imagem, A.googleshop_img, E.nome categoria  FROM tb_produtos_personalizados A INNER JOIN tb_produtos_produtos B ON B.id = A.id_produto INNER JOIN tb_seo_paginas C ON C.id = A.id_seo INNER JOIN tb_produtos_produtos_has_tb_produtos_categorias D ON D.id_produto = A.id_produto INNER JOIN tb_produtos_categorias E ON E.id = D.id_categoria LEFT JOIN tb_produtos_personalizados_has_tb_produtos_atributos F ON F.id_produto_personalizado = A.id INNER JOIN tb_produtos_atributos G ON G.id = F.id_atributo WHERE A.apagado != 1 AND A.stats = 1 AND E.stats = 1 AND B.apagado != 1 AND B.stats = 1 GROUP BY A.id, A.nome, A.resumo, B.custo, B.frete_embutido, C.seo_url_breadcrumbs, C.seo_url, A.imagem, A.googleshop_img, E.nome, A.ordem ORDER BY A.ordem");

if ($query->num_rows) {
    $sistema->upload_path = "uploads/";
    $xml = "";
    $xml .= '<?xml version="1.0" encoding="utf-8"?>';
    $xml .= '<rss xmlns:g="http://base.google.com/ns/1.0" version="2.0" encoding="utf-8">';
    $xml .= '<channel>';

    foreach ($query->rows as $row) {

        $qr = $sistema->DB_fetch_array("SELECT * FROM tb_produtos_has_tb_descontos A INNER JOIN tb_produtos_descontos B ON B.id = A.id_desconto WHERE A.id_produto = ".$row['id_produto']." ORDER BY B.quantidade LIMIT 1");
        $valor = $row['valor'];
        if($qr->num_rows){
            if($row['qtd_minima'] >= $qr->rows[0]['quantidade']){
                if($qr->rows[0]['porcentagem']){
                    $valor = $valor - ($valor * $qr->rows[0]['valor'] / 100);
                }else{
                    $valor = ($valor - $qr->rows[0]['valor']);
                }
            }
        }

        $valor = $valor - ($valor * 5 / 100);  // 5% de desconto avista

        $xml .= '<item>';
        $xml .= "<g:id>{$row['id']}</g:id>";
        $xml .= "<g:title>{$row['nome']}</g:title>";
        $xml .= "<g:description>{$row['resumo']}</g:description>";
        $xml .= "<g:product_url>https://www.realpoker.com.br/". $row['seo_url'] . "/?utm_source=googleshopping</g:product_url>";
        if ($row['googleshop_img'] != "") {
            $xml .= "<g:image_link>https://www.realpoker.com.br/" . $sistema->getImageFileSized($row['googleshop_img'],385,385) . "</g:image_link>";
        }
        $xml .= "<g:availability>in stock</g:availability>";
        $xml .= "<g:condition>new</g:condition>";
        $xml .= "<g:identifier_exists>FALSE</g:identifier_exists>";
        $xml .= "<g:google_product_category>2693</g:google_product_category>";
        $xml .= "<g:product_type>{$sistema->_empresa['nome']}</g:product_type>";
        $xml .= "<g:price>" . number_format($valor, 2, '.', '') . " BRL</g:price>";
        $xml .= '</item>';
    }

    $xml .= '</channel>';
    $xml .= '</rss>';

    $fp = fopen($filename, "w+", 0);

    fwrite($fp, $xml, strlen($xml));
    fclose($fp);
}