<?php

	//se tiver usando http, transfere para https enviando o id da sessão atual http para https
	/*
	if($_SERVER['HTTP_HOST'] == "www.realpoker.com.br" || $_SERVER['HTTP_HOST'] == "realpoker.com.br"){
		if (empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'on') {
		    header("location:https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']."/?via=".session_id());
		}
	}
	*/
	

	$pagina = "checkout";
        
        // CURRENTPAGE DATA ---- //
        $currentpage = $sistema->seo_pages[$pagina];
        // --------------------------------------------- //
        
	//RECALCULA O FRETE
	use classes\Frete;
	$frete = new Frete();
	$frete->calcFreteAction();

        $carrinho = $product->getCarrinhoProdutosHistoricoBySession($_SESSION['seo_session']);
        if (!$carrinho->num_rows) {
            header("Location: carrinho");
            exit();
        }
        
        
        if (isset($_SESSION['cliente_id'])) {
            $meusdados = $sistema->DB_fetch_array("SELECT * FROM tb_clientes_clientes  WHERE id = {$_SESSION['cliente_id']}");
        } else {
            $meusdados = new stdClass ();
            $meusdados->num_rows = false;
            
            $campos = $sistema->DB_columns('tb_clientes_clientes');
            foreach ($campos as $campo) {
                $meusdados->rows[0][$campo] = "";
            }
        }
                
        $estados = $sistema->DB_fetch_array("SELECT * FROM tb_utils_estados ORDER BY estado");

        $gateway_pagamento = $sistema->DB_fetch_array("SELECT servico_pagamento_padrao FROM tb_admin_empresas WHERE stats = 1");
        $gateway_pagamento = $gateway_pagamento->rows[0]['servico_pagamento_padrao'];
                
	include "_inc_headers.php"; 
?>
</head>
<body id="<?php echo $pagina; ?>">
<?php include "_inc_header.php"; ?>
<div class="breadcrumbs">
	<div class="comp-grid-main-in">
		<span><a href="<?php echo $sistema->root_path; ?>">Home</a></span>
		<span><a href="<?php echo $sistema->seo_pages['meus-pedidos']['seo_url']; ?>">Meus Pedidos</a></span>
		<span><a href="<?php echo $sistema->seo_pages['checkout']['seo_url']; ?>">Checkout</a></span>
	</div>
</div>

<div class="comp-grid-main-in">
	<div class="main_titles"><h1>FINALIZAR COMPRA</h1></div>
	<div class="comp-grid-row relative">
                <?php if (!isset($_SESSION['cliente_logado'])) :?>
		<button type="button" class="checkout_login">LOGIN</button>
                <?php endif; ?>
		<button type="button" class="checkout_resume"><span>R$ 0,00</span> <i class="fa fa-caret-down"></i></button>
		<div id="checkout_resume">
			<div class="header">RESUMO DO PEDIDO</div>
			<button type="button" class="close">X</button>
			<div class="itens"> </div>
			<div class="total">
				<div class="row subtotal">Subtotal <span>R$ 22.420,00</span></div>
				<div class="row cupom">Cupom <span>R$ 0,00</span></div>
				<div class="row frete">Frete <span>R$ 0,00</span></div>
			</div>
		</div>
	</div>
    <form action="javascript:;" method="post" class="metodo_pagamento">
        <input type="hidden" name="id_seo" value="<?php echo $dynamic_id; ?>" />
        <input type="hidden" class="frete_nome" name="frete_nome" value="" />
        <input type="hidden" class="frete_prazo" name="frete_prazo" value="" />
        <input type="hidden" name="tipo_cliente" value="<?php if(isset($_SESSION['tipo_cliente'])) echo $_SESSION['tipo_cliente'] ?>">
	<div class="checkout_panel">
		<div class="etapa checkout_cadastro">
			<div class="titulo"><span>01</span> DADOS PESSOAIS</div>
				<div class="loggedin">
                                    <?php if ($meusdados->num_rows) :?>
                                        <input type="hidden" name="id_cliente" value="<?php echo $meusdados->rows[0]['id']; ?>" />
					Olá <strong><?php echo $meusdados->rows[0]['nome']; ?></strong>, preencha os campos com o endereço que você deseja receber o seu pedido.
                                    <?php endif; ?>
				</div>
                                
				<div class="loggedout" <?php if ($meusdados->num_rows) echo "style='display:none'";?>>
					<div class="comp-forms-grid-once comp-forms-item">
						<label>
							<span>TIPO:</span>
							<select name="pessoa" class="pessoa">
								<option value="1">Pessoa Física</option>
								<option value="2">Pessoa Jurídica</option>
							</select>
						</label>
					</div>
					<div class="pessoa_juridica">
						<div class="comp-forms-grid-once comp-forms-item">
							<label>
								<span>RAZÃO SOCIAL:</span>
								<input type="text" name="razao_social">
							</label>
						</div>
						<div class="comp-grid-row">
							<div class="comp-forms-grid-half-mobile comp-forms-item">
								<label>
									<span>CNPJ:</span>
									<input type="text" name="cnpj" class="mask-cnpj">
								</label>
							</div>
							<div class="comp-forms-grid-half-mobile comp-forms-item">
								<label>
									<span>INSC. ESTADUAL:</span>
									<input type="text" name="inscricao_estadual" maxlength="18">
								</label>
							</div>
						</div>
					</div>
					<div class="comp-forms-grid-once comp-forms-item">
						<label>
							<span class="pessoa_fisica">NOME:</span>
							<span class="pessoa_juridica">RESPONSÁVEL:</span>
							<input type="text" name="nome">
						</label>
					</div>
					<div class="comp-forms-grid-once comp-forms-item">
						<label>
							<span>CPF:</span>
							<input type="text" name="cpf" class="mask-cpf">
						</label>
					</div>
					<div class="comp-forms-grid-once comp-forms-item">
						<label>
							<span>E-MAIL:</span>
							<input type="text" name="email" value="<?php echo $meusdados->rows[0]['email'];?>">
						</label>
					</div>
					<div class="comp-forms-grid-once comp-forms-item">
						<label>
							<span>TELEFONE OU CELULAR:</span>
							<input type="text" name="telefone" class="mask-telefone">
						</label>
					</div>
					<div class="comp-grid-row">
						<div class="comp-forms-grid-half-mobile comp-forms-item">
							<label>
								<span>SENHA:</span>
								<input type="password" name="senha">
							</label>
						</div>
						<div class="comp-forms-grid-half-mobile comp-forms-item">
							<label>
								<span>CONFIRMAR SENHA:</span>
								<input type="password" name="senha2">
							</label>
						</div>
					</div>
				</div>
                                
                        
			
		</div>
            
		<div class="etapa checkout_frete">
                    
                    <div class="titulo"><span>02</span> MÉTODO DE ENVIO</div>
                        <div class="comp-forms-grid-once comp-forms-item">
                                <label>
                                        <span>CEP: <span><a href="http://www.buscacep.correios.com.br/sistemas/buscacep/buscaCepEndereco.cfm" target="_blank">(Não sei meu CEP)</a></span></span>
                                        <input type="text" name="cep" class="mask-cep" value="<?php if(isset($_SESSION['cep']) && $_SESSION['cep'] != "") echo $_SESSION['cep']; else echo $meusdados->rows[0]['cep'];?>">
                                </label>
                        </div>
                        <div class="comp-forms-grid-once comp-forms-item">
                                <label>
                                        <span>ENDEREÇO:</span>
                                        <input type="text" name="endereco" value="<?php echo $meusdados->rows[0]['endereco'];?>">
                                </label>
                        </div>
                        <div class="comp-grid-row">
                                <div class="comp-forms-grid-half-mobile comp-forms-item">
                                        <label>
                                                <span>NÚMERO:</span>
                                                <input type="text" name="numero" class="mask-numero" value="<?php echo $meusdados->rows[0]['numero'];?>">
                                        </label>
                                </div>
                                <div class="comp-forms-grid-half-mobile comp-forms-item">
                                        <label>
                                                <span>BAIRRO:</span>
                                                <input type="text" name="bairro" value="<?php echo $meusdados->rows[0]['bairro'];?>">
                                        </label>
                                </div>
                        </div>
                        <div class="comp-forms-grid-once comp-forms-item">
                                <label>
                                        <span>COMPLEMENTO:</span>
                                        <input type="text" name="complemento" value="<?php echo $meusdados->rows[0]['complemento'];?>">
                                </label>
                        </div>
                        <div class="comp-grid-row">
                                <div class="comp-forms-grid-half-mobile comp-forms-item">
                                        <label>
                                                <span>ESTADO:</span>
                                                <select name="id_estado" class="estado" <?php if($meusdados->rows[0]['id_cidade']) echo "data-auto-select-cidade='".utf8_decode($meusdados->rows[0]['id_cidade'])."'";?>>
                                                    <option value="">Selecione o Estado</option>
                                                    <?php if ($estados->num_rows) :?>
                                                    <?php foreach($estados->rows as $estado):?>
                                                    <option <?php if ($estado['id'] == $meusdados->rows[0]['id_estado']) echo 'selected'; ?> data-id="<?php echo $estado['id'];?>" data-uf="<?php echo $estado['uf'];?>" value="<?php echo $estado['id'];?>"><?php echo $estado['estado'];?></option>
                                                    <?php endforeach;?>
                                                    <?php endif;?>
                                                </select>
                                        </label>
                                </div>
                                <div class="comp-forms-grid-half-mobile comp-forms-item">
                                        <label>
                                                <span>CIDADE:</span>
                                                <select name="id_cidade" class="cidade">
                                                        <option value="">Selecione uma Cidade</option>
                                                </select>
                                        </label>
                                </div>
                        </div>


                        <div class="form_group">
                                <div class="form_frete checkout_forms">
                                    <p>Os prazos abaixo já incluem o tempo de produção</p>
                                        <div class="resultados">
                                                <?php if (isset($_SESSION['opt-fretes']) && $_SESSION['opt-fretes'] != "") echo $_SESSION['opt-fretes']; else echo 'Aguardando CEP para cálculo do frete.';?>
                                        </div>
                                </div>
                        </div>
                        
                        
		</div>
		<div class="etapa checkout_pagamentos">
			<div class="titulo"><span>03</span> FORMAS DE PAGAMENTO</div>
                                <!-- by Dev1 -->
                                <div class="cupom-de-desconto" data-tipo="<?php if (isset($_SESSION['cupom']['tipo'])) echo $_SESSION['cupom']['tipo']; ?>" data-valor="<?php if (isset($_SESSION['cupom']['valor'])) echo $_SESSION['cupom']['valor']; else echo 0; ?>"><?php if (isset($_SESSION['cupom']['mensagem'])  && $_SESSION['cupom']['mensagem'] != "") echo '<span class="remove_cupom">X</span>'.$_SESSION['cupom']['mensagem']; ?></div>
                                <!-- end by Dev1 -->
                                <!--
                                <div class="form_group">
                                    <form action="javascript:;" method="post" class="form_cupom">
                                            <div class="label">Cupom de Desconto</div>
                                            Insira o código de cupom, caso tenha:
                                            <div class="input">
                                                    <div class="field"><input type="text" name="cupom" id="cupom" value="<?php if (isset($_SESSION['cupom']['cupom'])) echo $_SESSION['cupom']['cupom']; ?>"></div>
                                                    <button type="button" class="clickformsubmit">ok</button>
                                            </div>
                                            <div class="cupom-de-desconto" data-tipo="<?php if (isset($_SESSION['cupom']['tipo'])) echo $_SESSION['cupom']['tipo']; ?>" data-valor="<?php if (isset($_SESSION['cupom']['valor'])) echo $_SESSION['cupom']['valor']; else echo 0; ?>"><?php if (isset($_SESSION['cupom']['mensagem'])) echo $_SESSION['cupom']['mensagem']; ?></div>
                                    </form>
                                </div>
                                -->
                
					<div class="option_pgto pgto_cielo_transparente">
						<label><input type="radio" name="metodo_pagamento" value="<?php echo $gateway_pagamento; ?>" checked> <span>Cartão de Crédito</span></label>
						<span class="valor">R$ 0,00</span>
						<div class="opcoes_parcelas">
							<div class="chamada">Ver opção de parcelas <i class="fa fa-angle-down"></i></div>
							<ul></ul>
						</div>
					</div>

				<?php /* ?>
				<div class="option_pgto pgto_cielo">
					<label><input type="radio" name="metodo_pagamento" value="cielo" checked> <span>Cartão de Crédito ou Débito</span></label>
					<span class="valor">R$ 0,00</span>
					<div class="opcoes_parcelas">
						<div class="chamada">Ver opção de parcelas <i class="fa fa-angle-down"></i></div>
						<ul></ul>
					</div>
				</div>
				<?php */ ?>
				
				<div class="option_pgto pgto_deposito">
                    <label><input type="radio" name="metodo_pagamento" value="deposito"> <span>Depósito Bancário / PIX</span></label>
					<span class="valor">R$ 0,00 (5% de Desconto)</span>
				</div>
				<div class="option_pgto pgto_boleto">
					<label><input type="radio" name="metodo_pagamento" value="boleto"> <span>Boleto Bancário</span></label>
					<span class="valor">R$ 0,00 (5% de Desconto)</span>
				</div>
				<div class="option_pgto pgto_pagseguro">
					<label><input type="radio" name="metodo_pagamento" value="pagseguro"> <span>Pagseguro UOL</span></label>
					<span class="valor">R$ 0,00</span>
					<div class="opcoes_parcelas">
						<div class="chamada">Ver opção de parcelas <i class="fa fa-angle-down"></i></div>
						<ul></ul>
					</div>
				</div>
				<div class="option_pgto pgto_pokerstars">
					<label><input type="radio" name="metodo_pagamento" value="pokerstars"> <span>Transferência via Pokerstars</span></label>
					<span class="valor">US$ 0,00 (5% de Desconto)</span>
					<div class="opcoes_parcelas">
						<div class="chamada">Ver instruções <i class="fa fa-angle-down"></i></div>
						<ul>
							<li>Após finalizar a compra, transfira o valor indicado para a conta do PokerStars <strong>kurtx</strong> e em seguida responda o e-mail que iremos enviar com os dados do pedido. Na resposta nos informe o seu NICK no PokerStars para que possamos confirmar seu pagamento.</li>
						</ul>
					</div>
				</div>
				<div class="opt_in">
					<label><input type="checkbox" checked="checked" name="newsletter" value="1"> Quero receber novidades e promoções</label>
					<br>
					<label><input type="checkbox" name="termos" value="1"> Eu aceito <a href="#" class="link-termos" target="_blank" style="text-decoration:underline;">Termos de Uso</a></label>
				</div>
				<button type="button" class="clickformsubmit">FINALIZAR COMPRA</button>
			
		</div>
            
	</div>
    </form>
</div>

<?php 
	include "_inc_footer.php";
	include "plugins/photoswipe/photoswipe.php";
	include "plugins/owl/owl.php";
?>

<script src="js/jquery.banners.js"></script>
<script src="js/jquery.easing.js"></script>

<!-- DEVICE FINGERPRINT - REDE ANTIFRAUD -->
	<script type="application/javascript" src="https://fingerprint.userede.com.br/b.js"></script> 
	<script> 
		function simility(){
			var sc = {
				"customer_id": "c54b99d0-894e-11e7-adc9-77887d29e284",
				"session_id": "<?php echo $_SESSION["seo_session"] ?>",
				"event_types": "<?php echo $pagina ?>",
				"zone": "br",
				"request_endpoint": "https://fingerprint.userede.com.br"
			};
		var ss = new SimilityScript(sc);
		ss.execute();
	};
	</script>	
<!-- DEVICE FINGERPRINT - REDE ANTIFRAUD -->


<script>
    $(document).ready(function () {
        cidade = "<?php echo $meusdados->rows[0]['id_cidade']; ?>";
        $(".estado").trigger("change");
    });
</script>
</body>
</html>