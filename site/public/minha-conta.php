<?php

	//se tiver usando http, transfere para https enviando o id da sessão atual http para https
	/*
	if($_SERVER['HTTP_HOST'] == "www.realpoker.com.br" || $_SERVER['HTTP_HOST'] == "realpoker.com.br"){
		if (empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'on') {
		    header("location:https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']."/?via=".session_id());
		    exit();
		}
	}
	*/
	
	$pagina = "minha-conta";
        
        if (!isset($_SESSION['cliente_logado'])) {
            //header ("Location: " . $sistema->root_path . "home/?login");
            header ("Location: " . $sistema->root_path . "login");
            exit();
        }
        
        // CURRENTPAGE DATA ---- //
        $currentpage = $sistema->seo_pages[$pagina];
        // --------------------------------------------- //
        
        
        $pedidos = $product->getPedidosByCliente($_SESSION['cliente_id']);

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
		<span><a href="<?php echo $sistema->seo_pages['minha-conta']['seo_url']; ?>">Minha Conta</a></span>
	</div>
</div>

<div class="comp-grid-main-in">
	<div class="main_titles"><h1>MINHA CONTA</h1></div>
	<div class="comp-grid-row">
		<div class="comp-grid-aside-left">
			<?php include "_inc_menu_conta.php"; ?>
		</div>
		<div class="comp-grid-aside-main">
                    <?php if ($pedidos->num_rows) :?>
                    	
			<div class="tabela_pedidos">
				<div class="thead">
					<div class="tcell numero">Nº do Pedido</div>
					<div class="tcell data">Data da Compra</div>
					<?php /* ?><div class="tcell entrega">Previsão de Entrega</div> <?php */ ?>
					<div class="tcell status">Status do Pedido</div>
					<div class="tcell valor">Valor Total</div>
					<div class="tcell detalhes"></div>
				</div>
				<div class="tbody">
                                    <?php foreach ($pedidos->rows as $pedido) : ?>
                                	<?php 
			                    	    
			                    	    //se pagamento estiver definido como cartão de crédito, pegar qual serviço está configurado atualmente.
			        					if(in_array($pedido['metodo_pagamento'], ['rede_transparente', 'cielo_transparente'])){
			        						if($pedido['metodo_pagamento'] != $gateway_pagamento){
			        							$pedido['metodo_pagamento'] = $gateway_pagamento;
			        							$sistema->DB_update("tb_pedidos_pedidos", "metodo_pagamento = '".$gateway_pagamento."' WHERE id = ".$pedido['id']);
			        						}
			        					}
			        					
			        					$rastreios = $product->getRastreiosByPedido($pedido['id']);
			        					$produtos = $product->getCartProductsByPedido($pedido['id']);
			        					$endereco = $product->getEnderecoByPedido($pedido['id']);
                                        $valor_total = 0;
                                        $cupom = 0;
                                        
                                        $valor_total = $pedido['subtotal'] + $pedido['valor_frete'] - $pedido['descontos'];
                                        
                                        if ($pedido['valor_cupom']) {
                                            if ($pedido['tipo_cupom'] != 1)
                                                $cupom = $pedido['valor_cupom'];
                                            else
                                                $cupom = (($valor_total*$pedido['valor_cupom'])/100);
                                        }
                                        
                                        $valor_total = $valor_total - $cupom;
                                        
                                        if ($pedido['avista'] == 1)
                                            $valor_total = $valor_total - (($valor_total * 5) /100);
                                        
                                    ?>
					<div class="pedido">
						<div class="trow">
							<div class="tcell numero">
								<div class="mobile_caption">Nº do Pedido</div>
								<div class="cell"><?php echo $pedido['id'];?></div>
							</div>
							<div class="tcell data">
								<div class="mobile_caption">Data da Compra</div>
								<div class="cell"><?php echo $pedido['registro'];?></div>
							</div>
							<?php /* ?>
							<div class="tcell entrega">
								<div class="mobile_caption">Previsão de Entrega</div>
								<div class="cell"><?php echo $pedido['prazo_entrega'];?></div>
							</div>
							<?php */ ?>
							<div class="tcell status">
								<div class="mobile_caption">Status do Pedido</div>
									<div class="cell"><?php echo $pedido['status'];?> 
										<?php if($pedido['mostrar_botao_pagar'] == 1){ ?>
											<a href="#" data-id-pedido="<?php echo $pedido['id'];?>" data-id-cliente="<?php echo $pedido['id_cliente'];?>" data-metodo="<?php echo $pedido['metodo_pagamento'];?>" class="bt_pagar">PAGAR</a>
										<?php }else if($pedido['id_status'] == 5){ ?>
											<a href="#" class="bt_anexar" data-id-pedido="<?php echo $pedido['id'];?>">ANEXAR DOCUMENTOS</a>
										<?php } ?>
									</div>
							</div>
							<div class="tcell valor">
								<div class="mobile_caption">Valor Total</div>
								<div class="cell">R$ <?php echo $sistema->formataMoedaShow($valor_total);?></div>
							</div>
							<div class="tcell detalhes">
								<div class="cell"><button type="button" class="detalhes">Ver Detalhes <i class="fa fa-angle-down"></i></button></div>
							</div>
						</div>
						<div class="box_detalhes">
							<div class="itens">
                                                            <?php if ($rastreios->num_rows) :?>
								<div class="item rastreio">
									<div class="dados">
										<div class="descricao">
                                                                                    <?php foreach ($rastreios->rows as $rastreio)  : ?>
											<span><strong><a href="<?php echo $rastreio['link'];?>" target="_blank">Clique aqui para rastrear o envio</a></strong> <?php echo $rastreio['descricao'];?></span>
                                                                                    <?php endforeach; ?>
										</div>
									</div>
								</div>
                                                            <?php endif; ?>
                                                            <?php if ($produtos->num_rows) :?>
                                                            <?php foreach ($produtos->rows as $produto) : ?>
                                                            <?php $atributos =  $product->getAtributosInfoByProduto($produto['id']); ?>
								<div class="item">
									<div class="dados">
										<div class="nome"><?php echo $produto['nome_produto'];?></div>
										<div class="quantidade"><strong>Qtde.:</strong> <?php echo $produto['quantidade'];?></div>
                                                                                <?php if ($produto['desconto']):?>
                                                                                <div class="valor">De <span>R$ <?php echo $sistema->formataMoedaShow($produto['valor_produto']);?></span> por R$ <?php echo $sistema->formataMoedaShow($produto['valor_produto'] - $produto['desconto']);?></div>
                                                                                <?php else:?>
										<div class="valor">R$ <?php echo $sistema->formataMoedaShow($produto['valor_produto']);?></div>
                                                                                <?php endif;?>
                                                                                <?php if ($produto['desconto']) :?>
                                                                                <div class="desconto"><strong>Descrição de desconto: </strong><?php echo $produto['descricao_desconto'];?></div>
                                                                                <?php endif;?>
									</div>
									<div class="descricao">
                                                                                <?php if ($atributos->num_rows) :?>
                                                                                <?php foreach ($atributos->rows as $atributo) :?>
                                                                                <span><strong><?php echo $atributo['nome_conjunto']; ?></strong> <?php echo $atributo['nome_atributo']; ?> <?php if ($atributo['texto'] != "") echo "[{$atributo['texto']}]";?> <?php if ($atributo['cor'] != "") echo "<i style='display:inline-block;width:100px;padding:0 10px;color:#fff;background-color:{$atributo['cor']}'><i style='color: {$atributo['cor']};-webkit-filter: invert(100%);filter: invert(100%);'>{$atributo['cor']}</i></i>";?> <?php if ($atributo['arquivo'] != "") echo "<a style='font-size: 12px' target='_blank' href='".$sistema->root_path."uploads/".$atributo['arquivo']."'>[{$atributo['valor']}]</a>";?></span>
                                                                                <?php endforeach; ?>
                                                                                <?php endif; ?>
									</div>
								</div>
                                                            <?php endforeach;?>
                                                            <?php endif;?>
								<div class="item cupom">
									<div class="dados">
                                                                            <?php if ($pedido['mensagem_cupom'] != "") :?>
										<div class="nome"><?php echo $pedido['mensagem_cupom']; ?></div>
                                                                            <?php endif; ?>
                                                                            <?php if ($pedido['avista'] == 1) :?>
										<div class="nome">Você recebeu 5% de desconto por comprar à vista </div>
                                                                            <?php endif; ?>
									</div>
								</div>
                                                            <?php if ($rastreios->num_rows) :?>
								<div class="item rastreio">
									<div class="dados">
										<div class="descricao">
                                                                                    <?php foreach ($rastreios->rows as $rastreio)  : ?>
											<span><strong><a href="<?php echo $rastreio['link'];?>" target="_blank">Clique aqui para rastrear o envio</a></strong> <?php echo $rastreio['descricao'];?></span>
                                                                                    <?php endforeach; ?>
										</div>
									</div>
								</div>
                                                            <?php endif; ?>
							</div>
							<div class="resumo">
								<div class="box">
									<div class="label">Forma de pagamento:</div>
                                                                        <?php
                                                                            if ($pedido['metodo_pagamento'] == 'deposito')
                                                                                $modo_pagamento = "Depósito Bancário";
                                                                            else if ($pedido['metodo_pagamento'] == 'boleto')
                                                                                $modo_pagamento = "Boleto Bancário";
                                                                            else if ($pedido['metodo_pagamento'] == 'cielo')
                                                                                $modo_pagamento = "Cartão de Crédito ou Débito";
                                                                            else if ($pedido['metodo_pagamento'] == 'rede_transparente')
                                                                                $modo_pagamento = "Cartão de Crédito";
                                                                            else if ($pedido['metodo_pagamento'] == 'pagseguro')
                                                                                $modo_pagamento = "Pagseguro UOL";
                                                                            else if ($pedido['metodo_pagamento'] == 'pokerstars')
                                                                                $modo_pagamento = "Transferência via Pokerstars";
                                                                        ?>
									<?php echo $modo_pagamento; ?>
								</div>
                                                            <?php if ($endereco->num_rows) :?>
								<div class="box">
									<div class="label">Endereço:</div>
									<?php echo $endereco->rows[0]['endereco'];?>, <?php echo $endereco->rows[0]['numero'];?>. <?php echo $endereco->rows[0]['bairro'];?> <?php echo $endereco->rows[0]['complemento'];?> <br>
									<?php echo $endereco->rows[0]['cidade'];?>, <?php echo $endereco->rows[0]['uf'];?>, CEP: <?php echo $endereco->rows[0]['cep'];?>
								</div>
                                                            <?php endif; ?>
								<div class="box totals">
									<div class="subtotals"><span>Subtotal</span> <?php echo $sistema->formataMoedaShow($pedido['subtotal']); ?></div>
                                                                        <?php if ($pedido['descontos']) :?>
                                                                        <?php 
                                                                        
                                                                            $valor_avista = 0; 
                                                                            if ($pedido['avista'] == 1) { 
                                                                                $valor_avista = (($pedido['subtotal'] - $pedido['descontos'] - $cupom + $pedido['valor_frete'])*5)/100; 
                                                                                $pedido['descontos'] = $pedido['descontos'] + $valor_avista;
                                                                            }
                                                                        
                                                                        ?>
									<div class="subtotals"><span>Descontos</span> -<?php echo $sistema->formataMoedaShow($pedido['descontos']); ?></div>
                                                                        <?php endif;?>
                                                                        <?php if ($cupom) :?>
									<div class="subtotals"><span>Cupom</span> -<?php echo $sistema->formataMoedaShow($cupom);?></div>
                                                                        <?php endif;?>
									<div class="subtotals"><span>Frete</span>
										<?php 
											if($pedido['frete'] == 'A Consultar'){
												echo 'A Consultar';
											}else{
												
												if ($pedido['valor_frete']) 
													echo $sistema->formataMoedaShow($pedido['valor_frete']); 
												else 
													echo 'Frete Grátis'; 
											}
										?>
									</div>
									<div class="subtotals total"><span>Total</span> <?php echo $sistema->formataMoedaShow($valor_total); ?></div>
								</div>
							</div>
						</div>
					</div>
                                    <?php endforeach;?>
				</div>
                            
			</div>
                    <?php else:?>
                    Você ainda não possui pedidos realizados em nosso site.
                    <?php endif;?>
		</div>
	</div>
</div>

<?php include "_inc_footer.php"; ?>

<script src="js/jquery.banners.js"></script>
<script src="js/jquery.easing.js"></script>
</body>
</html>