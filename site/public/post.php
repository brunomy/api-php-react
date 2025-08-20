<?php
	$pagina = "post";
        
        $currentpage = $sistema->DB_fetch_array("SELECT *, B.id id_pagina, CASE WHEN A.seo_url_breadcrumbs IS NOT NULL THEN CONCAT(A.seo_url_breadcrumbs,A.seo_url) ELSE A.seo_url END seo_url, A.id, A.seo_pagina_referencia, A.seo_keywords, A.seo_description FROM tb_seo_paginas A INNER JOIN tb_institucional_paginas B ON A.id = B.id_seo WHERE A.id = $dynamic_id and B.stats = 1 AND B.id_categoria = 2");
        if($currentpage->num_rows)
            $currentpage = $currentpage->rows[0];
        
	include "_inc_headers.php";
?>
</head>
<body id="<?php echo $pagina; ?>">
<?php include "_inc_header.php"; ?>
<div class="breadcrumbs">
	<div class="comp-grid-main-in">
		<span><a href="<?php echo $sistema->root_path; ?>">Home</a></span>
                <span><a href="<?php echo $sistema->seo_pages['blog']['seo_url']; ?>">Blog</a></span>
		<span><a href="<?php echo $currentpage['seo_url'];?>"><?php echo $currentpage['nome'];?></a></span>
	</div>
</div>

<div class="comp-grid-main-in">
    <div class="main_titles"></div>
	<div class="editable_content">
		<div class="comp-grid-row">
			<div class="comp-grid-blog">
                                <h1><?php echo $currentpage['nome'];?></h1>
                                <div class="data" style="font-size:11px;"><?php echo $sistema->formataDataDeBanco($currentpage['data']); ?></div>
                                <?php if ($currentpage['imagem']) : ?>
<!--				<p><img src="<?php echo $sistema->getImageFileSized($currentpage['imagem'],500,280); ?>" alt="<?php echo $currentpage['nome'];?>" class="resize-off"></p>-->
                                <?php echo $sistema->trataTexto($currentpage['texto']); ?>
                                <br>
                                <div style="background-color: #eeeeee; padding: 18px; font-size: 20px;">
                                	COMPARTILHE ESSE POST &nbsp &nbsp &nbsp

	                                <div class="fb-like" data-href="http://www.realpoker.com.br/<?php echo $currentpage['seo_url'];?>" data-layout="button_count" data-action="like" data-size="large" data-show-faces="true" data-share="true">
	                                </div>
                                <?php 
                                    $where = ""; 
                                    if($pagina=="post") $where = " AND A.id != {$currentpage['id_pagina']} "; 
                                    if($pagina=="post" && $currentpage['id_pagina'] > 14) $where .= "AND A.id < {$currentpage['id_pagina']} "; 

                                    $blogs = $sistema->DB_fetch_array("SELECT A.*, DATE_FORMAT(A.data, '%d/%m/%Y') data, CONCAT(B.seo_url_breadcrumbs,B.seo_url) seo_url FROM tb_institucional_paginas A INNER JOIN tb_seo_paginas B ON B.id = A.id_seo LEFT JOIN tb_institucional_categorias D ON D.id = A.id_categoria  WHERE A.stats = 1 AND A.id_categoria = 2 $where ORDER BY A.data DESC LIMIT 5");  ?>
                                	   
                                </div>
                                <div align="center">
                                <br>
                                <div align="left" style="font-size: 16px; padding-left: 5px" >
                                RECOMENDADOS PARA VOCÊ:
                                </div>
                                        <?php if ($blogs->num_rows) :?>
                                        <?php foreach ($blogs->rows as $blog) : ?>
                                        <div class="ultimas" style="width: 273px; float: left; margin: 5px; border: 0px;">
                                            <div class="produto-list" style="height: 190px; border: 0px; " >
                                                <a href="<?php echo $blog['seo_url'];?>">
                                                    <img src="<?php echo $sistema->getImageFileSized($blog['imagem'],500,280); ?>" alt="<?php echo $blog['nome'];?>" class="resize-off">
                                                    <h3 align="left" style="font-size: 16px;"><b><?php echo $blog['nome'];?></b></h3>
                                                </a>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                        <?php endif; ?>
                                        <div class="ultimas" style="width: 273px; float: left; margin: 5px; border: 0px;">
                                            <div class="produto-list" style="height: 190px; border: 0px; " >
                                                <a href="/mesas/mesas-de-poker">
                                                    <img src="/img/mesadepoker.jpg" alt="Mesa de Poker" class="resize-off">
                                                    <h3 align="left" style="font-size: 16px;"><b>Amigos se divertem em casa com Mesa de Poker Profissional</b></h3>
                                                </a>
                                            </div>
                                        </div>
                                </div>        
                                <div style="padding-top: 10px;" class="fb-comments" data-href="http://realpoker.com.br/<?php echo $currentpage['seo_url'];?>"  data-numposts="5">
                                </div>

			</div>
                    <?php endif;?>
                    <div class="comp-grid-blog-side">
                        <?php require_once '_inc_blog_pagina_fixa.php'; ?>
                    </div>
		</div>
	</div>
</div>

<?php 
	include "_inc_footer.php";
?>
<script src="js/jquery.banners.js"></script>
<script src="js/jquery.easing.js"></script>
</body>
</html>