<?php
	$pagina = "blog";
        
        // CURRENTPAGE DATA ---- //
        $currentpage = $sistema->seo_pages[$pagina];
        // --------------------------------------------- //
        
        if (!$sistema->getParameter("pg"))
            $pag = 1;
        else
            $pag = $sistema->getParameter("pg");

        $order = " ORDER BY A.data DESC ";

        $itens = 15;
        $range = 3;
        $total = $sistema->DB_num_rows("SELECT A.*, DATE_FORMAT(A.data, '%d/%m/%Y') data, CONCAT(B.seo_url_breadcrumbs,B.seo_url) seo_url FROM tb_institucional_paginas A INNER JOIN tb_seo_paginas B ON B.id = A.id_seo LEFT JOIN tb_institucional_categorias D ON D.id = A.id_categoria  WHERE A.stats = 1 AND A.id_categoria = 2 $order");
        $pagination = new pagination($itens,$total,$range,$pag);

        $posts = false;
        $query = $sistema->DB_fetch_array("SELECT A.*, DATE_FORMAT(A.data, '%d/%m/%Y') data, CONCAT(B.seo_url_breadcrumbs,B.seo_url) seo_url FROM tb_institucional_paginas A INNER JOIN tb_seo_paginas B ON B.id = A.id_seo LEFT JOIN tb_institucional_categorias D ON D.id = A.id_categoria  WHERE A.stats = 1 AND A.id_categoria = 2 $order LIMIT ".$pagination->bd_search_starts_at.", ".$pagination->itens_per_page);
        if ($query->num_rows) {            
            $posts = $query->rows;
        }
        

        $complemento = "";
        if($sistema->getParameter('categoria')) {
            $complemento .= "/categoria/".$sistema->trataParameter($sistema->getParameter('categoria'));
        } 
        
	include "_inc_headers.php"; 
?>
</head>
<body id="<?php echo $pagina; ?>">
<?php include "_inc_header.php"; ?>
<div class="breadcrumbs">
	<div class="comp-grid-main-in">
		<span><a href="<?php echo $sistema->root_path; ?>">Home</a></span>
		<span><a href="<?php echo $sistema->seo_pages['blog']['seo_url']; ?>">Blog</a></span>
	</div>
</div>

<div class="comp-grid-main-in">
	<div class="main_titles"><h1 class="destaque">BLOG REAL POKER</h1></div>

    <?php if($posts):?>
	   <div class="comp-grid-row">
            <div class="comp-grid-row  blog-list-side">
        		<?php foreach($posts as $i => $post): ?>    
        			<div class="comp-grid-third two-one">
        				<div class="produto-list">
                            <div class="img"><a href="<?php echo $post['seo_url'];?>" class=""><img src="<?php echo $sistema->getImageFileSized($post['imagem'],500,280);?>" alt="<?php echo $post['nome'];?>"></a></div>
        					<div class="head">
        						<div class="titulo"><a href="<?php echo $post['seo_url'];?>" class="titulo"><?php echo $post['nome'];?></a></div>
                                <div class="data" style="font-size:11px;"><?php echo $post['data']; ?></div>
                                <div class="subtitulo"><?php echo nl2br($post['resumo']);?></div>
        					</div>					
        					<div class="info">
        						<a href="<?php echo $post['seo_url'];?>" class="">LEIA MAIS</a>
        					</div>
        				</div>
        			</div>
                    <?php if (($i+1)%3==0): ?>
                        <div class="clear"></div>
                    <?php endif ?>
        		<?php endforeach ?>
                <div class="comp-grid-row">
                    <?php if ($total > $itens) : ?>
                        <div class="paginacao left">

                            <a href="<?php echo $currentpage['seo_url'] . $complemento . "/" . $sistema->pagination_tag . "/1"; ?>"><i class="fa fa-angle-double-left"></i></a>

                            <?php
                            $pag = $sistema->getParameter('pg');
                            $a = $pag - 1;
                            $p = $pag + 1;

                            if ($a < 1)
                                $a = 1;

                            if ($p > $pagination->pages_total)
                                $p = $pagination->pages_total;
                            ?>

                            <a href="<?php echo $currentpage['seo_url'] . $complemento . "/" . $sistema->pagination_tag . "/" . $a; ?>" title="Anterior"><i class="fa fa-angle-left"></i></a>

                            <?php
                            $c =  0;
                            for ($i = $pagination->range_initial_number; $i <= $pagination->range_end_number; $i++) {
                                if ($i > 0) {
                                    $c++;

                                    if ($c < 9)
                                        $c = "0" . $c;
                                    ?>
                                    <a href="<?php echo $currentpage['seo_url'] . $complemento . "/" . $sistema->pagination_tag . "/" . $i; ?>" class="<?php if ($pagination->page_current == $i) echo "active"; ?>"><?php echo $c; ?></a>

                                <?php
                            }
                        }// end for()
                        ?>

                            <a href="<?php echo $currentpage['seo_url'] . $complemento . "/" . $sistema->pagination_tag . "/" . $p; ?>" title="Próximo"><i class="fa fa-angle-right"></i></a>
                            <a href="<?php echo $currentpage['seo_url'] . $complemento . "/" . $sistema->pagination_tag . "/" . $pagination->pages_total; ?>"><i class="fa fa-angle-double-right"></i></a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
	   </div>
    <?php endif;?>
	
</div>
<?php 
	include "_inc_footer.php";
?>

<script src="js/jquery.banners.js"></script>
<script src="js/jquery.easing.js"></script>
</body>
</html>