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

    $data->ip = $_SERVER['REMOTE_ADDR'];
    $data->session = $_SESSION["seo_session"];
    $data->senha = $sistema->embaralhar($data->senha);
    $data->stats = 1;

    $newNumberFormat = $data->telefone;
    if($data->telefone){
        list($ddd, $numero) =  explode(' ', $data->telefone);

        $numero = str_replace('_', '', $numero);
        if(strlen($numero) == 9){
            $newNumberFormat = $ddd . ' ' . substr($numero, 1);
        } else if(strlen($numero) == 8){
            $newNumberFormat = $ddd . ' ' . '9' . $numero;
        }
    }

    $query = $sistema->DB_fetch_array("SELECT * FROM $main_table WHERE (( email != '' and email = '".$data->email."') or (cpf != '' and cpf = '".$data->cpf."') or (cnpj != '' and cnpj = '".$data->cnpj."') or (telefone != '' and telefone = '".$data->telefone."' ) or (telefone != '' and telefone = '".$newNumberFormat."'))");

    $positionEmail = 0;

    if($query->num_rows > 1){
        foreach ($query->rows as $key => $result){
            if($result['email'] == $data->email){
                $positionEmail = $key;
                break;
            }
        }

    }
    if($query->num_rows and $query->rows[$positionEmail]['senha'] != ''){
        $resposta->type = "attention";
        $resposta->message = "Este e-mail já está cadastrado em nossa base.";
        echo json_encode($resposta);
        exit();
    }

    if($query->num_rows and $query->rows[$positionEmail]['senha'] == '') {
        foreach ($data as $key => $value) {
            if ($value == "NULL") {
                $fields_values[] = "$key=$value";
            } else {
                $fields_values[] = "$key='$value'";
            }
        }

        $query = $sistema->DB_update($main_table, implode(',', $fields_values) . " WHERE id=" . $query->rows[$positionEmail]['id']);
        $idCliente = $query->rows[$positionEmail]['id'];
    } else {
        foreach ($data as $key => $value) {
            $fields[] = $key;
            $values[] = "'$value'";
        }

        $query = $sistema->DB_insert($main_table, implode(',', $fields), implode(',', $values));
        $idCliente = $query->insert_id;
        $query = $query->query;
    }



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


        // PREPARA EMAIL -------------------
        $emails = $sistema->DB_fetch_array("SELECT C.email, C.nome FROM tb_admin_forms A INNER JOIN tb_admin_email_notification B ON A.id = B.id_form INNER JOIN tb_admin_users C ON B.id_user = C.id WHERE A.id = 1 OR A.id = 4 GROUP BY B.id_user");

        if ($emails->num_rows) {

            foreach ($emails->rows as $mail) {
                $to[] = array("email" => $mail['email'], "nome" => utf8_decode($mail['nome']));
            }

            $cidades = $sistema->DB_fetch_array("SELECT cidade FROM tb_utils_cidades WHERE id = $data->id_cidade");
            $cidade = $cidades->rows[0]['cidade'];


            $estados = $sistema->DB_fetch_array("SELECT estado FROM tb_utils_estados WHERE id = $data->id_estado");
            $estado = $estados->rows[0]['estado'];

            $assunto = "Formulário Cadastro de Cliente [$formulario->nome]";  // Assunto da mensagem de contato.

            if ($formulario->pessoa == 1)
                $pessoa = "Pessoa Física";
            else
                $pessoa = "Pessoa Jurídica";

            $body = file_get_contents("../mailing_templates/form_cadastro.html");
            $body = str_replace("{PESSOA}", $pessoa, $body);
            $body = str_replace("{NOME}", $formulario->nome, $body);
            $body = str_replace("{RAZAO_SOCIAL}", $formulario->razao_social, $body);
            $body = str_replace("{CNPJ}", $formulario->cnpj, $body);
            $body = str_replace("{INSCRICAO_ESTADUAL}", $formulario->inscricao_estadual, $body);
            $body = str_replace("{EMAIL}", $formulario->email, $body);
            $body = str_replace("{TELEFONE}", $formulario->telefone, $body);
            $body = str_replace("{CPF}", $formulario->cpf, $body);
            $body = str_replace("{CEP}", $formulario->cep, $body);
            $body = str_replace("{ENDERECO}", $formulario->endereco, $body);
            $body = str_replace("{NUMERO}", $formulario->numero, $body);
            $body = str_replace("{BAIRRO}", $formulario->bairro, $body);
            $body = str_replace("{COMPLEMENTO}", $formulario->complemento, $body);
            $body = str_replace("{CIDADE}", $cidade, $body);
            $body = str_replace("{ESTADO}", $estado, $body);

            $sistema->enviarEmail($to, $formulario->email, utf8_decode($assunto), utf8_decode($body));
        }


        unset($to);
        //EMAIL PARA CLIENTE
        $to[] = array("email" => $formulario->email, "nome" => utf8_decode($formulario->nome));

        $mensagem_cliente = "Você está recebendo este e-mail pois se cadastrou no site $sistema->root_path.<br><br> Caso não tenha realizado este cadastro por favor desconsidere este e-mail!";

        $assunto = "Formulário Cadastro de Cliente [$formulario->nome]";  // Assunto da mensagem de contato.

        $body = file_get_contents("../mailing_templates/form_cadastro_cliente.html");
        $body = str_replace("{MENSAGEM_CLIENTE}", $mensagem_cliente, $body);
        $body = str_replace("{PESSOA}", $pessoa, $body);
        $body = str_replace("{NOME}", $formulario->nome, $body);
        $body = str_replace("{RAZAO_SOCIAL}", $formulario->razao_social, $body);
        $body = str_replace("{CNPJ}", $formulario->cnpj, $body);
        $body = str_replace("{INSCRICAO_ESTADUAL}", $formulario->inscricao_estadual, $body);
        $body = str_replace("{EMAIL}", $formulario->email, $body);
        $body = str_replace("{TELEFONE}", $formulario->telefone, $body);
        $body = str_replace("{CPF}", $formulario->cpf, $body);
        $body = str_replace("{CEP}", $formulario->cep, $body);
        $body = str_replace("{ENDERECO}", $formulario->endereco, $body);
        $body = str_replace("{NUMERO}", $formulario->numero, $body);
        $body = str_replace("{BAIRRO}", $formulario->bairro, $body);
        $body = str_replace("{COMPLEMENTO}", $formulario->complemento, $body);
        $body = str_replace("{CIDADE}", $cidade, $body);
        $body = str_replace("{ESTADO}", $estado, $body);

        $sistema->enviarEmail($to, '', utf8_decode($assunto), utf8_decode($body));


        $sistema->DB_update("tb_seo_acessos", "cadastro = 1 WHERE id_seo = $formulario->id_seo AND session = '{$_SESSION["seo_session"]}' ORDER BY id DESC LIMIT 1");
        $sistema->DB_update("tb_seo_acessos_historicos", "cadastro = 1 WHERE id_seo = $formulario->id_seo AND session = '{$_SESSION["seo_session"]}' ORDER BY id DESC LIMIT 1");

        $_SESSION['cliente_logado'] = true;
        $_SESSION['cliente_id'] = $idCliente;
        $_SESSION['cliente_nome'] = $formulario->nome;
        $_SESSION['cliente_email'] = $formulario->email;
        $_SESSION['cliente_cep'] = $formulario->cep;
        $_SESSION['cliente_endereco'] = $formulario->endereco;
        $_SESSION['cliente_numero'] = $formulario->numero;
        $_SESSION['cliente_bairro'] = $formulario->bairro;
        $_SESSION['cliente_complemento'] = $formulario->complemento;
        $_SESSION['cliente_id_cidade'] = $data->id_cidade;
        $_SESSION['cliente_id_estado'] = $data->id_estado;
        $_SESSION['cep'] = $formulario->cep;

        $resposta->type = "success";
        $resposta->time = 4000;
        $resposta->message = "Cadastrado com sucesso!";
        $sistema->inserirRelatorio("Cliente: [" . $formulario->email . "] Id: [" . $idCliente . "]");
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
        } else if (!$sistema->validaCNPJ($form->cnpj)) {
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
    $form->cep = str_replace("_", "", $form->cep);
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
    } else if ($form->pessoa == 1 AND !$sistema->validaCPF($form->cpf)) {
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
    } else if ($form->senha == "") {
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
    } else if ($form->cep == "") {
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
    }else if ($form->id_estado == "") {
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
