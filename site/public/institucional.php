<?php
	$pagina = "institucional";
        
        $currentpage = $sistema->DB_fetch_array("SELECT *, B.id id_pagina, CASE WHEN A.seo_url_breadcrumbs IS NOT NULL THEN CONCAT(A.seo_url_breadcrumbs,A.seo_url) ELSE A.seo_url END seo_url, A.id, A.seo_pagina_referencia, A.seo_keywords, A.seo_description FROM tb_seo_paginas A INNER JOIN tb_institucional_paginas B ON A.id = B.id_seo WHERE A.id = $dynamic_id and B.stats = 1 AND B.id_categoria = 1");

        if($currentpage->num_rows){
            $currentpage = $currentpage->rows[0];

            $produtos = 0;

            if($currentpage['listar_produtos']){
            	$produtos = $sistema->DB_fetch_array("SELECT IFNULL((SELECT IF(D.porcentagem=1,((A.custo+A.frete_embutido)*D.valor/100),D.valor) valor FROM tb_produtos_has_tb_descontos C JOIN tb_produtos_descontos D ON C.id_desconto = D.id WHERE C.id_produto = A.id ORDER BY D.quantidade LIMIT 1),0) desconto, A.id, A.nome, A.resumo, A.imagem, A.custo, A.frete_embutido, CONCAT(B.seo_url_breadcrumbs,B.seo_url) seo_url , (SELECT CONCAT(ROUND(SUM(pa.nota)/COUNT(*),1), '|', COUNT(*)) avaliacao FROM tb_produtos_avaliacoes pa WHERE pa.stats = 1 AND pa.id_produto=A.id GROUP BY pa.id_produto) avaliacao FROM tb_produtos_produtos A JOIN tb_seo_paginas B ON A.id_seo=B.id WHERE A.id IN (".$currentpage['lista_produtos'].") ORDER BY FIELD(A.id,".$currentpage['lista_produtos'].")");

            	$produtos = $produtos->rows;
            }
        }else{
            Header( "HTTP/1.1 301 Moved Permanently" );
            Header( "Location: ".$sistema->site_url );
        }

        function getTotal ($custo = 0, $idPersonalizado = null, $idProduto = null) {
            global $sistema;
            if ($idPersonalizado != null) {
                $query = $sistema->DB_fetch_array("SELECT B.custo FROM tb_produtos_personalizados_has_tb_produtos_atributos A INNER JOIN tb_produtos_atributos B ON B.id = A.id_atributo WHERE A.id_produto_personalizado = $idPersonalizado AND A.selecionado = 1");
                if ($query->num_rows) {
                    foreach ($query->rows as $linha) {
                        $custo = $custo + ($linha['custo']);
                    }
                }

            } else {
                $query = $sistema->DB_fetch_array("SELECT B.custo FROM tb_produtos_conjuntos_atributos A INNER JOIN tb_produtos_atributos B ON B.id_conjunto_atributo = A.id WHERE A.id_produto = $idProduto AND B.selecionado = 1");
                if ($query->num_rows) {
                    foreach ($query->rows as $linha) {
                    	if($linha['custo']!="") $custo = $custo + ($linha['custo']);
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
		<span><a href="<?php echo $currentpage['seo_url'];?>"><?php echo $currentpage['nome'];?></a></span>
	</div>
</div>

<div class="comp-grid-main-in">
	<div class="main_titles"><h1><?php echo $currentpage['nome'];?></h1></div>
	<div class="editable_content">
		<div class="comp-grid-row">

                    <?php /*if ($currentpage['imagem']) : ?>
			<div class="comp-grid-half two-one">
				<p><img src="<?php echo $sistema->getImageFileSized($currentpage['imagem'],559,280); ?>" alt="<?php echo $currentpage['nome'];?>" class="resize-off"></p>
			</div>
                    <?php endif; */?>
				<?php echo $sistema->trataTexto($currentpage['texto']); ?>
		</div>
	</div>
	<div class="comp-grid-row">
		<?php 
			if ($produtos!=0) {
				foreach ($produtos as $key => $produto) {
					$preco = getTotal($produto['custo']+$produto['frete_embutido']-$produto['desconto'],null,$produto['id']);
					if($key % 3==0) $third = "two-one";
					else $third = "";
			    	$avaliacao = explode('|', $produto['avaliacao']);
			    	$avaliacao_nota = $avaliacao[0];
			    	$avaliacao_qtde = $avaliacao[1];
		?>				
			<div class="comp-grid-third <?php echo $third; ?>">
				<div class="produto-list">
					<div class="head">
						<div class="titulo"><?php echo $produto['nome']; ?></div>
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
					<div class="img">
						<img src="<?php echo $sistema->getImageFileSized($produto['imagem'],700,385);?>" alt="" />
					</div>
					<div class="info">
						<div class="comp-grid-row">
							<div class="preco">
								<div class="val">Apartir de: R$ <?php echo $sistema->formataMoedaShow($preco);?></div>
								Em até <strong style="font-weight: bold;">10x de R$ <?php echo $sistema->formataMoedaShow(($preco/10)); ?></strong> sem juros
							</div>
							<div class="fretes">
								<div class="ter">Frete Grátis para o Brasil*</div>
								<div class="aer">Entrega Expressa</div>
							</div>
						</div>
						<div class="comp-grid-row opcoes">
							<div class="personalizar"><a href="../<?php echo $produto['seo_url'] ?>">VER DETALHES</a></div>
							<div class="mesas">Personalize 100% o seu produto</div>
						</div>
					</div>
				</div>
			</div>
		<?php 
				}
			}
		?>

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