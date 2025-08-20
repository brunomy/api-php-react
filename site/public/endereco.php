<?php
	//se tiver usando http, transfere para https enviando o id da sessão atual http para https
	/*
	if($_SERVER['HTTP_HOST'] == "www.realpoker.com.br" || $_SERVER['HTTP_HOST'] == "realpoker.com.br"){
		if (empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'on') {
		    header("location:https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']."/?via=".session_id());
		}
	}
	*/
	
	$pagina = "endereco";
        
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
        
        $estados = $sistema->DB_fetch_array("SELECT * FROM tb_utils_estados ORDER BY estado");
        $cidades = $sistema->DB_fetch_array("SELECT * FROM tb_utils_cidades WHERE id_estado = {$meusdados['id_estado']} ORDER BY cidade");

        
	include "_inc_headers.php"; 
?>
</head>
<body id="<?php echo $pagina; ?>">
    
<script>
    $(document).ready(function () {
        var cidade = "";
    });
</script>
<?php include "_inc_header.php"; ?>
<div class="breadcrumbs">
	<div class="comp-grid-main-in">
		<span><a href="<?php echo $sistema->root_path; ?>">Home</a></span>
		<span><a href="<?php echo $sistema->seo_pages['endereco']['seo_url']; ?>">Endereço</a></span>
	</div>
</div>

<div class="comp-grid-main-in">
	<div class="main_titles"><h1>ENDEREÇO</h1></div>
	<div class="comp-grid-row">
		<div class="comp-grid-aside-left">
			<?php include "_inc_menu_conta.php"; ?>
		</div>
		<div class="comp-grid-aside-main">
			<form class="form_endereco comp-forms comp-grid-row" action="javascript:;" method="post">
				<div class="comp-grid-row">
					<div class="comp-forms-grid-half-tablet comp-forms-item">
						<label>
							<span>CEP:</span>
							<input type="text" name="cep" class="mask-cep" value="<?php echo $meusdados['cep'];?>">
						</label>
					</div>
					<div class="comp-forms-grid-half-tablet comp-forms-item">
						<label>
							<span>Endereço:</span>
							<input type="text" name="endereco" value="<?php echo $meusdados['endereco'];?>">
						</label>
					</div>
					<div class="comp-forms-grid-half-tablet comp-forms-item">
						<label>
							<span>Bairro:</span>
							<input type="text" name="bairro" value="<?php echo $meusdados['bairro'];?>">
						</label>
					</div>
					<div class="comp-forms-grid-half-tablet comp-forms-item">
						<label>
							<span>Número:</span>
							<input type="text" name="numero" value="<?php echo $meusdados['numero'];?>">
						</label>
					</div>
					<div class="comp-forms-grid-half-tablet comp-forms-item">
						<label>
							<span>Complemento:</span>
							<input type="text" name="complemento" value="<?php echo $meusdados['complemento'];?>">
						</label>
					</div>
					<div class="comp-forms-grid-half-tablet comp-forms-item">
						<label>
							<span>Estado:</span>
							<select name="id_estado" class="estado">
                                <option value="">Selecione o Estado</option>
                                <?php if ($estados->num_rows) :?>
                                <?php foreach($estados->rows as $estado):?>
                                <option <?php if ($estado['id'] == $meusdados['id_estado']) echo 'selected'; ?> data-id="<?php echo $estado['id'];?>" data-uf="<?php echo $estado['uf'];?>" value="<?php echo $estado['id'];?>"><?php echo $estado['estado'];?></option>
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
                                <?php if ($cidades->num_rows) :?>
                                <?php foreach($cidades->rows as $cidade):?>
                                <option <?php if ($cidade['id'] == $meusdados['id_cidade']) echo 'selected'; ?> data-id="<?php echo $cidade['id'];?>" data-uf="<?php echo $cidade['uf'];?>" value="<?php echo $cidade['id'];?>"><?php echo $cidade['cidade'];?></option>
                                <?php endforeach;?>
                                <?php endif;?>
                            </select>
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
<script>
    $(document).ready(function () {
        //cidade = "<?php echo $meusdados['id_cidade']; ?>";
        //$(".estado").trigger("change");
    });
</script>
</html>