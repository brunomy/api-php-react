<?php 

/*
    Este script dispara vários processos para processar a ferramenta de throttling
    Precisa ser executado minuto a minuto para que seja possivel detectar algum ataque malicioso,
    Se estiver rodando pelo forge, use o recurso schedule do próprio forge
    Caso contrário, configure CRONTAB 
*/

//executa o controle de cache
shell_exec("php ".__DIR__."/iptables_cache_control.php >/dev/null 2>&1 &");

for ($i = 0; $i < 12; $i++) {
    shell_exec("php ".__DIR__."/iptables_redis_to_mysql.php >/dev/null 2>&1 &");
    sleep(5);



    /* 
        Recurso para interromper o script caso ultrapasse 55 segundos
        Por algum motivo a função set_time_limit() não funciona,
        por isso dessa gambiarra aqui embaixo.
    */
    if (time() - $_SERVER['REQUEST_TIME'] > 55) {
        break;
    }
}