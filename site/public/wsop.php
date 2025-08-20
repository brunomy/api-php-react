<?php
	$pagina = "wsop";
        
        // CURRENTPAGE DATA ---- //
        $currentpage = $sistema->seo_pages[$pagina];
        // --------------------------------------------- //
        
        $p = $product->getProdutoById(37);
        
	include "_inc_headers.php";
?>
</head>
<body id="<?php echo $pagina; ?>">
<?php include "_inc_header.php"; ?>
<div class="topo_wsop">
	<div class="comp-grid-main">
		<div class="video_box">
			<div class="titulo">OPORTUNIDADE ÚNICA PARA VOCÊ QUE É FÃ DE POKER</div>
			<div class="video"><iframe width="100%" height="100%" src="https://www.youtube.com/embed/kz2aWc5mN6w?rel=0&amp;controls=1&amp;showinfo=0&amp;autoplay=1" frameborder="0" allowfullscreen></iframe></div>
		</div>
		<div class="caixa_form">
			<form class="form_participe" method="post" action="javascript:;">
                                <input name="retorno" type="hidden" value="<?php echo $sistema->seo_pages['carrinho']['seo_url']; ?>">
                                <input type="hidden" name="id_produto" value="<?php echo $p->rows[0]['id']; ?>">
                                <input type="hidden" name="custo" value="<?php echo $p->rows[0]['custo']+$p->rows[0]['frete_embutido']; ?>">
                                <input type="hidden" name="id_seo" value="<?php echo $p->rows[0]['id_seo']; ?>">
                                <input type="hidden" name="nome_produto" value="<?php echo $p->rows[0]['nome']; ?>">
                                <input type="hidden" name="peso" value="<?php echo $p->rows[0]['peso']; ?>">
                                <input type="hidden" name="quantidade" value="1">
				<div class="head">PARTICIPE AQUI</div>
				<div class="miolo">
					<div class="row">
						<label>Nome:</label>
						<input type="text" name="nome">
					</div>
					<div class="row">
						<label>E-mail:</label>
						<input type="text" name="email">
					</div>
					<div class="row">
						<label>Telefone:</label>
						<input type="text" name="telefone" class="mask-telefone">
					</div>
					<div class="row">
						<label>Mesa:</label>
						<select name="mesa">
							<option value="0">ESGOTADO</option>
						</select>
					</div>
					<div class="row"><button type="button" class="clickformsubmit">EU QUERO</button></div>
				</div>
			</form>
		</div>
	</div>
</div>
<div class="mesa_wsop row">
	<div class="titulo"><div class="comp-grid-main"><strong>MESA DE POKER</strong> WSOP CIRCUIT BRAZIL - LIMITED EDITION</div></div>
	<div class="comp-grid-main">
		<div class="mesa_foto"><img src="img/mesa_wsop.jpg" alt="Mesa WSOP"></div>
		<div class="mesa_texto">
			<p>As mesas que serão o palco do WSOP CIRCUIT BRAZIL, onde serão disputados milhões de reais, anéis do WSOP, onde vão sentar e jogar os melhores jogadores de poker do Brasil e do mundo, foram seladas com uma placa com uma série numerada e limitada. </p>
			<p>Fabricadas dentro do alto padrão de qualidade da Real poker:</p>
			<ul>
				<li>Corte computadorizado</li>
				<li>MDF com acabamento na face para maior durabilidade</li>
				<li>Tecido especial de carteado que garante o deslisar das cartas</li>
				<li>Couro da borda sem costuras de emendas</li>
				<li>Pernas dobráveis em aço com tratamento antiferrugem e pintura epoxi que não descasca. </li>
				<li>E o melhor, uma dessas mesas de poker exclusivas pode ser sua.</li>
			</ul>
		</div>
	</div>
</div>
<div class="bonus_wsop row">
	<div class="comp-grid-main">
		<div class="bonus_box">
			<div class="row">
				<div class="titulo"><strong>BÔNUS EXCLUSIVOS</strong> PARA VOCÊ FECHAR AGORA</div>
				<div class="bonus_revista">
					<div class="img"><img src="img/revista_cardplay2.png" alt="Card Play"></div>
					<p><b>BÔNUS 1</b> - Um ano de assinatura da revista Cardplayer. Receba todo mês na sua casa a principal revista de poker do Brasil.</p>
				</div>
				<div class="bonus_bauer">
					<div class="img"><img src="img/joao_bauer2.jpg" alt="João Bauer"></div>
					<p><b>BÔNUS 2</b> - Curso on-line com o atual campeão brasileiro de poker João Bauer. No valor de R$ 690, e você não pagará nada por ele.</p>
				</div>
			</div>
		</div>
		<div class="certificado_wsop">
			<p><img src="img/certificado_wsop.png" alt="Certificado WSOP"></p>
			<p><b>BÔNUS 3</b> - Certificado de autenticidade para cada uma das mesas, assinados manualmente pelo diretor do WSOP CIRCUIT BRAZIL</p>
		</div>
	</div>
</div>
<div class="comp-grid-main">
	<div class="comp-grid-row">
		<div class="wsop_operacao">
			<div class="texto">As mesas são do WSOP, mas é a Real Poker que vai cuidar de toda a operação com toda credibilidade e segurança que você já conhece,  no dia 3 de novembro de 2016, após o evento as mesas serão devidamente higienizadas e enviadas para seu endereço, o frete é grátis para a maioria das cidades do brasil, você pode consultar pelo seu CEP após clicar em EU QUERO.</div>
	        <div class="comp-grid-row features">
	            <div class="comp-grid-fourth">
	                <div class="featured">
	                    <div class="icon"><img src="img/icon_features_frete.png" alt="FRETE GRÁTIS BRASIL"></div>
	                    <div class="info">
	                        <span>FRETE GRÁTIS BRASIL</span>
	                        Para todas as compras, exceto mesas para norte e nordeste.
	                    </div>
	                </div>
	            </div>
	            <div class="comp-grid-fourth">
	                <div class="featured">
	                    <div class="icon"><img src="img/icon_features_parcelamento.png" alt="EM ATÉ 10X SEM JUROS"></div>
	                    <div class="info">
	                        <span>EM ATÉ 10X SEM JUROS</span>
	                        Todo site parcelado sem juros nos cartões. Aproveite.
	                    </div>
	                </div>
	            </div>
	            <div class="comp-grid-fourth">
	                <div class="featured">
	                    <div class="icon"><img src="img/icon_features_desconto.png" alt="5% DE DESCONTO"></div>
	                    <div class="info">
	                        <span>5% DE DESCONTO</span>
	                        Desconto de 5% para compras à vista no boleto.
	                    </div>
	                </div>
	            </div>
	            <div class="comp-grid-fourth">
	                <div class="featured">
	                    <div class="icon"><img src="img/icon_features_cadeado.png" alt="SITE 100% SEGURO"></div>
	                    <div class="info">
	                        <span>SITE 100% SEGURO</span>
	                        Pode confiar, compre com tranquilidade.
	                    </div>
	                </div>
	            </div>
	        </div>
	        <div class="preco">
	        	<div class="texto">1 Mesa de Poker Limited Edition (9 Lugares - 2,40 x 1,10 m) + Certificado de Autenticidade</div>
	        	<div class="line1">Em até</div>
	        	<div class="line2">10x de R$ <span>237</span></div>
	        	<div class="line3">ou R$ 2.370</div>
	        	<div class="line4">Desconto no Boleto: <strong>R$ 2.252</strong></div>
	        </div>

			<div class="caixa_form">
				<form class="form_participe2" method="post" action="javascript:;">
                    <input name="retorno" type="hidden" value="<?php echo $sistema->seo_pages['carrinho']['seo_url']; ?>">
                    <input type="hidden" name="id_produto" value="<?php echo $p->rows[0]['id']; ?>">
                    <input type="hidden" name="custo" value="<?php echo $p->rows[0]['custo']+$p->rows[0]['frete_embutido']; ?>">
                    <input type="hidden" name="id_seo" value="<?php echo $p->rows[0]['id_seo']; ?>">
                    <input type="hidden" name="nome_produto" value="<?php echo $p->rows[0]['nome']; ?>">
                    <input type="hidden" name="peso" value="<?php echo $p->rows[0]['peso']; ?>">
                    <input type="hidden" name="quantidade" value="1">
					<div class="head">GARANTA AGORA A SUA</div>
					<div class="miolo row">
						<div class="col1">
							<div class="row">
								<label>Nome:</label>
								<input type="text" name="nome">
							</div>
							<div class="row">
								<label>E-mail:</label>
								<input type="text" name="email">
							</div>
							<div class="row">
								<label>Telefone:</label>
								<input type="text" name="telefone" class="mask-telefone">
							</div>
						</div>
						<div class="col2">
							<div class="row">
								<label>Mesa:</label>
								<select name="mesa">
							<option value="0">ESGOTADO</option>
						</select>
							</div>
							<div class="row"><button type="button" class="clickformsubmit">EU QUERO</button></div>
						</div>
					</div>
				</form>
			</div>
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