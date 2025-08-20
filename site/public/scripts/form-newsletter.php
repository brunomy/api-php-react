<?php

session_start();

require_once '../sistema/System/Core/Loader.php';

use System\Core\Bootstrap;

require_once "../_system.php";
require '../../classes/mailchimp/Mailchimp.php';

$sistema = new _sys();

$sistema->DB_connect();

$main_table = "tb_newsletters_newsletters";

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

    $fields = array(
        'email',
        'ip',
        'session'
    );

    $values = array(
        '"' . $formulario->email . '"',
        '"' . $_SERVER['REMOTE_ADDR'] . '"',
        '"' . $_SESSION["seo_session"] . '"'
    );

    $query = $sistema->DB_insert($main_table, implode(',', $fields), implode(',', $values));
    $idContato = $query->insert_id;

    if ($query->query) {

        $verifica = $sistema->DB_num_rows("SELECT * FROM tb_emails_emails A INNER JOIN tb_listas_listas_has_tb_emails_emails B ON B.id_email = A.id AND A.email = '$formulario->email' AND B.id_lista = 2");
        if (!$verifica) {
            $verifica = $sistema->DB_fetch_array("SELECT * FROM tb_emails_emails WHERE email = '$formulario->email'");
            if (!$verifica->num_rows) {
                $addEmail = $sistema->DB_insert('tb_emails_emails', "email", "'$formulario->email'");
                $idEmail = $addEmail->insert_id;
            } else {
                $idEmail = $verifica->rows[0]['id'];
            }
            $addListaHasEmail = $sistema->DB_insert('tb_listas_listas_has_tb_emails_emails', "id_lista,id_email", "2,$idEmail");
        }

        //envia e-mail para cliente
        $to[] = array("email" => $formulario->email, "nome" => "");
        $assunto = "Bem vindo à lista VIP da Real Poker";  // Assunto da mensagem de contato.

        $body = file_get_contents("../mailing_templates/email_cadastro_cliente.html");
        $body = str_replace("{EMAIL}", $formulario->email, $body);

        $sistema->enviarEmail($to, '', utf8_decode($assunto), utf8_decode($body));

        unset($to);

        //envia e-mail para notificados
        $emails = $sistema->DB_fetch_array("SELECT C.email, C.nome FROM tb_admin_forms A INNER JOIN tb_admin_email_notification B ON A.id = B.id_form INNER JOIN tb_admin_users C ON B.id_user = C.id WHERE A.id = 1 OR A.id = 3 GROUP BY B.id_user");

        if ($emails->num_rows) {

            foreach ($emails->rows as $mail) {
                $to[] = array("email" => $mail['email'], "nome" => utf8_decode($mail['nome']));
            }

            $assunto = "Formulário Newsletter.";  // Assunto da mensagem de contato.

            $body = file_get_contents("../mailing_templates/form_newsletter.html");
            $body = str_replace("{EMAIL}", $formulario->email, $body);

            $sistema->enviarEmail($to, $formulario->email, utf8_decode($assunto), utf8_decode($body));
        }

        $resposta->type = "success";
        $resposta->time = 4000;
        $resposta->message = "E-mail cadastrado com sucesso!";



        /*define('MAILCHIMP_API_KEY', '24604decb77b9ab290cea6eec4f40d66-us7'); // Sua chave da API
        define('MAILCHIMP_LIST_ID', 'fdfc9dd9dd'); // O ID da sua lista



        try {
            $mailchimp = new Mailchimp(MAILCHIMP_API_KEY);
            $lists = new Mailchimp_Lists($mailchimp);
            $email = array(
                'email' => $formulario->email
            );
            $lists->subscribe(
                    MAILCHIMP_LIST_ID, // List ID
                    $email, // Subscriber ID, his/her email
                    $merge, // Custom fields
                    'html', // E-mail type
                    false              // Confirm subscription by email (double opt-in)?
            );
        } catch (Mailchimp_List_AlreadySubscribed $e) {
            $resposta->type = "success";
            $resposta->message = "Já está registrado em nossa base.";
        } catch (Mailchimp_Email_AlreadySubscribed $e) {
            $resposta->type = "success";
            $resposta->message = "Este e-mail já está registrado em nossa base.";
        } catch (Mailchimp_Email_NotExists $e) {
            $resposta->type = "error";
            $resposta->message = "Este e-mail não existe.";
        } catch (Mailchimp_Invalid_Email $e) {
            $resposta->type = "error";
            $resposta->message = "Este e-mail é inválido.";
        } catch (Mailchimp_List_InvalidImport $e) {
            $resposta->type = "error";
            $resposta->message = "Este e-mail provavelmente é um e-mail inválido.";
        } catch (Exception $e) {
            $resposta->type = "error";
            $resposta->message = $e->getMessage(); // Do not show it to the user
        }
        */



        $sistema->inserirRelatorio("Newsletter: [" . $formulario->email . "] Id: [" . $idContato . "]");
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


    if ($form->email == "") {
        $resposta->type = "attention";
        $resposta->message = "Preencha o campo com seu e-mail";
        $resposta->field = "email";
        $resposta->return = false;
        return $resposta;
    } else if ($sistema->validaEmail($form->email) == 0) {
        $resposta->type = "attention";
        $resposta->message = "E-mail inválido, verifique a digitação e tente novamente.";
        $resposta->field = "email";
        $resposta->return = false;
        return $resposta;
    } else {
        return $resposta;
    }
}

?>