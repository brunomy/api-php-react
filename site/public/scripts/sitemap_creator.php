<?php 

require_once '../sistema/System/Core/Loader.php';
require_once "../_system.php";

$sistema = new _sys();

$query = $sistema->DB_fetch_array("SELECT * FROM tb_institucional_paginas a JOIN tb_seo_paginas b ON a.id_seo = b.id WHERE a.stats = 1");

$paginas = $query->rows;

$xml = '<?xml version="1.0" encoding="UTF-8"?>';
$xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

foreach ($paginas as $i => $pagina) {
    $url = $pagina['seo_url_breadcrumbs'] != "" ?  $pagina['seo_url_breadcrumbs'].$pagina['seo_url'] : $pagina['seo_url'];
    //echo $sistema->site_url .'/'. $url . '<br>' . PHP_EOL;

    $xml .= '<url>
                <loc>'.$sistema->site_url .'/'. $url .'</loc>
                <lastmod>'.date('Y-m-d').'T'.date('H:i:sP', time()).'</lastmod>
                <changefreq>daily</changefreq>
                <priority>0.8</priority>
            </url>';
}

$query = $sistema->DB_fetch_array("SELECT *, a.id id_produto FROM tb_produtos_produtos a JOIN tb_seo_paginas b ON a.id_seo = b.id WHERE a.stats = 1 AND a.apagado = 0");

$paginas = $query->rows;

foreach ($paginas as $i => $pagina) {
    $url = $pagina['seo_url_breadcrumbs'] != "" ?  $pagina['seo_url_breadcrumbs'].$pagina['seo_url'] : $pagina['seo_url'];
    //echo $sistema->site_url .'/'. $url . '<br>' . PHP_EOL;

    $xml .= '<url>
                <loc>'.$sistema->site_url .'/'. $url .'</loc>
                <lastmod>'.date('Y-m-d').'T'.date('H:i:sP', time()).'</lastmod>
                <changefreq>daily</changefreq>
                <priority>0.8</priority>
            </url>';
}

$xml .= '</urlset>';

$arquivo = fopen('sitemap.xml', 'w');
if (fwrite($arquivo, $xml)) {
    echo "Arquivo sitemap.xml criado com sucesso";
} else {
    echo "Não foi possível criar o arquivo. Verifique as permissões do diretório.";
}
fclose($arquivo);





?>