<?php 


/*
    Este script cria os produtos que existem no sistema dentro da conta azul e vincula o seu id
*/

require_once __DIR__.'/../public/sistema/System/Core/Loader.php';
use System\Core\Bootstrap;
use System\Libs\ContaAzul;

if (!isset($sistema)) {
    $sistema = new Bootstrap();
}

$contaazul = new ContaAzul($sistema);


$query = $sistema->DB_fetch_array("SELECT * FROM tb_produtos_produtos p WHERE p.apagado = 0 AND p.contaazul_id IS NULL");

if(!$query->num_rows){
    exit('nenhum produto para inserir');
}else{
    foreach($query->rows as $produto){
        print_r($contaazul->newProduct($produto['id']));
        sleep(1);
    }
}


?>