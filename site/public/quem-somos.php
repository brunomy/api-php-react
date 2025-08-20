<?php
	$pagina = "quem-somos";
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
	<div class="main_titles"><h1>QUEM SOMOS</h1></div>
	<div class="editable_content">
		<div class="comp-grid-row">
			<div class="comp-grid-half two-one">
				<p><img src="img/temp_quem_somos.jpg" alt="Quem Somos" class="resize-off"></p>
			</div>
			<div class="comp-grid-half two-one">
				<p>A Real Poker é uma indústria especializada na fabricação de mesas de poker, profissionais e personalizadas, e acessórios para carteado. Com grande experiência no mercado, a empresa possui sua sede no coração do Brasil, atendendo a todo o território nacional através do televendas e loja virtual. Todos os produtos que levam a marca Real Poker passam por um criterioso processo de certificação para garantir que o produto entregue à você seja único.</p>
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