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

    $query = $sistema->DB_update($main_table, implode(',', $fields_values) . " WHERE id=" . $data->id);
    if ($query) {

        $verifica = $sistema->DB_num_rows("SELECT * FROM tb_emails_emails A INNER JOIN tb_listas_listas_has_tb_emails_emails B ON B.id_email = A.id AND A.email = '$formulario->email' AND B.id_lista = 3");
        if (!$verifica) {
            $verifica = $sistema->DB_fetch_array("SELECT * FROM tb_emails_emails WHERE email = '$formulario->email'");
            if (!$verifica->num_rows) {
                $addEmail = $sistema->DB_insert('tb_emails_emails', "nome,email", "'$formulario->nome','$formulario->email'");
                $idEmail = $addEmail->insert_id;
            } else {
                $idEmail = $verifica->rows[0]['id'];
            }
            $addListaHasEmail = $sistema->DB_insert('tb_listas_listas_has_tb_emails_emails', "id_lista,id_email", "3,$idEmail");
        }

        $resposta->type = "success";
        $resposta->time = 4000;
        $resposta->message = "Cadastro atualizado com sucesso!";
        $sistema->inserirRelatorio("Cliente atualizou dados pessoais: [" . $_SESSION['cliente_email'] . "] Id: [" . $_SESSION['cliente_id'] . "]");
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


    if ($form->pessoa == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "pessoa";
        $resposta->return = false;
        return $resposta;
    } else if ($form->pessoa == 2) {
        if ($form->razao_social == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo corretamente";
            $resposta->field = "razao_social";
            $resposta->return = false;
            return $resposta;
        } else if ($form->cnpj == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo corretamente";
            $resposta->field = "cnpj";
            $resposta->return = false;
            return $resposta;
        } else {
            return validaFormularioContinuacao($form);
        }
    } else {
        return validaFormularioContinuacao($form);
    }
}

function validaFormularioContinuacao($form) {
    $resposta = new stdClass();
    $resposta->return = true;

    global $sistema, $main_table;

    if ($form->nome == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "nome";
        $resposta->return = false;
        return $resposta;
    } else if ($form->pessoa == 1 AND $form->cpf == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "cpf";
        $resposta->return = false;
        return $resposta;
    } else if ($form->email == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "email";
        $resposta->return = false;
        return $resposta;
    } else if ($sistema->validaEmail($form->email) == 0) {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo com um E-mail válido";
        $resposta->field = "email";
        $resposta->return = false;
        return $resposta;
    } else if ($form->telefone == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "telefone";
        $resposta->return = false;
        return $resposta;
    } else {
        return $resposta;
    }
}

?>