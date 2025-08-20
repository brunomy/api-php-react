<?php

session_start();

require_once '../sistema/System/Core/Loader.php';

use System\Core\Bootstrap;
use System\Libs\Notificacoes;
use classes\Product;

require_once "../_system.php";

$sistema = new Product();

$sistema->DB_connect();

$main_table = "tb_pedidos_pedidos";

$sys = new _sys();

$formulario = $sistema->formularioObjeto($_POST);

$validacao = valida($formulario);

$crop_sizes = array();

if (!isset($_SESSION["seo_session"])) {
    $_SESSION["seo_session"] = uniqid();
}

if (!$validacao->return) {
    echo json_encode($validacao);
} else {
    $resposta = new stdClass();
    $resposta->time = 4000;

    $data = $sistema->formularioObjeto($_POST, $main_table);

    $cliente = false;
    $clientes = $sistema->DB_fetch_array("SELECT * FROM tb_clientes_clientes WHERE id = {$_SESSION['cliente_id']}");
    if ($clientes->num_rows)
        $cliente = $clientes->rows[0];

    $cpf_cnpj = $cliente['cpf'];
    if($cliente['pessoa']==2){ $cpf_cnpj=$cliente['cnpj']; }

    //--vincula vendedor ----

        $vendedor = $sistema->DB_fetch_array("SELECT A.id_user, B.nome, B.email FROM tb_crm_crm A INNER JOIN tb_admin_users B ON A.id_user = B.id WHERE (A.cpf_cnpj LIKE '%{$cpf_cnpj}%' OR A.email = '{$cliente['email']}' OR A.telefone LIKE '%{$cliente['telefone']}%') AND A.ultima_atualizacao <= NOW() AND A.ultima_atualizacao >= NOW() - INTERVAL 5 DAY ORDER BY A.ultima_atualizacao DESC");

        if($vendedor->num_rows){
            $sistema->inserirRelatorio('atendente '.$vendedor->rows[0]['nome'].' vinculado à pedido pelo CRM');
            $data->id_vendedor = (int)$vendedor->rows[0]['id_user'];
            $data->agendor = 1;
        }else{
            //$data->id_vendedor = NULL;
        }

    //-----------------------

    $data->porcentagem_comissao = $sistema->_empresa['comissao_vendas'];

    $data->id_cliente = $_SESSION['cliente_id'];

    $data->valor_cupom = 0;
    $data->tipo_cupom = 0;
    $data->subtotal = "";
    $data->descontos = "";

    if (isset($_SESSION['cupom']['valor']) && $_SESSION['cupom']['valor'] != "")
        $data->valor_cupom = $_SESSION['cupom']['valor'];

    if (isset($_SESSION['cupom']['tipo_int']) && $_SESSION['cupom']['tipo_int'] != "")
        $data->tipo_cupom = $_SESSION['cupom']['tipo_int'];

    if (isset($_SESSION['cupom']['mensagem']) && $_SESSION['cupom']['mensagem'] != "")
        $data->mensagem_cupom = $_SESSION['cupom']['mensagem'];

    $data->metodo_pagamento = $formulario->metodo_pagamento;

    $data->valor_frete = $formulario->frete;
    $data->frete = $formulario->frete_nome;

    if (isset($formulario->frete_prazo) && $formulario->frete_prazo != ""){
        $data->prazo_entrega = date('Y-m-d', strtotime("+$formulario->frete_prazo days", strtotime(date('Y-m-d'))));
        $data->dias_entrega = $formulario->frete_prazo;
    }else{
        $data->prazo_entrega = date('Y-m-d');
        $data->dias_entrega = 0;
    }

    $data->code = $sistema->uniqueNumber(10, "tb_pedidos_pedidos", "code");

    if ($formulario->metodo_pagamento == "deposito" || $formulario->metodo_pagamento == "boleto" || $formulario->metodo_pagamento == "pokerstars") {
        //a vista [ganha 5% de desconto]
        $data->avista = 1;
    } else {
        //a prazo
        $data->avista = 2;
    }

    $sistema->calcAction();
    $info = $sistema->calcTotalCarrinhoBySession();
    if (isset($info['valor']))
        $data->subtotal = $info['valor'];

    if (isset($info['desconto']))
        $data->descontos = $info['desconto'];

    // TODO: FIX - Cupom de porcentagem tem o valor alterado pelo frete
    $valor_final = $data->subtotal + $data->valor_frete - $data->descontos;

    if ($data->tipo_cupom != 1)
        $valor_cupom = $data->valor_cupom;
    else
        $valor_cupom = (($valor_final * $data->valor_cupom) / 100);

    $valor_final = $valor_final- $valor_cupom;

    if ($data->avista == 1)
        $valor_final = $valor_final - (($valor_final * 5) / 100);

    $data->valor_final = $valor_final;

    $data->ip = $_SERVER['REMOTE_ADDR'];
    $data->session = $_SESSION["seo_session"];

    foreach ($data as $key => $value) {
        $fields[] = $key;
        $values[] = "'$value'";
    }

    //echo "<pre>";print_r($fields);echo "</pre>";
    //echo "<pre>";print_r($values);echo "</pre>";
    //if($cliente['email']!='joao@hibrida.biz'){
        $query = $sistema->DB_insert($main_table, implode(',', $fields), implode(',', $values));
        $idPedido = $query->insert_id;
    //}

    unset($fields, $values);
    unset($_SESSION['tipo_cliente']);
    if ($query->query) {

        $sistema->DB_update("tb_carrinho_produtos_historico", "id_pedido = $idPedido WHERE session = '{$_SESSION["seo_session"]}' AND id_pedido IS NULL");

        // SETA O VALOR DO FRETE EMBUTIDO NOS PRODUTOS DO PEDIDO
        $sistema->mysqli->query('UPDATE tb_carrinho_produtos_historico a INNER JOIN tb_produtos_produtos b ON a.id_produto = b.id SET a.frete_embutido = b.frete_embutido WHERE a.id_pedido = '.$idPedido);

        if (isset($formulario->newsletter) && $formulario->newsletter) {
            newsletter($formulario);
        }

        $sistema->calcAction();

        $dados = $sistema->formularioObjeto($_POST, "tb_pedidos_enderecos");
        $dados->id_pedido = $idPedido;
        foreach ($dados as $key => $value) {
            $fields[] = $key;
            $values[] = "'$value'";
        }

        $sistema->DB_insert("tb_pedidos_enderecos", implode(',', $fields), implode(',', $values));

        $endereco = false;
        $enderecos = $sistema->DB_fetch_array("SELECT A.*, C.cidade, E.estado, E.uf FROM tb_pedidos_enderecos A INNER JOIN tb_utils_cidades C ON C.id = A.id_cidade INNER JOIN tb_utils_estados E ON E.id = C.id_estado WHERE A.id_pedido = $idPedido");
        if ($enderecos->num_rows)
            $endereco = $enderecos->rows[0];

        /*
        $cliente = false;
        $clientes = $sistema->DB_fetch_array("SELECT * FROM tb_clientes_clientes WHERE id = {$_SESSION['cliente_id']}");
        if ($clientes->num_rows)
            $cliente = $clientes->rows[0];
        */

        //PREPARAR E-MAILS

        $to[] = array("email" => $formulario->email, "nome" => utf8_decode($cliente['nome']));

        $emails = $sistema->DB_fetch_array("SELECT C.email, C.nome FROM tb_admin_forms A INNER JOIN tb_admin_email_notification B ON A.id = B.id_form INNER JOIN tb_admin_users C ON B.id_user = C.id WHERE A.id = 1 OR A.id = 5 GROUP BY C.email, C.nome");
        if ($emails->num_rows) {

            if ($emails->num_rows) {
                foreach ($emails->rows as $mail) {
                    $to[] = array("email" => $mail['email'], "nome" => utf8_decode($mail['nome']));
                }
            }
        }

        if(isset($vendedor) && $vendedor->num_rows){
            $to[] = array("email" => $vendedor->rows[0]['email'], "nome" => utf8_decode($vendedor->rows[0]['nome']));
        }

        if($data->dias_entrega == 0){
            $sistema->inserirRelatorio("Erro: prazo de entrega zerado! Pedido: " . $idPedido . ", data de entrega ".$data->prazo_entrega.", seo_session=".$_SESSION['seo_session']);
            $sistema->enviarEmail('jfelipesilva@gmail.com', 'contato@realpoker.com', 'Prazo de entrega zerado $idPedido', utf8_decode("Erro: prazo de entrega zerado! Pedido: " . $idPedido . ", data de entrega ".$data->prazo_entrega));
        }

        $mensagem = "
                        <p style='text-align: center;'><a href='{$sistema->root_path}?utm_source=rodape_email_transacoes'><img src='{$sistema->root_path}/files/pedido.jpg' alt='' width='600' height='119' /></a></p>
                        <table style='height: 278px; margin-left: auto; margin-right: auto;' width='592'>
                        <tbody>
                        <tr>
                        <td style='text-align: left;'>
                        Olá, <strong>{$cliente['nome']}</strong>,<br>
                        <span style='font-size: 18pt;'>Parabéns pela sua compra!</span><br><br>
                        Estaremos acompanhando de perto o seu pedido e te informaremos<br>
                        por aqui todos os passos até que ele seja entregue.<br><br>
                        
                        Assim que recebermos a confirmação de pagamento,<br>
                        encaminharemos para produção. Caso ainda não tenha feito o<br>
                        pagamento, você pode rever as instruções no seu painel em nosso<br>
                        site no link abaixo:

                        <br /><br /><span style='color: #008000; font-size: 14pt;'><a href='{$sistema->root_path}minha-conta' target='_blank'><span style='color: #008000;'><strong>http://www.realpoker.com.br/minha-conta</strong> </span></a></span><strong><br /><br />
                        ";

        if ($formulario->metodo_pagamento == "pokerstars")
            $frt = $data->valor_frete / $sys->cotacao_dollar;
        else
            $frt = $data->valor_frete;

        $frt = "<b>Valor do Frete:</b> R$ {$sistema->formataMoedaShow($frt)}<br>";
        if(stripos(mb_strtolower($data->frete), 'consultar'))
            $frt = "<b>Valor do Frete:</b> À Consultar<br>";

        $mensagem .= "
                        <b>Nome:</b> {$cliente['nome']}<br>
                        ".(
                    ($cliente['pessoa']==2) ?
                    "
                        <b>Razão Social:</b> {$cliente['razao_social']}<br>
                        <b>CNPJ:</b> {$cliente['cnpj']}<br>
                        <b>Inscrição Estadual:</b> {$cliente['inscricao_estadual']}<br>
                    " :
                    "
                        <b>CPF:</b> {$cliente['cpf']}<br> 
                    "
                     )."<b>E-mail:</b> $formulario->email<br>
                        <b>Endereço:</b> $formulario->endereco, $formulario->complemento<br>
                        <b>Número:</b> $formulario->numero<br>
                        <b>Bairro:</b> $formulario->bairro<br>
                        <b>Cidade:</b> {$endereco['cidade']}<br>
                        <b>Estado:</b> {$endereco['uf']}<br>
                        <b>CEP:</b> $formulario->cep<br>".
                        /*<b>Prazo para Entrega:</b> {$data->dias_entrega} dias úteis<br>*/
                        $frt."
                        <b>Pedido:</b> $idPedido<br><br>
                            
                        ";

        $forma_pagamento = "Forma Desconhecida";
        if ($formulario->metodo_pagamento == "deposito") {
            $forma_pagamento = "Depósito";
        } else if ($formulario->metodo_pagamento == "boleto") {
            $forma_pagamento = "Boleto";
        } else if ($formulario->metodo_pagamento == "cielo") {
            $forma_pagamento = "Cielo";
        } else if ($formulario->metodo_pagamento == "cielo_transparente") {
            $forma_pagamento = "Cartão de Crédito";
        } else if ($formulario->metodo_pagamento == "rede_transparente") {
            $forma_pagamento = "Cartão de Crédito";
        } else if ($formulario->metodo_pagamento == "pagseguro") {
            $forma_pagamento = "Pagseguro";
        } else if ($formulario->metodo_pagamento == "pokerstars") {
            $forma_pagamento = "Pokerstars";
        }

        $mensagem .= "<b>Forma de Pagamento:</b> $forma_pagamento<br><br>";

        if ($data->avista == 1)
            $mensagem .= "<b>Você ganhou 5% de desconto por escolher um pagamento à vista.</b><br><br>";

        if ($data->tipo_cupom != "")
            $mensagem .= "<b>{$_SESSION['cupom']['mensagem']}</b><br><br>";


        if ($formulario->metodo_pagamento == "pokerstars")
            $mensagem .= "<b>Valor:</b> US$ {$sistema->formataMoedaShow($data->valor_final / $sys->cotacao_dollar)}<br><br><br>";
        else
            $mensagem .= "<b>Valor:</b> R$ {$sistema->formataMoedaShow($data->valor_final)}<br><br><br>";


        $mensagem .= "<b>Detalhes do Pedido:</b><br><br>";

        //#open  Montar Demonstrativo de Produtos
        $produtos = $sistema->getCartProductsByPedido($idPedido);
        foreach ($produtos->rows as $produto) {
            $atributos = $sistema->getAtributosInfoByProduto($produto['id']);
            $mensagem .= "<b>Produto:</b> {$produto['nome_produto']}<br>";

            if ($atributos != '') {
                foreach ($atributos->rows as $atributo) {
                    $mensagem .= "<b>{$atributo['nome_conjunto']}:</b> {$atributo['nome_atributo']}<br>";
                    if ($atributo['texto'] != "") {
                        $mensagem .= "[{$atributo['texto']}]<br>";
                    }
                    if ($atributo['cor'] != "") {
                        $mensagem .= "<i style='display:inline-block;width:100px;padding:0 10px;color:#fff;background-color:{$atributo['cor']}'><i style='color: {$atributo['cor']};-webkit-filter: invert(100%);filter: invert(100%);'>{$atributo['cor']}</i></i><br>";
                    }
                    if ($atributo['arquivo'] != "") {
                        $mensagem .= "<a style='font-size: 12px' target='_blank' href='" . $sistema->root_path . "uploads/" . $atributo['arquivo'] . "'>[{$atributo['valor']}]</a><br>";
                    }
                }
            }
            if($produto['desconto']=="") $produto['desconto']=0;
            $mensagem .= "<br><br><b>Quantidade:</b> {$produto['quantidade']}<br>";
            if ($formulario->metodo_pagamento == "pokerstars")
                $mensagem .= "<b>Total:</b> R$ {$sistema->formataMoedaShow((($produto['valor_produto'] * $produto['quantidade']) - ($produto['desconto'] * $produto['quantidade'])) / $sys->cotacao_dollar)}<br><br>";
            else
                $mensagem .= "<b>Total:</b> R$ {$sistema->formataMoedaShow(($produto['valor_produto'] * $produto['quantidade']) - ($produto['desconto'] * $produto['quantidade']))}<br><br>";
        }
        //#close Montar Demonstrativo de Produtos
        //$mensagem .= "<span style='color:#ff0000'>Veja detalhes do pedido ao fazer login no site da Real Poker:</span> <a href='$sistema->root_path' target='_blank'>$sistema->root_path</a>.<br><br>";

        $mensagem .= "<b>Política de entrega de produtos:</b><br><br>";

        $mensagem .= "
                        Por favor, confira as dimensões do produto e certifique-se de que estão adequadas<br>
                        aos elevadores, portas e corredores do local da entrega, pois não fazemos a<br>
                        montagem, desmontagem de portas e janelas, transporte pela escada ou içamento<br>
                        pelo lado de fora do prédio.<br><br>

                        A montagem do produto é feita pelo cliente por encaixe e trava das peças, sem<br>
                        necessidade de um profissional especializado.<br><br> 

                        A transportadora se responsabiliza pela entrega do produto no endereço informado,<br>
                        no caso de apartamento, por lei, não tem o dever de levar o produto até o andar<br>
                        do cliente, em caso de condomínio residencial entregam até a portaria e em nenhum<br>
                        endereço entram com as mercadorias no interior da casa/apartamento.<br><br>

                        O endereço de entrega preenchido no pedido é de responsabilidade do cliente, por<br>
                        favor confira novamente o endereço preenchido e nos avise se não estiver correto.<br>
                        Caso a entrega não seja feita por endereço errado, os custos da reentrega são do<br>
                        cliente.<br><br>

                        Após a conclusão do pedido, caso não seja enviado dentro de um mês, será necessário<br>
                        abrir as embalagens para envio posterior. Qualquer ajuste necessário devido ao tempo<br>
                        de armazenamento do produto, após esse prazo de 30 dias da embalagem, será de<br>
                        responsabilidade do cliente.<br><br>
                        
                        Atenciosamente,<br>
                        equipe <strong>Real Poker</strong><br><br>
                        </td>
                        </tr>
                        </tbody>
                        </table>

                        <p><a href='{$sistema->root_path}?utm_source=rodape_email_transacoes'><img style='display: block; margin-left: auto; margin-right: auto;' src='{$sistema->root_path}/files/rodapeemail.jpg' alt='' width='600' height='241' /></a></p>
                        ";

        $assunto = "Pedido Finalizado #$idPedido [{$cliente['nome']}]";  // Assunto da mensagem de contato.
        //$body = file_get_contents("../mailing_templates/form_pedido.html");
        //$body = str_replace("{MENSAGEM}", $mensagem, $body);


        // BUSCA NOME DAS CATEGORIAS DOS PRODUTOS DO PEDIDO PARA TAGEAR O HEADER DO EMAIL
        $categorias = $sistema->DB_fetch_array("SELECT DISTINCT(c.nome) FROM tb_carrinho_produtos_historico a INNER JOIN tb_produtos_produtos_has_tb_produtos_categorias b ON a.id_produto = b.id_produto INNER JOIN tb_produtos_categorias c ON b.id_categoria = c.id WHERE a.id_pedido = $idPedido");
        $xmctags = '';
        if ($categorias->num_rows) {
            foreach ($categorias->rows as $categoria) {
                $xmctagscategorias[] = $categoria['nome'];
            }
            $xmctags = 'X-MC-Tags: '.implode(',', $xmctagscategorias);
        }

        if(isset($vendedor) && $vendedor->num_rows){
            $setFrom = array(array('email'=>$vendedor->rows[0]['email'], 'nome'=>'Real Poker'));
        }else{
            $setFrom = '';
        }

        $sistema->enviarEmail($to, $setFrom, utf8_decode($assunto), utf8_decode($mensagem),'', utf8_decode($xmctags));

        // Envia mensagem por whatsapp
        $telefones = [
            // $cliente['telefone'],
        ];

        $sistema->DB_update("tb_seo_acessos", "compra = 1 WHERE id_seo = $formulario->id_seo AND session = '{$_SESSION["seo_session"]}' AND compra IS NULL ORDER BY id DESC LIMIT 1");
        $sistema->DB_update("tb_seo_acessos_historicos", "compra = 1 WHERE id_seo = $formulario->id_seo AND session = '{$_SESSION["seo_session"]}' AND compra IS NULL ORDER BY id DESC LIMIT 1");

        $sistema->clearCupomAction();

        //verifica se o frete é à consultar e dispara alerta
        if(stripos(mb_strtolower($data->frete), 'consultar')){
            $vars = new stdClass();
            $vars->PEDIDO = $idPedido;
            $vars->DATAHORA = date('d/m/Y').' às '.date('H:m');
            $vars->CLIENTE = $cliente['nome'];
            $vars->CEP = $formulario->cep;
            $vars->ENDERECO = $formulario->endereco . ', nº '. $formulario->numero . ', '. $formulario->complemento . ' - '. $formulario->bairro;
            $vars->CIDADE = $endereco['cidade'];
            $vars->ESTADO = $endereco['uf'];
            (new Notificacoes($sistema, $vars, 'cotacao-frete',$vars))->disparaNotificao();
        }

        $resposta->metodo = $formulario->metodo_pagamento;
        $resposta->pedido = $idPedido;
        $resposta->id_cliente = $_SESSION['cliente_id'];
        $resposta->type = "success";
        $resposta->time = 4000;
        $resposta->message = "Estamos lhe direcionando para finalizar o pagamento!!";
        $sistema->inserirRelatorio("Checkout cliente id: [" . $_SESSION['cliente_id'] . "] pedido id: [" . $idPedido . "]");
    } else {

        $resposta->type = "error";
        $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
    }

    echo json_encode($resposta);
}

$sistema->DB_disconnect();

function valida($form) {



    $resposta = new stdClass();
    $resposta->return = true;
    global $sistema, $main_table;



    if (isset($_SESSION['cliente_id']) && $_SESSION['cliente_id'] != "" && (!isset($form->frete) || $form->frete == "")) {
        $resposta->type = "attention";
        $resposta->message = "Selecione o método de envio [FRETE]";
        $resposta->field = "frete";
        $resposta->return = false;
        return $resposta;
    } else if (isset($_SESSION['cliente_id']) && $_SESSION['cliente_id'] != "" && $form->frete_nome == "") {
        $resposta->type = "attention";
        $resposta->message = "Selecione o método de envio [FRETE]";
        $resposta->field = "frete";
        $resposta->return = false;
        return $resposta;
    } else if (!isset($form->termos)) {
        $resposta->type = "attention";
        $resposta->message = "Para realizar sua compra, você precisa aceitar nossos Termos de Uso";
        $resposta->field = "termos";
        $resposta->time = 5000;
        $resposta->return = false;
        return $resposta;
    } else if (isset($_SESSION['cliente_id']) && $_SESSION['cliente_id'] != "") {
        return validaEndereco($form);
    } else {
        return validaCadastro($form);
    }
}

function validaEndereco($form) {
    $resposta = new stdClass();
    $resposta->return = true;
    $form->cep = str_replace("_", "", $form->cep);
    if (trim($form->cep) == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "cep";
        $resposta->return = false;
        return $resposta;
    } else if (strlen($form->cep) != 10) {
        $resposta->type = "validation";
        $resposta->message = "Preencha todo o campo";
        $resposta->field = "cep";
        $resposta->return = false;
        return $resposta;
    } else if (trim($form->endereco) == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "endereco";
        $resposta->return = false;
        return $resposta;
    } else if (trim($form->bairro) == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "bairro";
        $resposta->return = false;
        return $resposta;
    }else if ($form->id_estado == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "id_estado";
        $resposta->return = false;
        return $resposta;
    } else if ($form->id_cidade == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "id_cidade";
        $resposta->return = false;
        return $resposta;
    } else {
        return $resposta;
    }
}

function validaCadastro($form) {

    $resposta = new stdClass();
    $resposta->return = true;

    //$sistema = new sistema();
    global $sistema, $main_table;

    if ($form->pessoa == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "pessoa";
        $resposta->return = false;
        return $resposta;
    } else if ($form->pessoa == 2) {
        if ($form->cnpj != "") {
            $verificacnpj = $sistema->DB_fetch_array("SELECT id FROM tb_clientes_clientes WHERE cnpj = '$form->cnpj'");
        }
        if ($form->razao_social == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo corretamente";
            $resposta->field = "razao_social";
            $resposta->return = false;
            return $resposta;
        } else if ($form->cnpj == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo corretamente";
            $resposta->field = "cnpj";
            $resposta->return = false;
            return $resposta;
        } else if (!$sistema->validaCNPJ($form->cnpj)) {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo corretamente";
            $resposta->field = "cnpj";
            $resposta->return = false;
            return $resposta;
        } else if ($verificacnpj->num_rows) {
            $resposta->type = "validation";
            $resposta->message = "CNPJ já cadastrado, faça login antes de continuar";
            $resposta->field = "cnpj";
            $resposta->return = false;
            return $resposta;
        } else {
            return validaFormularioContinuacao($form);
        }
    } else {
        return validaFormularioContinuacao($form);
    }
}

function validaFormularioContinuacao($form) {
    $resposta = new stdClass();
    $resposta->return = true;

    global $sistema, $main_table;

    $newNumberFormat = $form->telefone;
    if($form->telefone){
        list($ddd, $numero) =  explode(' ', $form->telefone);

        $numero = str_replace('_', '', $numero);
        if(strlen($numero) == 9){
            $newNumberFormat = $ddd . ' ' . substr($numero, 1);
        } else if(strlen($numero) == 8){
            $newNumberFormat = $ddd . ' ' . '9' . $numero;
        }
    }

    $query = $sistema->DB_fetch_array("SELECT * FROM tb_clientes_clientes WHERE (( email != '' and email = '".$form->email."') or (cpf != '' and cpf = '".$form->cpf."') or (cnpj != '' and cnpj = '".$form->cnpj."') or (telefone != '' and telefone = '".$form->telefone."') or (telefone != '' and telefone = '".$newNumberFormat."')) and senha is null");
    $positionDados = -1;
    if($query->num_rows > 0){
        $positionDados = 0;
        foreach ($query->rows as $key => $result){
            if($result['email'] == $form->email){
                $positionDados = $key;
                break;
            }
        }
    }

    if($positionDados == -1){
        if ($form->cpf != "") {
            $verificacpf = $sistema->DB_fetch_array("SELECT id FROM tb_clientes_clientes WHERE cpf = '$form->cpf'");
        }

        if ($form->email != "" && $sistema->validaEmail($form->email)) {
            $verificaemail = $sistema->DB_fetch_array("SELECT id FROM tb_clientes_clientes WHERE email = '$form->email'");
        }
    }


    if ($form->nome == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "nome";
        $resposta->return = false;
        return $resposta;
    } else if ($form->pessoa == 1 && $form->cpf == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "cpf";
        $resposta->return = false;
        return $resposta;
    } else if ($form->pessoa == 1 && !$sistema->validaCPF($form->cpf)) {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "cpf";
        $resposta->return = false;
        return $resposta;
    } else if ($verificacpf->num_rows) {
        $resposta->type = "validation";
        $resposta->message = "CPF já cadastrado, faça login antes de continuar";
        $resposta->field = "cpf";
        $resposta->return = false;
        return $resposta;
    } else if ($form->email == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "email";
        $resposta->return = false;
        return $resposta;
    } else if ($sistema->validaEmail($form->email) == 0) {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo com um E-mail válido";
        $resposta->field = "email";
        $resposta->return = false;
        return $resposta;
    } else if ($verificaemail->num_rows) {
        $resposta->type = "validation";
        $resposta->message = "E-mail já cadastrado, faça login antes de continuar";
        $resposta->field = "email";
        $resposta->return = false;
        return $resposta;
    } else if ($form->telefone == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "telefone";
        $resposta->return = false;
        return $resposta;
    } else if ($form->senha == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "senha";
        $resposta->return = false;
        return $resposta;
    } else if ($form->senha != $form->senha2) {
        $resposta->type = "validation";
        $resposta->message = "As senhas não conferem";
        $resposta->field = "senha";
        $resposta->return = false;
        return $resposta;
    } else if ($form->cep == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "cep";
        $resposta->return = false;
        return $resposta;
    } else if (trim($form->endereco) == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "endereco";
        $resposta->return = false;
        return $resposta;
    } else if (trim($form->bairro) == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "bairro";
        $resposta->return = false;
        return $resposta;
    } else if ($form->id_estado == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "id_estado";
        $resposta->return = false;
        return $resposta;
    } else if ($form->id_cidade == "") {
        $resposta->type = "validation";
        $resposta->message = "Preencha este campo corretamente";
        $resposta->field = "id_cidade";
        $resposta->return = false;
        return $resposta;
    } else if (!isset($form->frete) || $form->frete == "") {
        $resposta->type = "attention";
        $resposta->message = "Selecione o método de envio [FRETE]";
        $resposta->field = "frete";
        $resposta->return = false;
        return $resposta;
    } else if ($form->frete_nome == "") {
        $resposta->type = "attention";
        $resposta->message = "Selecione o método de envio [FRETE]";
        $resposta->field = "frete_nome";
        $resposta->return = false;
        return $resposta;
    } else {

        $formulario = $sistema->formularioObjeto($_POST);
        $data = $sistema->formularioObjeto($_POST, "tb_clientes_clientes");

        $data->ip = $_SERVER['REMOTE_ADDR'];
        $data->session = $_SESSION["seo_session"];
        $data->senha = $sistema->embaralhar($data->senha);
        $data->stats = 1;

        if($query->num_rows) {
            foreach ($data as $key => $value) {
                if ($value == "NULL") {
                    $fields_values[] = "$key=$value";
                } else {
                    $fields_values[] = "$key='$value'";
                }
            }

            $idCliente = $query->rows[$positionDados]['id'];



            $query = $sistema->DB_update("tb_clientes_clientes", implode(',', $fields_values) . " WHERE id=" . $query->rows[$positionDados]['id']);

        } else {
            foreach ($data as $key => $value) {
                $fields[] = $key;
                $values[] = "'$value'";
            }

            $query = $sistema->DB_insert("tb_clientes_clientes", implode(',', $fields), implode(',', $values));
            $idCliente = $query->insert_id;

            $query = $query->query;
        }

        if ($query) {

            $_SESSION['cliente_logado'] = true;
            $_SESSION['cliente_id'] = $idCliente;
            $_SESSION['cliente_nome'] = $data->nome;
            $_SESSION['cliente_email'] = $data->email;
            $_SESSION['cliente_cep'] = $data->cep;
            $_SESSION['cliente_endereco'] = $data->endereco;
            $_SESSION['cliente_numero'] = $data->numero;
            $_SESSION['cliente_bairro'] = $data->bairro;
            $_SESSION['cliente_complemento'] = $data->complemento;
            $_SESSION['cliente_id_cidade'] = $data->id_cidade;
            $_SESSION['cliente_id_estado'] = $data->id_estado;


            $verifica = $sistema->DB_num_rows("SELECT * FROM tb_emails_emails A INNER JOIN tb_listas_listas_has_tb_emails_emails B ON B.id_email = A.id AND A.email = '$formulario->email' AND B.id_lista = 3");
            if (!$verifica) {
                $verifica = $sistema->DB_fetch_array("SELECT * FROM tb_emails_emails WHERE email = '$formulario->email'");
                if (!$verifica->num_rows) {
                    $addEmail = $sistema->DB_insert('tb_emails_emails', "nome,email", "'$formulario->nome','$formulario->email'");
                    $idEmail = $addEmail->insert_id;
                } else {
                    $idEmail = $verifica->rows[0]['id'];
                }
                $addListaHasEmail = $sistema->DB_insert('tb_listas_listas_has_tb_emails_emails', "id_lista,id_email", "3,$idEmail");
            }


            // PREPARA EMAIL -------------------
            $emails = $sistema->DB_fetch_array("SELECT C.email, C.nome FROM tb_admin_forms A INNER JOIN tb_admin_email_notification B ON A.id = B.id_form INNER JOIN tb_admin_users C ON B.id_user = C.id WHERE A.id = 1 OR A.id = 4 GROUP BY B.id_user");

            if ($emails->num_rows) {

                foreach ($emails->rows as $mail) {
                    $to[] = array("email" => $mail['email'], "nome" => utf8_decode($mail['nome']));
                }

                $cidades = $sistema->DB_fetch_array("SELECT cidade FROM tb_utils_cidades WHERE id = $data->id_cidade");
                $cidade = $cidades->rows[0]['cidade'];


                $estados = $sistema->DB_fetch_array("SELECT estado FROM tb_utils_estados WHERE id = $data->id_estado");
                $estado = $estados->rows[0]['estado'];

                $assunto = "Formulário Cadastro de Cliente [$formulario->nome]";  // Assunto da mensagem de contato.

                if ($formulario->pessoa == 1)
                    $pessoa = "Pessoa Física";
                else
                    $pessoa = "Pessoa Jurídica";

                $body = file_get_contents("../mailing_templates/form_cadastro.html");
                $body = str_replace("{PESSOA}", $pessoa, $body);
                $body = str_replace("{NOME}", $formulario->nome, $body);
                $body = str_replace("{RAZAO_SOCIAL}", $formulario->razao_social, $body);
                $body = str_replace("{CNPJ}", $formulario->cnpj, $body);
                $body = str_replace("{INSCRICAO_ESTADUAL}", $formulario->inscricao_estadual, $body);
                $body = str_replace("{EMAIL}", $formulario->email, $body);
                $body = str_replace("{TELEFONE}", $formulario->telefone, $body);
                $body = str_replace("{CPF}", $formulario->cpf, $body);
                $body = str_replace("{CEP}", $formulario->cep, $body);
                $body = str_replace("{ENDERECO}", $formulario->endereco, $body);
                $body = str_replace("{NUMERO}", $formulario->numero, $body);
                $body = str_replace("{BAIRRO}", $formulario->bairro, $body);
                $body = str_replace("{COMPLEMENTO}", $formulario->complemento, $body);
                $body = str_replace("{CIDADE}", $cidade, $body);
                $body = str_replace("{ESTADO}", $estado, $body);

                $sistema->enviarEmail($to, $formulario->email, utf8_decode($assunto), utf8_decode($body));
            }


            unset($to);
            //EMAIL PARA CLIENTE
            $to[] = array("email" => $formulario->email, "nome" => utf8_decode($formulario->nome));

            $mensagem_cliente = "Você está recebendo este e-mail pois se cadastrou no site $sistema->root_path.<br><br> Caso não tenha realizado este cadastro por favor desconsidere este e-mail!";

            $assunto = "Formulário Cadastro de Cliente [$formulario->nome]";  // Assunto da mensagem de contato.

            $body = file_get_contents("../mailing_templates/form_cadastro_cliente.html");
            $body = str_replace("{MENSAGEM_CLIENTE}", $mensagem_cliente, $body);
            $body = str_replace("{PESSOA}", $pessoa, $body);
            $body = str_replace("{NOME}", $formulario->nome, $body);
            $body = str_replace("{RAZAO_SOCIAL}", $formulario->razao_social, $body);
            $body = str_replace("{CNPJ}", $formulario->cnpj, $body);
            $body = str_replace("{INSCRICAO_ESTADUAL}", $formulario->inscricao_estadual, $body);
            $body = str_replace("{EMAIL}", $formulario->email, $body);
            $body = str_replace("{TELEFONE}", $formulario->telefone, $body);
            $body = str_replace("{CPF}", $formulario->cpf, $body);
            $body = str_replace("{CEP}", $formulario->cep, $body);
            $body = str_replace("{ENDERECO}", $formulario->endereco, $body);
            $body = str_replace("{NUMERO}", $formulario->numero, $body);
            $body = str_replace("{BAIRRO}", $formulario->bairro, $body);
            $body = str_replace("{COMPLEMENTO}", $formulario->complemento, $body);
            $body = str_replace("{CIDADE}", $cidade, $body);
            $body = str_replace("{ESTADO}", $estado, $body);

            $sistema->enviarEmail($to, '', utf8_decode($assunto), utf8_decode($body));

            $fields_values[] = "ultimo_acesso='" . date("Y-d-m H:i:s") . "'";

            $sistema->DB_update('tb_clientes_clientes', implode(',', $fields_values) . " WHERE email = '$formulario->email'");

            $sistema->DB_update("tb_seo_acessos", "cadastro = 1 WHERE id_seo = $formulario->id_seo AND session = '{$_SESSION["seo_session"]}' ORDER BY id DESC LIMIT 1");
            $sistema->DB_update("tb_seo_acessos_historicos", "cadastro = 1 WHERE id_seo = $formulario->id_seo AND session = '{$_SESSION["seo_session"]}' ORDER BY id DESC LIMIT 1");
            $sistema->inserirRelatorio("Cliente: [" . $formulario->email . "] Id: [" . $idCliente . "]");
        } else {
            $resposta->return = false;
            $resposta->type = "attention";
            $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
        }

        return $resposta;
    }
}

function newsletter($formulario) {
    global $sistema;

    $fields = array(
        'email',
        'ip',
        'session'
    );

    $values = array(
        '"' . $formulario->email . '"',
        '"' . $_SERVER['REMOTE_ADDR'] . '"',
        '"' . $_SESSION["seo_session"] . '"'
    );

    $existe = $sistema->DB_fetch_array("SELECT * FROM tb_newsletters_newsletters WHERE email = '$formulario->email'");
    if (!$existe->num_rows) {

        $query = $sistema->DB_insert("tb_newsletters_newsletters", implode(',', $fields), implode(',', $values));
        $idContato = $query->insert_id;

        if ($query->query) {

            $verifica = $sistema->DB_num_rows("SELECT * FROM tb_emails_emails A INNER JOIN tb_listas_listas_has_tb_emails_emails B ON B.id_email = A.id AND A.email = '$formulario->email' AND B.id_lista = 2");
            if (!$verifica) {
                $verifica = $sistema->DB_fetch_array("SELECT * FROM tb_emails_emails WHERE email = '$formulario->email'");
                if (!$verifica->num_rows) {
                    $addEmail = $sistema->DB_insert('tb_emails_emails', "email", "'$formulario->email'");
                    $idEmail = $addEmail->insert_id;
                } else {
                    $idEmail = $verifica->rows[0]['id'];
                }
                $addListaHasEmail = $sistema->DB_insert('tb_listas_listas_has_tb_emails_emails', "id_lista,id_email", "2,$idEmail");
            }

            // PREPARA EMAIL -------------------
            $emails = $sistema->DB_fetch_array("SELECT C.email, C.nome FROM tb_admin_forms A INNER JOIN tb_admin_email_notification B ON A.id = B.id_form INNER JOIN tb_admin_users C ON B.id_user = C.id WHERE A.id = 1 OR A.id = 3 GROUP BY B.id_user");

            if ($emails->num_rows) {

                foreach ($emails->rows as $mail) {
                    $to[] = array("email" => $mail['email'], "nome" => utf8_decode($mail['nome']));
                }

                $assunto = "Formulário Newsletter.";  // Assunto da mensagem de contato.

                $body = file_get_contents("../mailing_templates/form_newsletter.html");
                $body = str_replace("{EMAIL}", $formulario->email, $body);

                $sistema->enviarEmail($to, $formulario->email, utf8_decode($assunto), utf8_decode($body));
            }

            $sistema->inserirRelatorio("Newsletter: [" . $formulario->email . "] Id: [" . $idContato . "]");
        }
    }
}

?>
