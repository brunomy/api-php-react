<?php
session_start();
require_once '../../System/Core/Loader.php';
use System\Core\System;

$sistema = new System();

$formulario = $sistema->formularioObjeto($_POST);
$validacao = validaFormulario($formulario);

if (!$validacao->return) {
    echo json_encode($validacao);
} else {
    $resposta = new stdClass();

    $dados = $sistema->DB_fetch_array("SELECT * FROM tb_admin_users WHERE user_key='" . $formulario->user_key . "'");
    if ($dados->num_rows) {
        if ($dados->rows[0]["stats"] == 1) {

            $_SESSION['admin_logado'] = true;
            $_SESSION['admin_nome'] = $dados->rows[0]["nome"];
            $_SESSION['admin_id'] = $dados->rows[0]["id"];
            $_SESSION['admin_grupo'] = $dados->rows[0]["id_grupo"];

            if (!isset($_SESSION["login_session"])) {
                $_SESSION["login_session"] = uniqid();
            }

            $sistema->inserirRelatorio("[Login Ponto]");

            $resposta->type = "success";
            $resposta->senha = $sistema->desembaralhar($dados->rows[0]['senha']);
            $resposta->message = "Olá " . $dados->rows[0]["nome"]. ". Aguarde um momento.";

        } else {

            $resposta->type = "attention";
            $resposta->message = "Este usuário encontra-se bloqueado";

        }
    }else{
        $resposta->type = "error";
        $resposta->message = "Usuário não encontrado.";
    }

    echo json_encode($resposta);
}


function validaFormulario($form) {

    $resposta = new stdClass();
    $resposta->return = true;

    if ($form->user_key == "") {
        $resposta->type = "attention";
        $resposta->message = "Preencha o campo em vermelho com seu login";
        $resposta->field = "user_key";
        $resposta->return = false;
        return $resposta;
    } else {
        return $resposta;
    }

}