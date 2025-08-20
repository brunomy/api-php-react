<?php

session_start();
$_SESSION["cotacaoDolarOff"] = true;


if (!isset($_SESSION["seo_session"])) {
    $_SESSION["seo_session"] = uniqid();
}

require_once '../../sistema/System/Core/Loader.php';

use System\Core\Bootstrap;
use classes\Product;

include "../../_system.php";

$sistema = new _sys();
$product = new Product();

$formulario = $sistema->formularioObjeto($_POST);

if (!isset($formulario->pedido)) {
    header("Location: $sistema->root_path");
    exit();
}


$pedido = false;
$pedidos = $sistema->DB_fetch_array("SELECT * FROM tb_pedidos_pedidos WHERE id = $formulario->pedido");
if ($pedidos->num_rows) {
    $pedido = $pedidos->rows[0];
} else {
    header("Location: $sistema->root_path");
    exit();
}

$endereco = false;
$enderecos = $sistema->DB_fetch_array("SELECT A.*, C.cidade, E.estado FROM tb_pedidos_enderecos A INNER JOIN tb_utils_cidades C ON C.id = A.id_cidade INNER JOIN tb_utils_estados E ON E.id = C.id_estado WHERE A.id_pedido = $formulario->pedido");
if ($enderecos->num_rows)
    $endereco = $enderecos->rows[0];

$cliente = false;
$clientes = $sistema->DB_fetch_array("SELECT * FROM tb_clientes_clientes WHERE id = {$_SESSION['cliente_id']}");
if ($clientes->num_rows)
    $cliente = $clientes->rows[0];

$produtos = $product->getCartProductsByPedido($formulario->pedido);

$product->encode($pedido);


$valorTotal = $pedido['valor_final'];

//$valorTotal *= 0.95;

$dias_de_prazo_para_pagamento = 3;
$taxa_boleto = 0;
$data_venc = date("d/m/Y", time() + ($dias_de_prazo_para_pagamento * 86400));  // Prazo de X dias OU informe data: "13/04/2006";
$valor_cobrado = $valorTotal; // Valor - REGRA: Sem pontos na milhar e tanto faz com "." ou "," ou com 1 ou 2 ou sem casa decimal
$valor_cobrado = str_replace(",", ".", $valor_cobrado);
$valor_boleto = number_format($valor_cobrado + $taxa_boleto, 2, ',', '');

$dadosboleto["nosso_numero"] = $pedido['id']; //"75896452";  // Nosso numero sem o DV - REGRA: Máximo de 11 caracteres!
$dadosboleto["numero_documento"] = $pedido['id']; // Num do pedido ou do documento = Nosso numero
$dadosboleto["data_vencimento"] = $data_venc; // Data de Vencimento do Boleto - REGRA: Formato DD/MM/AAAA
$dadosboleto["data_documento"] = date("d/m/Y"); // Data de emissão do Boleto
$dadosboleto["data_processamento"] = date("d/m/Y"); // Data de processamento do boleto (opcional)
$dadosboleto["valor_boleto"] = number_format($valor_cobrado + $taxa_boleto, 2, ',', '');  // Valor do Boleto - REGRA: Com vírgula e sempre com duas casas depois da virgula
// DADOS DO SEU CLIENTE
$dadosboleto["sacado"] = ($cliente['pessoa']==1) ? $cliente['nome'] : $cliente['razao_social'];
$dadosboleto["endereco1"] = $endereco['endereco'] . ', ' . $endereco['complemento'] . ' n. ' . $endereco['numero'];
$dadosboleto["endereco2"] = $endereco['cidade'] . '/' . $endereco['estado'] . ' CEP:' . $endereco['cep'];

// INFORMACOES PARA O CLIENTE
$dadosboleto["demonstrativo1"] = "Pagamento de compra. Pedido n. " . $pedido['id'];
$dadosboleto["demonstrativo2"] = "";
$dadosboleto["demonstrativo3"] = "Real Poker - http://www.realpoker.com.br";
$dadosboleto["instrucoes1"] = "- Sr. Caixa, favor não receber após o vencimento.";
$dadosboleto["instrucoes2"] = "";
$dadosboleto["instrucoes3"] = "- Em caso de dúvidas entre em contato conosco: contato@realpoker.com.br";
$dadosboleto["instrucoes4"] = "";

// DADOS OPCIONAIS DE ACORDO COM O BANCO OU CLIENTE
$dadosboleto["quantidade"] = "001";
$dadosboleto["valor_unitario"] = number_format($valorTotal, 2, ',', '');
;
$dadosboleto["aceite"] = "";
$dadosboleto["especie"] = "R$";
$dadosboleto["especie_doc"] = "DS";


// ---------------------- DADOS FIXOS DE CONFIGURAÇÃO DO SEU BOLETO --------------- //
// DADOS DA SUA CONTA - Bradesco
$dadosboleto["agencia"] = "1840"; // Num da agencia, sem digito
$dadosboleto["agencia_dv"] = "6"; // Digito do Num da agencia
$dadosboleto["conta"] = "3807";  // Num da conta, sem digito
$dadosboleto["conta_dv"] = "5";  // Digito do Num da conta
// DADOS PERSONALIZADOS - Bradesco
$dadosboleto["conta_cedente"] = "3807"; // ContaCedente do Cliente, sem digito (Somente Números)
$dadosboleto["conta_cedente_dv"] = "5"; // Digito da ContaCedente do Cliente
$dadosboleto["carteira"] = "06";  // Código da Carteira: pode ser 06 ou 03
// SEUS DADOS
$dadosboleto["logo_empresa"] = "imagens/logo_realpoker.png";
$dadosboleto["identificacao"] = "Real Poker";
$dadosboleto["cpf_cnpj"] = "20.087.324/0001-16";
$dadosboleto["endereco"] = "R R 5 QUADRAD7 LOTE 76-77 SALA 608 SETOR OESTE CEP 74.125-070";
$dadosboleto["cidade_uf"] = "GOIÂNIA - GO";
$dadosboleto["cedente"] = "REAL POKER MESAS E FICHAS PERSONALIZADAS EIRELI-EPP";

/*

  // DADOS DO BOLETO PARA O SEU CLIENTE
  $dias_de_prazo_para_pagamento = 3;
  $taxa_boleto = 0;
  $data_venc = date("d/m/Y", time() + ($dias_de_prazo_para_pagamento * 86400));  // Prazo de X dias OU informe data: "13/04/2006";
  $valor_cobrado = "1225,50"; // Valor - REGRA: Sem pontos na milhar e tanto faz com "." ou "," ou com 1 ou 2 ou sem casa decimal
  $valor_cobrado = str_replace(",", ".", $valor_cobrado);
  $valor_boleto = number_format($valor_cobrado + $taxa_boleto, 2, ',', '');

  $dadosboleto["nosso_numero"] = "22"; // id do pedido
  $dadosboleto["numero_documento"] = "22"; // id do pedido
  $dadosboleto["data_vencimento"] = $data_venc; // Data de Vencimento do Boleto - REGRA: Formato DD/MM/AAAA
  $dadosboleto["data_documento"] = date("d/m/Y"); // Data de emissão do Boleto
  $dadosboleto["data_processamento"] = date("d/m/Y"); // Data de processamento do boleto (opcional)
  $dadosboleto["valor_boleto"] = $valor_boleto;  // Valor do Boleto - REGRA: Com vírgula e sempre com duas casas depois da virgula
  // DADOS DO SEU CLIENTE
  $dadosboleto["sacado"] = 'Rafael Ramos da Silva';
  $dadosboleto["endereco1"] = 'Rua Dinamarca, Qd 74 Lt 23 n. 2';
  $dadosboleto["endereco2"] = 'Goiânia/Goiás CEP:74.330-050 ';

  // INFORMACOES PARA O CLIENTE
  $dadosboleto["demonstrativo1"] = "Pagamento de compra. Pedido n. 22";
  $dadosboleto["demonstrativo2"] = "";
  $dadosboleto["demonstrativo3"] = "Real Poker - http://www.realpoker.com.br";
  $dadosboleto["instrucoes1"] = "- Sr. Caixa, favor não receber após o vencimento.";
  $dadosboleto["instrucoes2"] = "";
  $dadosboleto["instrucoes3"] = "- Em caso de dúvidas entre em contato conosco: contato@realpoker.com.br";
  $dadosboleto["instrucoes4"] = "";

  // DADOS OPCIONAIS DE ACORDO COM O BANCO OU CLIENTE
  $dadosboleto["quantidade"] = "001";
  $dadosboleto["valor_unitario"] = $valor_boleto;
  $dadosboleto["aceite"] = "";
  $dadosboleto["especie"] = "R$";
  $dadosboleto["especie_doc"] = "DS";


  // ---------------------- DADOS FIXOS DE CONFIGURAÇÃO DO SEU BOLETO --------------- //
  // DADOS DA SUA CONTA - Bradesco
  $dadosboleto["agencia"] = "1840"; // Num da agencia, sem digito
  $dadosboleto["agencia_dv"] = "6"; // Digito do Num da agencia
  $dadosboleto["conta"] = "3807";  // Num da conta, sem digito
  $dadosboleto["conta_dv"] = "5";  // Digito do Num da conta
  // DADOS PERSONALIZADOS - Bradesco
  $dadosboleto["conta_cedente"] = "3807"; // ContaCedente do Cliente, sem digito (Somente Números)
  $dadosboleto["conta_cedente_dv"] = "5"; // Digito da ContaCedente do Cliente
  $dadosboleto["carteira"] = "06";  // Código da Carteira: pode ser 06 ou 03
  // SEUS DADOS
  $dadosboleto["logo_empresa"] = "imagens/logo_realpoker.png";
  $dadosboleto["identificacao"] = "Real Poker";
  $dadosboleto["cpf_cnpj"] = "20.087.324/0001-16";
  $dadosboleto["endereco"] = "R R 5 QUADRAD7 LOTE 76-77 SALA 608 SETOR OESTE CEP 74.125-070";
  $dadosboleto["cidade_uf"] = "GOIÂNIA - GO";
  $dadosboleto["cedente"] = "REAL POKER MESAS E FICHAS PERSONALIZADAS EIRELI-EPP";
  /*
 * 
 */
ob_start();

// NÃO ALTERAR!
include("include/funcoes_bradesco.php");
include("include/layout_bradesco.php");

$content = ob_get_clean();

// convert
require_once(dirname(__FILE__) . '/html2pdf/html2pdf.class.php');
try {
    $html2pdf = new HTML2PDF('P', 'A4', 'fr', array(0, 0, 0, 0));
    /* Abre a tela de impressão */
    //$html2pdf->pdf->IncludeJS("print(true);");

    $html2pdf->pdf->SetDisplayMode('real');

    $html2pdf->pdf->SetTitle('Pedido #' . $pedido['id'] . ' - ' . $sistema->_empresa['nome']);

    $html2pdf->pdf->SetAuthor($cliente['nome'] . ' - ' . $sistema->_empresa['nome']);

    $html2pdf->pdf->SetSubject('Pedido #' . $pedido['id'] . ' - ' . $sistema->_empresa['nome']);

    $html2pdf->pdf->SetKeywords('Pedido #' . $pedido['id'] . ' - ' . $sistema->_empresa['nome']);

    /* Parametro vuehtml = true desabilita o pdf para desenvolvimento do layout */
    $html2pdf->writeHTML($content, isset($_GET['vuehtml']));

    /* Abrir no navegador */
    $html2pdf->Output('realpoker-pedido-' . $pedido['id'] . '-boleto.pdf');

    /* Salva o PDF no servidor para enviar por email */
    //$html2pdf->Output('caminho/boleto.pdf', 'F');

    /* Força o download no browser */
    //$html2pdf->Output('boleto.pdf', 'D');
} catch (HTML2PDF_exception $e) {
    echo $e;
    exit;
}
