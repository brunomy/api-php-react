<?php

session_start();

require_once '../sistema/System/Core/Loader.php';

use System\Core\Bootstrap;

require_once "../_system.php";

$sistema = new _sys();

$sistema->DB_connect();

$formulario = $sistema->formularioObjeto($_POST);
$validacao = validaFormulario($formulario);

if (!isset($_SESSION["seo_session"])) {
    $_SESSION["seo_session"] = uniqid();
}


if (!$validacao->return) {
    echo json_encode($validacao);
} else {

    $resposta = new stdClass();
    $resposta->time = 4000;

    $query = $sistema->DB_fetch_array("SELECT * FROM tb_clientes_clientes WHERE id=".$_SESSION['cliente_id']);
    $cliente = $query->rows[0];

    $query = $sistema->DB_update("tb_clientes_clientes","deleted_at=NOW(), deleted_name='".$cliente['nome']."', deleted_email='".$cliente['email']."', deleted_phone='".$cliente['telefone']."', deleted_cpf='".$cliente['cpf']."', deleted_cnpj='".$cliente['cnpj']."', nome='anonimizado', email='anonimizado', telefone='anonimizado', cpf='anonimizado', cnpj='anonimizado' WHERE id = ".$cliente['id']);

    if ($query) {
        $resposta->type = "success";
        $resposta->time = 4000;
        $resposta->message = "Seus dados foram excuídos com sucesso.";
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
    $resposta->time = 5000;

    //$sistema = new sistema();
    global $sistema, $main_table;


    $query = $sistema->DB_fetch_array("SELECT * FROM tb_clientes_clientes WHERE id = {$_SESSION['cliente_id']}");
    $dados = $query->rows[0];
    $hash = sha1($form->email.$dados['id']);

    if ($form->email == "") {
        $resposta->type = "attention";
        $resposta->message = "Preencha o campo com seu e-mail";
        $resposta->return = false;
        $resposta->email = $form->email.' '.$form->hash;
        return $resposta;
    } else if ($sistema->validaEmail($form->email) == 0) {
        $resposta->type = "attention";
        $resposta->message = "E-mail inválido, verifique a digitação e tente novamente.";
        $resposta->return = false;
        return $resposta;
    } else if ($form->hash != $hash) {
        $resposta->type = "attention";
        $resposta->message = "Identificação inválida. Tente novamente.";
        $resposta->return = false;
        $resposta->hash1 = $form->hash;;
        $resposta->hash2 = $hash;
        return $resposta;
    } else {
        return $resposta;
    }
}

?>