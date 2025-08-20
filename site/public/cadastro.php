<?php

	//se tiver usando http, transfere para https enviando o id da sessão atual http para https
	/*
	if($_SERVER['HTTP_HOST'] == "www.realpoker.com.br" || $_SERVER['HTTP_HOST'] == "realpoker.com.br"){
		if (empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'on') {
		    header("location:https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']."/?via=".session_id());
		}
	}
	*/

	$pagina = "cadastro";
        
        // CURRENTPAGE DATA ---- //
        $currentpage = $sistema->seo_pages[$pagina];
        // --------------------------------------------- //
        
        $estados = $sistema->DB_fetch_array("SELECT * FROM tb_utils_estados ORDER BY estado");
        
        
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
	<div class="main_titles"><h1>CADASTRE-SE</h1></div>
	<div class="editable_content">
		<?php echo $sistema->trataTexto($currentpage['seo_pagina_conteudo']);?>
	</div>
	<form class="form_cadastro comp-forms comp-grid-row" action="javascript:;" method="post">
		<div class="comp-grid-half comp-forms-divisao">
                        <input type="hidden" name="id_seo" value="<?php echo $dynamic_id; ?>" />
			<div class="comp-forms-divisao-titulo">DADOS PESSOAIS</div>
			<div class="comp-grid-row">
				<div class="comp-forms-grid-once comp-forms-item">
					<label>
						<span>Tipo:</span>
						<select name="pessoa" class="pessoa">
							<option value="1">Pessoa Física</option>
							<option value="2">Pessoa Jurídica</option>
						</select>
					</label>
				</div>
			</div>
			<div class="comp-grid-row pessoa_juridica">
				<div class="comp-forms-grid-half-tablet comp-forms-item">
					<label>
						<span>Razão Social:</span>
						<input type="text" name="razao_social">
					</label>
				</div>
				<div class="comp-forms-grid-half-tablet">
					<div class="comp-grid-row">
						<div class="comp-forms-grid-half-tablet comp-forms-item">
							<label>
								<span>CNPJ:</span>
								<input type="text" name="cnpj" class="mask-cnpj">
							</label>
						</div>
						<div class="comp-forms-grid-half-tablet comp-forms-item">
							<label>
								<span>Insc. Estadual:</span>
								<input type="text" name="inscricao_estadual" maxlength="18">
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
						<input type="text" name="nome">
					</label>
				</div>
				<div class="comp-forms-grid-half-tablet comp-forms-item">
					<label>
						<span>CPF:</span>
						<input type="text" name="cpf" class="mask-cpf">
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
						<span>Telefone ou Celular:</span>
						<input type="text" name="telefone" class="mask-telefone">
					</label>
				</div>
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
		</div>
		<div class="comp-grid-half comp-forms-divisao">
			<div class="comp-forms-divisao-titulo"> ENDEREÇO </div>
			<div class="comp-grid-row">
				<div class="comp-forms-grid-half-tablet comp-forms-item">
					<label>
						<span>CEP:</span>
						<input type="text" name="cep" class="mask-cep">
					</label>
				</div>
				<div class="comp-forms-grid-half-tablet comp-forms-item">
					<label>
						<span>Endereço:</span>
						<input type="text" name="endereco">
					</label>
				</div>
				<div class="comp-forms-grid-half-tablet comp-forms-item">
					<label>
						<span>Bairro:</span>
						<input type="text" name="bairro">
					</label>
				</div>
				<div class="comp-forms-grid-half-tablet comp-forms-item">
					<label>
						<span>Número:</span>
						<input type="text" name="numero" class="mask-numero">
					</label>
				</div>
				<div class="comp-forms-grid-half-tablet comp-forms-item">
					<label>
						<span>Complemento:</span>
						<input type="text" name="complemento">
					</label>
				</div>
				<div class="comp-forms-grid-half-tablet comp-forms-item">
					<label>
						<span>Estado:</span>
						<select name="id_estado" class="estado">
							<option value="">Selecione o Estado</option>
                                                        <?php if ($estados->num_rows) :?>
                                                        <?php foreach($estados->rows as $estado):?>
                                                        <option data-id="<?php echo $estado['id'];?>" data-uf="<?php echo $estado['uf'];?>" value="<?php echo $estado['id'];?>"><?php echo $estado['estado'];?></option>
                                                        <?php endforeach;?>
                                                        <?php endif;?>
						</select>
					</label>
				</div>
				<div class="comp-forms-grid-half-tablet comp-forms-item">
					<label>
						<span>Cidade:</span>
						<select name="id_cidade" class="cidade">
							<option value="">Selecione uma Cidade</option>
						</select>
					</label>
				</div>
				<div class="comp-forms-grid-half-tablet comp-forms-item">
					<button type="button" class="clickformsubmit">CADASTRAR</button>
				</div>
			</div>
		</div>
	</form>
</div>

<?php 
	include "_inc_footer.php";
?>

<script src="js/jquery.banners.js"></script>
<script src="js/jquery.easing.js"></script>
</body>
</html>