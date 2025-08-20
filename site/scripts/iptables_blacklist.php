<?php 
set_time_limit(3);
/*
    Este script contem as regras para atribuir um ip em nossa blacklist 
    Deve ser executado logo após a execução do script iptables_redis_to_mysql.php
    Timeout curto pois terão várias chamadas a cada 5 segundos.
*/

require_once __DIR__.'/../public/sistema/System/Core/Loader.php';
use System\Core\Bootstrap;

if (!isset($sistema)) {
    $sistema = new Bootstrap();
}

$blacklisted = "SELECT ip_id FROM tb_iptables_blacklists WHERE deleted_at > NOW()";

$query = 'SELECT r.created_at, r.ip_id FROM tb_iptables_requests r JOIN tb_iptables_ips i ON i.id=r.ip_id WHERE r.created_at > (DATE_SUB(NOW(),INTERVAL 1 MINUTE)) AND i.whitelist IS NULL AND r.ip_id NOT IN ('.$blacklisted.') ORDER BY r.ip_id, r.created_at';

$fetch = $sistema->DB_fetch_array($query);

$ips = array();
$requests = array();

if($fetch->num_rows){
    
    //adiciona registro no final do array para o último ip entre na verificaçao de blacklist.
    $fetch->rows[] = array('ip_id'=>'0',date('Y-m-d H:i:s'));

    foreach ($fetch->rows as $key => $value) {
        if(!in_array($value['ip_id'], $ips)){
            array_push($ips, $value['ip_id']);
            if(count($requests)){
                if(blacklistPer10Second($requests)){
                    
                    $data = new DateTime();
                    $minutes = 1;

                    //Busca recorrência do ip em nossa blacklist nos últimos 13 meses
                    $query = $sistema->DB_fetch_array("SELECT b.ip_id, COUNT(*) qtd FROM tb_iptables_blacklists b WHERE b.ip_id = {$requests[0]['ip_id']} AND b.created_at > (DATE_SUB(NOW(),INTERVAL 13 MONTH)) GROUP BY b.ip_id");
                    
                    if($query->num_rows){
                        //Regra para tempo de permanencia em blacklist baseado na quantidade vezes entrou na blacklist.
                        $minutes = $query->rows[0]['qtd'] * $query->rows[0]['qtd'];

                    }

                    $data->modify("+{$minutes} minutes");
                    $deleted_at = $data->format('Y-m-d H:i:s');

                    $fetch = $sistema->DB_insert('tb_iptables_blacklists', 'ip_id, deleted_at', $requests[0]['ip_id'] . ', "'.$deleted_at.'"');

                    //envia para cache
                    $command = "php ".__DIR__."/iptables_cache_control.php >/dev/null 2>&1 &";
                    shell_exec($command);

                }
            }
            $requests = array();
        }

        array_push($requests, $value);

    }
}

function blacklistPer10Second($requests){

    //Regra: se tiver mais de {x} request em {n} segundos entra na blacklist

    $segundos = 5;
    $timestamp = $requests[0]['created_at'];
    $faixa_ate = strtotime($timestamp) + $segundos;

    $limit = 5;
    $count = 0;

    foreach ($requests as $request) {
        if(strtotime($request['created_at']) < $faixa_ate){
            $count++;
            if($count > $limit){
                //echo $request['ip_id'].' at '.$timestamp.PHP_EOL;
                return true;
            }
        }else{
            $count = 1;
            $timestamp = $request['created_at'];
            $faixa_ate = strtotime($timestamp) + $segundos;
        }
    }

    return false;

}