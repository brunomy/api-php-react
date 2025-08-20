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

    $fields_values[] = "ultimo_acesso='" . date("Y-m-d H:i:s") . "'";

    $query = $sistema->DB_update($main_table, implode(',', $fields_values) . " WHERE email = '$formulario->email'");

    if ($query) {

        $usuarios = $sistema->DB_fetch_array("SELECT * FROM tb_clientes_clientes WHERE email = '$formulario->email'");
        $usuario = $usuarios->rows[0];

        $_SESSION['cliente_logado'] = true;
        $_SESSION['cliente_id'] = $usuario['id'];
        $_SESSION['cliente_nome'] = $usuario['nome'];
        $_SESSION['cliente_email'] = $usuario['email'];
        $_SESSION['cliente_cep'] = $usuario['cep'];
        $_SESSION['cliente_endereco'] = $usuario['endereco'];
        $_SESSION['cliente_numero'] = $usuario['numero'];
        $_SESSION['cliente_bairro'] = $usuario['bairro'];
        $_SESSION['cliente_complemento'] = $usuario['complemento'];
        $_SESSION['cliente_id_cidade'] = $usuario['id_cidade'];
        $_SESSION['cliente_id_estado'] = $usuario['id_estado'];
        $_SESSION['cep'] = $usuario['cep'];

        $resposta->type = "success";
        $resposta->time = 4000;
        $resposta->message = "{$_SESSION['cliente_nome']}, Seja bem vindo(a)!";
        $sistema->inserirRelatorio("Cliente logou: [" . $_SESSION['cliente_email'] . "] Id: [" . $_SESSION['cliente_id'] . "]");
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

    //if (isset($form->email) && $form->email != "" && $sistema->validaEmail($form->email) == 1)
    if (isset($form->email) && $form->email != "")
        $cliente = $sistema->DB_fetch_array("SELECT * FROM $main_table WHERE email = '$form->email'");


    if ($form->email == "") {
        $resposta->type = "attention";
        $resposta->message = "Preencha seu e-mail!";
        $resposta->field = "email";
        $resposta->return = false;
        return $resposta;
    } else if ($sistema->validaEmail($form->email) == 0) {
        $resposta->type = "attention";
        $resposta->message = "E-mail inválido";
        $resposta->field = "email";
        $resposta->return = false;
        return $resposta;
    } else if (!$cliente->num_rows) {
        $resposta->type = "attention";
        $resposta->message = "Cadastro não localizado";
        $resposta->field = "email";
        $resposta->return = false;
        return $resposta;
    } else if ($form->senha == "") {
        $resposta->type = "attention";
        $resposta->message = "Preencha sua senha";
        $resposta->field = "senha";
        $resposta->return = false;
        return $resposta;
    } else if ($cliente->num_rows && $form->senha != $sistema->desembaralhar($cliente->rows[0]['senha'])) {
        $resposta->type = "attention";
        $resposta->message = "Senha não confere";
        $resposta->field = "senha";
        $resposta->return = false;
        return $resposta;
    } else {
        return $resposta;
    }
}

?>