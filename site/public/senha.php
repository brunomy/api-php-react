<?php

	//se tiver usando http, transfere para https enviando o id da sessão atual http para https
	/*
	if($_SERVER['HTTP_HOST'] == "www.realpoker.com.br" || $_SERVER['HTTP_HOST'] == "realpoker.com.br"){
		if (empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'on') {
			if(isset($_GET['code'])){
				$_SESSION['senha_code'] = $_GET['code'];
			}
		    header("location:https://".$_SERVER['HTTP_HOST']."/senha/?via=".session_id());
		}
	}
	*/

	$pagina = "senha";
       
		if (!isset($_SESSION['cliente_logado']) AND !isset($_GET['code']) AND !isset($_SESSION['senha_code'])) {
		    //header ("Location: " . $sistema->root_path . "home/?login");
		    header ("Location: " . $sistema->root_path . "login");
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
		<span><a href="<?php echo $sistema->seo_pages['senha']['seo_url']; ?>">Alterar Senha</a></span>
	</div>
</div>

<div class="comp-grid-main-in">
	<div class="main_titles"><h1>ALTERAR SENHA</h1></div>
	<div class="comp-grid-row">
		<div class="comp-grid-aside-left">
                    <?php if(!isset($_GET['code']) AND !isset($_SESSION['senha_code'])) :?>
			<?php include "_inc_menu_conta.php"; ?>
                    <?php endif; ?>
		</div>
		<div class="comp-grid-aside-main">
			<form class="form_senha comp-forms comp-grid-row" action="javascript:;" method="post">
                            <input type="hidden" name="code" value="<?php if(isset($_GET['code'])) echo $_GET['code'];?>">
				<div class="comp-grid-row">
					<div class="comp-forms-grid-half-tablet comp-forms-item">
						<label>
							<span>Senha:</span>
							<input type="password" name="senha">
						</label>
					</div>
					<div class="comp-forms-grid-half-tablet comp-forms-item">
						<label>
							<span>Confirmar senha:</span>
							<input type="password" name="senha2">
						</label>
					</div>
				</div>
				<div class="comp-grid-row">
					<div class="comp-forms-item">
						<button type="button" class="clickformsubmit comp-forms-bt_submit">ALTERAR</button>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>

<?php include "_inc_footer.php"; ?>

<script src="js/jquery.banners.js"></script>
<script src="js/jquery.easing.js"></script>
</body>
</html>