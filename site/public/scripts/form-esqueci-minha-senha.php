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

    $data = new stdClass();

    $data->code = $sistema->criptPass(uniqid());

    foreach ($data as $key => $value) {
        $fields_values[] = "$key='$value'";
    }

    $query = $sistema->DB_update($main_table, implode(',', $fields_values) . " WHERE email = '{$formulario->email}'");
    if ($query) {
        
        $query = $sistema->DB_fetch_array("SELECT * FROM $main_table WHERE email = '{$formulario->email}'");
        
        // PREPARA EMAIL -------------------
        $assunto = "Esqueci Minha Senha. [$formulario->email]";  //Assunto da mensagem de contato.

        $mensagem = "Olá, {$query->rows[0]['nome']}!<br>Para alterar sua senha clique <a href='" . $sistema->root_path . "senha/?code=" . $data->code . "'> aqui</a>!<br><br>Caso não tenha solicitado esse serviço, por favor, desconsidere esse e-mail!";

        $body = file_get_contents("../mailing_templates/form_esqueci_minha_senha.html");
        $body = str_replace("{LINK}", $mensagem, $body);

        $to[] = array("email" => $formulario->email, "nome" => utf8_decode(''));

        $sistema->enviarEmail($to, '', utf8_decode($assunto), utf8_decode($body));

        $resposta->type = "success";
        $resposta->time = 4000;
        $resposta->message = "Informações para recuperar sua senha foram enviadas para $formulario->email!";
        $sistema->inserirRelatorio("Esqueci minha senha: [" . $formulario->email . "] Id: [" . $query->rows[0]['id'] . "]");
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

    if ($form->email == "") {
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
    } else {
        return $resposta;
    }
}

?>