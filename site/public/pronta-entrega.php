<?php
	$pagina = "pronta-entrega";
	include "_inc_headers.php"; 
?>
</head>
<body id="<?php echo $pagina; ?>">
<?php include "_inc_header.php"; ?>
<div class="breadcrumbs">
	<div class="comp-grid-main-in">
		<span><a href="<?php echo $sistema->root_path; ?>">Home</a></span>
		<span><a href="">Cadastre-se</a></span>
	</div>
</div>

<div class="comp-grid-main-in">
	<div class="main_titles"><h1 class="destaque">PRODUTOS A PRONTA ENTREGA, ENTREGUE NA SUA CASA EM ATÉ 72 H</h1></div>
	<div class="comp-grid-row">
		<?php for ($i=0;$i<9;$i++): ?>
			<div class="comp-grid-third two-one">
				<div class="produto-list">
					<div class="head">
						<div class="titulo">Mesas de Poker Profissionais</div>
						<div class="subtitulo">Para clubes de Poker, residências e torneios</div>
					</div>
					<div class="img"><img src="img/temp_thumb_produto.jpg" alt="Mesas de Poker Profissionais"></div>
					<div class="info">
						<div class="comp-grid-row">
							<div class="preco">
								<div class="val">A partir de: <span>R$ 1.129,90</span></div>
								Em até 10x de R$ 112,99 sem juros
							</div>
							<div class="fretes">
								<span class="ter"><img src="img/icon_frete_terrestre.png" alt=""></span>
								<span class="aer"><img src="img/icon_frete_aereo.png" alt=""></span>
							</div>
						</div>
						<a href="produto" class="link-comprar">COMPRAR</a>
					</div>
				</div>
			</div>
		<?php endfor ?>
	</div>
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