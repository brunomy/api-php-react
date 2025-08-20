<?php $fixa = $sistema->DB_fetch_array("SELECT * FROM tb_institucional_paginas WHERE id = 10"); ?>
<div class="row blog-pagina-fixa">
    <?php echo $sistema->trataTexto($fixa->rows[0]['texto']);?>
</div>
<?php $where = ""; if($pagina=="post") $where = " AND A.id != {$currentpage['id_pagina']} "; $blogs = $sistema->DB_fetch_array("SELECT A.*, DATE_FORMAT(A.data, '%d/%m/%Y') data, CONCAT(B.seo_url_breadcrumbs,B.seo_url) seo_url FROM tb_institucional_paginas A INNER JOIN tb_seo_paginas B ON B.id = A.id_seo LEFT JOIN tb_institucional_categorias D ON D.id = A.id_categoria  WHERE A.stats = 1 AND A.id_categoria = 2 $where ORDER BY A.data DESC LIMIT 20");  ?>
<div class="row blog-ultimas">  
    <h1>Leia também</h1>
 




<?php if ($blogs->num_rows) :?>
    <?php foreach ($blogs->rows as $blog) : ?>
    <div class="ultimas">
        <div class="">
            <a href="<?php echo $blog['seo_url'];?>">
                
                <h3 style="font-size: 14px;"><b>- <?php echo $blog['nome'];?></b></h3>
            </a>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>




    <div class="info">
                        <a href="/blog" class="">LER TODAS DO BLOG</a>
                    </div>
</div>