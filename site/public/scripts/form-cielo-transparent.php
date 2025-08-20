<?php

session_start();

require_once '../sistema/System/Core/Loader.php';

use System\Core\Bootstrap;
use payments\cielo_transparente\CieloCheckoutTransparent;
use classes\Product;

require_once "../_system.php";

$sistema = new _sys();
$product = new Product();

$sistema->DB_connect();

$formulario = $sistema->formularioObjeto($_POST);
$idPedido = $formulario->id_pedido;

$validacao = validaFormulario($formulario);
$codigos_retorno_cielo = messageRetornoCielo();

$crop_sizes = array();

if (!isset($_SESSION["seo_session"])) {
    $_SESSION["seo_session"] = uniqid();
}

if (!$validacao->return) {
    echo json_encode($validacao);
} else {

    $resposta = new stdClass();
    $resposta->time = 4000;

    $pedido = $sistema->DB_fetch_array('SELECT a.*, a.id pedido_id, b.id cliente_id, b.nome cliente_nome, b.email cliente_email, b.endereco cliente_endereco, b.numero cliente_numero, b.bairro cliente_bairro, b.complemento cliente_complemento, b.cep cliente_cep, c.cidade cliente_cidade, d.uf cliente_uf FROM tb_pedidos_pedidos a INNER JOIN tb_clientes_clientes b ON a.id_cliente = b.id INNER JOIN tb_utils_cidades c ON c.id = b.id_cidade INNER JOIN tb_utils_estados d ON c.id_estado = d.id WHERE a.id = '.$idPedido);

    $carrinho = $sistema->DB_fetch_array('SELECT *, d.nome categoria FROM tb_carrinho_produtos_historico a INNER JOIN tb_produtos_produtos b ON a.id_produto = b.id INNER JOIN tb_produtos_produtos_has_tb_produtos_categorias c ON b.id = c.id_produto INNER JOIN tb_produtos_categorias d ON c.id_categoria = d.id WHERE a.id_pedido = '.$idPedido);

    if($pedido->num_rows){
        $pedido = $pedido->rows[0];
        $cielo = new CieloCheckoutTransparent();

        $transaction                    = new StdClass();
        $card                           = new StdClass();
        $costumer                       = new stdClass();
        $payment                        = new stdClass();
        $delivery                       = new stdClass();
        $produtos                       = array();
        $fraude                         = new stdClass();

        $i=0;
        foreach ($carrinho->rows as $produto) {
            $produtos[$i] = new stdClass();
            $produtos[$i]->Name         = $produto['nome_produto'];
            $produtos[$i]->Quantity     = $produto['quantidade'];
            $produtos[$i]->Sku          = $produto['ncm'];
            $produtos[$i]->Type         = $produto['categoria'];
            $produtos[$i]->UnitPrice    = preg_replace('/[^0-9]/', '', (string) ((float) $produto['valor_produto']) * 100);
            $i++;
        }

        $fraude->Cart                   = $produtos;

        $delivery->Street               = $pedido['cliente_endereco'];
        $delivery->Number               = $pedido['cliente_numero'];
        $delivery->Complement           = $pedido['cliente_complemento'] . ' - ' . $pedido['cliente_bairro'];
        $delivery->ZipCode              = preg_replace('/[^0-9]/', '', (string)$pedido['cliente_cep']);
        $delivery->City                 = $pedido['cliente_cidade'];
        $delivery->State                = $pedido['cliente_uf'];
        $delivery->Country              = "BRA";

        $costumer->Name                 = $formulario->Holder;
        $costumer->Email                = $pedido['cliente_email'];
        $costumer->DeliveryAddress      = $delivery;

        $card->Holder                   = $formulario->Holder;
        $card->CardNumber               = preg_replace('/\D/', '', $formulario->CardNumber);
        $card->Brand                    = $formulario->Brand;
        $card->SecurityCode             = $formulario->SecurityCode;
        $card->ExpirationDate           = $formulario->ExpirationMonth."/".$formulario->ExpirationYear;

        $payment->Type                  = 'CreditCard';
        $payment->Amount                = number_format($pedido['valor_final'], 2, '', '');
        $payment->Installments          = $formulario->Installments;
        $payment->SoftDescriptor        = "REAL POKER";
        $payment->Capture               = false;
        $payment->Authenticate          = false;
        $payment->CreditCard            = $card;



        $transaction->MerchantOrderId   = $pedido['id'];
        //$transaction->MerchantOrderId   = str_pad(str_pad($pedido['id'], 10, '0', STR_PAD_LEFT), 32, md5((int) $pedido['id'] + (int) $pedido['pedido_id']), STR_PAD_LEFT);
        $transaction->Customer          = $costumer;
        $transaction->Payment           = $payment;
        $transaction->FraudAnalysis     = $fraude;


        $empresa = $sistema->DB_fetch_array('SELECT * FROM tb_admin_empresas A WHERE A.stats = 1');
        $empresa = $empresa->rows[0];

        $is_sandbox = $empresa['payment_cielo_environment']=='sandbox' ? true : false;
        $cielo->setMerchant($empresa['payment_cielo_merchant_id'],$empresa['payment_cielo_merchant_key'],$is_sandbox);
        $sistema->inserirRelatorio("{payment_cielo_merchant_id: ".$empresa['payment_cielo_merchant_id'].", payment_cielo_merchant_key:".$empresa['payment_cielo_merchant_key'].", is_sandbox:".$is_sandbox."}");
        $cieloTransaction = $cielo->simpleTransaction($transaction);
        

        if($cieloTransaction->statusCode != 200 && $cieloTransaction->statusCode != 201){
            $resposta->type = "error";
            $resposta->message = "Erro indefinido. Verifique os dados e tente novamente, se o problema persistir entre em contato conosco";
            $sistema->inserirRelatorio("Retorno De Erro Cielo: ".json_encode($cieloTransaction));
            $sistema->inserirRelatorio("Transação que gerou Erro na Cielo: ".json_encode($transaction));
            //$sistema->inserirRelatorio("Erro CIELO: #".$cieloTransaction->response[0]->Code." | ".$cieloTransaction->response[0]->Message);
            $sistema->inserirRelatorio("Erro CIELO");
        }else{

            $retornoCielo = $cieloTransaction->response;

                $cielo_transacao = new \stdClass();
                $cielo_transacao->id_pedido                         = $idPedido;
                $cielo_transacao->Tid                               = $retornoCielo->Payment->Tid;
                $cielo_transacao->MerchantOrderId                   = $retornoCielo->MerchantOrderId;
                $cielo_transacao->PaymentId                         = $retornoCielo->Payment->PaymentId;
                $cielo_transacao->Amount                            = $retornoCielo->Payment->Amount;
                $cielo_transacao->Installments                      = $retornoCielo->Payment->Installments;
                $cielo_transacao->Provider                          = $retornoCielo->Payment->Provider;
                $cielo_transacao->Status                            = $retornoCielo->Payment->Status;
                $cielo_transacao->ReturnCode                        = $retornoCielo->Payment->ReturnCode;
                $cielo_transacao->ReturnMessage                     = $retornoCielo->Payment->ReturnMessage;
                $cielo_transacao->CardNumber                        = $retornoCielo->Payment->CreditCard->CardNumber;
                $cielo_transacao->Holder                            = $retornoCielo->Payment->CreditCard->Holder;
                $cielo_transacao->ExpirationDate                    = $retornoCielo->Payment->CreditCard->ExpirationDate;
                $cielo_transacao->Brand                             = $retornoCielo->Payment->CreditCard->Brand;

                foreach ($cielo_transacao as $key => $value) {
                    $fields[] = $key;
                    $values[] = "'$value'";
                }

                $query = $sistema->DB_insert('tb_pedidos_transacoes_cielo_transparente', implode(',', $fields), implode(',', $values));
                $resposta->queryInsertResult = $query;

            $resposta->type = "attention";
            //$resposta->message = $retornoCielo->Payment->ReturnMessage;
            $resposta->message = $codigos_retorno_cielo[$retornoCielo->Payment->ReturnCode];
            if(
                $retornoCielo->Payment->ReturnCode == '0' || 
                $retornoCielo->Payment->ReturnCode == '00' || 
                $retornoCielo->Payment->ReturnCode == '000' || 
                $retornoCielo->Payment->ReturnCode == '1' || 
                $retornoCielo->Payment->ReturnCode == '2' || 
                $retornoCielo->Payment->ReturnCode == '4' || 
                $retornoCielo->Payment->ReturnCode == '6' || 
                $retornoCielo->Payment->ReturnCode == '10'|| 
                $retornoCielo->Payment->ReturnCode == '11'
            ){

                $sistema->inserirRelatorio('Retorno Transação Cielo: '.json_encode($retornoCielo));

                $tipo_pagamento = 'Cartão de Crédito - '.$retornoCielo->Payment->CreditCard->Brand;

                $sistema->DB_update("tb_pedidos_pedidos", "metodo_pagamento_id = '".$cielo_transacao->Tid."', tipo_pagamento = '".$tipo_pagamento."' , id_empresa = ".$empresa['id']." WHERE id = $idPedido");

                $resposta->type = "success";
                //$resposta->message = "Transação autorizada com sucesso!";
                $sistema->inserirRelatorio("Pagamento Cielo Transparente realizado: Pedido id: [" . $idPedido . "]");

            }
        }

        $resposta->cielo = $cieloTransaction;
        $resposta->time = 4000;

    }else{
        $resposta->type = "error";
        $resposta->message = "Aconteceu um erro no sistema, seu pedido não foi encontrato pelo sistema!";
    }
    
    if($resposta->type == "success"){
        $sistema->alterarStatusPedido($pedido['pedido_id'], 5);
    }else{
        $sistema->alterarStatusPedido($pedido['pedido_id'], 4);
    }

    echo json_encode($resposta);
}

$sistema->DB_disconnect();

function validaFormulario($form) {

    $resposta = new stdClass();
    $resposta->return = true;

    //$sistema = new sistema();
    global $sistema, $main_table;

    if ($form->CardNumber == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "CardNumber";
        $resposta->return = false;
        return $resposta;
    } else if ($form->Brand == "") {
        $resposta->type = "attention";
        $resposta->message = "Bandeira do seu cartão não foi identificada";
        $resposta->field = "Brand";
        $resposta->return = false;
        return $resposta;
    } else if ($form->SecurityCode == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "SecurityCode";
        $resposta->return = false;
        return $resposta;
    } else if ($form->Holder == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "Holder";
        $resposta->return = false;
        return $resposta;
    }/* else if ($form->ExpirationDate == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "ExpirationDate";
        $resposta->return = false;
        return $resposta;
    }*/ else {
        return $resposta;
    }
}

function messageRetornoCielo(){

    $codigos_retorno_cielo['0']     = "Operação realizada com sucesso.";
    $codigos_retorno_cielo['00']    = "Operação realizada com sucesso.";
    $codigos_retorno_cielo['000']   = "Operação realizada com sucesso.";
    $codigos_retorno_cielo['1']     = "Operação inicializada com sucesso.";
    $codigos_retorno_cielo['2']     = "Processo de autenticação confirmado.";
    $codigos_retorno_cielo['4']     = "Transação autorizada com sucesso.";
    $codigos_retorno_cielo['6']     = "Operação realizada com sucesso.";
    $codigos_retorno_cielo['10']    = "Estamos autenticando sua transação, Aguarde confirmação por e-mail";
    $codigos_retorno_cielo['11']    = "Transação autorizada com sucesso.";


    $codigos_retorno_cielo['01']    = "Transação não autorizada. Transação referida.";
    $codigos_retorno_cielo['02']    = "Transação não autorizada. Transação referida.";
    $codigos_retorno_cielo['3']     = "Operação Não Autenticada.";
    $codigos_retorno_cielo['03']    = "Transação não permitida. Erro no cadastramento do código do estabelecimento.";
    $codigos_retorno_cielo['04']    = "Transação não autorizada. Cartão bloqueado pelo banco emissor.";
    $codigos_retorno_cielo['5']     = "Operação Não Autorizada.";
    $codigos_retorno_cielo['05']    = "Transação não autorizada. Cartão inadimplente.";
    $codigos_retorno_cielo['06']    = "Transação não autorizada. Cartão cancelado.";
    $codigos_retorno_cielo['07']    = "Transação negada. Reter cartão condição especial";
    $codigos_retorno_cielo['08']    = "Transação não autorizada. Código de segurança inválido.";
    $codigos_retorno_cielo['9']     = "Operação cancelada.";
    $codigos_retorno_cielo['12']    = "Transação inválida, erro no cartão.";
    $codigos_retorno_cielo['13']    = "Transação não permitida. Valor da transação Inválido.";
    $codigos_retorno_cielo['14']    = "Transação não autorizada. Cartão Inválido";
    $codigos_retorno_cielo['15']    = "Transação não autorizada. Banco emissor indisponível.";
    $codigos_retorno_cielo['19']    = "Refaça a transação ou tente novamente mais tarde.";
    $codigos_retorno_cielo['21']    = "Cancelamento não efetuado. Transação não localizada.";
    $codigos_retorno_cielo['22']    = "Parcelamento inválido. Número de parcelas inválidas.";
    $codigos_retorno_cielo['23']    = "Transação não autorizada. Valor da prestação inválido.";
    $codigos_retorno_cielo['24']    = "Quantidade de parcelas inválido.";
    $codigos_retorno_cielo['25']    = "Pedido de autorização não enviou número do cartão";
    $codigos_retorno_cielo['28']    = " Arquivo temporariamente indisponível.";
    $codigos_retorno_cielo['30']    = "Não foi possível processar a transação. Reveja os dados e tente novamente. Se o erro persistir, entre em contato com a loja";
    $codigos_retorno_cielo['39']    = "Transação não autorizada. Entre em contato com seu banco emissor.";
    $codigos_retorno_cielo['41']    = "Transação não autorizada. Entre em contato com seu banco emissor.";
    $codigos_retorno_cielo['43']    = "Transação não autorizada. Entre em contato com seu banco emissor.";
    $codigos_retorno_cielo['51']    = "Transação não autorizada. Entre em contato com seu banco emissor.";
    $codigos_retorno_cielo['52']    = "Transação não autorizada. Reveja os dados informados e tente novamente.";
    $codigos_retorno_cielo['53']    = "Não foi possível processar a transação. Entre em contato com seu banco emissor.";
    $codigos_retorno_cielo['54']    = "Transação não autorizada. Refazer a transação confirmando os dados.";
    $codigos_retorno_cielo['55']    = "Transação não autorizada. Entre em contato com seu banco emissor.";
    $codigos_retorno_cielo['57']    = "Transação não autorizada. Entre em contato com seu banco emissor.";
    $codigos_retorno_cielo['58']    = "Transação não autorizada. Entre em contato com sua loja virtual.";
    $codigos_retorno_cielo['59']    = "Transação não autorizada. Entre em contato com seu banco emissor.";
    $codigos_retorno_cielo['60']    = "Não foi possível processar a transação. Tente novamente mais tarde. Se o erro persistir, entre em contato com seu banco emissor.";
    $codigos_retorno_cielo['61']    = "Transação não autorizada. Tente novamente. Se o erro persistir, entre em contato com seu banco emissor.";
    $codigos_retorno_cielo['62']    = "Transação não autorizada. Entre em contato com seu banco emissor.";
    $codigos_retorno_cielo['63']    = "Transação não autorizada. Entre em contato com seu banco emissor.";
    $codigos_retorno_cielo['64']    = "Transação não autorizada. Valor abaixo do mínimo exigido pelo banco emissor.";
    $codigos_retorno_cielo['65']    = "Transação não autorizada. Entre em contato com seu banco emissor.";
    $codigos_retorno_cielo['67']    = "Transação não autorizada. Cartão bloqueado temporariamente. Entre em contato com seu banco emissor.";
    $codigos_retorno_cielo['70']    = "Transação não autorizada. Entre em contato com seu banco emissor.";
    $codigos_retorno_cielo['72']    = "Cancelamento não efetuado. Tente novamente mais tarde. Se o erro persistir, entre em contato com a loja virtual.";
    $codigos_retorno_cielo['74']    = "Transação não autorizada. Entre em contato com seu banco emissor.";
    $codigos_retorno_cielo['75']    = "Sua Transação não pode ser processada. Entre em contato com o Emissor do seu cartão.";
    $codigos_retorno_cielo['76']    = "Cancelamento não efetuado. Entre em contato com a loja virtual.";
    $codigos_retorno_cielo['77']    = "Cancelamento não efetuado. Entre em contato com a loja virtual.";
    $codigos_retorno_cielo['78']    = "Transação não autorizada. Entre em contato com seu banco emissor e solicite o desbloqueio do cartão.";
    $codigos_retorno_cielo['80']    = "Transação não autorizada. Refazer a transação confirmando os dados.";
    $codigos_retorno_cielo['82']    = "Transação não autorizada. Refazer a transação confirmando os dados. Se o erro persistir, entre em contato com seu banco emissor.";
    $codigos_retorno_cielo['83']    = "Transação não autorizada. Refazer a transação confirmando os dados. Se o erro persistir, entre em contato com seu banco emissor.";
    $codigos_retorno_cielo['85']    = "Transação não permitida. Informe os dados do cartão novamente. Se o erro persistir, entre em contato com a loja virtual.";
    $codigos_retorno_cielo['86']    = "Transação não permitida. Informe os dados do cartão novamente. Se o erro persistir, entre em contato com a loja virtual.";
    $codigos_retorno_cielo['89']    = "Transação não autorizada. Erro na transação. Tente novamente e se o erro persistir, entre em contato com seu banco emissor.";
    $codigos_retorno_cielo['90']    = "Transação não permitida. Informe os dados do cartão novamente. Se o erro persistir, entre em contato com a loja virtual.";
    $codigos_retorno_cielo['91']    = "Transação não autorizada. Banco emissor temporariamente indisponível. Entre em contato com seu banco emissor.";
    $codigos_retorno_cielo['92']    = "Transação não autorizada. Comunicação temporariamente indisponível. Entre em contato com a loja virtual.";
    $codigos_retorno_cielo['93']    = "Sua transação não pode ser processada. Entre em contato com a loja virtual.";
    $codigos_retorno_cielo['96']    = "Sua Transação não pode ser processada, Tente novamente mais tarde. Se o erro persistir, entre em contato com a loja virtual.";
    $codigos_retorno_cielo['97']    = "Transação não autorizada. Valor não permitido para essa transação.";
    $codigos_retorno_cielo['98']    = "Sua Transação não pode ser processada, Tente novamente mais tarde. Se o erro persistir, entre em contato com a loja virtual.";
    $codigos_retorno_cielo['99']    = "Sua Transação não pode ser processada, Tente novamente mais tarde. Se o erro persistir, entre em contato com a loja virtual.";
    $codigos_retorno_cielo['999']    = "Sua Transação não pode ser processada, Tente novamente mais tarde. Se o erro persistir, entre em contato com a loja virtual.";
    $codigos_retorno_cielo['AA']    = "Tempo excedido na sua comunicação com o banco emissor, tente novamente mais tarde. Se o erro persistir, entre em contato com seu banco.";
    $codigos_retorno_cielo['AC']    = "Transação não autorizada. Tente novamente selecionando a opção de pagamento cartão de débito.";
    $codigos_retorno_cielo['AE']    = "Tempo excedido na sua comunicação com o banco emissor, tente novamente mais tarde. Se o erro persistir, entre em contato com seu banco.";
    $codigos_retorno_cielo['AF']    = "Transação não permitida. Informe os dados do cartão novamente. Se o erro persistir, entre em contato com a loja virtual.";
    $codigos_retorno_cielo['AG']    = "Transação não permitida. Informe os dados do cartão novamente. Se o erro persistir, entre em contato com a loja virtual.";
    $codigos_retorno_cielo['AH']    = "Transação não autorizada. Tente novamente selecionando a opção de pagamento cartão de crédito.";
    $codigos_retorno_cielo['AI']    = "Transação não autorizada. Autenticação não foi realizada com sucesso. Tente novamente e informe corretamente os dados solicitado. Se o erro persistir, entre em contato com o lojista.";
    $codigos_retorno_cielo['AJ']    = "Transação não permitida. Transação de crédito ou débito em uma operação que permite apenas Private Label. Tente novamente e selecione a opção Private Label. Em caso de um novo erro entre em contato com a loja virtual.";
    $codigos_retorno_cielo['AV']    = "Falha na validação dos dados. Reveja os dados informados e tente novamente.";
    $codigos_retorno_cielo['BD']    = "Transação não permitida. Informe os dados do cartão novamente. Se o erro persistir, entre em contato com a loja virtual.";
    $codigos_retorno_cielo['BL']    = "Transação não autorizada. Limite diário excedido. Entre em contato com seu banco emissor.";
    $codigos_retorno_cielo['BM']    = "Transação não autorizada. Cartão inválido. Refaça a transação confirmando os dados informados.";
    $codigos_retorno_cielo['BN']    = "Transação não autorizada. O cartão ou a conta do portador está bloqueada. Entre em contato com seu banco emissor.";
    $codigos_retorno_cielo['BO']    = "Transação não permitida. Houve um erro no processamento. Digite novamente os dados do cartão, se o erro persistir, entre em contato com o banco emissor.";
    $codigos_retorno_cielo['BP']    = "Transação não autorizada. Não possível processar a transação por um erro relacionado ao cartão ou conta do portador. Entre em contato com o banco emissor.";
    $codigos_retorno_cielo['BV']    = "Transação não autorizada. Refazer a transação confirmando os dados.";
    $codigos_retorno_cielo['CF']    = "Transação não autorizada. Falha na validação dos dados. Entre em contato com o banco emissor.";
    $codigos_retorno_cielo['CG']    = "Transação não autorizada. Falha na validação dos dados. Entre em contato com o banco emissor.";
    $codigos_retorno_cielo['DA']    = "Transação não autorizada. Falha na validação dos dados. Entre em contato com o banco emissor.";
    $codigos_retorno_cielo['DF']    = "Transação não permitida. Falha no cartão ou cartão inválido. Digite novamente os dados do cartão, se o erro persistir, entre em contato com o banco";
    $codigos_retorno_cielo['DM']    = "Transação não autorizada. Entre em contato com seu banco emissor.";
    $codigos_retorno_cielo['DQ']    = "Transação não autorizada. Falha na validação dos dados. Entre em contato com o banco emissor.";
    $codigos_retorno_cielo['DS']    = "Transação não autorizada. Entre em contato com seu banco emissor.";
    $codigos_retorno_cielo['EB']    = "Transação não autorizada. Limite diário excedido. Entre em contato com seu banco emissor.";
    $codigos_retorno_cielo['EE']    = "Transação não permitida. O valor da parcela está abaixo do mínimo permitido. Entre em contato com a loja virtual.";
    $codigos_retorno_cielo['EK']    = "Transação não autorizada. Entre em contato com seu banco emissor.";
    $codigos_retorno_cielo['FA']    = "Transação não autorizada. Entre em contato com seu banco emissor.";
    $codigos_retorno_cielo['FC']    = "Transação não autorizada. Entre em contato com seu banco emissor.";
    $codigos_retorno_cielo['FD']    = "Transação não autorizada. Entre em contato com seu banco emissor.";
    $codigos_retorno_cielo['FE']    = "Transação não autorizada. Refazer a transação confirmando os dados.";
    $codigos_retorno_cielo['FF']    = "Transação de cancelamento autorizada com sucesso";
    $codigos_retorno_cielo['FG']    = "Transação não autorizada. Entre em contato com a Central de Atendimento AmEx no telefone 08007285090";
    $codigos_retorno_cielo['GA']    = "Transação não autorizada. Entre em contato com o lojista.";
    $codigos_retorno_cielo['HJ']    = "Transação não permitida. Código da operação Coban inválido. Entre em contato com o lojista.";
    $codigos_retorno_cielo['IA']    = "Transação não permitida. Indicador da operação Coban inválido. Entre em contato com o lojista.";
    $codigos_retorno_cielo['JB']    = "Transação não permitida. Valor da operação Coban inválido. Entre em contato com o lojista.";
    $codigos_retorno_cielo['KA']    = "Transação não permitida. Houve uma falha na validação dos dados. reveja os dados informados e tente novamente. Se o erro persistir entre em contato com a Loja Virtual.";
    $codigos_retorno_cielo['KB']    = "Transação não permitida. Selecionado a opção incorreta. Tente novamente. Se o erro persistir entre em contato com a Loja Virtual.";
    $codigos_retorno_cielo['KE']    = "Transação não autorizada. Falha na validação dos dados. Opção selecionada não está habilitada. Entre em contato com a loja virtual.";
    $codigos_retorno_cielo['N7']    = "Transação não autorizada. Reveja os dados e informe novamente.";
    $codigos_retorno_cielo['R1']    = "Transação não autorizada. Entre em contato com seu banco emissor.";
    $codigos_retorno_cielo['U3']    = "Transação não permitida. Houve uma falha na validação dos dados. reveja os dados informados e tente novamente. Se o erro persistir entre em contato com a Loja Virtual.";
    $codigos_retorno_cielo['GD']    = "Transação não é possível ser processada no estabelecimento. Entre em contato com a Cielo para obter mais detalhes.";

    return $codigos_retorno_cielo;
}
?>