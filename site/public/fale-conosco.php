<?php
	$pagina = "fale-conosco";
        
        // CURRENTPAGE DATA ---- //
        $currentpage = $sistema->seo_pages[$pagina];
        // --------------------------------------------- //
        
	include "_inc_headers.php"; 
?>
</head>
<body id="<?php echo $pagina; ?>">
<?php include "_inc_header.php"; ?>
<div class="breadcrumbs">
	<div class="comp-grid-main-in">
		<span><a href="<?php echo $sistema->root_path; ?>">Home</a></span>
		<span><a href="<?php echo $sistema->seo_pages['cadastro']['seo_url']; ?>">Cadastre-se</a></span>
	</div>
</div>

<div class="comp-grid-main-in">
	<div class="main_titles"><h1>FALE CONOSCO</h1></div>
	<div class="comp-grid-row">
		<div class="contact_infos">
			<div class="info contatos">
				<div class="title">NOSSOS CONTATOS</div>
	            <span class="whatsapp">WhatsApp: <a href="tel:11930813044">(11) 93081-3044</a></span>
			    <span class="whatsapp">WhatsApp: <a href="tel:11930743885">(11) 93074-3885</a></span>
	            <span>Belém: <a href="tel:9129920003">(91) 2992-0003</a></span>
	            <span>Belo Horizonte: <a href="tel:3126268028">(31) 2626-8028</a></span>
	            <span>Brasília: <a href="tel:6136860003">(61) 3686-0003</a></span>
	            <span>Campinas: <a href="tel:1940404106">(19) 4040-4106</a></span>
	            <span>Curitiba: <a href="tel:4126262136">(41) 2626-2136</a></span>
	            <span>Goiânia: <a href="tel:6231810303">(62) 3181-0303</a></span>
	            <span>Porto Alegre: <a href="tel:5126264236">(51) 2626-4236</a></span>
	            <span>Recife: <a href="tel:8126261520">(81) 2626-1520</a></span>
	            <span>Rio de Janeiro: <a href="tel:2130052328">(21) 3005-2328</a></span>
	            <span>Salvador: <a href="tel:7126261295">(71) 2626-1295</a></span>
	            <span>Santos: <a href="tel:1321910007">(13) 2191-0007</a></span>
	            <span>São Paulo: <a href="tel:1126261632">(11) 2626-1632</a></span>
	            <span>São Jose: <a href="tel:1727860548">(17) 2786-0548</a></span>
			</div>
			<div class="info horarios">
				<div class="title">HORÁRIO DE FUNCIONAMENTO</div>
				<span>Horário de atendimento das 8:00 às 11:00 e das 13:00 às 18:00</span>
			</div>
		</div>
		<form class="form_contato" action="javascript:;" method="post">
                    <input type="hidden" name="id_seo" value="<?php echo $dynamic_id; ?>" />
			<div class="editable_content">
				<p>Entre em contato conosco preenchendo o formulário abaixo. Em breve retornaremos o contato.</p>
			</div>
			<div class="comp-grid-row">
				<div class="comp-forms-grid-half-tablet comp-forms-item">
					<label>
						<span>Nome:</span>
						<input type="text" name="nome">
					</label>
				</div>
				<div class="comp-forms-grid-half-tablet comp-forms-item">
					<label>
						<span>E-mail:</span>
						<input type="text" name="email">
					</label>
				</div>
				<div class="comp-forms-grid-half-tablet comp-forms-item">
					<label>
						<span>Telefone:</span>
						<input type="text" name="fone" class="mask-telefone">
					</label>
				</div>
				<div class="comp-forms-grid-half-tablet comp-forms-item">
					<label>
						<span>Celular:</span>
						<input type="text" name="celular" class="mask-telefone">
					</label>
				</div>
			</div>
			<div class="comp-grid-row">
				<div class="comp-forms-grid-once comp-forms-item">
					<label>
						<span>Mensagen:</span>
						<textarea name="mensagem" rows="7"></textarea>
					</label>
				</div>
			</div>
			<div class="comp-grid-row">
				<div class="comp-forms-grid-once comp-forms-item">
					<button type="button" class="clickformsubmit">ENVIAR</button>
				</div>
			</div>
		</form>
	</div>
</div>

<?php include "_inc_footer.php"; ?>

<script src="js/jquery.banners.js"></script>
<script src="js/jquery.easing.js"></script>
</body>
</html>