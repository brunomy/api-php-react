<?php

	//se tiver usando http, transfere para https enviando o id da sessão atual http para https
	/*
	if($_SERVER['HTTP_HOST'] == "www.realpoker.com.br" || $_SERVER['HTTP_HOST'] == "realpoker.com.br"){
		if (empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'on') {
		    header("location:https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']."/?via=".session_id());
		}
	}
	*/

	$pagina = "dados-pessoais";
        
        if (!isset($_SESSION['cliente_logado'])) {
            //header ("Location: " . $sistema->root_path . "home/?login");
            header ("Location: " . $sistema->root_path . "login");
            exit();
        }
        
        // CURRENTPAGE DATA ---- //
        $currentpage = $sistema->seo_pages[$pagina];
        // --------------------------------------------- //
        
        $meusdados = $sistema->DB_fetch_array("SELECT * FROM tb_clientes_clientes WHERE id = {$_SESSION['cliente_id']}");
        if($meusdados->num_rows)
            $meusdados = $meusdados->rows[0];
        else
            header("Location: sair");
        
      
        
	include "_inc_headers.php"; 
        
?>
</head>
<body id="<?php echo $pagina; ?>">
<?php include "_inc_header.php"; ?>
<div class="breadcrumbs">
	<div class="comp-grid-main-in">
		<span><a href="<?php echo $sistema->root_path; ?>">Home</a></span>
		<span><a href="<?php echo $sistema->seo_pages['dados-pessoais']['seo_url']; ?>">Dados Pessoais</a></span>
	</div>
</div>

<div class="comp-grid-main-in">
	<div class="main_titles"><h1>DADOS PESSOAIS</h1></div>
	<div class="comp-grid-row">
		<div class="comp-grid-aside-left">
			<?php include "_inc_menu_conta.php"; ?>
		</div>
		<div class="comp-grid-aside-main">
			<form class="form_dados_pessoais comp-forms comp-grid-row" action="javascript:;" method="post">
                            <input name="id" type="hidden" value="<?php echo $meusdados['id'];?>" />
				<div class="comp-grid-row">
					<div class="comp-forms-grid-once comp-forms-item">
						<label>
							<span>Tipo:</span>
							<select name="pessoa" class="pessoa">
								<option <?php if($meusdados['pessoa'] == 1) echo 'selected';?> value="1">Pessoa Física</option>
								<option <?php if($meusdados['pessoa'] == 2) echo 'selected';?> value="2">Pessoa Jurídica</option>
							</select>
						</label>
					</div>
				</div>
				<div class="comp-grid-row pessoa_juridica">
					<div class="comp-forms-grid-half-tablet comp-forms-item">
						<label>
							<span>Razão Social:</span>
							<input type="text" name="razao_social" value="<?php echo $meusdados['razao_social'];?>">
						</label>
					</div>
					<div class="comp-forms-grid-half-tablet">
						<div class="comp-grid-row">
							<div class="comp-forms-grid-half-tablet comp-forms-item">
								<label>
									<span>CNPJ:</span>
									<input type="text" name="cnpj" class="mask-cnpj" value="<?php echo $meusdados['cnpj'];?>">
								</label>
							</div>
							<div class="comp-forms-grid-half-tablet comp-forms-item">
								<label>
									<span>Inscrição Estadual:</span>
									<input type="text" name="inscricao_estadual" maxlength="18" value="<?php echo $meusdados['inscricao_estadual'];?>">
								</label>
							</div>
						</div>
					</div>
				</div>
				<div class="comp-grid-row">
					<div class="comp-forms-grid-half-tablet comp-forms-item">
						<label>
							<span class="pessoa_fisica">Nome:</span>
							<span class="pessoa_juridica">Responsável:</span>
							<input type="text" name="nome" value="<?php echo $meusdados['nome'];?>">
						</label>
					</div>
					<div class="comp-forms-grid-half-tablet comp-forms-item">
						<label>
							<span>CPF:</span>
							<input type="text" name="cpf" class="mask-cpf" value="<?php echo $meusdados['cpf'];?>">
						</label>
					</div>
					<div class="comp-forms-grid-half-tablet comp-forms-item">
						<label>
							<span>E-mail:</span>
							<input type="text" name="email" value="<?php echo $meusdados['email'];?>">
						</label>
					</div>
					<div class="comp-forms-grid-half-tablet comp-forms-item">
						<label>
							<span>Telefone ou Celular:</span>
							<input type="text" name="telefone" class="mask-telefone" value="<?php echo $meusdados['telefone'];?>">
						</label>
					</div>
				</div>
				<div class="comp-grid-row">
					<div class="comp-forms-item">
						<button type="button" class="clickformsubmit comp-forms-bt_submit">ALTERAR</button>
						<button type="button" class="excluir_conta">EXCLUIR DADOS</button>
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