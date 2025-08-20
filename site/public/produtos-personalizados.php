<?php
	$pagina = "produtos-personalizados";
        
        // CURRENTPAGE DATA ---- //
        $currentpage = $sistema->seo_pages[$pagina];
        // --------------------------------------------- //
        
        if (!$sistema->getParameter("pg"))
            $pag = 1;
        else
            $pag = $sistema->getParameter("pg");

        $where1 = "";
        $where2 = "";

        $clear_params = $sistema->getParameter($sistema->seo_pages['produtos-personalizados']['seo_url']);
        if(strpos($clear_params,'?')){
            $clear_params = explode('?',$clear_params);
            $clear_params = array_shift($clear_params);
        }

        if ($clear_params){
           $where1 .= " AND (A.id = (SELECT A.id_produto FROM tb_produtos_personalizados A INNER JOIN tb_seo_paginas B ON B.id = A.id_seo INNER JOIN tb_produtos_personalizados S ON S.id_produto = A.id WHERE A.stats = 1 AND A.apagado = 0 AND B.seo_url = '{$clear_params}') OR A.id = (SELECT A.id FROM tb_produtos_produtos A INNER JOIN tb_seo_paginas B ON B.id = A.id_seo WHERE B.seo_url = '{$clear_params}') ) ";
           $where2 .= " AND (A.id_produto = (SELECT A.id_produto FROM tb_produtos_personalizados A INNER JOIN tb_seo_paginas B ON B.id = A.id_seo WHERE A.stats = 1 AND A.apagado = 0 AND B.seo_url = '{$clear_params}') OR A.id_produto = (SELECT A.id FROM tb_produtos_produtos A INNER JOIN tb_seo_paginas B ON B.id = A.id_seo WHERE B.seo_url = '{$clear_params}') ) ";
        }

        $itens = 30;
        $range = 3;
        $total = $sistema->DB_num_rows("SELECT P.frete_embutido, P.id id_produto, A.id id_personalizado, A.nome,A.resumo,A.imagem,P.custo, CONCAT(B.seo_url_breadcrumbs,B.seo_url) seo_url, A.ordem FROM tb_produtos_personalizados A INNER JOIN tb_seo_paginas B ON B.id = A.id_seo INNER JOIN tb_produtos_produtos P ON P.id = A.id_produto WHERE A.stats = 1 AND A.apagado = 0 $where2 ORDER BY A.ordem
        ");
        $pagination = new pagination($itens,$total,$range,$pag);

        $produtos = false;
        $query = $sistema->DB_fetch_array("SELECT P.qtd_minima, P.frete_embutido, P.id id_produto, A.id id_personalizado, A.nome,A.resumo,A.imagem,P.custo, CONCAT(B.seo_url_breadcrumbs,B.seo_url) seo_url, A.ordem FROM tb_produtos_personalizados A INNER JOIN tb_seo_paginas B ON B.id = A.id_seo INNER JOIN tb_produtos_produtos P ON P.id = A.id_produto WHERE A.stats = 1 AND A.apagado = 0 $where2 ORDER BY A.ordem LIMIT ".$pagination->bd_search_starts_at.", ".$pagination->itens_per_page
        );

        if ($query->num_rows) {
            $produtos = $query->rows;
            $id_produto_pai = $produtos[0]['id_produto'];
            $query = $sistema->DB_fetch_array('SELECT * FROM tb_produtos_produtos a INNER JOIN tb_seo_paginas b ON a.id_seo = b.id WHERE a.id = '.$id_produto_pai);
            $produto_pai = $query->rows[0];

            $avaliacao =  $sistema->DB_fetch_array("SELECT ROUND(SUM(pa.nota)/COUNT(*),1) avaliacao, COUNT(*) quantidade FROM tb_produtos_avaliacoes pa WHERE pa.stats = 1 AND pa.id_produto = ".$id_produto_pai);
            $pontuacao = $avaliacao->rows[0]['avaliacao'];
            $avaliacoes = $avaliacao->rows[0]['quantidade'];
            $estrelas = '';
            for ($i=0; $i < 5; $i++) {
                if(floor($pontuacao) <= $i){
                    $estrela = 'off';
                    if($i < $pontuacao){
                        $estrela = 'half';
                    }
                }else{
                    $estrela = 'on';
                }
                $estrelas .= '<li><img src="img/estrela_avaliacao_'.$estrela.'.png" alt="Estrela"></li>';
            }
            $estrelas = '<ul class="estrelas" style="display:inline;">'.$estrelas.'</ul> <span>'.$avaliacoes.'</span> avaliações';
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

    $currentpage['seo_title'] = $produto_pai['titulo_lista_personalizados'] ?? $currentpage['seo_title'];
        
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
	<div class="main_titles"><h1 class="destaque"><?php echo empty($produto_pai['titulo_lista_personalizados']) ? 'INSPIRE-SE EM ITENS QUE JÁ FABRICAMOS E PERSONALIZE O SEU.': $produto_pai['titulo_lista_personalizados']; ?></h1></div>

        <?php if($produtos):?>
        	<div class="comp-grid-row">
        		<?php foreach($produtos as $produto): ?>  
                        <?php $preco = getTotal($produto['custo']+ $produto['frete_embutido'],$produto['id_personalizado'],$produto['id_produto'],$produto['qtd_minima']); ?>
        			<div class="comp-grid-third two-one">
        				<div class="produto-list">
        					<div class="head">
        						<div class="titulo"><h2><?php echo $produto['nome'];?></h2></div>
        						<div class="subtitulo"><h3><?php echo $produto['resumo'];?></h3></div>
        					</div>
                            <div style="padding-bottom: 20px;">
                                <?php echo $estrelas; ?>
                            </div>
        					<div class="img"><img src="<?php echo $sistema->getImageFileSized($produto['imagem'],700,385);?>" alt="<?php echo $produto['nome'];?>"></div>
        					<div class="info">
        						<div class="comp-grid-row">
        							<div class="preco">
        								<div class="val">Comprar a partir de: <span>R$ <?php echo $sistema->formataMoedaShow($preco);?></span></div>
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
        		<?php endforeach ?>
        	</div>
        <?php endif;?>
        
        <?php if(!empty($produto_pai['descricao_lista_personalizados'])) echo $sistema->trataTexto($produto_pai['descricao_lista_personalizados']) . '<br><br>'; ?>
	<div class="comp-grid-row">
		<?php include "_inc_paginacao.php"; ?>
	</div>
</div>

<?php 
	include "_inc_footer.php";
?>

<script src="js/jquery.banners.js"></script>
<script src="js/jquery.easing.js"></script>
</body>
</html>