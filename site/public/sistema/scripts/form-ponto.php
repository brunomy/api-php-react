<?php

session_start();

require_once '../System/Core/Loader.php';

use System\Core\System;

$sistema = new System();

if (!$_SESSION['admin_logado'])
    header("Location: " . $sistema->system_path . "login");

$sistema->DB_connect();

$main_table = "tb_ponto_eletronico";

$resposta = new stdClass();
$resposta->action = "";

$funcionario = $sistema->DB_fetch_array("SELECT * FROM tb_admin_users A WHERE A.id = {$_SESSION['admin_id']} AND A.stats = 1");
if (!$funcionario->num_rows) {
    $resposta->time = 6000;
    $resposta->type = "error";
    $resposta->action = "reload";
    $resposta->message = "Não foi possível lhe identificar, favor tente novamente mais tarde!";
    echo json_encode($resposta);
    exit;
} else {
    $funcionario = $funcionario->rows[0];
}

$formulario = $sistema->formularioObjeto($_POST);

if (!isset($_SESSION["seo_session"])) {
    $_SESSION["seo_session"] = uniqid();
}

if ($formulario->senha == $sistema->desembaralhar($funcionario['senha'])) {

    $faltas = $sistema->DB_fetch_array("SELECT * FROM tb_ponto_eletronico_faltas A WHERE A.id_user = {$funcionario['id']} AND A.data = CURDATE()");

    if(!$faltas->num_rows){

        $pontos = $sistema->DB_fetch_array("SELECT DATE_FORMAT(A.data, '%d/%m/%Y - %H:%i:%s') data, DATE_FORMAT(A.data, '%H:%i:%s') hora, identificador FROM tb_ponto_eletronico A WHERE A.id_user = {$funcionario['id']} AND DATE(A.data) = CURDATE() ORDER BY A.data DESC");


        if ($funcionario['almoco'] == 1) {
            $hour = $sistema->DB_fetch_array("SELECT SEC_TO_TIME((((TIME_TO_SEC( A.hora_almoco ) - TIME_TO_SEC( A.hora_entrada )) + (TIME_TO_SEC( A.hora_saida ) - TIME_TO_SEC( A.hora_retorno ))))) hora FROM tb_admin_users  A WHERE A.id = {$funcionario['id']}");
            if (!$pontos->num_rows) {
                //if(time() >= strtotime($funcionario['hora_almoco'])){
                $time_luch = explode(":", $funcionario['hora_almoco']);
                $time_luch = $time_luch[0].$time_luch[1];
                if(date("Hi") >= $time_luch) {
                    $hora_contrato = $funcionario['hora_retorno'];
                    $identificador = "volta_almoco"; 
                }else{
                    $hora_contrato = $funcionario['hora_entrada'];
                    $identificador = "entrada"; 
                }
            } else if ($pontos->num_rows == 1) {
                if($pontos->rows[0]["identificador"] == "volta_almoco"){
                    $hora_contrato = $funcionario['hora_saida'];
                    $identificador = "saida";
                }else{
                    $hora_contrato = $funcionario['hora_almoco'];
                    $identificador = "saida_almoco";
                }
            } else if ($pontos->num_rows == 2) {
                if($pontos->rows[0]["identificador"] == "saida"){
                    $resposta->time = 6000;
                    $resposta->type = "error";
                    $resposta->message = "Você não pode mais bater ponto hoje!";
                    echo json_encode($resposta);
                    exit();
                }else{
                    $hora_contrato = $funcionario['hora_retorno'];
                    $identificador = "volta_almoco";
                }
            } else if ($pontos->num_rows == 3) {
                $hora_contrato = $funcionario['hora_saida'];
                $identificador = "saida";
            } else if ($pontos->num_rows == 4) {
                $resposta->time = 6000;
                $resposta->type = "error";
                $resposta->message = "Você não pode mais bater ponto hoje!";
                echo json_encode($resposta);
                exit();
            }
        } else {
            $hour = $sistema->DB_fetch_array("SELECT SEC_TO_TIME((((TIME_TO_SEC( A.hora_saida ) - TIME_TO_SEC( A.hora_entrada ))))) hora FROM tb_admin_users  A WHERE A.id = {$funcionario['id']}");
            if (!$pontos->num_rows) {
                $hora_contrato = $funcionario['hora_entrada'];
                $identificador = "entrada";
            } else if ($pontos->num_rows == 1) {
                $hora_contrato = $funcionario['hora_saida'];
                $identificador = "saida";
            } else if ($pontos->num_rows == 2) {
                $resposta->time = 6000;
                $resposta->type = "error";
                $resposta->message = "Você não pode mais bater ponto hoje!";
                echo json_encode($resposta);
                exit();
            }
        }

        if ($pontos->num_rows) {
            $minutos_banco = horaParaMinutos($pontos->rows[0]['hora']);
            $minutos_agora = horaParaMinutos(date("H:i:s"));
            $minutos_diferenca = $minutos_agora - $minutos_banco;
            if ($minutos_diferenca < 10) {
                $resposta->time = 6000;
                $resposta->type = "error";
                $aguarde = 10 - $minutos_diferenca;
                $resposta->message = "Aguarde $aguarde minuto(s) para bater ponto novamente!";
                echo json_encode($resposta);
                exit();
            }
        }

        $ip = "";
        if (getenv('HTTP_CLIENT_IP'))
            $ip = getenv('HTTP_CLIENT_IP');
        else if(getenv('HTTP_X_FORWARDED_FOR'))
            $ip = getenv('HTTP_X_FORWARDED_FOR');
        else if(getenv('HTTP_X_FORWARDED'))
            $ip = getenv('HTTP_X_FORWARDED');
        else if(getenv('HTTP_FORWARDED_FOR'))
            $ip = getenv('HTTP_FORWARDED_FOR');
        else if(getenv('HTTP_FORWARDED'))
           $ip = getenv('HTTP_FORWARDED');
        else if(getenv('REMOTE_ADDR'))
            $ip = getenv('REMOTE_ADDR');
        else
            $ip = 'DESCONHECIDO';

        $total_minutes = horaParaMinutos($hour->rows[0]['hora']);

        $fields = array(
            'id_user',
            'almoco',
            'hora_contrato',
            'minutos_dia',
            'data',
            'identificador',
            'ip'
        );

        $values = array(
            '"' . $funcionario['id'] . '"',
            '"' . $funcionario['almoco'] . '"',
            '"' . $hora_contrato . '"',
            '"' . $total_minutes . '"',
            '"' . date("Y-m-d H:i:s") . '"',
            '"' . $identificador . '"',
            '"' . $ip . '"'
        );

        $query = $sistema->DB_insert($main_table, implode(',', $fields), implode(',', $values));

        if ($query->query) {


            $resposta->type = "success";

            $resposta->message = "Ponto eletrônico confirmado!";
            $sistema->inserirRelatorio("Bateu ponto: [" . $funcionario['nome'] . "] Id: [" . $funcionario['id'] . "] Id do ponto: [" . $query->insert_id . "]");
        } else {
            $resposta->type = "error";
            $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
        }
    } else {
        $resposta->type = "error";
        $resposta->message = "Você tem uma falta registrada para hoje, entre em contato com a administração!";
    }
} else {
    $resposta->type = "error";
    $resposta->message = "Senha inválida, favor tente novamente mais tarde!";
}

function horaParaMinutos($hora) {

    list($h, $m, $s) = explode(':', $hora);
    $hours = $h * 60;
    $mins = $m + $hours;

    if ($s > 30)
        $mins = $mins + 1;

    return $mins;
}

$sistema->DB_disconnect();

echo json_encode($resposta);
