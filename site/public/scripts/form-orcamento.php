<?php

session_start();

require_once '../sistema/System/Core/Loader.php';

use System\Core\Bootstrap;
use classes\Product;

require_once "../_system.php";

$sistema = new Product();

$sistema->DB_connect();

$main_table = "tb_pedidos_pedidos";

$sys = new _sys();

$formulario = $sistema->formularioObjeto($_POST);

$validacao = valida($formulario);

$crop_sizes = array();

if (!isset($_SESSION["seo_session"])) {
    $_SESSION["seo_session"] = uniqid();
}

if (!$validacao->return) {
    echo json_encode($validacao);
} else {
    $resposta = new stdClass();
    $resposta->time = 4000;

    $data = $sistema->formularioObjeto($_POST, $main_table);

    $data->orc_id_crm = $formulario->orc_id_crm;
    $data->orc_status = 'aberto';
    $data->orc_etapa = 'orcamento';

    $crm = $sistema->DB_fetch_array("SELECT * FROM tb_crm_crm A WHERE A.id = $data->orc_id_crm LIMIT 1");
    if($crm->num_rows) {

        if($crm->rows[0]['possui_orcamento'] == 1) {
            unset($crm->rows[0]['id']);
            unset($crm->rows[0]['data']);
            unset($crm->rows[0]['ultima_atualizacao']);
            unset($crm->rows[0]['finalizado']);
            $crm->rows[0]['possui_orcamento'] = 0;
            foreach ($crm->rows[0] as $key => $value) {
                $fields[] = $key;
                $values[] = "'$value'";
            }

            $query = $sistema->DB_insert("tb_crm_crm", implode(',', $fields), implode(',', $values));
            unset($fields);
            unset($values);
            $data->orc_id_crm = $query->insert_id;

        }


    }


    $vendedor = $sistema->DB_fetch_array("SELECT A.id_user, B.nome, B.email, A.id_cliente FROM tb_crm_crm A INNER JOIN tb_admin_users B ON B.id = A.id_user WHERE A.id = $data->orc_id_crm LIMIT 1");
    if ($vendedor->num_rows) {
        $sistema->inserirRelatorio('atendente '.$vendedor->rows[0]['nome'].' vinculado à orçamento pelo CRM');
        $data->id_vendedor = (int) $vendedor->rows[0]['id_user'];
        $data->agendor = 1;
        $data->id_cliente = $vendedor->rows[0]['id_cliente'];

    } else {
        //$data->id_vendedor = NULL;
    }

    $data->porcentagem_comissao = $sistema->_empresa['comissao_vendas'];

    $data->valor_cupom = 0;
    $data->tipo_cupom = 0;
    $data->subtotal = "";
    $data->descontos = "";

    if (isset($_SESSION['cupom']['valor']) && $_SESSION['cupom']['valor'] != "")
        $data->valor_cupom = $_SESSION['cupom']['valor'];

    if (isset($_SESSION['cupom']['tipo_int']) && $_SESSION['cupom']['tipo_int'] != "")
        $data->tipo_cupom = $_SESSION['cupom']['tipo_int'];

    if (isset($_SESSION['cupom']['mensagem']) && $_SESSION['cupom']['mensagem'] != "")
        $data->mensagem_cupom = $_SESSION['cupom']['mensagem'];

    $data->frete = null;
    $data->valor_frete = 0;

    if (isset($formulario->frete_nome) && $formulario->frete_nome != "") {
        $data->frete = $formulario->frete_nome;
    }

    if (isset($formulario->frete_valor) && $formulario->frete_valor != "") {
        $data->valor_frete = (float) $formulario->frete_valor;
    }

    if (isset($formulario->frete_prazo) && $formulario->frete_prazo != "")
        $data->prazo_entrega = date('Y-m-d', strtotime("+$formulario->frete_prazo days", strtotime(date('Y-m-d'))));
    else
        $data->prazo_entrega = date('Y-m-d');

    $dataAtual = date("Y-m-d");
    $date = new DateTime($dataAtual);
    $date2 = new DateTime($data->prazo_entrega);
    $intervalo = $date->diff($date2);

    $data->dias_entrega = $intervalo->d;

    $data->code = $sistema->uniqueNumber(10, "tb_pedidos_pedidos", "code");

    $sistema->calcAction();
    $info = $sistema->calcTotalCarrinhoBySession();
    if (isset($info['valor']))
        $data->subtotal = $info['valor'];

    if (isset($info['desconto']))
        $data->descontos = $info['desconto'];

    // TODO: FIX - Cupom de porcentagem tem o valor alterado pelo frete
    $valor_final = $data->subtotal + $data->valor_frete - $data->descontos;

    if ($data->tipo_cupom != 1)
        $valor_cupom = $data->valor_cupom;
    else
        $valor_cupom = (($valor_final * $data->valor_cupom) / 100);

    $data->valor_final = $valor_final- $valor_cupom;

    $data->ip = $_SERVER['REMOTE_ADDR'];
    $data->session = $_SESSION["seo_session"];

    foreach ($data as $key => $value) {
        $fields[] = $key;
        $values[] = "'$value'";
    }

    //echo "<pre>";print_r($fields);echo "</pre>";
    //echo "<pre>";print_r($values);echo "</pre>";
    //if($cliente['email']!='joao@hibrida.biz'){
        $query = $sistema->DB_insert($main_table, implode(',', $fields), implode(',', $values));
        $idOrcamento = $query->insert_id;
    //}

    unset($fields, $values);
    if ($query->query) {

        $sistema->DB_update("tb_carrinho_produtos_historico", "id_pedido = $idOrcamento WHERE session = '{$_SESSION["seo_session"]}' AND id_pedido IS NULL");

        // SETA O VALOR DO FRETE EMBUTIDO NOS PRODUTOS DO ORÇAMENTO
        $sistema->mysqli->query('UPDATE tb_carrinho_produtos_historico a INNER JOIN tb_produtos_produtos b ON a.id_produto = b.id SET a.frete_embutido = b.frete_embutido WHERE a.id_pedido = '.$idOrcamento);

        $sistema->calcAction();

        $sistema->clearCupomAction();

        // atualizando crm informando que possui orçamento
        $fields_values[] = "possui_orcamento=1";
        $query = $sistema->DB_update('tb_crm_crm',
            implode(',', $fields_values) . " WHERE id=" . $data->orc_id_crm);

        // $resposta->metodo = $formulario->metodo_pagamento;
        $resposta->orcamento = $idOrcamento;
        // $resposta->id_cliente = $_SESSION['cliente_id'];
        $resposta->type = "success";
        $resposta->time = 4000;
        $resposta->message = "Orçamento gerado com sucesso!!";
        $sistema->inserirRelatorio("Orçamento gerado id: [" . $idOrcamento . "]");
    } else {
        $resposta->type = "error";
        $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
    }

    echo json_encode($resposta);
}

$sistema->DB_disconnect();

function valida($form) {
    $resposta = new stdClass();
    $resposta->return = true;

    global $sistema, $main_table;

    if (!isset($form->orc_id_crm) || $form->orc_id_crm == '') {
        $resposta->type = 'attention';
        $resposta->message = 'Selecione um atendimento para o orçamento';
        $resposta->field = 'orc_id_crm';
        $resposta->return = false;
        return $resposta;
    } else {
        return $resposta;
    }
}

?>
