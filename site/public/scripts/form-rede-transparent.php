<?php

session_start();

require_once '../../vendor/autoload.php';
require_once '../sistema/System/Core/Loader.php';
use System\Core\Bootstrap;

require_once "../_system.php";

$sistema = new _sys();

$sistema->DB_connect();

$formulario = $sistema->formularioObjeto($_POST);
$idPedido = $formulario->id_pedido;

$validacao = validaFormulario($formulario);

$crop_sizes = array();

if (!isset($_SESSION["seo_session"])) {
    $_SESSION["seo_session"] = uniqid();
}

if (!$validacao->return) {
    echo json_encode($validacao);
} else {

    $resposta = new stdClass();
    $resposta->time = 9000;

    $pedido = $sistema->DB_fetch_array('SELECT a.*, a.id pedido_id, b.id cliente_id, b.nome cliente_nome, b.email cliente_email, b.telefone cliente_telefone, b.pessoa tipo_pessoa, b.cpf cliente_cpf, b.cnpj cliente_cnpj, b.endereco cliente_endereco, b.numero cliente_numero, b.bairro cliente_bairro, b.complemento cliente_complemento, b.cep cliente_cep, c.cidade cliente_cidade, d.uf cliente_uf FROM tb_pedidos_pedidos a INNER JOIN tb_clientes_clientes b ON a.id_cliente = b.id INNER JOIN tb_utils_cidades c ON c.id = b.id_cidade INNER JOIN tb_utils_estados d ON c.id_estado = d.id WHERE a.id = '.$idPedido);

    $carrinho = $sistema->DB_fetch_array('SELECT *, d.nome categoria FROM tb_carrinho_produtos_historico a INNER JOIN tb_produtos_produtos b ON a.id_produto = b.id INNER JOIN tb_produtos_produtos_has_tb_produtos_categorias c ON b.id = c.id_produto INNER JOIN tb_produtos_categorias d ON c.id_categoria = d.id WHERE a.id_pedido = '.$idPedido);

    $empresa = $sistema->DB_fetch_array('SELECT * FROM tb_admin_empresas A WHERE A.stats = 1');
    $empresa = $empresa->rows[0];

    if($pedido->num_rows){
        $pedido = $pedido->rows[0];

        $telefone = explode('(',$pedido['cliente_telefone']);
        $telefone = explode(') ',$telefone[1]);
        $ddd = "0".$telefone[0];
        $fone = $telefone[1];
        if($pedido['tipo_pessoa'] == 1){
            $cliente_documento = str_replace("-","",str_replace(".","",$pedido['cliente_cpf']));
        }else{
            $cliente_documento = str_replace("/","",str_replace("-","",str_replace(".","",$pedido['cliente_cnpj'])));
        }

        $ExpirationDate = explode("/",$formulario->ExpirationDate);
        $ExpirationMonth = $ExpirationDate[0];
        $ExpirationYear = '20'.$ExpirationDate[1];

        if($empresa['payment_rede_environment'] == "sandbox") $environment = \Rede\Environment::sandbox();
        else $environment = \Rede\Environment::production();

        $environment->setIp($_SERVER['REMOTE_ADDR']);
        $environment->setSessionId($_SESSION["seo_session"]);


        // $store = new \Rede\Store('10001638', 'b719b7f90d2a4233ad978ce0b4476219', $environment); //<< TESTE
        $store = new \Rede\Store($empresa['payment_rede_store_id'], $empresa['payment_rede_token'], $environment); //<< PRODUÇÃO

        // Transação que será autorizada
        $transaction = (new \Rede\Transaction($pedido['valor_final'], $idPedido))->creditCard(
            preg_replace('/[^0-9]/', '', (string) $formulario->CardNumber),
            $formulario->SecurityCode,
            $ExpirationMonth,
            $ExpirationYear,
            $formulario->Holder
        )->capture(false)->setInstallments($formulario->Installments);


        //Dados do antifraude
        /*
        $antifraud = $transaction->antifraud($environment);
        $antifraud->consumer($pedido['cliente_nome'], $pedido['cliente_email'], $cliente_documento)
            ->setPhone(new \Rede\Phone($ddd, $fone));

        $antifraud->address()
            ->setAddress($pedido['cliente_endereco'])
            ->setNumber($pedido['cliente_numero'])
            ->setZipCode(preg_replace('/[^0-9]/', '', (string) $pedido['cliente_cep']))
            ->setNeighbourhood('Bairro legal')
            ->setCity($pedido['cliente_bairro'])
            ->setState($pedido['cliente_uf'])
            ->setType(\Rede\Address::COMMERCIAL);

        foreach ($carrinho->rows as $produto) {
            $antifraud->addItem(
                (new \Rede\Item($produto['id_produto'], 1, \Rede\Item::PHYSICAL))
                    ->setAmount(preg_replace('/[^0-9]/', '', (string) ((float) $produto['valor_produto']) * 100))
                    ->setDescription($produto['nome_produto'])
                    ->setDiscount(preg_replace('/[^0-9]/', '', (string) ((float) $produto['desconto']) * 100))
                    ->setShippingType('Sedex')
            );
        }
        */

        // Autoriza a transação
        try {
            $transaction = (new \Rede\eRede($store))->create($transaction);
        } catch (\Rede\Exception\RedeException $e){
            $sistema->inserirRelatorio("[REDE][TRY-CATCH]: ".json_encode($e->getMessage()));
        }

        $rede_transacao = new \stdClass();
        $rede_transacao->id_pedido                          = $idPedido;
        $rede_transacao->tid                                = $transaction->getTid();
        $rede_transacao->reference                          = $transaction->getReference();
        $rede_transacao->returnCode                         = $transaction->getReturnCode();
        $rede_transacao->returnMessage                      = $transaction->getReturnMessage();
        $rede_transacao->nsu                                = $transaction->getNsu();
        $rede_transacao->authorizationCode                  = $transaction->getAuthorizationCode();
        $rede_transacao->card                               = $transaction->getCardBin()."******".$transaction->getLast4();
        $rede_transacao->amount                             = $transaction->getAmount();
        //$rede_transacao->antifraud                          = $transaction->getAntifraud();

        $sistema->inserirRelatorio("[REDE][TRANSACTION][#".$rede_transacao->returnCode."]: ".json_encode($rede_transacao));

        if ($transaction->getReturnCode() == '00') {


            $resposta->type = "success";
            $resposta->time = 4000;
            $resposta->message = "Transação autorizada com sucesso!";

            $sistema->DB_update("tb_pedidos_pedidos", "metodo_pagamento_id = '".$rede_transacao->tid."', id_empresa = ".$empresa['id']." WHERE id = ".$idPedido);
            
            /*
            $antifraud = $transaction->getAntifraud();
            
            printf("Transação autorizada com sucesso; tid=%s\n", $transaction->getTid());
            
            printf("\tAntifraude: %s\n", $antifraud->isSuccess() ? 'Sucesso' : 'Falha');
            printf("\tScore: %s\n", $antifraud->getScore());
            printf("\tNível de Risco: %s\n", $antifraud->getRiskLevel());
            printf("\tRecomendação: %s\n", $antifraud->getRecommendation());
            */



        }else if((int)$rede_transacao->returnCode > 52 && (int)$rede_transacao->returnCode < 205){

            switch ((int)$rede_transacao->returnCode) {
                case 53:
                case 57:

                    $resposta->type = "attention";
                    $resposta->message = 'Erro interno em nosso sistema, por favor entre em contato conosco e informe o código #'.$rede_transacao->returnCode;
                    $sistema->inserirRelatorio("[REDE][ERROR] : #".$rede_transacao->returnCode." - ".$rede_transacao->returnMessage);
                    $sistema->alertaCritico("Integração da eRede", "Pedido:#".$idPedido." Código de Retorno : #".$rede_transacao->returnCode." - ".$rede_transacao->returnMessage);
                    break;

                case 56:

                    $resposta->type = "attention";
                    $resposta->message = 'Erro nos dados reportados. Confira atentamente os dados digitados.';
                    $sistema->inserirRelatorio("[REDE][ERROR] : #".$rede_transacao->returnCode." - ".$rede_transacao->returnMessage);
                    break;

                case 119:

                    $resposta->type = "attention";
                    $resposta->message = 'Atenção, o código de segurança informado é inválido, verifique os dados e tente novamente.';
                    $sistema->inserirRelatorio("[REDE][ERROR] : #".$rede_transacao->returnCode." - ".$rede_transacao->returnMessage);
                    break;

                case 122:

                    $resposta->type = "attention";
                    $resposta->message = 'Essa transação já foi enviada anteriormente, verifique seus pedidos.';
                    $sistema->inserirRelatorio("[REDE][ERROR] : #".$rede_transacao->returnCode." - ".$rede_transacao->returnMessage);
                    break;

                case 74:
                case 103:
                case 104:
                case 105:
                case 106:
                case 107:
                case 119:
                case 121:

                    $resposta->type = "attention";
                    $resposta->message = 'Pagamento não realizado, por favor confira os dados digitados e tente novamente.';
                    $sistema->inserirRelatorio("[REDE][ERROR] : #".$rede_transacao->returnCode." - ".$rede_transacao->returnMessage);
                    break;

                case 58:
                case 69:
                case 72:
                case 79:
                case 80:
                case 83:
                case 84:
                case 101:
                case 102:
                case 108:
                case 109:
                case 110:
                case 111:
                case 112:
                case 113:
                case 114:
                case 115:
                case 116:
                case 117:
                case 118:
                case 123:
                case 124:
                case 204:

                    $resposta->type = "error";
                    $resposta->message = 'Transação não autorizada, entre em contato seu emissor ou tente outro cartão.';
                    $sistema->inserirRelatorio("[REDE][UNAUTHORIZED][#".$rede_transacao->returnCode."]");
                    break;

                default:

                    $resposta->type = "error";
                    $resposta->message = 'Aconteceu algum problema, por favor, entre em contato através de um de nossos canais de comunicação';
                    $sistema->inserirRelatorio("[REDE][ERROR][#".$rede_transacao->returnCode."]");

            }

        }else if($rede_transacao->returnCode == 900){

            $resposta->type = "error";
            $resposta->message = 'Transação recusada, entre em contato seu emissor.';
            $sistema->inserirRelatorio("[REDE][DENIED]");


        }else if((int)$rede_transacao->returnCode > 900 && (int)$rede_transacao->returnCode < 1000){

            $resposta->type = "attention";
            $resposta->message = 'Erro interno em nosso sistema, por favor entre em contato conosco e informe o código #'.$rede_transacao->returnCode;
            $sistema->inserirRelatorio("[REDE][ERROR] : #".$rede_transacao->returnCode." - ".$rede_transacao->returnMessage);
            $sistema->alertaCritico("Integração da eRede", "Pedido:#".$idPedido." Código de Retorno : #".$rede_transacao->returnCode." - ".$rede_transacao->returnMessage);

        }else{

            $resposta->type = "error";
            $resposta->message = 'Transação recusada, entre em contato seu emissor.';
        }

    }else{
        $resposta->type = "error";
        $resposta->message = "Aconteceu um erro no sistema, seu pedido não foi encontrato pelo sistema!";
        $sistema->alertaCritico("Integração da eRede", "Pedido:#".$idPedido.". Retorno inesperado eRede.  Solicitar análise do arquivo scripts/form-rede-transparente.php");
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
    } /*else if ($form->Brand == "") {
        $resposta->type = "attention";
        $resposta->message = "Bandeira do seu cartão não foi identificada";
        $resposta->field = "Brand";
        $resposta->return = false;
        return $resposta;
    }*/ else if ($form->SecurityCode == "") {
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
    } else if ($form->ExpirationDate == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "ExpirationDate";
        $resposta->return = false;
        return $resposta;
    } else {
        return $resposta;
    }
}
?>