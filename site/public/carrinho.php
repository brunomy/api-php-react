<?php
	$pagina = "carrinho";

	// CURRENTPAGE DATA ---- //
	$currentpage = $sistema->seo_pages[$pagina];

	//RECALCULA O FRETE
	use classes\Frete;
	$frete = new Frete();
	$frete->calcFreteAction();

	//REPRODUZIR ORÇAMENTO NO CARRINHO
	if($sistema->getParameter("orcamento") != ""){
		$id_pedido = $sistema->getParameter("orcamento");
		$query = $sistema->DB_fetch_array("SELECT A.*, B.custo custo_atualizado, B.peso peso_atualizado, COALESCE(B.frete_embutido, 0) frete_embutido_atualizado FROM tb_carrinho_produtos_historico A INNER JOIN tb_produtos_produtos B ON A.id_produto = B.id WHERE A.id_pedido = ".$id_pedido);

		if($query->num_rows){

			//APAGA CARRINHO ATUAL
			//--------------------------------------------
				$get_carrinho = $sistema->DB_fetch_array("SELECT * FROM tb_carrinho_produtos_historico WHERE id_pedido IS NULL AND session = '".$_SESSION['seo_session']."'");
				if($get_carrinho->num_rows){
					foreach ($get_carrinho->rows as $produto) {
						$sistema->DB_delete("tb_carrinho_atributos_historico", "id_carrinho_produto_historico=".$produto['id']);
					}
				}
				$sistema->DB_delete("tb_carrinho_produtos_historico", " id_pedido IS NULL AND session='".$_SESSION['seo_session']."'");

			//ADICIONA OS PRODUTOS DO ORÇAMENTO NO CARRINHO
			//--------------------------------------------
		        foreach ($query->rows as $produto) {
		            $produto['session'] = $_SESSION['seo_session'];
		            $produto['data'] = date("Y-m-d H:i:s");
		            $produto['peso'] = $produto['peso_atualizado']; //atualiza peso do produto
		            $produto['frete_embutido'] = $produto['frete_embutido_atualizado']; //atualiza preço do frete embutido
		            $produto['custo'] = $produto['custo_atualizado'] + $produto['frete_embutido_atualizado']; //atualiza preço do produto
		            $valor_produto = $produto['custo'];
		            $produto['custo'] = number_format($produto['custo'],2,'.','');
		            
		        	$id_carrinho = $produto['id'];

		            unset($produto['id_pedido']);
		            unset($produto['id']);
		            unset($produto['custo_atualizado']);
		            unset($produto['frete_embutido_atualizado']);
		            unset($produto['peso_atualizado']);
		            unset($produto['valor_editado']);

		            foreach ($produto as $key => $value) {
		            	if($value != ""){
			                $fields[] = $key;
			                $values[] = "'$value'";
			            }
		            }
		            $insert = $sistema->DB_insert("tb_carrinho_produtos_historico", implode(',', $fields), implode(',', $values));
		            $insert_id = $insert->insert_id;
		            unset($fields);
		            unset($values);
		            
		            $custo_atributos = 0;

		            //ADICIONA OS ATRIBUTOS 
					//---------------------
			            $atributos = $sistema->DB_fetch_array("SELECT A.*, COALESCE(B.custo, 0) custo_atualizado FROM tb_carrinho_atributos_historico A INNER JOIN tb_produtos_atributos B ON A.id_atributo=B.id WHERE A.id_carrinho_produto_historico = ".$id_carrinho);
			            if($atributos->num_rows){
			            	foreach ($atributos->rows as $atributo) {
			            		$atributo['id_carrinho_produto_historico'] = $insert_id;
			            		$atributo['data'] = date("Y-m-d H:i:s");
			            		$atributo['custo'] = number_format($atributo['custo_atualizado'],2,'.',''); //atualiza preço do atributo
			            		$custo_atributos = $custo_atributos + $atributo['custo_atualizado'];
			            		unset($atributo['custo_atualizado']);
			            		foreach ($atributo as $key => $value) {
					                $fields[] = $key;
					                $values[] = "'$value'";
			            		}
			            		$insert = $sistema->DB_insert("tb_carrinho_atributos_historico", implode(',', $fields), implode(',', $values));
			            		unset($fields);
		            			unset($values);
			            	}
			            }

			        //ATUALIZA VALOR TOTAL DO PRODUTO
					//-------------------------------
			            $valor_produto = $valor_produto+$custo_atributos;
			           	$valor_produto = number_format($valor_produto,2,'.','');
			           	if($produto['valor_produto'] != $valor_produto) $sistema->DB_update("tb_carrinho_produtos_historico", "valor_produto = '".$valor_produto."' WHERE id = $insert_id");

		        }

		    $sistema->inserirRelatorio("Recolocou orçamento [" . $id_pedido . "] no carrinho");
	    }
	}

	include "_inc_headers.php";
?>
</head>
<body id="<?php echo $pagina; ?>">
<?php include "_inc_header.php"; ?>
<?php
        $cupom_valor = 0;
        if(isset($_SESSION['cupom_valor']) && $_SESSION['cupom_valor'] > 0) {
            if (!isset($_SESSION['cupom_tipo']))
                $_SESSION['cupom_tipo'] = 0;


            if ($_SESSION['cupom_tipo'] == 1) {
                $cupom_valor = (($carrinho_geral['valor']*$_SESSION['cupom_valor'])/100);
            } else{
                $cupom_valor =  $carrinho_geral['valor'] - $_SESSION['cupom_valor'];
            }

        }

		// Módulo orçamentos
		if ($produtos_carrinho->num_rows && isset($_COOKIE['adm_logado'])) {
				$id = $_COOKIE['adm_id'];

				$crmDB = $sistema->DB_fetch_array(" SELECT DISTINCT
                                                                id_cliente,
                                                                tb_crm_crm.id,
                                                                tb_clientes_clientes.nome,
                                                                tb_clientes_clientes.cpf,
                                                                tb_clientes_clientes.cnpj,
                                                                tb_crm_crm.id_cliente,
                                                                tb_crm_crm.ultima_atualizacao
                                                            FROM
                                                                tb_crm_crm
                                                                    LEFT JOIN
                                                                tb_clientes_clientes ON tb_clientes_clientes.id = tb_crm_crm.id_cliente
                                                            WHERE
                                                                tb_crm_crm.id IN (SELECT 
                                                                        MAX(id) AS id
                                                                    FROM
                                                                        tb_crm_crm
                                                                    WHERE
                                                                        id_user = $id
                                                                            AND ultima_atualizacao >= NOW() - INTERVAL 5 DAY
                                                                            AND DATE(tb_crm_crm.data) <= DATE(NOW())
                                                                            AND possui_orcamento IS NOT NULL
                                                                            AND finalizado IS NULL
                                                                    GROUP BY id_cliente)
                                                            ORDER BY ultima_atualizacao DESC "
				);
				if ($crmDB->num_rows) {
					$atendimentos = $crmDB->rows;
				}
		}
?>
<div class="breadcrumbs">
	<div class="comp-grid-main-in">
		<span><a href="<?php echo $sistema->root_path; ?>">Home</a></span>
		<span><a href="">Meus Pedidos</a></span>
	</div>
</div>

<div class="comp-grid-main-in">
	<div class="main_titles"><h1>CARRINHO</h1></div>
	<div class="main_carrinho mycart">
		<div class="itens">
                    <?php if ($produtos_carrinho->num_rows) : ?>
                    <?php foreach ($produtos_carrinho->rows as $produto) : ?>
                    <?php $carrinho_produto = $product->getCarrinhoInfoByProduto($produto['id']); ?>
			<div class="item" data-id="<?php echo $produto['id']; ?>">
				<div class="table">
					<div class="icone"><img src="<?php echo $sistema->getImageFileSized($produto['icone'],160,160); ?>" alt="<?php echo $produto['nome_produto']; ?>"></div>
					<div class="nome"><?php echo $produto['nome_produto']; ?></div>
					<div class="quantidade"><input type="text" name="quantidade" value="<?php echo $produto['quantidade']; ?>" data-quantidade-minima="<?php echo $produto['qtd_minima']; ?>" data-id="<?php echo $produto['id']; ?>"></div>
					<div class="valor_unitario">
						<span class="sem_desconto"><span>R$ 0,00</span></span>
						<span class="valor_final">R$ 0,00</span>
					</div>
					<div class="subtotal">R$ 0,00</div>
					<div class="actions">
						<button type="button" class="detalhes">Ver Detalhes <i class="fa fa-angle-down"></i></button>
						<a href="<?php echo $produto['seo_url']; ?>/edit/<?php echo $produto['id']; ?>" class="editar"><i class="fa fa-pencil-square-o"></i></a>
						<button type="button" class="excluir" data-id="<?php echo $produto['id']; ?>"><i class="fa fa-trash-o"></i></button>
					</div>
				</div>
				<div class="configuracoes">
					<div class="label">Configurações:</div>
					<div class="dados">
                                                <?php $atributos =  $product->getAtributosInfoByProduto($produto['id']); ?>
                                                <?php if ($atributos->num_rows) :?>
                                                <?php foreach ($atributos->rows as $atributo) :?>
						<span><strong><?php echo $atributo['nome_conjunto']; ?></strong> <?php echo $atributo['nome_atributo']; ?> <?php if ($atributo['texto'] != "") echo "[{$atributo['texto']}]";?> <?php if ($atributo['cor'] != "") echo "<i style='display:inline-block;width:100px;padding:0 10px;color:#fff;background-color:{$atributo['cor']}'><i style='color: {$atributo['cor']};-webkit-filter: invert(100%);filter: invert(100%);'>{$atributo['cor']}</i></i>";?> <?php if ($atributo['arquivo'] != "") echo "[{$atributo['valor']}]";?></span>
                                                <?php endforeach; ?>
                                                <?php endif; ?>
					</div>
				</div>
			</div>
                    <?php endforeach; ?>
                    <?php endif; ?>
		</div>
                <div class="vazio">
                    Seu Carrinho Está Vazio.
                </div>
	</div>
	<div class="checkout_carrinho">
		<form action="javascript:;" method="post" class="form_frete checkout_forms">
			<div class="label">Prazo de Entrega</div>
			<a href="http://www.buscacep.correios.com.br/sistemas/buscacep/buscaCepEndereco.cfm" target="_blank">não sei meu CEP</a>
			<div class="input">
				<div class="field"><input type="text" name="cep" id="cep" class="mask-cep" value="<?php if(isset($_SESSION['cep']) && $_SESSION['cep'] != "") echo $_SESSION['cep'];?>"></div>
				<button type="button" class="clickformsubmit">ok</button>
			</div>
                                <p>*prazo após aprovação de arte e sujeito a confirmação na transportadora</p>
			<div class="resultados">
				<?php if (isset($_SESSION['opt-fretes']) && $_SESSION['opt-fretes'] != "") echo $_SESSION['opt-fretes'];?>
			</div>
		</form>
		<div class="totals">
			<div class="col1">
				<div class="total_box">
					<div class="soma subtotal">Subtotal <span>R$ 0,00</span></div>
					<div class="soma descontos">Descontos <i class="fa fa-info-circle" aria-hidden="true"></i> <span>R$ 0,00</span></div>
					<div class="soma cupom">Cupom <span>R$ 0,00</span></div>
					<div class="soma frete">Frete <span>R$ 0,00</span></div>
					<div class="total_geral">Total Geral <span>R$ 0,00</span></div>
				</div>
				<form action="javascript:;" method="post" class="form_cupom checkout_forms">
					<div class="label">Cupom de Desconto</div>
					Insira o código de cupom, caso tenha: <span class="open_hidden"><i class="fa fa-pencil-square-o"></i></span>
					<div class="hidden">
						<div class="input">
							<div class="field"><input type="text" name="cupom" id="cupom" value="<?php if (isset($_SESSION['cupom']['cupom'])) echo $_SESSION['cupom']['cupom']; ?>"></div>
							<button type="button" class="clickformsubmit">ok</button>
						</div>
						<div class="cupom-de-desconto" data-tipo="<?php if (isset($_SESSION['cupom']['tipo'])) echo $_SESSION['cupom']['tipo']; ?>" data-valor="<?php if (isset($_SESSION['cupom']['valor'])) echo $_SESSION['cupom']['valor']; else echo 0; ?>"><?php if (isset($_SESSION['cupom']['mensagem']) && $_SESSION['cupom']['mensagem'] != "") echo '<span class="remove_cupom">X</span>'.$_SESSION['cupom']['mensagem']; ?></div>
					</div>
				</form>

				<?php if (isset($atendimentos)) :?>
					<form action="javascript:;" method="post" class="form_orcamento checkout_forms metodo_pagamento" <?php if (!isset($atendimentos)) echo "style='display:none'";?>>
						<input type="hidden" name="id_seo" value="<?php echo $dynamic_id; ?>" />
						<input type="hidden" class="frete_nome" name="frete_nome" value="" />
						<input type="hidden" class="frete_prazo" name="frete_prazo" value="" />
						<input type="hidden" class="frete_valor" name="frete_valor" value="" />

						<div class="comp-forms-item label">Orçamento</div>
						<div class="comp-forms-item">
							<label>
								<span>Atendimento (CRM):</span>
								<select name="orc_id_crm">
									<option value="">Selecione o atendimento</option>
									<?php foreach($atendimentos as $atendimento):?>
										<option value="<?php echo $atendimento['id'] ?>"><?php echo '#' . $atendimento['id'] . ' - ' . $atendimento['nome'] . ' - CPF/CNPJ: ' . $atendimento['cpf'] . ' ' . $atendimento['cnpj'] ?></option>
									<?php endforeach; ?>
								</select>
							</label>
						</div>
						<div class="comp-forms-item">
							<br>
							<label>
								<span>Tipo de cliente:</span>
								<select name="tipo_cliente">
									<option value="Não Respondeu">Não responder</option>
									<option value="Clube de Poker">Clube de Poker</option>
									<option value="Home Game">Home Game</option>
									<option value="Cassino Temático">Cassino Temático</option>
									<option value="Troféus">Troféus</option>
								</select>
							</label>
						</div>

						<div class="comp-forms-item">
							<button type="button" class="clickformsubmit">Gerar orçamento</button>
						</div>
					</form>
				<?php endif; ?>

			</div>
			<div class="col2">
				<div class="options parcelamento">
					ATÉ 10X SEM JUROS DE
					<span><strong>R$ <?php echo $sistema->formataMoedaShow(($carrinho_geral['valor']-$cupom_valor)/10); ?></strong> OU R$ <?php echo $sistema->formataMoedaShow($carrinho_geral['valor'] - $cupom_valor); ?></span>
				</div>
				<div class="options avista">
					5% DE DESCONTO À VISTA
					<span>R$ <?php echo $sistema->formataMoedaShow(($carrinho_geral['valor'] - $cupom_valor) * 0.95); ?></span>
				</div>
				<div class="options pokerstars">
					POKERSTARS
					<span>US$ <?php echo $sistema->formataMoedaShow(str_replace(",", ".",str_replace(".", "", $carrinho_geral['valor']-$cupom_valor)) * 0.95 / $sistema->cotacao_dollar); ?></span>
				</div>
				<a href="checkout" class="bt_finalizar">FINALIZAR COMPRA</a>
			</div>
		</div>
	</div>
</div>

<?php
	include "_inc_footer.php";
	include "plugins/photoswipe/photoswipe.php";
	include "plugins/owl/owl.php";
?>

<script src="js/jquery.banners.js"></script>
<script src="js/jquery.easing.js"></script>
</body>
</html>
