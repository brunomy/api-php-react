<?php
	$pagina = "busca";
        
        // CURRENTPAGE DATA ---- //
        $currentpage = $sistema->seo_pages[$pagina];
        // --------------------------------------------- //
        
        if (!$sistema->getParameter("busca")){
            header('Location: home');
            exit();
        }

        $busca = $sistema->DB_anti_injection($sistema->getParameter("busca"));
        
        $clear_params = $sistema->getParameter($sistema->seo_pages['produtos-personalizados']['seo_url']);
        if(strpos($clear_params,'?')){
            $clear_params = explode('?',$clear_params);
            $clear_params = array_shift($clear_params);
        }

        $produtos = false;
        $query = $sistema->DB_fetch_array("SELECT P.qtd_minima, P.frete_embutido, P.id id_produto, A.id id_personalizado, A.nome,A.resumo,A.imagem,P.custo, CONCAT(B.seo_url_breadcrumbs,B.seo_url) seo_url, A.ordem, (SELECT CONCAT(ROUND(SUM(pa.nota)/COUNT(*),1), '|', COUNT(*)) avaliacao FROM tb_produtos_avaliacoes pa WHERE pa.stats = 1 AND pa.id_produto=A.id_produto GROUP BY pa.id_produto) avaliacao  FROM tb_produtos_personalizados A INNER JOIN tb_seo_paginas B ON B.id = A.id_seo INNER JOIN tb_produtos_produtos P ON P.id = A.id_produto WHERE A.stats = 1 AND A.apagado = 0 AND (A.nome LIKE '%".$busca."%' OR B.seo_title LIKE '%".$busca."%' OR B.seo_description LIKE '%".$busca."%' OR B.seo_keywords LIKE '%".$busca."%') ORDER BY A.ordem"
        );

        /*echo '<pre>';
        print_r($query);
        exit();*/

        if ($query->num_rows) {
            $produtos = $query->rows;
        }

        $posts = false;
        $query = $sistema->DB_fetch_array("SELECT A.*, DATE_FORMAT(A.data, '%d/%m/%Y') data, CONCAT(B.seo_url_breadcrumbs,B.seo_url) seo_url FROM tb_institucional_paginas A INNER JOIN tb_seo_paginas B ON B.id = A.id_seo LEFT JOIN tb_institucional_categorias D ON D.id = A.id_categoria  WHERE A.stats = 1 AND A.id_categoria = 2 AND (A.nome LIKE '%".$busca."%' OR B.seo_title LIKE '%".$busca."%' OR B.seo_description LIKE '%".$busca."%' OR B.seo_keywords LIKE '%".$busca."%')");
        if ($query->num_rows) {            
            $posts = $query->rows;
        }


        $complemento = "";
        if($clear_params) {
            $complemento .= "/".$clear_params."/".$clear_params;
        } 
        
        function getTotal ($custo = 0, $idPersonalizado = null, $idProduto = null, $qtd_minima = 1) {
            global $sistema;
            if ($idPersonalizado != null) {
                $query = $sistema->DB_fetch_array("SELECT CASE WHEN B.custo IS NULL THEN 0 ELSE B.custo END custo FROM tb_produtos_personalizados_has_tb_produtos_atributos A INNER JOIN tb_produtos_atributos B ON B.id = A.id_atributo WHERE A.id_produto_personalizado = $idPersonalizado AND A.selecionado = 1");
                if ($query->num_rows) {
                    foreach ($query->rows as $linha) {
                        $custo = $custo + $linha['custo'];
                    }
                }
                
            } else {
                $query = $sistema->DB_fetch_array("SELECT CASE WHEN B.custo IS NULL THEN 0 ELSE B.custo END custo FROM tb_produtos_conjuntos_atributos A INNER JOIN tb_produtos_atributos B ON B.id_conjunto_atributo = A.id WHERE A.id_produto = $idProduto AND B.selecionado = 1");
                if ($query->num_rows) {
                    foreach ($query->rows as $linha) {
                        $custo = $custo + $linha['custo'];
                    }
                }
            }


            //VERIFICA SE TEM DESCONTO --------
                $qr = $sistema->DB_fetch_array("SELECT * FROM tb_produtos_has_tb_descontos A INNER JOIN tb_produtos_descontos B ON B.id = A.id_desconto WHERE A.id_produto = ".$idProduto." ORDER BY B.quantidade LIMIT 1");

                if($qr->num_rows){
                    if($qtd_minima >= $qr->rows[0]['quantidade']){
                        if($qr->rows[0]['porcentagem']){
                            $custo = $custo - ($custo * $qr->rows[0]['valor'] / 100);
                        }else{
                            $custo = ($custo - $qr->rows[0]['valor']);
                        }
                    }
                }
            
            return $custo;
            
        }
        
	include "_inc_headers.php"; 
?>
</head>
<body id="<?php echo $pagina; ?>">
<?php include "_inc_header.php"; ?>
<div class="breadcrumbs">
	<div class="comp-grid-main-in">
		<span><a href="<?php echo $sistema->root_path; ?>">Home</a></span>
		<span><a href="<?php echo $clear_params; ?>">Produtos Personalizados</a></span>
	</div>
</div>
<div class="comp-grid-main-in">
	<div class="main_titles"><h1 class="destaque">RESULTADO DE BUSCA PARA: <?php echo $busca; ?></h1></div>

        <?php if($produtos):?>
	<div class="comp-grid-row">
		<?php foreach($produtos as $i => $produto){
            $preco = getTotal($produto['custo']+ $produto['frete_embutido'],$produto['id_personalizado'],$produto['id_produto'],$produto['qtd_minima']);
            $avaliacao_nota = 0;
            $avaliacao_qtde = 0;
            if($produto['avaliacao']){
                $avaliacao = explode('|', $produto['avaliacao']);
                $avaliacao_nota = $avaliacao[0];
                $avaliacao_qtde = $avaliacao[1];
            }
        ?>
			<div class="comp-grid-third two-one">
				<div class="produto-list">
					<div class="head">
						<div class="titulo"><?php echo $produto['nome'];?></div>
						<div class="subtitulo"><?php echo $produto['resumo'];?></div>
					</div>
                    <div style="padding-bottom: 20px;">
                        <ul class="estrelas" style="display: inline;">
                            <?php
                                for ($j=0; $j < 5; $j++) {
                                    if(floor($avaliacao_nota) <= $j){
                                        $estrela = 'off';
                                        if($j < $avaliacao_nota){
                                            $estrela = 'half';
                                        }
                                    }else{
                                        $estrela = 'on';
                                    }
                            ?>
                                    <li><img src="img/estrela_avaliacao_<?php echo $estrela; ?>.png" alt="Estrela"></li>
                            <?php
                                }
                            ?>
                        </ul>
                        <?php echo $avaliacao_qtde;?> avaliações
                    </div>
					<div class="img"><img src="<?php echo $sistema->getImageFileSized($produto['imagem'],700,385);?>" alt="<?php echo $produto['nome'];?>"></div>
					<div class="info">
						<div class="comp-grid-row">
							<div class="preco">
								<div class="val">A partir de: <span>R$ <?php echo $sistema->formataMoedaShow($preco);?></span></div>
								Em até <strong style="font-weight: bold;">10x de <?php echo $sistema->formataMoedaShow(($preco/10)); ?></strong> sem juros
							</div>
							<div class="fretes">
								<span class="ter"><img src="img/icon_frete_terrestre.png" alt=""></span>
								<span class="aer"><img src="img/icon_frete_aereo.png" alt=""></span>
							</div>
						</div>
						<a href="<?php echo $produto['seo_url'];?>" class="link-comprar">MONTE A SUA A PARTIR DESSA</a>
					</div>
				</div>
			</div>
            <?php if (($i+1)%3==0): ?>
                <div class="clear"></div>
            <?php endif ?>
		<?php } ?>
	</div>
        <?php endif;?>

    <?php if($posts):?>

        <div class="main_titles"><h1 class="destaque">RESULTADO DE PESQUISA NO BLOG REAL POKER</h1></div>
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