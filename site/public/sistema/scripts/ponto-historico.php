<?php
session_start();

require_once '../System/Core/Loader.php';

use System\Core\System;

$sistema = new System();

$sistema->DB_connect();

$funcionario = $sistema->DB_fetch_array("SELECT * FROM tb_admin_users A WHERE A.id = {$_SESSION['admin_id']} AND A.stats = 1");
if (!$funcionario->num_rows) {
    header("Location: ".$sistema->system_path);
    exit;
} else {
    $funcionario = $funcionario->rows[0];
}

$ponto = $sistema->DB_fetch_array("SELECT DATE_FORMAT(A.data, '%d/%m/%Y - %H:%i:%s') data, identificador FROM tb_ponto_eletronico A WHERE A.id_user = {$funcionario['id']} AND DATE(A.data) = CURDATE()");

$return = new \stdClass();


    if ($ponto->num_rows) : 
        $c = 0;
        $return->listagem = "";
        foreach ($ponto->rows as $ponto) :
            $cor = "verde";
            $texto = "entrada";                 
            if ($funcionario['almoco']) {

                if ($ponto["identificador"] == "saida_almoco") {
                    $cor = "vermelho";
                    $texto = "almoço";
                }else if ($ponto["identificador"] == "volta_almoco") {
                    $cor = "verde";
                    $texto = "retorno";
                } else if ($ponto["identificador"] == "saida") {
                    $cor = "vermelho";
                    $texto = "saída";
                }
                
            } else {
                if (($c % 2) == 1) {
                    $cor = "vermelho";
                    $texto = "saída";
                }
            }

            
            $return->listagem .= '<li><div style="min-width: 125px; display:inline-block">'.$ponto["data"].'</div> <span class="'.$cor.'">'.$texto.'</span></li>';
            $sql = 
            "SELECT *,
            (
                ROUND(
                    (
                        CASE identificador 
                        WHEN 'falta' THEN minutos_dia*-1 
                        WHEN 'folga' THEN minutos_dia*-1 
                        WHEN 'venda' THEN minutos_dia*-1
                        WHEN 'saida_almoco' THEN  TIME_TO_SEC((TIMEDIFF(hora_ponto, hora_contrato)))/60
                        WHEN 'saida' THEN  TIME_TO_SEC((TIMEDIFF(hora_ponto, hora_contrato)))/60
                        ELSE TIME_TO_SEC((TIMEDIFF(hora_contrato, hora_ponto)) )/60
                        END
                    )
                )
            ) saldo_horas FROM
            (
                SELECT A.data data_usa, DATE_FORMAT(A.data, '%d/%m/%Y') data,DATE_FORMAT(A.data, '%H:%i:%s') hora_ponto, B.nome, B.hora_entrada, B.hora_almoco, B.hora_retorno, B.hora_saida, A.id, A.id_user, A.almoco, A.hora_contrato, A.minutos_dia, A.ip, NULL motivo, A.identificador, NULL valor, NULL data_inicio, NULL data_fim 
                    FROM tb_ponto_eletronico A INNER JOIN tb_admin_users B ON B.id = A.id_user WHERE B.id = {$funcionario['id']}
                    
                UNION 
                
                SELECT A.data data_usa, DATE_FORMAT(A.data, '%d/%m/%Y') data ,NULL hora_ponto, B.nome, B.hora_entrada, B.hora_almoco, B.hora_retorno, B.hora_saida, A.id, NULL id_user, NULL almoco, NULL hora_contrato, A.minutos minutos_dia, NULL ip, A.motivo ,A.identificador, NULL valor, DATE_FORMAT(A.data_inicio, '%d/%m/%Y') data_inicio, DATE_FORMAT(A.data_fim, '%d/%m/%Y') data_fim 
                    FROM tb_ponto_eletronico_abates A INNER JOIN tb_admin_users B ON B.id = A.id_user WHERE B.id = {$funcionario['id']}
            ) x 
            ORDER BY DATE(data_usa) DESC, TIME(data_usa) ASC


            ";
            $query = $sistema->DB_fetch_array($sql);
            $saldo = 0;
            foreach($query->rows as $index => $ponto){
                if($ponto['identificador'] == "volta_almoco" AND (((isset($query->rows[$index-1]) AND $query->rows[$index-1]["data"] != $ponto["data"])) OR (!isset($query->rows[$index-1])))){
                    $hour = $sistema->DB_fetch_array("SELECT SEC_TO_TIME((((TIME_TO_SEC( A.hora_almoco ) - TIME_TO_SEC( A.hora_entrada ))))) hora, A.hora_entrada, A.hora_almoco FROM tb_admin_users  A WHERE A.id = {$ponto['id_user']}");
                    $saldo -= horaParaMinutos($hour->rows[0]['hora']);
                }

                $saldo += $ponto["saldo_horas"];

                if($ponto['identificador'] == "saida_almoco" AND $ponto["data"] != date('d/m/Y', time()) AND (((isset($query->rows[$index+1]) AND $query->rows[$index+1]["data"] != $ponto["data"])) OR (!isset($query->rows[$index+1])))){
                    $hour = $sistema->DB_fetch_array("SELECT SEC_TO_TIME((((TIME_TO_SEC( A.hora_saida ) - TIME_TO_SEC( A.hora_retorno ))))) hora, A.hora_entrada, A.hora_almoco FROM tb_admin_users  A WHERE A.id = {$ponto['id_user']}");
                    $saldo -= horaParaMinutos($hour->rows[0]['hora']);
                }
            }

            $return->saldo = $sistema->minToHours($saldo);
            $c++;
            endforeach;
        else: 
        $return->listagem = '<li>Você ainda não registrou o seu ponto hoje</li>';
        endif; 

echo json_encode($return);
$sistema->DB_disconnect();

function horaParaMinutos($hora) {

    list($h, $m, $s) = explode(':', $hora);
    $hours = $h * 60;
    $mins = $m + $hours;

    if ($s > 30)
        $mins = $mins + 1;

    return $mins;
}