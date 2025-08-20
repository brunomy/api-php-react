<?php

session_start();
$_SESSION["cotacaoDolarOff"] = true;


if (!isset($_SESSION["seo_session"])) {
    $_SESSION["seo_session"] = uniqid();
}

require_once '../../sistema/System/Core/Loader.php';

use System\Core\Bootstrap;
use classes\Product;

include "../../_system.php";

$sistema = new _sys();
$product = new Product();

function getMensagemByStatus($id) {
    global $product, $sistema;
    $query = $product->DB_fetch_array("SELECT A.*, C.nome nome_cielo, C.id id_cielo FROM tb_pedidos_status A INNER JOIN tb_pedidos_status_has_tb_pedidos_status_cielo B ON B.id_pedido_status = A.id INNER JOIN tb_pedidos_status_cielo C ON C.id = B.id_cielo_status WHERE B.id_cielo_status = $id");
    if ($query)
        return $query->rows[0];
}

function retorno_automatico($transacao) {
    ob_start();

    global $product, $sistema;

    $pedido = $product->pedidoByOrderNumberCielo($transacao['order_number'], ((float) $transacao['amount']) / 100);


    if (!$pedido->id)
        exit;

    $usuario = $product->usuarioById($pedido->id_cliente);

    $product->encode($usuario);

    // Remove a flag atual de todos os status da transação, para cadastrar o novo status
    $statusZerado = $product->zerarStatusTransacaoCielo($transacao);

    $transacao['dt_hora'] = date('Y-m-d H:i:s');

    $statusCadastrado = $product->cadastrarStatusTransacaoCielo($transacao);

    $formPedido = new \stdClass();
    $formPedido->id = $pedido->id;
    $formPedido->metodo_pagamento_id = $transacao['checkout_cielo_order_number'];

    switch ($transacao["payment_method_type"]) {

        case '1':
            $meioPagto = 'Cartão de Crédito';
            break;

        case '2':
            $meioPagto = 'Boleto Bancário';
            break;

        case '3':
            $meioPagto = 'Débito Online';
            break;

        case '4':
            $meioPagto = 'Cartão de Débito';
            break;

        default:

            $meioPagto = '';

            break;
    }

    switch ($transacao["payment_method_brand"]) {

        case '1':
            $cartaoCredito = 'Visa';
            break;

        case '2':
            $cartaoCredito = 'Mastercad';
            break;

        case '3':
            $cartaoCredito = 'AmericanExpress';
            break;

        case '4':
            $cartaoCredito = 'Diners';
            break;

        case '5':
            $cartaoCredito = 'Elo';
            break;

        case '6':
            $cartaoCredito = 'Aura';
            break;

        case '7':
            $cartaoCredito = 'JCB';
            break;

        default:

            $cartaoCredito = '';

            break;
    }

    if ($meioPagto || $cartaoCredito) {

        $formPedido->tipo_pagamento = $meioPagto . ' - ' . $cartaoCredito;
    }

    $product->decode($formPedido);

    $product->alterarStatusPagamento($formPedido);

    $transacao['pedido_id'] = $pedido->id;

    $cadastroTransacao = $product->cadastrarTransacaoCielo($transacao);

    $arquivo = "../../mailing_templates/email/compra.php";
    $fp = fopen($arquivo, "r");
    $mensagem = fread($fp, filesize($arquivo));
    @fclose($arquivo);

    $form = new \stdClass;

    $infostatus = getMensagemByStatus($transacao['payment_status']);
    $statusPtbr = $infostatus['nome_cielo'];
    $assunto = $infostatus['assunto'] . " - Pedido #{$pedido->id}";


    $infostatus['mensagem'] = $sistema->trataTextoNotificar($infostatus['mensagem']);

    $infostatus['mensagem'] = str_replace("[({ID})]", $pedido->id, $infostatus['mensagem']);
    $infostatus['mensagem'] = str_replace("[({NOME})]", utf8_decode($usuario->nome), $infostatus['mensagem']);

    $msg = $infostatus['mensagem'];

    $form->id = $pedido->id;
    $form->id_status = $infostatus['id'];
    $form->status_pagamento = $infostatus['id_cielo'];
    $product->alterarStatusPagamento($form);

    //$sistema->inserirRelatorio('Debug cielo enviar enviar 1');
    if ($infostatus['enviar_email']) {

        //$sistema->inserirRelatorio('Debug cielo enviar enviar 2');

        $to[] = array("email" => $usuario->email, "nome" => utf8_decode($usuario->nome));

        $emails = $sistema->DB_fetch_array("SELECT A.nome, A.email FROM tb_admin_users A INNER JOIN tb_pedidos_status_has_users_notification B ON B.id_usuario = A.id AND B.id_pedido_status = {$infostatus['id']}");
        if ($emails->num_rows) {
            if ($emails->num_rows) {
                foreach ($emails->rows as $mail) {
                    $to[] = array("email" => $mail['email'], "nome" => utf8_decode($mail['nome']));
                }
            }
        }
        
        $setFrom = '';
        $notificar_vendedor = $sistema->DB_fetch_array('SELECT * FROM tb_pedidos_pedidos WHERE id = '.$pedido->id.' AND id_vendedor IS NOT NULL AND id_vendedor <> 0');
        if($notificar_vendedor->num_rows){
            //$sistema->inserirRelatorio('Debug cielo enviar enviar 3');
            $vendedor = $sistema->DB_fetch_array("SELECT * FROM tb_admin_users WHERE id = ".$notificar_vendedor->rows[0]['id_vendedor']." AND stats = 1");
            if($vendedor->num_rows){
                //$sistema->inserirRelatorio('Debug cielo enviar enviar 4');
                $setFrom = array(array('email'=>$vendedor->rows[0]['email'], 'nome'=>'Real Poker'));
                if($infostatus['notificar_vendedor']==1){
                    //$sistema->inserirRelatorio('Debug cielo enviar enviar 5');
                    $to[] = array("email" => $vendedor->rows[0]['email'], "nome" => utf8_decode($vendedor->rows[0]['nome']));
                }
            }
        }

        $sistema->inserirRelatorio('Debug cielo enviar enviar 6');

        $mensagem = str_replace("[[corpo]]", $msg, $mensagem);


        $sistema->enviarEmail($to, '', utf8_decode($assunto), utf8_decode($mensagem));
    }
    $sistema->inserirRelatorio('Debug cielo enviar enviar 7');

    if ($cadastroTransacao->result) {
        echo '<status>OK</status>';
        $sistema->inserirRelatorio("retorno finished");
        exit;
    }
}

class RetornoCielo {

    function verifica($post) {

        $confirma = true;

        retorno_automatico(
                $post
        );



        return $confirma;
    }

}

if ($_POST) {

    $sistema->inserirRelatorio("retorno started: " . json_encode($_POST));

    $cielo = new RetornoCielo();
    $cielo->verifica($_POST);

    die();
}
?>
