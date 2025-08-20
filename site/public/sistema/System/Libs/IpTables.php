<?php 

namespace System\Libs;

class IpTables
{
    private $sistema;

    function __construct($sistema)
    {
        $this->sistema = $sistema;
    }    

    public function whitelist($ip) {
        
        $query = $this->sistema->DB_fetch_array("SELECT id, whitelist FROM tb_iptables_ips WHERE ip = '".$ip."'");

        if(!$query->num_rows){
            $this->sistema->DB_insert('tb_iptables_ips','ip,whitelist','"'.$ip.'",NOW()');
        }else if(empty($query->rows[0]['whitelist'])){
            $this->sistema->DB_update('tb_iptables_ips','whitelist=NOW() WHERE id = '.$query->rows[0]['id']);
        }

        $_SESSION['ip_whitelisted'] = $ip;
        
    }

    public static function saveRequests($blocked) {
        /* 
            IP TRACE LOGGER 
            aqui gravamos o ip de toda requisição ao sistema temporariamente dentro do redis
            um segundo script vai pegar esses dados e envia-los para mysql
            um terceiro script vai analisar esses dados e de acordo com as regras deve enviar para bloqueio do ip no servidor
            os dados devem ser mantidos temporariamente no mysql, apenas para que seja possivel analisar o comportamento em caso de ataque

        */
        $redis = new \Redis();
        $redis->connect('127.0.0.1', 6379);

        $sequenceNumber = $redis->incr('sequence_of_'.date('d'),86460); 
        //chave expirará após (86460 segundos) 24 horas e 1 minuto após sua inicialização. 
        //Ou seja, todo dia irá zerar a contagem sequencial e iniciar uma nova chave 

        $redisKey = "request:$sequenceNumber";
        $redis->setex($redisKey, 180, json_encode([
            'ip' => $_SERVER['REMOTE_ADDR'],
            'uri' => $_SERVER['REQUEST_URI'],
            'timestamp' => date('Y-m-d H:i:s'),
            'blocked'=> (string)$blocked,
        ]));
        $redis->close();
    }

}

?>