<?php

session_start();

require_once '../sistema/System/Core/Loader.php';

use System\Core\Bootstrap;

require_once "../_system.php";

$sistema = new _sys();

$sistema->DB_connect();

$main_table = "tb_clientes_clientes";

$formulario = $sistema->formularioObjeto($_POST);

$validacao = validaFormulario($formulario);

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

    foreach ($data as $key => $value) {
        $fields_values[] = "$key='$value'";
    }

    $query = $sistema->DB_update($main_table, implode(',', $fields_values) . " WHERE id=" . $_SESSION['cliente_id']);
    if ($query) {
        $resposta->type = "success";
        $resposta->time = 4000;
        $resposta->message = "Cadastro atualizado com sucesso!";
        $sistema->inserirRelatorio("Cliente atualizou endereço: [" . $_SESSION['cliente_email'] . "] Id: [" . $_SESSION['cliente_id'] . "]");
    } else {
        $resposta->type = "error";
        $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
    }


    echo json_encode($resposta);
}

$sistema->DB_disconnect();

function validaFormulario($form) {

    $resposta = new stdClass();
    $resposta->return = true;

    //$sistema = new sistema();
    global $sistema, $main_table;
    $form->cep = str_replace("_", "", $form->cep);
    if ($form->cep == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "cep";
        $resposta->return = false;
        return $resposta;
    } else if (strlen($form->cep) != 10) {
        $resposta->type = "validation";
        $resposta->message = "Preencha todo o campo";
        $resposta->field = "cep";
        $resposta->return = false;
        return $resposta;
    } else if ($form->endereco == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "endereco";
        $resposta->return = false;
        return $resposta;
    } else if ($form->bairro == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "bairro";
        $resposta->return = false;
        return $resposta;
    } else if ($form->numero == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "numero";
        $resposta->return = false;
        return $resposta;
    } else if ($form->id_estado == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "id_estado";
        $resposta->return = false;
        return $resposta;
    } else if ($form->id_cidade == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "id_cidade";
        $resposta->return = false;
        return $resposta;
    } else {
        return $resposta;
    }
}

?>