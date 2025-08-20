<?php 

/*
    Este script agrupa os dados diarios da tabela requests na tabela historical 
    Também apaga todos os dados da tabela com mais de 1 mês do registro
    Precisa ser executado uma vez por dia
    Se estiver rodando pelo forge, use o recurso schedule do próprio forge
    Caso contrário, configure CRONTAB 
*/

require_once __DIR__.'/../public/sistema/System/Core/Loader.php';
use System\Core\Bootstrap;

if (!isset($sistema)) {
    $sistema = new Bootstrap();
}

$daysbefore = 1;

$query_frequency = "
    SELECT ip_id, MAX(frequencia) frequencia, minuto FROM (
        SELECT CONCAT(HOUR(r.created_at),':',MINUTE(r.created_at)) minuto, COUNT(*) frequencia, ip_id 
            FROM tb_iptables_requests r 
            WHERE DATE(r.created_at) = DATE(SUBDATE(NOW(), $daysbefore)) 
            GROUP BY CONCAT(HOUR(r.created_at),':',MINUTE(r.created_at)), ip_id 
    ) A
    GROUP BY A.ip_id
";

$query_requests = "
    SELECT ip_id, COUNT(*) requests, DATE(SUBDATE(NOW(), $daysbefore)) date 
        FROM tb_iptables_requests r 
        WHERE DATE(r.created_at) = DATE(SUBDATE(NOW(), $daysbefore)) 
        GROUP BY r.ip_id
";
$sistema->DB_connect();
$get_requests = $sistema->DB_fetch_array($query_requests);
$get_frequency = $sistema->DB_fetch_array($query_frequency);

$data = array();
$insertData = array();
if($get_requests->num_rows){
    foreach ($get_requests->rows as $key => $ip_requests) {
        foreach ($get_frequency->rows as $key => $ip_frequency) {
            if($ip_requests['ip_id']==$ip_frequency['ip_id']){
                $data[] = array(
                    'ip_id'=>$ip_requests['ip_id'],
                    'date'=>$ip_requests['date'],
                    'requests'=>$ip_requests['requests'],
                    'higher_frequency'=>$ip_frequency['frequencia'],
                );
                $insertData[] = "(" . $ip_requests['ip_id'] . ",'" . $ip_requests['date'] . "','" . $ip_requests['requests'] . "','" . $ip_frequency['frequencia'] . "')";
            }
        }
    }
    $sistema->mysqli->query("INSERT INTO tb_iptables_historical (ip_id,date,requests,higher_frequency) VALUES " . implode(',', $insertData));
}


//APAGA REGISTROS COM MAIS DE 1 MÊS.
$sistema->DB_delete("tb_iptables_requests", "DATE(created_at) < (DATE_SUB(NOW(),INTERVAL 1 MONTH))");

$sistema->DB_disconnect();

/*foreach ($data as $key => $value) {
    echo $value['date']. ' - ' . str_pad($value['ip_id'],5) . ' - ' . str_pad($value['requests'],5) . ' - ' . str_pad($value['higher_frequency'],5).PHP_EOL;
}*/
