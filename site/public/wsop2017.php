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
			<div class="video"><iframe width="100%" height="100%" src="https://www.youtube.com/embed/3Lz7W4CtjYs?rel=0&amp;controls=1&amp;showinfo=0&amp;autoplay=1" frameborder="0" allowfullscreen></iframe></div>
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
							<option value="0">Escolha o número</option>
							<option value="1vendido">1 (VENDIDO)</option>
							<option value="2">2 VENDIDO)</option>
							<option value="3">3 (VENDIDO)</option>
							<option value="4">4 (VENDIDO)</option>
							<option value="5">5 (VENDIDO)</option>
							<option value="6">6 (VENDIDO)</option>
							<option value="7">7</option>
							<option value="8">8 (VENDIDO)</option>
							<option value="9">9</option>
							<option value="10vendido">10 (VENDIDO)</option>
							<option value="11">11 (VENDIDO)</option>
							<option value="12">12 (VENDIDO)</option>
							<option value="13vendido">13 (VENDIDO)</option>
							<option value="14">14 (VENDIDO)</option>
							<option value="15">15</option>
							<option value="16">16 </option>
							<option value="17">17 (VENDIDO)</option>
							<option value="18">18 (VENDIDO)</option>
							<option value="19">19 </option>
							<option value="20">20</option>
						</select>
					</div>
					<div class="row"><button type="button" class="clickformsubmit">EU QUERO</button></div>
				</div>
			</form>
		</div>
	</div>
</div>
<div class="mesa_wsop row">
	<div class="titulo"><div class="comp-grid-main"><strong>KIT MESA E FICHAS DE POKER</strong> WSOP CIRCUIT BRAZIL - ESPECIAL EDITION</div></div>
	<div class="comp-grid-main">
		<div class="mesa_foto"><img src="img/mesa_wsop2017.jpg" alt="Mesa WSOP"></div>
		<div class="mesa_texto">
			<p>As mesas que serão o palco do WSOP CIRCUIT BRAZIL, onde serão disputados milhões de reais, anéis do WSOP, onde vão sentar e jogar os melhores jogadores de poker do Brasil e do mundo, foram seladas com uma placa com uma série numerada e limitada. </p>
			<p>Fabricadas dentro do alto padrão de qualidade da Real poker:</p>
			<ul>
				<li>Corte computadorizado</li>
				<li>MDF com acabamento na face para maior durabilidade</li>
				<li>Tecido impermeável e especial de carteado que garante o deslisar das cartas</li>
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
					<div class="img"><img src="img/capa.jpg" alt="Capa de Proteção"></div>
					<p><b>BÔNUS 1</b> - Uma capa em courino de alta durabilidade para protejer sua mesa de poker.</p>
				</div>
				<div class="bonus_bauer">
					<div class="img"><img src="img/ticket.png" alt="Buy-in"></div>
					<p><b>BÔNUS 2</b> - Um Buy-in para o torneio de convidados Real Poker dia 29/09/2017 às 20h no WSOP com mais de R$2.000,00 em prêmios.</p>
				</div>
			</div>
		</div>
		<div class="certificado_wsop">
			<p><img src="img/jack.png" alt="Jack"></p>
			<p><b>BÔNUS 3</b> - Autografo do ícone do poker mundial Jack Effel na sua Mesa de Poker</p>
		</div>
	</div>
</div>
<div class="comp-grid-main">
	<div class="comp-grid-row">
		<div class="wsop_operacao">
			<div class="texto">As mesas são do WSOP, mas é a Real Poker que vai cuidar de toda a operação com toda credibilidade e segurança que você já conhece,  no dia 9 de outubro de 2017, após o evento as mesas serão devidamente higienizadas e enviadas para seu endereço, o frete é grátis para a maioria das cidades do brasil, você pode consultar pelo seu CEP após clicar em EU QUERO.</div>
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
	        	<div class="texto">1 Mesa de Poker Limited Edition (9 Lugares - 2,40 x 1,10 m) + 1 Maleta de 500 fichas de plástico do WSOP</div>
	        	<div class="line1">Em até</div>
	        	<div class="line2">10x de R$ <span>297</span></div>
	        	<div class="line3">ou R$ 2.970</div>
	        	<div class="line4">Desconto no Boleto: <strong>R$ 2.822</strong></div>
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
							<option value="0">Escolha o número</option>
							<option value="1vendido">1 (VENDIDO)</option>
							<option value="2">2 VENDIDO)</option>
							<option value="3">3 (VENDIDO)</option>
							<option value="4">4 (VENDIDO)</option>
							<option value="5">5 (VENDIDO)</option>
							<option value="6">6 (VENDIDO)</option>
							<option value="7">7</option>
							<option value="8">8 (VENDIDO)</option>
							<option value="9">9</option>
							<option value="10vendido">10 (VENDIDO)</option>
							<option value="11">11 (VENDIDO)</option>
							<option value="12">12 (VENDIDO)</option>
							<option value="13vendido">13 (VENDIDO)</option>
							<option value="14">14 (VENDIDO)</option>
							<option value="15">15</option>
							<option value="16">16 </option>
							<option value="17">17 (VENDIDO)</option>
							<option value="18">18 (VENDIDO)</option>
							<option value="19">19 </option>
							<option value="20">20</option>
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