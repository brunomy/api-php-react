<?php 

/*
//ESSE SCRIPT VERIFICA OS RASTREIOS ENTREGUES E NÃO ENTREGUES, 
VERIFICA SE OS PEDIDOS RELACIONADOS AOS RASTREIOS AINDA NÃO FOI ALTERADO PARA ENTREGUE
VERIFICA SE TOSOS OS RASTREIOS DESTE PEDIDO FORAM ENTREGUES E ALTERA O PEDIDO PARA STATUS DE 'ENTREGUE'
ENVIA EMAIL PARA CLIENTE E RESPONSÁVEIS CADASTRADOS NO SISTEMA
*/

set_time_limit(0);
require_once '../System/Core/Loader.php';

use System\Core\System;

$sistema = new System();

$sistema->inserirRelatorio("CRONJOB de pedidos entregues acaba de ser executado");

$sistema->DB_connect();

//BUSCA POR RASTREIOS ONDE OS PEDIDOS AINDA NÃO ALTEROOU STATUS PARA ENTREGUE
$pedidos = $sistema->DB_fetch_array('SELECT c.id pedido, COUNT(a.id) qtd_rastreios, SUM(a.stats) qtd_rastreios_entregues, d.nome, d.email FROM tb_pedidos_rastreios a INNER JOIN tb_pedidos_has_tb_rastreios b ON a.id = b.id_rastreio INNER JOIN tb_pedidos_pedidos c ON b.id_pedido = c.id INNER JOIN tb_clientes_clientes d ON c.id_cliente = d.id WHERE c.entregue IS NULL GROUP BY c.id');

$flag = true;
$status_entregue = 12;

if ($pedidos->num_rows) {

	$notificar = $sistema->DB_fetch_array("SELECT * FROM tb_pedidos_status WHERE id = $status_entregue AND enviar_email = 1");
	if ($notificar->num_rows) {
		$notifica = $notificar->rows[0];
        $destinos = $sistema->DB_fetch_array("SELECT IFNULL(B.nome, B.usuario) nome, email FROM tb_pedidos_status_has_users_notification A INNER JOIN tb_admin_users B ON B.id = A.id_usuario WHERE A.id_pedido_status = $status_entregue AND B.stats = 1");
        if ($destinos->num_rows) {
            foreach ($destinos->rows as $destino) {
                $to[] = array("email" => $destino['email'], "nome" => utf8_decode($destino['nome']));
            }
        }
	}

	foreach ($pedidos->rows as $pedido) {

		if($pedido['qtd_rastreios'] == $pedido['qtd_rastreios_entregues']){
			
			$sistema->inserirRelatorio('Debug pedido entregue 1');
	        if (isset($notifica)) {

	        	$destinatarios = array();
	        	$destinatarios = $to;
	            $destinatarios[] = array("email" => $pedido['email'], "nome" => utf8_decode($pedido['nome']));

	            $assunto = $notifica['assunto'];
	            $mensagem = $notifica['mensagem'];
	            
	            $mensagem = $sistema->trataTextoNotificar($mensagem);

	            $assunto = str_replace("[({NOME})]", $pedido['nome'], $assunto);
	            $assunto = str_replace("[({ID})]", $pedido['pedido'], $assunto);
	            $mensagem = str_replace("[({NOME})]", $pedido['nome'], $mensagem);
	            $mensagem = str_replace("[({ID})]", $pedido['pedido'], $mensagem);

		        $setFrom = '';
		        $notificar_vendedor = $sistema->DB_fetch_array('SELECT * FROM tb_pedidos_pedidos WHERE id = '.$pedido['pedido'].' AND id_vendedor IS NOT NULL AND id_vendedor <> 0');
		        $sistema->inserirRelatorio('Debug pedido entregue 2');
		        if($notificar_vendedor->num_rows){
		        	$sistema->inserirRelatorio('Debug pedido entregue 3');
		            $vendedor = $sistema->DB_fetch_array("SELECT * FROM tb_admin_users WHERE id = ".$notificar_vendedor->rows[0]['id_vendedor']." AND stats = 1");
		            if($vendedor->num_rows){
		            	$sistema->inserirRelatorio('Debug pedido entregue 4');
		                $setFrom = array(array('email'=>$vendedor->rows[0]['email'], 'nome'=>'Real Poker'));
		                if($notifica['notificar_vendedor']==1){
		                	$sistema->inserirRelatorio('Debug pedido entregue 5');
		                    $destinatarios[] = array("email" => $vendedor->rows[0]['email'], "nome" => utf8_decode($vendedor->rows[0]['nome']));
		                    
		                }
		            }
		        }

	            $sistema->enviarEmail($destinatarios, $setFrom, utf8_decode($assunto), utf8_decode($mensagem),'','X-MC-Tags: Status Pedido '.$notifica['id']);
	            //echo "Enviar email para: ";
	            //echo "<pre>";print_r($destinatarios);echo "</pre><br>";
	            $sistema->inserirRelatorio('Debug pedido entregue 6');
	        }
	        

	        $ids[] = ' id='.$pedido['pedido'].' ';
	        $insertData[] = '('.$pedido['pedido'].',"12 - Entregue","CRONJOB")';

			$sistema->inserirRelatorio("CRONJOB alterou status do pedido nº: [".$pedido['pedido']."] para entregue");
			
			//echo 'Pedido: '.$pedido['pedido'].' foi totalmente entregue ao destinatario '.$pedido['nome'].' ('.$pedido['email'].')<br>';

			$flag = false;
		}

	}

}


if($flag){
	echo 'Nenhum pedido foi totalmente entregue neste periodo <br><br>';
}else{
	$sistema->DB_update('tb_pedidos_pedidos','entregue = NOW(), id_status = '.$status_entregue.' WHERE '.implode(' OR ', $ids));
	$sistema->mysqli->query("INSERT INTO tb_pedidos_historicos (id_pedido, status, usuario) VALUES " . implode(',', $insertData));
}

$sistema->DB_disconnect();

echo 'Done!';
