<?php

	$pagina = "pagamento";
        
        // CURRENTPAGE DATA ---- //
        $currentpage = $sistema->seo_pages[$pagina];
        // --------------------------------------------- //
        
        $formulario = $sistema->formularioObjeto($_POST);
        
        
        if (!isset($formulario->pedido) && !isset($formulario->metodo)) {
            header("Location: $sistema->root_path");
            exit();
        }
        
        $pedido = false;
        $pedidos = $sistema->DB_fetch_array("SELECT * FROM tb_pedidos_pedidos WHERE id = $formulario->pedido");
        if ($pedidos->num_rows)
            $pedido = $pedidos->rows[0];

        $endereco = false;
        $enderecos = $sistema->DB_fetch_array("SELECT A.*, C.cidade, E.estado, E.uf FROM tb_pedidos_enderecos A INNER JOIN tb_utils_cidades C ON C.id = A.id_cidade INNER JOIN tb_utils_estados E ON E.id = C.id_estado WHERE A.id_pedido = $formulario->pedido");
        if ($enderecos->num_rows)
            $endereco = $enderecos->rows[0];
        
        $cliente = false;
        $clientes = $sistema->DB_fetch_array("SELECT * FROM tb_clientes_clientes WHERE id = {$_SESSION['cliente_id']}");
        if ($clientes->num_rows)
            $cliente = $clientes->rows[0];
        
        $produtos = $product->getCartProductsByPedido($formulario->pedido);
        
        
        $mostra = "";
        $mensagem = "";
        
        if ($formulario->metodo == "cielo") {

            include_once "../payments/cielo/cielo_checkout.php";
            $objCheckoutCielo = new CieloCheckout();

            //adicionar produtos
            
            if ($produtos->num_rows) {
                foreach ($produtos->rows as $produto) {
                    $objCheckoutCielo->adicionar(array(
                        "name" => $produto['nome_produto'], // nome do produto
                        "description" => $produto['nome_produto'], // descrição do produto
                        "unitprice" => preg_replace('/[^0-9]/', '', (string) ((float) $produto['valor_produto']) * 100), // valor do produto
                        "quantity" => $produto['quantidade'], // qtde do produto
                        "type" => '1', //tipo do produto: material físico
                        "code" => '', // SKU do produto (não existe id para produto)
                        "weight" => $produto['peso'], //$produtos[$i]->peso,
                        "zipcode" => '74115060' // CEP de origem do produto
                    ));
                }
            }

            //dados do vendedor, site
            $dadosPedido = array(
                "merchant_id" => "f2f8f2d5-584d-45ba-a151-8d1a25c82ae4",
                "order_number" => str_pad(str_pad($formulario->pedido, 10, '0', STR_PAD_LEFT), 32, md5((int) $formulario->pedido + (int) $formulario->id_cliente), STR_PAD_LEFT), //$pedido['id'],
                "soft_descriptor" => "RealPoker",
                );
            
            if (!$pedido['valor_frete']) {
                $pedido['valor_frete'] = 0;
                $dadosPedido['shipping_type'] = 3;
            } else  {
                $dadosPedido['shipping_type'] = 2;
            }
            
            $objCheckoutCielo->dadosPedido($dadosPedido);
            

            //descontos
            $valor_final = $pedido['subtotal'] + $pedido['valor_frete'] - $pedido['descontos'];
                $valor_cupom = 0;
                if ($pedido['tipo_cupom'] != 1)
                    $valor_cupom = $pedido['valor_cupom'];
                else
                    $valor_cupom = (($valor_final * $pedido['valor_cupom']) / 100);
                
            if ($pedido['descontos'] > 0 OR $valor_cupom > 0) {
                
                $descontoPedido = $pedido['descontos'] + $valor_cupom;
                $dadosDesconto = array(
                    "discount_type" => '1',
                    "discount_value" => $descontoPedido * 100);
                $objCheckoutCielo->dadosDesconto($dadosDesconto);
            }
            
            
            //frete
            $frete = array(
                "shipping_1_name" => $pedido['frete'],#'Transportadora',
                "shipping_1_price" => $pedido['valor_frete'] * 100,
                "shipping_zipcode" => $product->numeros($endereco['cep']),
                "shipping_address_name" => $endereco['endereco'] . ' - ' . $endereco['complemento'],
                "shipping_address_number" => $endereco['numero'],
                "shipping_address_complement" => '',
                "shipping_address_district" => $endereco['bairro'],
                "shipping_address_city" => $endereco['cidade'],
                "shipping_address_state" => $endereco['uf'],
                );
            $objCheckoutCielo->frete($frete);
            
            //dados do cliente
            $consumidor = array(
                "customer_name" => $cliente['nome'],
                "customer_identity" => $product->numeros($cliente['pessoa']==2?$cliente['cnpj']:$cliente['cpf']),
                "customer_email" => $cliente['email'],
                "customer_phone" => $product->numeros($cliente['telefone'])
                );
            $objCheckoutCielo->consumidor($consumidor);
            
            $antifraude = array(
                "Antifraud_enabled" => 'true'
                );

            $objCheckoutCielo->antifraude($antifraude);
            
            $mostra = $objCheckoutCielo->mostra();
            $mensagem = '
            <p style="font-size:18px; text-align:center;"><strong>Você será redirecionado ao site da Cielo para efetuar o pagamento. </strong><br>*Lembre-se de escolher a quantidade de parcelas junto aos dados do cartão.<br><br><a href="javascript:;" onclick="irCielo();">Clique aqui</a> ou aguarde <span id="tempo" style="font-weight:bold;">10</span> segundos.<p>
            <p style="text-align:center;"><img src="img/icon_cielo_redirecionamento.jpg"></p>
            ';
           
            
        } else if ($formulario->metodo == "boleto") {

            require_once('../vendor/autoload.php');

            $companyCode           = 'J0327462590001720000032666';
            $encrytionKey          = 'REALACES10304319';
            $orderNumber           = $formulario->pedido;
            $amount                = number_format($pedido['valor_final'], 2, ',', '');
            $draweeName            = $product->numeros($cliente['pessoa']==2?$cliente['razao_social']:$cliente['nome']);
            $draweeDocTypeCode     = $product->numeros($cliente['pessoa']==2?'02':'01');
            $draweeDocNumber       = $product->numeros($cliente['pessoa']==2?$cliente['cnpj']:$cliente['cpf']);
            $draweeAddress         = $endereco['endereco'] . ' - ' . $endereco['complemento'];
            $draweeAddressDistrict = $endereco['bairro'];
            $draweeAddressCity     = $endereco['cidade'];
            $draweeAddressState    = $endereco['uf'];
            $draweeAddressZipCode  = $product->numeros($endereco['cep']);
            $bankSlipDueDate       = date('dmY', strtotime($pedido['data'].'+2 day'));
            $bankSlipNoteLine1     = 'Sr. Caixa,';
            $bankSlipNoteLine2     = 'Não receber após o vencimento.';
            $bankSlipNoteLine3     = 'Obrigado.';

            $itaucripto = new \Itaucripto\Itaucripto();
            $itaucripto->setCompanyCode          ($companyCode);
            $itaucripto->setEncryptionKey        ($encrytionKey);
            $itaucripto->setOrderNumber          ($orderNumber);
            $itaucripto->setAmount               ($amount);
            $itaucripto->setDraweeName           ($draweeName);
            $itaucripto->setDraweeDocTypeCode    ($draweeDocTypeCode);
            $itaucripto->setDraweeDocNumber      ($draweeDocNumber);
            $itaucripto->setDraweeAddress        ($draweeAddress);
            $itaucripto->setDraweeAddressDistrict($draweeAddressDistrict);
            $itaucripto->setDraweeAddressCity    ($draweeAddressCity);
            $itaucripto->setDraweeAddressState   ($draweeAddressState);
            $itaucripto->setDraweeAddressZipCode ($draweeAddressZipCode);
            $itaucripto->setBankSlipDueDate      ($bankSlipDueDate);
            $itaucripto->setBankSlipNoteLine1    ($bankSlipNoteLine1);
            $itaucripto->setBankSlipNoteLine2    ($bankSlipNoteLine2);
            $itaucripto->setBankSlipNoteLine3    ($bankSlipNoteLine3);

            $itaucripto->setCallbackUrl('https://'.$_SERVER['HTTP_HOST'].'/minha-conta');

            $dataGenerate = $itaucripto->generateData();
            
            $mensagem = '

            <form action="https://shopline.itau.com.br/shopline/shopline.aspx" method="post" name="form" onsubmit="itauShoplinePopup()" target="SHOPLINE">
                <input type="hidden" name="DC" value="'.$dataGenerate.'" />
                <p><span style="font-size:18px;font-weight:bold;">Parabéns, seu pedido foi concluído!</span><br><span style="font-size:16px;">Já te mandamos as informações por e-mail, agora siga as instruções abaixo para pagamento.</span></p>
                <p style="font-size:16px;">Boleto pagável em qualquer banco!</p>
                <p><input type="submit" name="Shopline" value="CLIQUE AQUI PARA ABRIR O BOLETO" style="display:inline-block;padding:5px;background-color:#6aa84f;color:#fff;font-size:18px;font-weight:bold;cursor:pointer;" /> 
                </p>
                <p>Valor: R$ ' . $sistema->formataMoedaShow($pedido['valor_final']) .' </p>
            </form>


            <script src="https://apis.google.com/js/platform.js?onload=renderOptIn" async defer></script>

                <script>
                  window.renderOptIn = function() {
                    window.gapi.load(\'surveyoptin\', function() {
                      window.gapi.surveyoptin.render(
                        {
                          // REQUIRED FIELDS
                          "merchant_id": 112316588,
                          "order_id": "'.$pedido['id'].'",
                          "email": "'.$cliente['email'].'",
                          "delivery_country": "BR",
                          "estimated_delivery_date": "20'.date('y-m-d', strtotime('+1 week')).'",
                          
                        });
                    });
                  }
                </script>
                <!-- BEGIN GCR Language Code -->
                <script>
                  window.___gcfg = {
                    lang: \'pt_BR\'
                  };
                </script>
                <!-- END GCR Language Code -->
            ';
            
        } else if ($formulario->metodo == "pagseguro") {
            
            include_once "../payments/pagseguro/pgs.php";
            $objPagSeguro = new pgs();

            
            if ($produtos->num_rows) {
                $i = 0;
                foreach ($produtos->rows as $produto) {
                    $objPagSeguro->adicionar(array(
                        "id" => $pedido['id'] . '_' . $i,
                        "quantidade" => $produto['quantidade'],
                        "valor" => $produto['valor_produto'],
                        "descricao" => $produto['nome_produto']
                    ));
                    $i++;
                }
            }

            $aux = $pedido['id'] . "|";


            // dados do vendedor, site
            $vendedor = array("email_cobranca" => "financeiro@realpoker.com.br", "tipo" => "CP", "moeda" => "BRL", "encoding" => "utf-8", "ref_transacao" => $aux);
            $objPagSeguro->pgs($vendedor);

            // dados do cliente

            $client = array("nome" => $cliente['nome'],
                "cep" => $product->numeros($endereco['cep']),
                "end" => $endereco['endereco'],
                "compl" => $endereco['complemento'],
                "bairro" => $endereco['bairro'],
                "cidade" => $endereco['cidade'],
                "uf" => $endereco['uf'],
                "pais" => "Brasil",
                "email" => $cliente['email'],
                "num" => $endereco['numero'],
                "ddd" => '',
                "tel" => '');

            $objPagSeguro->cliente($client);
            
            $frete['valor'] = $pedido['valor_frete'];
            
            $valor_final = $pedido['subtotal'] + $pedido['valor_frete'] - $pedido['descontos'];
            $valor_cupom = 0;
            if ($pedido['tipo_cupom'] != 1)
                $valor_cupom = $pedido['valor_cupom'];
            else
                $valor_cupom = (($valor_final * $pedido['valor_cupom']) / 100);
            
            if ($pedido['descontos'])
                $valor_cupom = $valor_cupom + $pedido['descontos'];
            
            if ($valor_cupom > 0)
                $valor_cupom = $valor_cupom * (-1);
            
            //extraAmount
            
            $mostra = $objPagSeguro->mostra(array(),$frete,$valor_cupom);
            //$mensagem = 'Você será redirecionado ao site do Pagseguro para efetuar o pagamento. <a href="javascript:;" onclick="irPagseguro();">Clique aqui</a> ou aguarde <span id="tempo" style="font-weight:bold;">10</span> segundos.';

            $mensagem = '
            <p style="font-size:18px; text-align:center;"><strong>Você será redirecionado ao site do Pagseguro para efetuar o pagamento.</strong><br><a href="javascript:;" onclick="irPagseguro();">Clique aqui</a> ou aguarde <span id="tempo" style="font-weight:bold;">10</span> segundos.<p>
            <p style="text-align:center;"><img src="img/icon_pagseguro_redirecionamento.jpg"></p>
            ';


        } else if ($formulario->metodo == "deposito") {

            /*$empresa = 'REAL ACESSÓRIOS PARA JOGOS EIRELI-EPP';
            $cnpj = '32.746.259/0001-72';
            $banco = 'Banco Itaú - 341';
            $agencia = '4319';
            $conta = '32358-2';*/

            $empresa = 'RP Mesas e Fichas de Jogos LTD';
            $cnpj = '57.024.553/0001-00';
            $banco = 'Banco Itaú - 341';
            $agencia = '4319';
            $conta = '97915-1';
            
            $mensagem = '
            <p><span style="font-size:18px;font-weight:bold;">Parabéns, seu pedido foi concluído!</span><br><span style="font-size:16px;">Já te mandamos as informações por e-mail, agora siga as instruções abaixo para pagamento.</span></p>
            <p style="font-size:14px;">
            <img src="img/pagamento_pix.png" style="width:150px;"> <br>
            Chave PIX CNPJ: <br>
            <input type="text" value="'.$cnpj.'" id="chave_pix">
            <a href="#" class="bt_copiar">COPIAR CHAVE PIX</a><br><br>
            '.$banco.'<br>
            '.$empresa.'<br>
            Agência: '.$agencia.'<br>
            Conta Corrente: '.$conta.'<br>
            CNPJ: '.$cnpj.'
            
            </p>
            <p style="font-size:14px;">Valor: <strong>R$ ' . $sistema->formataMoedaShow($pedido['valor_final']) . '</strong></p>
            <p>*Após o pagamento é necessário que envie o comprovante para atendimento@realpoker.com.br e informe o nome completo e/ou número do pedido para identificação e confirmação.</p>


            <script src="https://apis.google.com/js/platform.js?onload=renderOptIn" async defer></script>

                <script>
                  window.renderOptIn = function() {
                    window.gapi.load(\'surveyoptin\', function() {
                      window.gapi.surveyoptin.render(
                        {
                          // REQUIRED FIELDS
                          "merchant_id": 112316588,
                          "order_id": "'.$pedido['id'].'",
                          "email": "'.$cliente['email'].'",
                          "delivery_country": "BR",
                          "estimated_delivery_date": "20'.date('y-m-d', strtotime('+1 week')).'",
                          
                        });
                    });
                  }
                </script>
                <!-- BEGIN GCR Language Code -->
                <script>
                  window.___gcfg = {
                    lang: \'pt_BR\'
                  };
                </script>
                <!-- END GCR Language Code -->




            ';

             

            
        } else if ($formulario->metodo == "pokerstars") {
            
            $mensagem = '
            <p><span style="font-size:18px;font-weight:bold;">Parabéns, seu pedido foi concluído!</span><br><span style="font-size:16px;">Já te mandamos as informações por e-mail, agora siga as instruções abaixo para pagamento.</span></p>
            <p>Transfira <strong>US$ '.$sistema->formataMoedaShow($pedido['valor_final'] / $sistema->cotacao_dollar).'</strong> para a conta do PokerStars <strong>kurtx</strong> e em seguida responda o e-mail que te mandamos do pedido com a confirmação do seu Nick do PokerStars para confirmarmos o pagamento.

 <script src="https://apis.google.com/js/platform.js?onload=renderOptIn" async defer></script>

                <script>
                  window.renderOptIn = function() {
                    window.gapi.load(\'surveyoptin\', function() {
                      window.gapi.surveyoptin.render(
                        {
                          // REQUIRED FIELDS
                          "merchant_id": 112316588,
                          "order_id": "'.$pedido['id'].'",
                          "email": "'.$cliente['email'].'",
                          "delivery_country": "BR",
                          "estimated_delivery_date": "20'.date('y-m-d', strtotime('+1 week')).'",
                          
                        });
                    });
                  }
                </script>
                <!-- BEGIN GCR Language Code -->
                <script>
                  window.___gcfg = {
                    lang: \'pt_BR\'
                  };
                </script>
                <!-- END GCR Language Code -->


            ';

        }



include "_inc_headers.php"; 
?>
</head>
<body id="<?php echo $pagina; ?>">
<?php include "_inc_header.php"; ?>
<div class="breadcrumbs">
	<div class="comp-grid-main-in">
		<span><a href="<?php echo $sistema->root_path; ?>">Home</a></span>
		<span><a href="<?php echo $sistema->seo_pages['pagamento']['seo_url']; ?>">Pagamento</a></span>
	</div>
</div>

<div class="comp-grid-main-in">
	<div class="main_titles"><h1>PAGAMENTO</h1></div>
	<div class="comp-grid-row">
            <?php echo $mensagem; ?>
            <div style="display:none">
		<?php
                    echo $mostra;
                ?>
            </div>
	</div>
</div>

<?php include "_inc_footer.php"; ?>

<script src="js/jquery.banners.js"></script>
<script src="js/jquery.easing.js"></script>
<script>
    
    <?php if ($formulario->metodo == "cielo") :?>
        function irCielo(){
                            
            document.getElementById('tempo').innerHTML = ('0');
            clearInterval(interval);

            document.frmCielo.submit();

        }

        interval = setInterval(function(){
            var tempo = new Number(document.getElementById('tempo').innerHTML);
            document.getElementById('tempo').innerHTML = (tempo-1);

            if(tempo-1==0){

                clearInterval(interval);
                document.frmCielo.submit();

            }

        }, 1000);
                        
    <?php endif;?>
        
    <?php if ($formulario->metodo == "boleto") :?>

        function itauShoplinePopup(){
            window.open('','SHOPLINE','toolbar=yes,menubar=yes,resizable=yes,status=no,scrollbars=yes,width=815,height=575');
        }
                        
    <?php endif;?>

    <?php if ($formulario->metodo == "pagseguro") :?>
        function irPagseguro(){

            document.getElementById('tempo').innerHTML = ('0');
            clearInterval(interval);

            document.frmPagSeguro.submit();

        }



        interval = setInterval(function(){
            var tempo = new Number(document.getElementById('tempo').innerHTML);
            document.getElementById('tempo').innerHTML = (tempo-1);

            if(tempo-1==0){

                clearInterval(interval);
                document.frmPagSeguro.submit();

            }

        }, 1000);
    <?php endif; ?>
    
    if (typeof _gaq != 'undefined') {
        _gaq.push(['_addTrans', '<?php echo $formulario->pedido; ?>', 'Real Poker', '<?php echo ($pedido["valor_final"]-$pedido["valor_frete"]); ?>', '0.0000', '<?php echo $pedido["valor_frete"]; ?>', '<?php echo $endereco["cidade"]; ?>', '<?php echo $endereco["uf"]; ?>', 'BR']);
        <?php foreach($produtos->rows as $produto): ?>
            _gaq.push(['_addItem', '<?php echo $formulario->pedido; ?>', '<?php if($produto["id_produto"] != ""){ echo $produto["id_produto"]; }else{ echo $produto["id_personalizado"]; } ?>', '<?php echo $produto["nome_produto"]; ?> ', '', '<?php echo $produto["valor_produto"]; ?>', '<?php echo $produto["quantidade"]; ?>']);
        <?php endforeach;?>
        _gaq.push(['_trackTrans']);
    }


    
</script>
</body>
</html>