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

    $data->senha = $sistema->embaralhar($data->senha);


    $data->code = "";

    if (isset($formulario->code) && $formulario->code != '')
        $query = $sistema->DB_update($main_table, " senha = '$data->senha' WHERE email = '{$_SESSION['cliente_email']}' OR code = '{$formulario->code}'");
    else
        $query = $sistema->DB_update($main_table, " senha = '$data->senha' WHERE email = '{$_SESSION['cliente_email']}'");
    if ($query) {
        $resposta->type = "success";
        $resposta->time = 4000;
        $resposta->redirect = "";
        if ($formulario->code != "")
            $resposta->redirect = $sistema->root_path;
        $resposta->message = "Senha atualizada com sucesso!";
        $sistema->inserirRelatorio("Cliente alterou senha: [" . $_SESSION['cliente_email'] . "] Id: [" . $_SESSION['cliente_id'] . "]");
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

    global $sistema, $main_table;

    if ($form->senha == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "senha";
        $resposta->return = false;
        return $resposta;
    } else if ($form->senha != $form->senha2) {
        $resposta->type = "validation";
        $resposta->message = "As senhas não conferem";
        $resposta->field = "senha";
        $resposta->return = false;
        return $resposta;
    } else {
        return $resposta;
    }
}

?>