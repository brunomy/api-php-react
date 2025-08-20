<?php

set_time_limit(0);
require_once '../System/Core/Loader.php';

use System\Core\System;

$sistema = new System();

$sistema->inserirRelatorio("CRONJOB de rastreios acaba de ser executado");

//BUSCA POR RASTREIOS NÃO ENTREGUES
$rastreios = $sistema->DB_fetch_array('SELECT a.id, c.id pedido, a.link, a.data, d.nome, d.telefone, e.cidade, f.estado, a.stats FROM tb_pedidos_rastreios a INNER JOIN tb_pedidos_has_tb_rastreios b ON b.id_rastreio = a.id INNER JOIN tb_pedidos_pedidos c ON b.id_pedido = c.id INNER JOIN tb_clientes_clientes d ON c.id_cliente = d.id INNER JOIN tb_utils_cidades e ON d.id_cidade = e.id INNER JOIN tb_utils_estados f ON d.id_estado = f.id WHERE a.stats = 0');

if ($rastreios->num_rows) {
	foreach ($rastreios->rows as $rastreio) {
		
		if(stristr($rastreio['link'], 'rastreie.com')){

			$end = explode("/", $rastreio['link']);
			$end = $end[count($end)-1];
			$url = "http://websro.correios.com.br/sro_bin/txect01$.QueryList?P_LINGUA=001&P_TIPO=001&P_COD_UNI=".$end;

			/*
			$doc = new DOMDocument();
			$doc->loadHTMLFile($url);
			$doc->saveHTMLFile("htmls/correios_pedido_".$rastreio['pedido']."_rastreio".$rastreio['id'].".html");
			echo $rastreio['link']." - OK!<br>";
			*/

			$content = file_get_contents($url);
			if(stristr($content, 'Entrega Efetuada')){
				$sistema->DB_update("tb_pedidos_rastreios","stats=1 WHERE id = ".$rastreio['id']);
				$sistema->inserirRelatorio("CRONJOB alterou status do rastreio nº: [".$rastreio['id']."] do pedido nº: [".$rastreio['pedido']."] para entregue (Correios)");

				//echo $rastreio['link']." - [ENTREGUE]<br>";
			}else{
				//echo $rastreio['link']." - [ENCAMINHADO]<br>";
			}

		}elseif(stristr($rastreio['link'], 'lancargo.com')){

			/*
			$doc = new DOMDocument();
			$doc->loadHTMLFile($rastreio['link']);
			$doc->saveHTMLFile("htmls/tam_pedido_".$rastreio['pedido']."_rastreio".$rastreio['id'].".html");
			echo $rastreio['link']." - OK!<br>";
			*/

			$content = file_get_contents($rastreio['link']);
			if(stristr($content, 'Entregue Físico')){
				$sistema->DB_update("tb_pedidos_rastreios","stats=1 WHERE id = ".$rastreio['id']);
				$sistema->inserirRelatorio("CRONJOB alterou status do rastreio nº: [".$rastreio['id']."] do pedido nº: [".$rastreio['pedido']."] para entregue (TAM)");
				//echo $rastreio['link']." - [ENTREGUE]<br>";
			}else{
				//echo $rastreio['link']." - [ENCAMINHADO]<br>";
			}

		}

	}
}
