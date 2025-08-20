<?php

	//se tiver usando http, transfere para https enviando o id da sessão atual http para https
	/*
	if($_SERVER['HTTP_HOST'] == "www.realpoker.com.br" || $_SERVER['HTTP_HOST'] == "realpoker.com.br"){
		if (empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'on') {
		    header("location:https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']."/?via=".session_id());
		}
	}
	*/

	$pagina = "login";
        
        if (isset($_SESSION['cliente_logado'])) {
            header ("Location: " . $sistema->root_path . "minha-conta");
            exit();
        }

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
		<span><a href="<?php echo $sistema->seo_pages['login']['seo_url']; ?>">Login</a></span>
	</div>
</div>

<div class="comp-grid-main-in">
	<div class="main_titles"><h1>LOGIN</h1></div>
	<div class="comp-grid-row">
	    <div class="col-login">
	        <div class="titulo">Já sou cliente</div>
	        <form action="javascript:;" method="post" class="loginbox form_login">
	            <div class="label">Endereço de e-mail <span>*</span></div>
	            <input type="text" name="email" placeholder="Digite seu e-mail">
	            <div class="label">Senha <span>*</span></div>
	            <input type="password" name="senha" placeholder="Digite seu senha">
	            <span class="esqueceu">Esqueci minha senha.</span>
	            <button type="button" class="clickformsubmit">ENTRAR</button>
	        </form>
	    </div>
	    <div class="col-cadastro">
	    	<div class="titulo">Ainda não sou cliente</div>
	    	<p>Se é sua primeira compra na REAL POKER, faça seu cadastro aqui, rápido e simples.</p>
	    	<p><a href="<?php echo $sistema->seo_pages['cadastro']['seo_url']; ?>" class="link-cadastro">CADASTRAR</a></p>
	    </div>
	</div>
</div>

<?php include "_inc_footer.php"; ?>

<script src="js/jquery.banners.js"></script>
<script src="js/jquery.easing.js"></script>
</body>
</html>