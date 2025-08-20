<?php
    
/*
    Este script tem como objetivo preencher as imagens que são 
    disponibilizadas no xml do google shopping, duplicando a imagem
    de capa dos produtos customizados quando não existe imagem cadastrada
    para o google shopping. No redimensionamento, é aplicado um efeito na imagem.
*/

require_once dirname(__DIR__).'/public/sistema/System/Core/Loader.php';

use System\Core\Bootstrap;
use System\Libs\SimpleImage;
$sistema = new Bootstrap();

$sistema->upload_path = dirname(__DIR__)."/public/uploads/";

$query = $sistema->DB_fetch_array("SELECT A.id, A.imagem, A.googleshop_img FROM tb_produtos_personalizados A INNER JOIN tb_produtos_produtos B ON B.id = A.id_produto INNER JOIN tb_seo_paginas C ON C.id = A.id_seo INNER JOIN tb_produtos_produtos_has_tb_produtos_categorias D ON D.id_produto = A.id_produto INNER JOIN tb_produtos_categorias E ON E.id = D.id_categoria LEFT JOIN tb_produtos_personalizados_has_tb_produtos_atributos F ON F.id_produto_personalizado = A.id INNER JOIN tb_produtos_atributos G ON G.id = F.id_atributo WHERE A.googleshop_img IS NULL AND A.apagado != 1 AND A.stats = 1 AND E.stats = 1 AND B.apagado != 1 AND B.stats = 1 GROUP BY A.id, A.nome, A.resumo, B.custo, B.frete_embutido, C.seo_url_breadcrumbs, C.seo_url, A.imagem, A.googleshop_img, E.nome, A.ordem ORDER BY A.ordem");

if ($query->num_rows) {
    foreach ($query->rows as $row) {
        $imagem_original = $row['imagem'];
        $crop = array(
            array("width" => 385, "height" => 385, "overlaped" => true)
        );
        $duplicate = $sistema->duplicateFile($imagem_original, array("jpg", "jpeg", "gif", "png"), $crop);
        if ($duplicate->return) {
            $sistema->DB_update("tb_produtos_personalizados", "googleshop_img = '".$duplicate->file_uploaded."' WHERE id = ".$row['id']);
            echo "Imagem gerada com sucesso do produto customizado #".$row['id']." - ".$duplicate->file_uploaded . PHP_EOL;
        }else{
            echo "Aconteceu algum erro ao tentar gerar imagem do produto ".$row['id'] . PHP_EOL;
            print_r($duplicate);
        }
    }
}

?>