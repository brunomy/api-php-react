<?php 

/*
    Este script gera o sitemap.xml do site realpoker
    Precisa ser executado todos os dias para manter o xml atualizado,
    Se estiver rodando pelo forge, use o recurso schedule do próprio forge
    Caso contrário, configure CRONTAB 
*/

require_once __DIR__.'/../public/sistema/System/Core/Loader.php';
use System\Core\Bootstrap;

if (!isset($sistema)) {
    $sistema = new Bootstrap();
}

$xml = '<?xml version="1.0" encoding="UTF-8"?>
<urlset
      xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
      xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
      xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9
            http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">';


//INSERÇÕES MANUAIS/FIXAS
$xml .= '
    <url>
        <loc>'.$sistema->site_url.'/</loc>
        <lastmod>'.date('Y-m-d').'T'.date('H:i:sP', time()).'</lastmod>
        <priority>0.8</priority>
    </url>
    <url>
        <loc>'.$sistema->site_url.'/login</loc>
        <lastmod>'.date('Y-m-d').'T'.date('H:i:sP', time()).'</lastmod>
        <priority>0.8</priority>
    </url>
    <url>
        <loc>'.$sistema->site_url.'/blog</loc>
        <lastmod>'.date('Y-m-d').'T'.date('H:i:sP', time()).'</lastmod>
        <priority>0.8</priority>
    </url>
    <url>
        <loc>'.$sistema->site_url.'/cadastro</loc>
        <lastmod>'.date('Y-m-d').'T'.date('H:i:sP', time()).'</lastmod>
        <priority>0.8</priority>
    </url>
    <url>
        <loc>'.$sistema->site_url.'/produtos-personalizados</loc>
        <lastmod>'.date('Y-m-d').'T'.date('H:i:sP', time()).'</lastmod>
        <priority>0.8</priority>
    </url>
    <url>
        <loc>'.$sistema->site_url.'/produtos</loc>
        <lastmod>'.date('Y-m-d').'T'.date('H:i:sP', time()).'</lastmod>
        <priority>0.8</priority>
    </url>
    <url>
        <loc>'.$sistema->site_url.'/wsop</loc>
        <lastmod>'.date('Y-m-d').'T'.date('H:i:sP', time()).'</lastmod>
        <priority>0.8</priority>
    </url>
';

//PÁGINAS INSTITUCIONAIS
$query = $sistema->DB_fetch_array("SELECT * FROM tb_institucional_paginas a JOIN tb_seo_paginas b ON a.id_seo = b.id WHERE a.stats = 1 AND a.id_categoria IN (1,2)");
$paginas = $query->rows;

foreach ($paginas as $i => $pagina) {
    $url = $pagina['seo_url_breadcrumbs'] != "" ?  $pagina['seo_url_breadcrumbs'].$pagina['seo_url'] : $pagina['seo_url'];

    $xml .= '
    <url>
        <loc>'.$sistema->site_url .'/'. $url .'</loc>
        <lastmod>'.date('Y-m-d').'T'.date('H:i:sP', time()).'</lastmod>
        <priority>0.8</priority>
    </url>';
}

//PRODUTOS
$query = $sistema->DB_fetch_array("SELECT *, a.id id_produto FROM tb_produtos_produtos a JOIN tb_seo_paginas b ON a.id_seo = b.id WHERE a.stats = 1 AND a.apagado = 0");
$paginas = $query->rows;

foreach ($paginas as $i => $pagina) {
    $url = $pagina['seo_url_breadcrumbs'] != "" ?  $pagina['seo_url_breadcrumbs'].$pagina['seo_url'] : $pagina['seo_url'];

    $xml .= '
    <url>
        <loc>'.$sistema->site_url .'/'. $url .'</loc>
        <lastmod>'.date('Y-m-d').'T'.date('H:i:sP', time()).'</lastmod>
        <priority>0.8</priority>
    </url>';
}

//LISTAGEM DOS PRODUTOS PERSONALIZADOS
$query = $sistema->DB_fetch_array("SELECT C.seo_url FROM tb_produtos_personalizados A 
JOIN tb_produtos_produtos B ON A.id_produto = B.id AND A.stats = 1 AND A.apagado = 0
JOIN tb_seo_paginas C ON B.id_seo = C.id
GROUP BY A.id_produto");

$paginas = $query->rows;

foreach ($paginas as $i => $pagina) {
    $xml .= '
    <url>
        <loc>'.$sistema->site_url .'/produtos-personalizados/'. $pagina['seo_url'] .'</loc>
        <lastmod>'.date('Y-m-d').'T'.date('H:i:sP', time()).'</lastmod>
        <priority>0.8</priority>
    </url>';
}

$xml .= '
</urlset>';

$arquivo = fopen(__DIR__.'/../public/sitemap.xml', 'w');
if (fwrite($arquivo, $xml)) {
    echo "Arquivo sitemap.xml criado com sucesso";
} else {
    echo "Não foi possível criar o arquivo. Verifique as permissões do diretório.";
}
fclose($arquivo);


?>