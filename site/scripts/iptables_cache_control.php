<?php 
/*
    Este script pega os ips do mysql e os adiciona no redis para 
    disponibilizar a lista de blacklist ao controlador de acessos.
*/

require_once __DIR__.'/../public/sistema/System/Core/Loader.php';
use System\Core\Bootstrap;

if (!isset($sistema)) {
    $sistema = new Bootstrap();
    $sistema->DB_connect();
}

//BUSCA LISTA ATUAL EM BLACKLIST
$query = $sistema->DB_fetch_array("SELECT i.ip FROM tb_iptables_blacklists b JOIN tb_iptables_ips i ON i.id=b.ip_id WHERE b.deleted_at > NOW()");

if($query->num_rows){
    $blacklist = array();
    foreach ($query->rows as $row) {    
        $blacklist[] = $row['ip'];
    }
}else{

    //DEFINE UM VALOR ZERADO PARA TER ALGO NA LISTA EM CACHE
    $blacklist = array('0');

}


$redis = new Redis();
$redis->connect('127.0.0.1', 6379);

//LIMPA A LISTA DO CACHE
$redis->del('iptables_blacklist');

//INSERE NOVA LISTA NO CACHE
$redis->sAdd('iptables_blacklist', ...$blacklist);
