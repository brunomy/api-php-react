<?php
	$pagina = "home";

        // CURRENTPAGE DATA ---- //
        $currentpage = $sistema->seo_pages[$pagina];
        // --------------------------------------------- //

        $banners = $sistema->DB_fetch_array("SELECT * FROM tb_banners_banners WHERE stats = 1 ORDER BY ordem");

        $destaques_personalizados = $sistema->DB_fetch_array("
                SELECT * FROM (

                (SELECT A.ordem, A.id_personalizado, B.id id_produto, B.nome, B.imagem, CONCAT(C.seo_url_breadcrumbs,C.seo_url) seo_url, C.seo_url seo_url_personalizado, B.titulo_box_frete, B.texto_box_frete, B.custo, B.frete_embutido, B.resumo, (SELECT CONCAT(ROUND(SUM(pa.nota)/COUNT(*),1), '|', COUNT(*)) avaliacao FROM tb_produtos_avaliacoes pa WHERE pa.stats = 1 AND pa.id_produto=B.id GROUP BY pa.id_produto) avaliacao FROM tb_produtos_destaques_personalizados A INNER JOIN tb_produtos_produtos B ON B.id = A.id_produto INNER JOIN tb_seo_paginas C ON C.id = B.id_seo WHERE B.stats = 1 AND A.id_personalizado IS NULL AND B.apagado != 1 AND B.id IN (SELECT id_produto FROM tb_produtos_personalizados WHERE stats = 1 AND apagado != 1) GROUP BY B.id, A.ordem, A.id_personalizado, B.id, B.nome, B.imagem, C.seo_url_breadcrumbs, C.seo_url, B.titulo_box_frete, B.texto_box_frete, B.custo,B.frete_embutido, B.resumo)

                UNION

                (SELECT A.ordem, A.id_personalizado, B.id_produto, IFNULL(B.nome, D.nome) nome, IFNULL(B.imagem, D.imagem) imagem, CONCAT(C.seo_url_breadcrumbs,C.seo_url) seo_url, C.seo_url seo_url_personalizado, D.titulo_box_frete, D.texto_box_frete, D.custo, D.frete_embutido, IFNULL(B.resumo,D.resumo) resumo, (SELECT CONCAT(ROUND(SUM(pa.nota)/COUNT(*),1), '|', COUNT(*)) avaliacao FROM tb_produtos_avaliacoes pa WHERE pa.stats = 1 AND pa.id_produto=B.id GROUP BY pa.id_produto) avaliacao FROM tb_produtos_destaques_personalizados A INNER JOIN tb_produtos_personalizados B ON B.id = A.id_personalizado INNER JOIN tb_seo_paginas C ON C.id = B.id_seo INNER JOIN tb_produtos_produtos D ON D.id = B.id_produto WHERE B.stats = 1 AND B.apagado != 1 GROUP BY B.id, A.ordem, A.id_personalizado, B.id_produto, B.nome, D.nome, B.imagem, D.imagem, C.seo_url_breadcrumbs, C.seo_url, D.titulo_box_frete, D.texto_box_frete, D.custo, D.frete_embutido, B.resumo, D.resumo)

                )
                tb_ ORDER BY ordem

        ");

        $destaques_produtos = $sistema->DB_fetch_array("SELECT A.id_personalizado, E.id id_produto, IFNULL(B.nome, E.nome) nome, IFNULL(B.imagem, E.imagem) imagem, IFNULL(CONCAT(D.seo_url_breadcrumbs,D.seo_url),CONCAT(F.seo_url_breadcrumbs,F.seo_url)) seo_url, IFNULL(C.titulo_box_frete,E.titulo_box_frete) titulo_box_frete, IFNULL(C.texto_box_frete,E.texto_box_frete) texto_box_frete, IFNULL(C.custo,E.custo) custo, IFNULL(C.frete_embutido,E.frete_embutido) frete_embutido, IFNULL(C.resumo,E.resumo) resumo, (SELECT CONCAT(ROUND(SUM(pa.nota)/COUNT(*),1), '|', COUNT(*)) avaliacao FROM tb_produtos_avaliacoes pa WHERE pa.stats = 1 AND pa.id_produto=A.id_produto GROUP BY pa.id_produto) avaliacao FROM tb_produtos_destaques A LEFT JOIN tb_produtos_personalizados B ON B.id = A.id_personalizado LEFT JOIN tb_produtos_produtos C ON C.id = B.id_produto LEFT JOIN tb_seo_paginas D ON D.id = B.id_seo LEFT JOIN tb_produtos_produtos E ON E.id = A.id_produto AND A.id_personalizado IS NULL LEFT JOIN tb_seo_paginas F ON F.id = E.id_seo WHERE (B.stats = 1 AND C.stats = 1) OR (E.stats = 1) AND ((B.apagado != 1 AND C.apagado != 1) ||  (E.apagado != 1)) ORDER BY A.ordem");
        
        $destaques_pronto_entrega = $sistema->DB_fetch_array("SELECT C.id id_produto, C.nome, C.imagem, CONCAT(D.seo_url_breadcrumbs,D.seo_url) seo_url, C.titulo_box_frete, C.texto_box_frete, C.custo, C.frete_embutido, C.resumo FROM tb_produtos_destaques_pronto_entrega A LEFT JOIN tb_produtos_produtos C ON C.id = A.id_produto LEFT JOIN tb_seo_paginas D ON D.id = C.id_seo WHERE C.stats = 1 AND C.apagado != 1 ORDER BY A.ordem");

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

        $banners_avulsos = $sistema->DB_fetch_array("SELECT * FROM tb_banners_avulsos ORDER BY id");
        $banner_avulso_titulo = $banners_avulsos->rows[0];
        $banner_avulso = $banners_avulsos->rows[1];

        $clientes_vitrine = $sistema->DB_fetch_array("SELECT * FROM tb_clientes_vitrine WHERE stats = 1 ORDER BY ordem");

	include "_inc_headers.php";
?>
</head>
<body id="<?php echo $pagina; ?>">
<?php include "_inc_header.php"; ?>

<?php if ($banners->num_rows) :?>
<div class="comp-banners">
	<div class="display">
		<div class="banner" data-image="loaded" style="background-image: url('<?php echo $sistema->getImageFileSized($banners->rows[0]['imagem'],2000,400);?>');">
			<div class="comp-grid-main relative">
				<div class="box">
                                        <?php if ($banners->rows[0]['nome']) : ?>
					<div class="titulo"><h1><?php echo $banners->rows[0]['nome'];?></h1></div>
                                        <?php endif; ?>
                                        <?php if ($banners->rows[0]['preco'] == "") : ?>
                                        <br><br>
                                        <?php endif; ?>
                                        <?php if ($banners->rows[0]['subtitulo']) : ?>
					<div class="subtitulo"><?php echo $banners->rows[0]['subtitulo'];?></div>
                                        <?php endif; ?>
                                        <?php if ($banners->rows[0]['preco'] && $banners->rows[0]['titulo_preco']) : ?>
					<div class="preco">
						<?php echo $banners->rows[0]['titulo_preco'];?>
						<span><?php echo $banners->rows[0]['preco'];?></span>
					</div>
                                        <?php endif; ?>
                                        <?php if ($banners->rows[0]['texto_link']) :?>
					<div><a href="<?php echo  $banners->rows[0]['link']; ?>" target="<?php echo  $banners->rows[0]['target']; ?>" class="btn"><?php echo  $banners->rows[0]['texto_link']; ?></a></div>
                                        <?php endif; ?>
				</div>
			</div>
		</div>
                <?php for ($i = 1; $i < $banners->num_rows; $i++) : ?>
		<div class="banner" data-image="<?php echo $sistema->getImageFileSized($banners->rows[$i]['imagem'],2000,400);?>">
			<div class="comp-grid-main relative">
				<div class="box">
                                        <?php if($banners->rows[$i]['nome']) : ?>
					<div class="titulo"><h1><?php echo $banners->rows[$i]['nome'];?></h1></div>
                                        <?php endif; ?>
                                        <?php if ($banners->rows[$i]['preco'] == "") : ?>
                                        <br><br>
                                        <?php endif; ?>
                                        <?php if($banners->rows[$i]['subtitulo']) : ?>
					<div class="subtitulo"><?php echo $banners->rows[$i]['subtitulo'];?></div>
                                        <?php endif; ?>
                                        <?php if ($banners->rows[$i]['preco'] && $banners->rows[$i]['titulo_preco']) : ?>
					<div class="preco">
						<?php echo $banners->rows[$i]['titulo_preco'];?>
						<span><?php echo $banners->rows[$i]['preco'];?></span>
					</div>
                                        <?php endif; ?>
                                        <?php if ($banners->rows[$i]['texto_link']) :?>
					<div><a href="<?php echo  $banners->rows[$i]['link']; ?>" target="<?php echo  $banners->rows[$i]['target']; ?>" class="btn"><?php echo  $banners->rows[$i]['texto_link']; ?></a></div>
                                        <?php endif; ?>
				</div>
			</div>
		</div>
                <?php endfor; ?>
	</div>
	<div class="numeros"></div>
	<div class="time"></div>
</div>
<?php endif; ?>
<div class="barra_features">
	<div class="comp-grid-main">
		<div class="comp-grid-row">
			<div class="comp-grid-fourth two-two">
				<div class="featured">
					<div class="icon"><img src="img/icon_features_frete.png" alt="FRETE GRÁTIS BRASIL"></div>
					<div class="info">
						<span>FRETE GRÁTIS BRASIL*</span>
						<p>*Consulte regiões.</p>
					</div>
				</div>
			</div>
			<div class="comp-grid-fourth two-two">
				<div class="featured">
					<div class="icon"><img src="img/icon_features_parcelamento.png" alt="EM ATÉ 10X SEM JUROS"></div>
					<div class="info">
						<span>EM ATÉ 10X SEM JUROS</span>
						<p>Todo site parcelado sem juros nos cartões. Aproveite.</p>
					</div>
				</div>
			</div>
			<div class="comp-grid-fourth two-two">
				<div class="featured">
					<div class="icon"><img src="img/icon_features_desconto.png" alt="5% DE DESCONTO"></div>
					<div class="info">
						<span>5% DE DESCONTO À VISTA</span>
						<p>Desconto de 5% para compras à vista no boleto.</p>
					</div>
				</div>
			</div>
			<div class="comp-grid-fourth two-two">
				<div class="featured">
					<div class="icon"><img src="img/icon_features_cadeado.png" alt="SITE 100% SEGURO"></div>
					<div class="info">
						<span>SITE 100% SEGURO</span>
						<p>Pode confiar, compre com tranquilidade.</p>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php if ($destaques_personalizados->num_rows) :?>

<div class="comp-grid-main-in">
	<div class="comp-grid-row">
		<?php 
			for ($i = 0; $i < $destaques_personalizados->num_rows; $i ++) {
		    	$preco = getTotal($destaques_personalizados->rows[$i]['custo']+$destaques_personalizados->rows[$i]['frete_embutido'],$destaques_personalizados->rows[$i]['id_personalizado'],$destaques_personalizados->rows[$i]['id_produto']);
		    	$avaliacao = explode('|', $destaques_personalizados->rows[$i]['avaliacao']);
		    	$avaliacao_nota = $avaliacao[0];
		    	$avaliacao_qtde = $avaliacao[1];
	    ?>
			<div class="comp-grid-third">
				<div class="produto-list">
					<div class="head">
						<div class="titulo"><h2><?php echo $destaques_personalizados->rows[$i]['nome'];?></h2></div>
						<div class="subtitulo"><h3><?php echo $destaques_personalizados->rows[$i]['resumo'];?></h3></div>
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
					<div class="img"><img src="<?php echo $sistema->getImageFileSized($destaques_personalizados->rows[$i]['imagem'],455,335);?>" alt="Mesas de Poker Profissionais"></div>
					<div class="info">
						<div class="comp-grid-row">
							<div class="preco">
								<div class="val">Comprar a partir de: <span>R$ <?php echo $sistema->formataMoedaShow($preco);?></span></div>
								Em até <strong style="font-weight: bold;">10x de R$ <?php echo $sistema->formataMoedaShow(($preco/10)); ?></strong> sem juros
							</div>
							<div class="fretes">
								<div class="ter">Frete Grátis para o Brasil*</div>
								<div class="aer">Entrega Expressa</div>
							</div>
						</div>
						<div class="comp-grid-row opcoes">
							<div class="personalizar">
								<a href="<?php echo $destaques_personalizados->rows[$i]['seo_url'];?>">MONTE A SUA</a>
								Personalize 100% a montagem da sua mesa.
							</div>
							<div class="mesas">
								<a href="<?php echo $sistema->seo_pages['produtos-personalizados']['seo_url']; ?>/<?php echo $destaques_personalizados->rows[$i]['seo_url_personalizado'];?>">VER MESAS</a>
								Use mesas que já fabricamos para te inspirar a montar a sua.
							</div>
						</div>
					</div>
				</div>
			</div>
	    <?php 
			} 
		?>
	</div>

	<div class="comp-grid-row">
		<div class="banner_fichas">
			<div class="main_titles"><?php echo $banner_avulso_titulo['titulo']; ?></div>
			<a href="<?php echo $banner_avulso_titulo['link1']; ?>" class="banner1" target="<?php echo $banner_avulso_titulo['target1']; ?>"><img src="<?php echo $sistema->getImageFileSized($banner_avulso_titulo['imagem1'],769,258);?>" alt="Imagem 1"></a>
			<a href="<?php echo $banner_avulso_titulo['link2']; ?>" class="banner2" target="<?php echo $banner_avulso_titulo['target2']; ?>"><img src="<?php echo $sistema->getImageFileSized($banner_avulso_titulo['imagem2'],353,258);?>" alt="Imagem 2"></a>
		</div>
	</div>
</div>
<?php endif; ?>
<?php if($destaques_produtos->num_rows) : ?>
<div class="background_poker">
	<div class="comp-grid-main-in comp-grid-row">
        <?php 
        	foreach ($destaques_produtos->rows as $destaque) {
        		$preco = getTotal($destaque['custo']+$destaque['frete_embutido'],$destaque['id_personalizado'],$destaque['id_produto']); 
		    	$avaliacao = explode('|', $destaque['avaliacao']);
		    	$avaliacao_nota = $avaliacao[0];
		    	$avaliacao_qtde = $avaliacao[1];
        ?>
			<div class="comp-grid-fourth two-two">
				<div class="produto-list">
					<div class="head">
						<div class="titulo"><h2><?php echo $destaque['nome']; ?></h2></div>
						<div class="subtitulo"><h3><?php echo $destaque['resumo']; ?></h3></div>
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
					<div class="img"><img src="<?php echo $sistema->getImageFileSized($destaque['imagem'],455,335);?>" alt="<?php echo $destaque['nome']; ?>"></div>
					<div class="info">
						<div class="comp-grid-row">
							<div class="preco">
	                                                        <div class="val">Comprar a partir de: <span>R$ <?php echo $sistema->formataMoedaShow($preco);?></span></div>
	                                                        Em até <strong style="font-weight: bold;">10x de R$ <?php echo $sistema->formataMoedaShow(($preco/10)); ?></strong> sem juros
							</div>
							<div class="fretes">
								<span class="ter"><img src="img/icon_frete_terrestre.png" alt=""></span>
								<span class="aer"><img src="img/icon_frete_aereo.png" alt=""></span>
							</div>
						</div>
						<a href="<?php echo $destaque['seo_url']; ?>" class="link-comprar">MONTE A SUA</a>
					</div>
				</div>
			</div>
        <?php } ?>
	</div>
</div>
<?php endif; ?>

<div class="comp-grid-main-in comp-grid-row">
        <?php if ($destaques_pronto_entrega->num_rows) : ?>
        <?php $preco = getTotal($destaque['custo']+$destaque['frete_embutido'],null,$destaque['id_produto']); ?>
	<div class="pronta_entrega">
		<div class="main_titles">MESAS DE POKER A PRONTA ENTREGA - <strong>ENTREGUE NA SUA CASA EM ATÉ 72 H</strong></div>
		<div class="roleta">
			<div class="setas">
				<span class="left"><img src="img/seta_roleta_clientes_left.png" alt=""></span>
				<span class="right"><img src="img/seta_roleta_clientes_right.png" alt=""></span>
			</div>
			<div class="items">
                                <?php foreach ($destaques_pronto_entrega->rows as $destaque) : ?>
				<div class="item">
					<div class="produto-list">
						<div class="img"><img src="<?php echo $sistema->getImageFileSized($destaque['imagem'],455,335);?>" alt="<?php echo $destaque['nome']; ?>"></div>
						<div class="head">
							<div class="titulo"><?php echo $destaque['nome']; ?></div>
						</div>
						<div class="info">
							<div class="comp-grid-row">
								<div class="preco">
									<div class="val">Comprar a partir de: <span>R$ <?php echo $sistema->formataMoedaShow($preco);?></span></div>
                                                                        Em até <strong style="font-weight: bold;">10x de R$ <?php echo $sistema->formataMoedaShow(($preco/10)); ?></strong> sem juros
								</div>
								<div class="fretes">
									<span class="ter"><img src="img/icon_frete_terrestre.png" alt=""></span>
									<span class="aer"><img src="img/icon_frete_aereo.png" alt=""></span>
								</div>
							</div>
							<a href="<?php echo $destaque['seo_url']; ?>" class="link-comprar">COMPRAR</a>
						</div>
					</div>
				</div>
                                <?php endforeach; ?>
			</div>
		</div>
	</div>
        <?php endif; ?>

	<div class="banners_avulsos comp-grid-row">
		<div class="comp-grid-half banner">
			<a href="<?php echo $banner_avulso['link1']; ?>" target="<?php echo $banner_avulso['target1']; ?>"><img src="<?php echo $sistema->getImageFileSized($banner_avulso['imagem1'],560,150);?>" alt=""></a>
		</div>
		<div class="comp-grid-half banner">
			<a href="<?php echo $banner_avulso['link2']; ?>" target="<?php echo $banner_avulso['target2']; ?>"><img src="<?php echo $sistema->getImageFileSized($banner_avulso['imagem2'],560,150);?>" alt=""></a>
		</div>
	</div>
        <?php if ($clientes_vitrine->num_rows) : ?>
	<div class="clientes">
		<div class="main_titles">ALGUNS DE NOSSOS CLIENTES</div>
		<div class="roleta">
			<div class="setas">
				<span class="left"><img src="img/seta_roleta_clientes_left.png" alt=""></span>
				<span class="right"><img src="img/seta_roleta_clientes_right.png" alt=""></span>
			</div>
			<div class="items">
                            <?php foreach ($clientes_vitrine->rows as $cliente) :?>
				<div class="item"><img src="<?php echo $sistema->getImageFileSized($cliente['imagem'],150,150);?>" alt="<?php echo $cliente['nome'];?>"></div>
                            <?php endforeach; ?>
			</div>
		</div>
	</div>
        <?php endif; ?>
</div>

<script src="js/jquery.banners.js"></script>
<?php
	include "_inc_footer.php";
	include "plugins/owl/owl.php";
?>

<script src="js/jquery.easing.js"></script>
</body>
</html>
