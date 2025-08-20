<?php 
set_time_limit(4);
/*
    Este script pega os dados dos ips do redis e insere no mysql
    Precisa ser executado minuto a minuto para que seja possivel detectar algum ataque malicioso,
    Timeout curto pois serão várias chamadas a cada 5 segundos pelo script iptable_process.php
*/

require_once __DIR__.'/../public/sistema/System/Core/Loader.php';
use System\Core\Bootstrap;

$redis = new Redis();
$redis->connect('127.0.0.1', 6379);

$registros = array();
$ips = array();
$ip_ids = array();

$keys = $redis->keys('request:*');

function comparaTimestamp($a,$b){
	return strtotime($a['timestamp']) - strtotime($b['timestamp']);
}

if (!isset($sistema)) {
    $sistema = new Bootstrap();
    $sistema->DB_connect();
}

foreach ($keys as $key) {
    // Obter o valor associado à chave no Redis
    $value = $redis->get($key);

    // Decodificar o valor JSON
    $data = json_decode($value, true);

    // Remover a chave do Redis
    $redis->del($key);

    if($data['ip'] != ''){
        array_push($registros, $data);

        if(!in_array($data['ip'],$ips)){
            array_push($ips, $data['ip']);
                
            $query = $sistema->DB_fetch_array('SELECT id FROM tb_iptables_ips WHERE ip = "'.$data['ip'].'"');
            
            if($query->num_rows){
                $ip_ids[$data['ip']] = $query->rows[0]['id'];
            } else {
                $query = $sistema->DB_insert('tb_iptables_ips', 'ip', '"'.$data['ip'].'"');
                $ip_ids[$data['ip']] = $query->insert_id;
            }
        }
    }
}


usort($registros, 'comparaTimestamp');

foreach ($registros as $key) {

    //echo $key['timestamp'] . ' - ' . str_pad($key['ip'], 40) . ' ' . $key['uri']. ' ' . $key['blocked'].PHP_EOL;

    $sistema->DB_insert('tb_iptables_requests', 'ip_id,uri,blocked,created_at', $ip_ids[$key['ip']].',"'.$key['uri'].'",'.$key['blocked'].',"'.$key['timestamp'].'"');

}

//EXECUTA O SCRIPT DE BLACKLIST
$command = "php ".__DIR__."/iptables_blacklist.php 2>/dev/null >/dev/null &";
echo shell_exec($command);