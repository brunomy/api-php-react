<?php 

/*
    Este script contem as regras para atribuir um ip em nossa blacklist 
    É usado apenas como backtest, para realizar testes do script de verificação
*/

$inicio = microtime(true);

require_once __DIR__.'/../public/sistema/System/Core/Loader.php';
use System\Core\Bootstrap;

if (!isset($sistema)) {
    $sistema = new Bootstrap();
}

$blacklisted = "SELECT ip_id FROM tb_iptables_blacklists WHERE created_at > (DATE_SUB(NOW(),INTERVAL 2 DAY)) AND deleted_at IS NULL";

$query = 'SELECT r.created_at, r.ip_id FROM tb_iptables_requests r JOIN tb_iptables_ips i ON i.id=r.ip_id WHERE r.created_at > (DATE_SUB(NOW(),INTERVAL 1 MINUTE)) AND i.whitelist IS NULL AND r.ip_id NOT IN ('.$blacklisted.') ORDER BY r.ip_id, r.created_at';

$fetch = $sistema->DB_fetch_array($query);

$ips = array();
$requests = array();

if($fetch->num_rows){
    foreach ($fetch->rows as $key => $value) {

        if(!in_array($value['ip_id'], $ips)){
            array_push($ips, $value['ip_id']);
            if(count($requests)){
                blacklistPer10Second($requests);
            }
            $requests = array();
        }

        array_push($requests, $value);

        //echo str_pad($value['ip_id'],5) . ' - ' . $value['created_at'] .  ' - ' . $value['uri'] .PHP_EOL;
    }
}

function blacklistPer10Second($requests){

    $segundos = 5;
    $timestamp = $requests[0]['created_at'];
    $faixa_ate = strtotime($timestamp) + $segundos;

    $limit = 5;
    $count = 0;

    foreach ($requests as $request) {
        if(strtotime($request['created_at']) < $faixa_ate){
            $count++;
            if($count > $limit){
                echo $request['ip_id'].' at '.$timestamp.PHP_EOL;
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

$fim = microtime(true);
$tempo_execucao = $fim - $inicio;
echo "Tempo de execução: " . number_format($tempo_execucao, 4) . " segundos".PHP_EOL;