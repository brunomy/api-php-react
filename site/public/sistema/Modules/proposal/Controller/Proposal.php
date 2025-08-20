<?php

use System\Core\Bootstrap;
use classes\Product;

Proposal::setAction();

class Proposal extends Bootstrap {

    public $module = "";
    public $permissao_ref = "orcamentos";
    public $table = "tb_pedidos_pedidos";

    function __construct() {
        parent::__construct();
        if (!isset($_SESSION['admin_logado'])) {
            header("Location: " . $this->system_path . "login");
            exit;
        }
        $this->getAllPermissions();

        $this->module_icon = "icomoon-icon-copy";
        $this->module_link = "proposal";
        $this->module_title = "Orçamentos";
        $this->retorno = "proposal";

        $this->etapas = [
            'orcamento' => 'Orçamento',
            'followup' => 'Follow-Up',
            'fechamento' => 'Fechamento',
            'desativado' => 'Desativado',
        ];

        $this->status = [
            'aberto' => 'Aberto',
            'ganho' => 'Ganho',
            'perda' => 'Perda',
        ];

        $this->product = new Product();
    }

    private function indexAction() {
        if (!$this->permissions[$this->permissao_ref]['ler']) {
            $this->noPermission();
        }

        // Estados para o modal de criação de usuários
        $query = $this->DB_fetch_array("SELECT * FROM tb_utils_estados ORDER BY estado");
        $this->estados = ($query->num_rows) ? $query->rows : [];

        // Categorias de produtos para os checkbox de filtro
        $query = $this->DB_fetch_array("SELECT id, nome FROM tb_produtos_categorias WHERE stats <> 0");
        $this->categorias = ($query->num_rows) ? $query->rows : [];

        $this->renderView($this->getModule(), "index");
    }

    //--------------- Buscar lista de usuários ------------------//

    private function searchClientsAction() {
        $termo = $_GET['q'] ?? '';
        $limite = $_GET['per_page'] ?? 10;
        $ultimo = $_GET['last_id'] ?? 0;

        $resposta = [
            'results' => [],
            'more' => false
        ];

        // Busca um a mais para saber se ainda existem mais resultados
        $lim = $limite + 1;
        $query = $this->DB_fetch_array("SELECT id, nome FROM tb_clientes_clientes WHERE nome LIKE '%{$termo}%' AND id > {$ultimo} ORDER BY id LIMIT {$lim}");
        if ($query->num_rows) {
            // Adiciona os resultados
            foreach ($query->rows as $i => $row) {
                if ($i >= $limite) break;
                $resposta['results'][] = [
                    'id' => $row['id'],
                    'text' => "#{$row['id']} - {$row['nome']}",
                ];
            }
            // Seta a flag de paginação
            if ($query->num_rows > $limite) {
                $resposta['more'] = true;
            }
        }

        echo json_encode($resposta);
    }

    //--------------- Retorna HTML da tabela de totais ------------------//

    private function totalsAction() {
        if (!$this->permissions[$this->permissao_ref]['ler']) exit();

        // IDs de categorias de produto para aplicar o filtro
        $having = ($_POST['categories_array']) ? 'HAVING ' . implode(' OR ', array_map(function($c) {
            return "cat_ids LIKE '%{$c}%'";
        }, explode(',', ($_POST['categories_array'])))) : '';

        // Limita o numero de elementos por data
        $where = "WHERE A.orc_status IS NOT NULL AND A.orc_etapa IS NOT NULL AND A.orc_etapa <> 'desativado'";
        if ($_POST['from_date'] && $_POST['to_date']) {
            $from = date('Y-m-d 00:00:00', strtotime($_POST['from_date']));
            $to = date('Y-m-d 23:59:59', strtotime($_POST['to_date']));
            // Corrige datas invertidas
            if (strtotime($_POST['to_date']) < strtotime($_POST['from_date'])) {
                $tmp = $from; $from = $to; $to = $tmp;
            }
            $where .= " AND ( A.orc_data BETWEEN '{$from}' AND '{$to}' )";
        } else {
            $where .= " AND A.orc_data > CURRENT_DATE - INTERVAL 60 DAY";
        }

        // Query aplicando os fitros de categoria e data
        $query = $this->DB_fetch_array("SELECT C.nome vendedor, A.id, A.valor_final, A.orc_etapa, A.orc_status
            FROM {$this->table} A
                INNER JOIN tb_crm_crm B ON B.id = A.orc_id_crm
                INNER JOIN tb_admin_users C ON C.id = B.id_user
                INNER JOIN (
                    SELECT X.id_pedido, GROUP_CONCAT(DISTINCT Z.id) cat_ids
                    FROM tb_carrinho_produtos_historico X
                        INNER JOIN tb_produtos_produtos_has_tb_produtos_categorias Y ON X.id_produto = Y.id_produto
                        INNER JOIN tb_produtos_categorias Z ON Y.id_categoria = Z.id
                    WHERE X.id_pedido IS NOT NULL
                    GROUP BY X.id_pedido
                    {$having}
                ) D ON D.id_pedido = A.id
            {$where}"
        );
        
        // Calcula os valores totais
        $totais = [
            'TOTAL' => [
                'orcamento' => 0,
                'followup' => 0,
                'fechamento' => 0,
                'desativado' => 0,
                // 'aberto' => 0,
                'ganho' => 0,
                'perda' => 0,
            ],
        ];
        if ($query->num_rows) {
            foreach ($query->rows as $row) {
                if ($row['orc_status'] == 'aberto') {
                    // Calcula totais das etapas
                    $v = (isset($totais[$row['vendedor']][$row['orc_etapa']])) ? $totais[$row['vendedor']][$row['orc_etapa']] : 0;
                    $totais[$row['vendedor']][$row['orc_etapa']] = $v + $row['valor_final'];
                    $totais['TOTAL'][$row['orc_etapa']] += $row['valor_final'];
                } else {
                    // Calcula totais dos status
                    $v = (isset($totais[$row['vendedor']][$row['orc_status']])) ? $totais[$row['vendedor']][$row['orc_status']] : 0;
                    $totais[$row['vendedor']][$row['orc_status']] = $v + $row['valor_final'];
                    $totais['TOTAL'][$row['orc_status']] += $row['valor_final'];
                }
            }
        }

        // Remove as chaves que não devem aparecer na tabela
        foreach ($this->etapas as $v => $label) {
            if (!in_array($v, ['desativado'])) {
                $labels[$v] = $label;
            }
        }
        foreach ($this->status as $v => $label) {
            if (!in_array($v, ['aberto'])) {
                $labels[$v] = $label;
            }
        }

        // Monta o HTML da tabela de totais
        $result = "<table class='table'><thead><tr><th><!-- Nomes dos vendedores--></th>";
        foreach ($labels as $v => $label) {
            $result .= "<th class='tal' data-key='{$v}'>{$label}</th>";
        }
        $result .= "</tr></thead><tbody>";
        foreach ($totais as $name => $values) {
            $result .= "<tr><td class='tal'>{$name}</td>";
            foreach ($labels as $v => $label) {
                $moeda = $this->formataMoedaShow((isset($values[$v])) ? $values[$v] : 0);
                $result .= "<td class='tal' data-key='{$v}'>R$ {$moeda}</td>";
            }
            $result .= "</tr>";
        }
        echo $result .= "</tbody></table>";
    }

    //--------------- Altera o status de um orçamento ------------------//

    private function saveAction() {
        if (!$this->permissions[$this->permissao_ref]['editar']) exit();

        $formulario = $this->formularioObjeto($_POST);
        $resposta = new \stdClass();

        // Validação
        $query = $this->DB_fetch_array("SELECT * FROM {$this->table} WHERE id = {$formulario->id_orcamento} LIMIT 1");
        if (!$query->num_rows) {
            $resposta->type = "error";
            $resposta->message = "Identificador de orçamento inválido!";
            echo json_encode($resposta);
            return;
        } else if (!in_array($formulario->etapa, ['orcamento', 'followup', 'fechamento', 'desativado'])) {
            $resposta->type = 'validation';
            $resposta->message = 'Selecione uma etapa válida';
            $resposta->field = 'etapa';
            echo json_encode($resposta);
            return;
        } else if (!in_array($formulario->status, ['aberto', 'ganho', 'perda'])) {
            $resposta->type = 'validation';
            $resposta->message = 'Selecione um status válido';
            $resposta->field = 'status';
            echo json_encode($resposta);
            return;
        }

        // Atualiza os valores
        $fields_values[] = "orc_etapa = '{$formulario->etapa}'";
        $fields_values[] = "orc_status = '{$formulario->status}'";
        $fields_values[] = "data = NOW()";

        // Atualiza o valor no banco de dados
        $query = $this->DB_update($this->table, implode(',', $fields_values) . " WHERE id = {$formulario->id_orcamento}");
        if ($query) {
            $resposta->type = "success";
            $resposta->message = "Alterado com sucesso!";
        } else {
            $resposta->type = "error";
            $resposta->message = "Aconteceu um erro no sistema, por favor tente novamente mais tarde!";
        }
        echo json_encode($resposta);
    }

    //--------------- Transforma um orçamento em pedido ------------------//

    private function transferAction() {
        if (!$this->permissions[$this->permissao_ref]['editar']) exit();

        $formulario = $this->formularioObjeto($_POST);
        $resposta = new \stdClass();
        $resposta->return = false;

        // Valida e cria cliente
        if (!isset($formulario->id_cliente)) {
            $validacao = $this->validaFormulario($formulario);

            if (!$validacao->return) {
                echo json_encode($validacao);
                return;
            } else {
                $table = 'tb_clientes_clientes';
                $dataCliente = $this->formularioObjeto($_POST, $table);
            
                $dataCliente->ip = $_SERVER['REMOTE_ADDR'];
                $dataCliente->senha = $this->embaralhar($dataCliente->senha);
                $dataCliente->stats = 1;
            
                $query = $this->DB_fetch_array("SELECT * FROM tb_clientes_clientes WHERE email = '$dataCliente->email'");
                if($query->num_rows){
                    $resposta->type = "attention";
                    $resposta->message = "Este e-mail já está cadastrado em nossa base.";
                    echo json_encode($resposta);
                    exit();
                }
    
                foreach ($dataCliente as $key => $value) {
                    $fields[] = $key;
                    $values[] = "'$value'";
                }
    
                $query = $this->DB_insert($table, implode(',', $fields), implode(',', $values));
                unset($fields);
                unset($values);
    
                if ($query->query) {
                    $formulario->id_cliente = $query->insert_id;

                    $verifica = $this->DB_num_rows("SELECT * FROM tb_emails_emails A INNER JOIN tb_listas_listas_has_tb_emails_emails B ON B.id_email = A.id AND A.email = '$formulario->email' AND B.id_lista = 3");
                    if (!$verifica) {
                        $verifica = $this->DB_fetch_array("SELECT * FROM tb_emails_emails WHERE email = '$formulario->email'");
                        if (!$verifica->num_rows) {
                            $addEmail = $this->DB_insert('tb_emails_emails', "nome,email", "'$formulario->nome','$formulario->email'");
                            $idEmail = $addEmail->insert_id;
                        } else {
                            $idEmail = $verifica->rows[0]['id'];
                        }
                        $addListaHasEmail = $this->DB_insert('tb_listas_listas_has_tb_emails_emails', "id_lista,id_email", "3,$idEmail");
                    }
    
                    $this->inserirRelatorio("Cliente: [" . $formulario->email . "] Id: [" . $formulario->id_cliente . "]");
                } else {
                    $resposta->type = "error";
                    $resposta->message = "Aconteceu um erro ao criar o novo usuário";
                    echo json_encode($resposta);
                    return;
                }
            }
        }

        // Validação
        $query = $this->DB_fetch_array("SELECT * FROM $this->table WHERE id = $formulario->id_orcamento LIMIT 1");
        if (!$query->num_rows) {
            $resposta->type = "error";
            $resposta->message = "Identificador de orçamento inválido!";
            echo json_encode($resposta);
            return;
        }
        $data = $this->formularioObjeto($query->rows[0], $this->table);

        $query = $this->DB_fetch_array("SELECT * FROM tb_clientes_clientes WHERE id = $formulario->id_cliente LIMIT 1");
        if (!isset($query->num_rows) || !$query->num_rows) {
            $resposta->type = "validation";
            $resposta->message = "ID de cliente inválido";
            $resposta->field = "id_cliente";
            echo json_encode($resposta);
            return;
        }
        $cliente = $query->rows[0];
        
        if (!isset($formulario->frete) || $formulario->frete == "" || !isset($formulario->frete_nome) || $formulario->frete_nome == "") {
            $fretes = $this->freteOptions($formulario->id_orcamento, preg_replace('/\D/', '', $cliente['cep']));
            if ($fretes !== false) {
                $resposta->type = "frete";
                $resposta->time = 0;
                $resposta->cliente = $cliente['nome'];
                $resposta->fretes = urlencode($fretes);
            } else {
                $resposta->type = "error";
                $resposta->message = "Erro no CEP cadastrado para este cliente";
            }
            echo json_encode($resposta);
            return;
        }

        if (!isset($formulario->metodo_pagamento) || $formulario->metodo_pagamento == "") {
            $resposta->type = "validation";
            $resposta->message = "Selecione um método de pagamento";
            $resposta->field = "metodo_pagamento";
            echo json_encode($resposta);
            return;
        }

        // Tenta inserir o endereço deste pedido
        $endereco = $this->formularioObjeto($cliente, "tb_pedidos_enderecos");
        $endereco->id_pedido = $formulario->id_orcamento;

        foreach ($endereco as $key => $value) {
            $fields[] = $key;
            $values[] = "'$value'";
        }

        $insert = $this->DB_insert("tb_pedidos_enderecos", implode(',', $fields), implode(',', $values));

        if (!$insert->query) {
            $resposta->type = "error";
            $resposta->message = "Aconteceu um erro ao salvar o endereço do novo pedido!";
            echo json_encode($resposta);
            return;
        }

        // Pega dados do vendedor (setado na criação do orçamento)
        $vendedor = $this->DB_fetch_array("SELECT nome, email FROM tb_admin_users WHERE id = {$data->id_vendedor} LIMIT 1");

        // Realiza o pedido com o ID do cliente, frete, e método de pagamento
        $data->orc_status = 'ganho';
        $data->id_cliente = $formulario->id_cliente;

        // Pagamento
        $data->metodo_pagamento = $formulario->metodo_pagamento;
        if ($formulario->metodo_pagamento == "deposito" || $formulario->metodo_pagamento == "boleto" || $formulario->metodo_pagamento == "pokerstars") {
            //a vista [ganha 5% de desconto]
            $data->avista = 1;
        } else {
            //a prazo
            $data->avista = 2;
        }

        // Frete
        $data->valor_frete = $formulario->frete;
        $data->frete = $formulario->frete_nome;
        if (isset($formulario->frete_prazo) && $formulario->frete_prazo != "")
            $data->prazo_entrega = date('Y-m-d', strtotime("+$formulario->frete_prazo days", strtotime(date('Y-m-d'))));
        else
            $data->prazo_entrega = date('Y-m-d');

        $dataAtual = date("Y-m-d");
        $date = new DateTime($dataAtual);
        $date2 = new DateTime($data->prazo_entrega);
        $intervalo = $date->diff($date2);
        $data->dias_entrega = $intervalo->d;

        // TODO: FIX - Cupom de porcentagem tem o valor alterado pelo frete
        // Recalcula o valor final (aplicando cupons, o novo frete, e método de pagamento)
        $valor_final = $data->subtotal + $data->valor_frete - $data->descontos;
    
        if ($data->tipo_cupom != 1)
            $valor_cupom = $data->valor_cupom;
        else
            $valor_cupom = (($valor_final * $data->valor_cupom) / 100);
        
        $valor_final = $valor_final- $valor_cupom;

        if ($data->avista == 1)
            $valor_final = $valor_final - (($valor_final * 5) / 100);

        $data->valor_final = $valor_final;

        // Prepara campos para a query de update
        foreach ($data as $key => $value) {
            if ($value == "NULL")
                $fields_values[] = "$key=$value";
            else
                $fields_values[] = "$key='$value'";
        }
        // Atualiza timestamp (data)
        $fields_values[] = "data = NOW()";

        //echo "<pre>";print_r($fields);echo "</pre>";
        //echo "<pre>";print_r($values);echo "</pre>";
        //if($cliente['email']!='joao@hibrida.biz'){
            $query = $this->DB_update($this->table, implode(',', $fields_values) . " WHERE id = $formulario->id_orcamento");
            // $idPedido = $query->insert_id;
        //}

        unset($fields_values);
        if ($query) {
            $endereco = false;
            $enderecos = $this->DB_fetch_array("SELECT A.*, C.cidade, E.estado, E.uf FROM tb_pedidos_enderecos A INNER JOIN tb_utils_cidades C ON C.id = A.id_cidade INNER JOIN tb_utils_estados E ON E.id = C.id_estado WHERE A.id_pedido = $data->id");
            if ($enderecos->num_rows)
                $endereco = $enderecos->rows[0];

            //PREPARAR E-MAILS
            $this->newsletter($cliente);

            $to[] = array("email" => $cliente['email'], "nome" => utf8_decode($cliente['nome']));

            $emails = $this->DB_fetch_array("SELECT C.email, C.nome FROM tb_admin_forms A INNER JOIN tb_admin_email_notification B ON A.id = B.id_form INNER JOIN tb_admin_users C ON B.id_user = C.id WHERE A.id = 1 OR A.id = 5 GROUP BY C.email, C.nome");
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

            $mensagem = "
                            <p style='text-align: center;'><a href='{$this->site_url}?utm_source=rodape_email_transacoes'><img src='{$this->site_url}/files/pedido.jpg' alt='' width='600' height='119' /></a></p>
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

                            <br /><br /><span style='color: #008000; font-size: 14pt;'><a href='{$this->site_url}minha-conta' target='_blank'><span style='color: #008000;'><strong>http://www.realpoker.com.br/minha-conta</strong> </span></a></span><strong><br /><br />
                            ";

            if ($data->metodo_pagamento == "pokerstars")
                $frt = $data->valor_frete / $sys->cotacao_dollar;
            else
                $frt = $data->valor_frete;

            $mensagem .= "
                            <b>Nome:</b> {$cliente['nome']}<br>
                            ".(
                        ($cliente['cnpj'] != "") ?
                        "
                            <b>Razão Social:</b> {$cliente['razao_social']}<br>
                            <b>CNPJ:</b> {$cliente['cnpj']}<br>
                            <b>Inscrição Estadual:</b> {$cliente['inscricao_estadual']}<br>
                        " :
                        "
                            <b>CPF:</b> {$cliente['cpf']}<br> 
                        "
                        )."<b>E-mail:</b> {$cliente['email']}<br>
                            <b>Endereço:</b> {$cliente['endereco']}<br>
                            <b>Número:</b> {$cliente['numero']}<br>
                            <b>Bairro:</b> {$cliente['bairro']}<br>
                            <b>Cidade:</b> {$endereco['cidade']}<br>
                            <b>Estado:</b> {$endereco['uf']}<br>
                            <b>CEP:</b> {$cliente['cep']}<br>".
                            /*<b>Prazo para Entrega:</b> {$data->dias_entrega} dias úteis<br>*/
                            "<b>Valor do Frete:</b> R$ {$this->formataMoedaShow($frt)}<br>
                            <b>Pedido:</b> {$data->id}<br><br>
                                
                            ";

            $forma_pagamento = "Forma Desconhecida";
            if ($data->metodo_pagamento == "deposito") {
                $forma_pagamento = "Depósito";
            } else if ($data->metodo_pagamento == "boleto") {
                $forma_pagamento = "Boleto";
            } else if ($data->metodo_pagamento == "cielo") {
                $forma_pagamento = "Cielo";
            } else if ($data->metodo_pagamento == "cielo_transparente") {
                $forma_pagamento = "Cartão de Crédito";
            } else if ($data->metodo_pagamento == "rede_transparente") {
                $forma_pagamento = "Cartão de Crédito";
            } else if ($data->metodo_pagamento == "pagseguro") {
                $forma_pagamento = "Pagseguro";
            } else if ($data->metodo_pagamento == "pokerstars") {
                $forma_pagamento = "Pokerstars";
            }

            $mensagem .= "<b>Forma de Pagamento:</b> $forma_pagamento<br><br>";

            if ($data->avista == 1)
                $mensagem .= "<b>Você ganhou 5% de desconto por escolher um pagamento à vista.</b><br><br>";
            
            if ($data->tipo_cupom != 0)
                $mensagem .= "<b>{$data->mensagem_cupom}</b><br><br>";
            

            if ($data->metodo_pagamento == "pokerstars")
                $mensagem .= "<b>Valor:</b> US$ {$this->formataMoedaShow($data->valor_final / $sys->cotacao_dollar)}<br><br><br>";
            else
                $mensagem .= "<b>Valor:</b> R$ {$this->formataMoedaShow($data->valor_final)}<br><br><br>";


            $mensagem .= "<b>Detalhes do Pedido:</b><br><br>";

            //#open  Montar Demonstrativo de Produtos
            $produtos = $this->product->getCartProductsByPedido($data->id);
            foreach ($produtos->rows as $produto) {
                $atributos = $this->product->getAtributosInfoByProduto($produto['id']);
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
                            $mensagem .= "<a style='font-size: 12px' target='_blank' href='" . $this->site_url . "uploads/" . $atributo['arquivo'] . "'>[{$atributo['valor']}]</a><br>";
                        }
                    }
                }
                if($produto['desconto']=="") $produto['desconto']=0;
                $mensagem .= "<br><br><b>Quantidade:</b> {$produto['quantidade']}<br>";
                if ($data->metodo_pagamento == "pokerstars")
                    $mensagem .= "<b>Total:</b> R$ {$this->formataMoedaShow((($produto['valor_produto'] * $produto['quantidade']) - ($produto['desconto'] * $produto['quantidade'])) / $sys->cotacao_dollar)}<br><br>";
                else
                    $mensagem .= "<b>Total:</b> R$ {$this->formataMoedaShow(($produto['valor_produto'] * $produto['quantidade']) - ($produto['desconto'] * $produto['quantidade']))}<br><br>";
            }
            //#close Montar Demonstrativo de Produtos
            //$mensagem .= "<span style='color:#ff0000'>Veja detalhes do pedido ao fazer login no site da Real Poker:</span> <a href='$this->site_url' target='_blank'>$this->site_url</a>.<br><br>";

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
                            do cliente.<br><br>

                            O endereço de entrega preenchido no pedido é de responsabilidade do cliente, por<br>
                            favor confira novamente o endereço preenchido e nos avise se não estiver correto.<br>
                            Caso a entrega não seja feira por endereço errado, os custos da reentrega são do<br>
                            cliente.<br><br>
                            
                            Atenciosamente,<br>
                            equipe <strong>Real Poker</strong><br><br>
                            </td>
                            </tr>
                            </tbody>
                            </table>

                            <p><a href='{$this->site_url}?utm_source=rodape_email_transacoes'><img style='display: block; margin-left: auto; margin-right: auto;' src='{$this->site_url}/files/rodapeemail.jpg' alt='' width='600' height='241' /></a></p>
                            ";

            $assunto = "Pedido Finalizado #$data->id [{$cliente['nome']}]";  // Assunto da mensagem de contato.
            //$body = file_get_contents("../mailing_templates/form_pedido.html");
            //$body = str_replace("{MENSAGEM}", $mensagem, $body);


            // BUSCA NOME DAS CATEGORIAS DOS PRODUTOS DO PEDIDO PARA TAGEAR O HEADER DO EMAIL
            $categorias = $this->DB_fetch_array("SELECT DISTINCT(c.nome) FROM tb_carrinho_produtos_historico a INNER JOIN tb_produtos_produtos_has_tb_produtos_categorias b ON a.id_produto = b.id_produto INNER JOIN tb_produtos_categorias c ON b.id_categoria = c.id WHERE a.id_pedido = $data->id");
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
            
            $this->enviarEmail($to, $setFrom, utf8_decode($assunto), utf8_decode($mensagem),'', utf8_decode($xmctags));

            $resposta->type = "success";
            $resposta->message = "Registro transferido com sucesso!";
            echo json_encode($resposta);
        } else {
            $resposta->type = "error";
            $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
            echo json_encode($resposta);
        }
    }

    function newsletter($cliente) {
        global $sistema;
    
        $fields = array(
            'email',
            'ip',
            'session'
        );
    
        $values = array(
            '"' . $cliente['email'] . '"',
            '"' . $_SERVER['REMOTE_ADDR'] . '"',
            '"' . $_SESSION["seo_session"] . '"'
        );
    
        $existe = $sistema->DB_fetch_array("SELECT * FROM tb_newsletters_newsletters WHERE email = '{$cliente['email']}'");
        if (!$existe->num_rows) {
    
            $query = $sistema->DB_insert("tb_newsletters_newsletters", implode(',', $fields), implode(',', $values));
            $idContato = $query->insert_id;
    
            if ($query->query) {
    
                $verifica = $sistema->DB_num_rows("SELECT * FROM tb_emails_emails A INNER JOIN tb_listas_listas_has_tb_emails_emails B ON B.id_email = A.id AND A.email = '{$cliente['email']}' AND B.id_lista = 2");
                if (!$verifica) {
                    $verifica = $sistema->DB_fetch_array("SELECT * FROM tb_emails_emails WHERE email = '{$cliente['email']}'");
                    if (!$verifica->num_rows) {
                        $addEmail = $sistema->DB_insert('tb_emails_emails', "email", "'{$cliente['email']}'");
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
                    $body = str_replace("{EMAIL}", $cliente['email'], $body);
    
                    $sistema->enviarEmail($to, $cliente['email'], utf8_decode($assunto), utf8_decode($body));
                }
    
                $sistema->inserirRelatorio("Newsletter: [" . $cliente['email'] . "] Id: [" . $idContato . "]");
            }
        }
    }

    //-------------- Retorna a listagem de orçamentos (AJAX) -----------------//

    private function datatableAction() {
        if (!$this->permissions[$this->permissao_ref]['ler']) exit();
        
        // IDs de categorias de produto para aplicar o filtro
        $sHaving = ($_POST['categories_array']) ? 'HAVING ' . implode(' OR ', array_map(function($c) {
            return "cat_ids LIKE '%{$c}%'";
        }, explode(',', ($_POST['categories_array'])))) : '';

        //defina os campos da tabela
        $aColumns = array('B.nome', 'B.telefone', 'A.id', 'E.nome vendedor', 'DATE_FORMAT(A.orc_data, CONCAT("%d/%m/%Y (", DATEDIFF(NOW(), A.orc_data), IF(DATEDIFF(NOW(), A.orc_data) = 1, " dia", " dias"), ")")) data', 'C.categorias', 'A.valor_final', 'A.orc_etapa', 'A.orc_status', 'A.code', 'A.orc_id_crm', 'C.prod_ids', 'D.maxID');

        //defina os campos que devem ser usados para a busca
        $aColumnsWhere = array('B.nome', 'B.telefone', 'A.id', 'E.nome', 'DATE_FORMAT(A.orc_data, CONCAT("%d/%m/%Y (", DATEDIFF(NOW(), A.orc_data), IF(DATEDIFF(NOW(), A.orc_data) = 1, " dia", " dias"), ")"))', 'C.categorias', 'A.valor_final', 'A.orc_etapa', 'A.orc_status');

        //defina o coluna índice
        $sIndexColumn = "A.id";

        //declarar condições extras
        $sWhere = "WHERE A.orc_status IS NOT NULL AND A.orc_etapa IS NOT NULL";

        // Controle de status de orçamentos (mostrar apenas abertos ou todos)
        if (!$_POST['status_flag']) {
            $sWhere .= " AND A.orc_status = 'aberto'";
        }

        // Limita o numero de elementos listados por data
        if ($_POST['from_date'] && $_POST['to_date']) {
            $from = date('Y-m-d 00:00:00', strtotime($_POST['from_date']));
            $to = date('Y-m-d 23:59:59', strtotime($_POST['to_date']));
            // Corrige datas invertidas
            if (strtotime($_POST['to_date']) < strtotime($_POST['from_date'])) {
                $tmp = $from; $from = $to; $to = $tmp;
            }
            $sWhere .= " AND ( A.orc_data BETWEEN '{$from}' AND '{$to}' )";
        } else {
            $sWhere .= " AND A.orc_data > CURRENT_DATE - INTERVAL 60 DAY";
        }

        //defina o nome da tabela, ou faça aqui seu INNER JOIN, LEFT JOIN, RIGHT JOIN
        //Ex: "tb_emails_emails A INNER JOIN tb_admin_users B ON B.id=A.id"
        $sTable = "{$this->table} A INNER JOIN tb_crm_crm B ON B.id = A.orc_id_crm INNER JOIN (
            SELECT X.id_pedido, GROUP_CONCAT(DISTINCT Z.nome SEPARATOR ', ') categorias, GROUP_CONCAT(DISTINCT Z.id) cat_ids, GROUP_CONCAT(DISTINCT X.id) prod_ids
            FROM tb_carrinho_produtos_historico X
                INNER JOIN tb_produtos_produtos_has_tb_produtos_categorias Y ON X.id_produto = Y.id_produto
                INNER JOIN tb_produtos_categorias Z ON Y.id_categoria = Z.id
            WHERE X.id_pedido IS NOT NULL
            GROUP BY X.id_pedido
            {$sHaving}
        ) C ON C.id_pedido = A.id INNER JOIN (
            SELECT A.orc_id_crm, MAX(A.id) as maxID
            FROM {$this->table} A
            {$sWhere} AND A.orc_id_crm IS NOT NULL
            GROUP BY A.orc_id_crm
        ) D ON D.orc_id_crm = A.orc_id_crm INNER JOIN tb_admin_users E ON E.id = B.id_user";

        $sGrouby = " GROUP BY A.id ";

        /*
         * INÍCIO DA ROTINA
         */

        $sLimit = "";
        if (isset($_POST['iDisplayStart']) && $_POST['iDisplayLength'] != '-1') {
            $sLimit = "LIMIT " . $_POST['iDisplayStart'] . ", " .
                    $_POST['iDisplayLength'];
        }

        if (isset($_POST['iSortCol_0'])) {

            $sOrder = "ORDER BY  ";
            for ($i = 0; $i < intval($_POST['iSortingCols']); $i++) {

                //PEGANDO A PRIMEIRA PALAVRA, PARA TIRAR O ÁLIAS
                $campo_array = explode(" ", $aColumns[intval($_POST['iSortCol_' . $i])]);
                $campo = $campo_array[0];

                // Leva em conta o último orçamento do cliente
                if ($_POST['iSortCol_0'] == 2) $campo = "D.maxID DESC, A.id";

                // Ordena por data da última modificação
                if ($_POST['iSortCol_0'] == 3) $campo = "A.orc_data";

                if (in_array("DATE_FORMAT", $campo_array)) {
                    $campo = end($campo_array);
                }


                if ($_POST['bSortable_' . intval($_POST['iSortCol_' . $i])] == "true") {
                    $sOrder .= $campo . "
                        " . $_POST['sSortDir_' . $i] . ", ";
                }
            }

            $sOrder = substr_replace($sOrder, "", -2);
            if ($sOrder == "ORDER BY") {
                $sOrder = "";
            }
        }

        if ($_POST['sSearch'] != "") {
            if ($sWhere == "") {
                $sWhere = "WHERE (";
            } else {
                $sWhere .= " and (";
            }
            for ($i = 0; $i < count($aColumnsWhere); $i++) {
                $sWhere .= $aColumnsWhere[$i] . " LIKE '%" . $_POST['sSearch'] . "%' OR ";
            }
            $sWhere = substr_replace($sWhere, "", -3);
            $sWhere .= ')';
        }

        for ($i = 0; $i < count($aColumns); $i++) {
            if (isset($_POST['bSearchable_' . $i]) && $_POST['bSearchable_' . $i] == "true" && $_POST['sSearch_' . $i] != '') {
                if ($sWhere == "") {
                    $sWhere = "WHERE ";
                } else {
                    $sWhere .= " AND ";
                }

                $sWhere .= $aColumns[$i] . " LIKE '%" . $_POST['sSearch_' . $i] . "%' ";
            }
        }
        
        $mainQuery = "SELECT SQL_CALC_FOUND_ROWS " . str_replace(" , ", " ", implode(", ", $aColumns)) . "
            FROM $sTable
            $sWhere
            $sGrouby
            $sOrder
            $sLimit";

        $rResult = array();
        $sQuery = $this->DB_fetch_array($mainQuery);
        if ($sQuery->num_rows)
            $rResult = $sQuery->rows;
        
        $queryExport = $mainQuery;

        $sQuery = $this->DB_num_rows(" SELECT $sIndexColumn
            FROM   $sTable
            $sWhere
        ");
        $iFilteredTotal = $sQuery;

        $sQuery = $this->DB_num_rows(" SELECT $sIndexColumn
            FROM   $sTable $sWhere
        ");
        $iTotal = $sQuery;

        $output = array(
            "sEcho" => intval($_POST['sEcho']),
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array()
        );

        for ($i = 0; $i < count($aColumns); $i++) {
            $aColumns[$i] = explode(".", $aColumns[$i]);
            $aColumns[$i] = end($aColumns[$i]);
        }
        /*
         * MONTA A TBODY
         */

        if ($rResult) {
            $lastIdCrm = 0;
            foreach ($rResult as $aRow) {
                $row = array();

                $cortado = ($aRow['orc_etapa'] == 'desativado') ? 'class="cortado"' : '';

                // Mostra nome e telefone apenas se o cliente não é repetido
                if ($aRow['orc_id_crm'] != $lastIdCrm) {
                    $lastIdCrm = $aRow['orc_id_crm'];

                    // NOME
                    $row[] = "<div align=left><a href='crm/edit/id/{$aRow['orc_id_crm']}'>{$aRow['nome']}</a></div>";

                    // TELEFONE (e link para whatsapp)
                    $displayPhone = substr_replace($aRow['telefone'], '-', -4, 0);
                    $whatsPhone = preg_replace('/\D/', '', $aRow['telefone']);
                    $whatsMessage = rawurlencode("Olá {$aRow['nome']}, estou entrando em contato para falar do seu orçamento #{$aRow['id']} na RealPoker.");
                    $row[] = "<div align=left>
                        <a target='_blank' href='https://web.whatsapp.com/send?phone=55{$whatsPhone}&text={$whatsMessage}'><img src='/img/icon_televendas_whatsapp.png' alt='Entre em contato' style='width: 20px; margin: 0 5px 0 0;'></img></a>
                        {$displayPhone}
                    </div>";
                } else {
                    $row[] = "<div align=left></div>";
                    $row[] = "<div align=left></div>";
                }

                //ID
                $row[] = "<div align=left {$cortado}>{$aRow['id']}</div>";

                // VENDEDOR
                $vendedor = array_pad(explode(' ', $aRow['vendedor']), 1, '')[0];
                $row[] = "<div align=left {$cortado}><a href='#'><i class='icomoon-icon-filter filtro_vendedor' data-nome='{$vendedor}'></i></a>{$vendedor}</div>";

                //DATA
                $row[] = "<div align=left {$cortado}>{$aRow['data']}</div>";

                //CATEGORIAS
                $row[] = "<div align=left {$cortado}>{$aRow['categorias']}</div>";

                //VALOR_FINAL
                $row[] = "<div align=left {$cortado}>R$ " . $this->formataMoedaShow($aRow['valor_final']) . "</div>";

                //ORC_ETAPA
                if ($aRow['orc_status'] == 'aberto') {
                    $options = implode(' ', array_map(function($etapa, $label) use($aRow) {
                        $selected = ($aRow['orc_etapa'] === $etapa) ? 'selected' : '';
                        return "<option value='{$etapa}' {$selected}>{$label}</option>";
                    }, array_keys($this->etapas), $this->etapas));
                    $row[] = "<div>
                        <select class='orc_etapa' data-id='{$aRow['id']}' data-etapa='{$aRow['orc_etapa']}' data-status='{$aRow['orc_status']}'>
                            {$options}
                        </select>
                    </div>";
                } else {
                    $row[] = "<div align='center'>
                        <div class='orc_etapa' data-id='{$aRow['id']}' data-etapa='{$aRow['orc_etapa']}' data-status='{$aRow['orc_status']}'>{$this->etapas[$aRow['orc_etapa']]}</div>
                    </div>";
                }

                //AÇÃO
                $hex = strtoupper(dechex($aRow['code']));
                $link = "{$this->site_url}orcamento/{$hex}";
                $row[] = "<div>
                    <a title='Visualizar orçamento' target='_blank' href='{$link}'><i class='s16 icomoon-icon-link'></i></a>
                    <span class='orc_status' data-id='{$aRow['id']}' data-etapa='{$aRow['orc_etapa']}' data-status='{$aRow['orc_status']}'>
                        <a title='Gerar pedido' href='#' class='orc_transfer " . (($aRow['orc_status'] == 'ganho') ? 'checked' : '') . "'><i class='s16 icomoon-icon-cart'></i></a>
                        <a title='Cancelar orçamento' href='#' class='orc_cancel " . (($aRow['orc_status'] == 'perda') ? 'checked' : '') . "'><i class='s16 icomoon-icon-cancel-circle'></i></a>
                    </span>
                </div>";

                $output['aaData'][] = $row;
            }
        }
        
        $output['queryExport'] = $queryExport;

        echo json_encode($output);
    }

    //--------------- Validação para criar de novo usuário ------------------//

    private function validaFormulario($form) {

        $resposta = new stdClass();
        $resposta->return = true;

        //$sistema = new sistema();
        global $sistema;

        if ($form->pessoa == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo corretamente";
            $resposta->field = "pessoa";
            $resposta->return = false;
            return $resposta;
        } else if ($form->pessoa == 2) {
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
            }else {
                return $this->validaFormularioContinuacao($form);
            }
        } else {
            return $this->validaFormularioContinuacao($form);
        }
    }

    private function validaFormularioContinuacao($form) {
        $resposta = new stdClass();
        $resposta->return = true;

        global $sistema;
        $form->cep = str_replace("_", "", $form->cep);
        if ($form->nome == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo corretamente";
            $resposta->field = "nome";
            $resposta->return = false;
            return $resposta;
        } else if ($form->pessoa == 1 AND $form->cpf == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo corretamente";
            $resposta->field = "cpf";
            $resposta->return = false;
            return $resposta;
        } else if ($form->pessoa == 1 AND !$sistema->validaCPF($form->cpf)) {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo corretamente";
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
        } else if ($form->cep == "") {
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
        } else if ($form->endereco == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo corretamente";
            $resposta->field = "endereco";
            $resposta->return = false;
            return $resposta;
        } else if ($form->bairro == "") {
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
        } else if ($form->metodo_pagamento == "") {
            $resposta->type = "validation";
            $resposta->message = "Selecione um método de pagamento";
            $resposta->field = "metodo_pagamento_cli";
            $resposta->return = false;
            return $resposta;
        } else {
            return $resposta;
        }
    }

    //--------------- Funções para cálculo opções de frete ------------------//

    private function freteAction() {
        $id = $_POST['id_orcamento'] ?? 0;
        $cep = preg_replace('/\D/', '', $_POST['cep'] ?? '00000000');
        $result = $this->freteOptions($id, $cep);
        if (!$id || !$result) {
            $result = '<div><p>Não foi possível carregar as opções de frete:</p><p>Orçamento ou CEP inválido</p></div>';
        }
        echo $result;
    }

    private function freteOptions($id, $cep) {
        $locais = $this->DB_fetch_array("SELECT id_estado, id_cidade FROM (
            (SELECT A.id id_estado, NULL id_cidade FROM tb_config_estados A WHERE A.cep_init <= '$cep' AND A.cep_end >= '$cep')
            UNION
            (SELECT A.id_estado, A.id id_cidade FROM tb_config_cidades A WHERE A.cep_init <= '$cep' AND A.cep_end >= '$cep')
        ) TAB  ORDER BY id_cidade DESC");
        if (!$locais->num_rows) return false;

        $cidades = array();
        $estados = array();

        $result['negar_aereo'] = false;
        $result['negar_terrestre'] = false;
        $negar_aereo = $this->negarAereo($id);
        $negar_terrestre = $this->negarTerrestre($id);

        foreach ($locais->rows as $local) {
            if (in_array($local['id_estado'] . "-" . $local['id_cidade'], $negar_aereo)) {
                $result['negar_aereo'] = true;
            }
            if (in_array($local['id_estado'] . "-" . $local['id_cidade'], $negar_terrestre)) {
                $result['negar_terrestre'] = true;
            }

            $cidades[] = $local['id_cidade'];
            $estados[] = $local['id_estado'];
        }

        $nao_negar_aereo = $this->naoNegarAereo($id, $cidades, $estados);
        if ($nao_negar_aereo)
            $result['negar_aereo'] = false;

        $nao_negar_terrestre = $this->naoNegarTerrestre($id, $cidades, $estados);
        if ($nao_negar_terrestre)
            $result['negar_terrestre'] = false;

        $pPadrao = 0;
        $pBase = 0;
        $prazo_padrao = $this->DB_fetch_array("SELECT B.id, B.prazo_producao
            FROM tb_carrinho_produtos_historico A 
            INNER JOIN tb_produtos_produtos B ON B.id = A.id_produto 
        WHERE A.id_pedido = $id ORDER BY B.prazo_producao DESC LIMIT 1");
        if ($prazo_padrao->num_rows) {
            $pPadrao = $prazo_padrao->rows[0]['prazo_producao'];
            $pBase = $prazo_padrao->rows[0]['id'];
        }

        $prazo_adicionais = $this->DB_fetch_array("SELECT B.id, IFNULL(B.prazo_producao_adic, 0) prazo_producao_adic, A.quantidade
            FROM tb_carrinho_produtos_historico A 
            INNER JOIN tb_produtos_produtos B ON B.id = A.id_produto         
        WHERE A.id_pedido = $id");

        $pPadraoNew = $pPadrao;
        if ($prazo_adicionais->num_rows) {
            foreach ($prazo_adicionais->rows as $pa) {
                if ($pBase == $pa['id'])
                    $pa['quantidade'] = $pa['quantidade'] - 1;
                $pPadraoNew = $pPadraoNew + ($pa['prazo_producao_adic'] * $pa['quantidade']);
            }
        }

        $pPadraoNewCep = $pPadraoNew;

        $preco_aereo = 0;
        $prazo_aereo = 0;
        $produtoAereo = true;
        $aereos = $this->DB_fetch_array("SELECT B.id_frete_aereo, B.frete_embutido, A.quantidade FROM tb_carrinho_produtos_historico A INNER JOIN tb_produtos_produtos B ON B.id = A.id_produto WHERE A.id_pedido = $id");
        if ($aereos->num_rows) {
            $a = array();
            foreach ($aereos->rows as $aereo) {
                if ($aereo['id_frete_aereo'] != "") {
                    $a[] = $this->product->getInfoFrete($aereo['id_frete_aereo'], $estados, $cidades, $aereo['frete_embutido'], $aereo['quantidade']);
                    $produtoAereo = $this->product->entregaAereo($aereo['id_frete_aereo'], $estados, $cidades);
                } else {
                    $produtoAereo = false;
                }
            }
            foreach ($a as $a) {

                if ($a['prazo'] > $prazo_aereo) {
                    $prazo_aereo = $a['prazo'];
                }

                $preco_aereo = $preco_aereo + $a['preco'];
            }
        }

        $pPrazoAereo = $pPadraoNewCep + $prazo_aereo;
        $pPrecoAereo = $preco_aereo;

        $preco_terrestre = 0;
        $prazo_terrestre = 0;
        $terrestres = $this->DB_fetch_array("SELECT B.id_frete_terrestre, B.frete_embutido, A.quantidade FROM tb_carrinho_produtos_historico A INNER JOIN tb_produtos_produtos B ON B.id = A.id_produto WHERE A.id_pedido = $id");
        if ($terrestres->num_rows) {
            $a = array();
            foreach ($terrestres->rows as $terrestre) {
                if ($terrestre['id_frete_terrestre'] != "") {
                    $a[] = $this->product->getInfoFrete($terrestre['id_frete_terrestre'], $estados, $cidades, $terrestre['frete_embutido'], $terrestre['quantidade']);
                }
            }

            foreach ($a as $a) {
                $preco_terrestre = $preco_terrestre + $a['preco'];

                if ($a['prazo'] > $prazo_terrestre) {
                    $prazo_terrestre = $a['prazo'];
                }
            }
        }

        $pPrazoTerrestre = $pPadraoNewCep + $prazo_terrestre;
        $pPrecoTerrestre = $preco_terrestre;

        $produtos_no_carrinho = $this->DB_fetch_array("SELECT * FROM tb_carrinho_produtos_historico A WHERE A.id_pedido = $id");
        
        $html = '';
        if ($produtos_no_carrinho->num_rows) {
            if (!$result['negar_terrestre']) {
                if ($pPrecoTerrestre > 0)
                    $valorDescrito = "R$ " . $this->formataMoedaShow($pPrecoTerrestre);
                else
                    $valorDescrito = 'Frete Grátis';

                $html .= "<div class='option-frete'><label for='frete-terrestre'><input data-prazo='$pPrazoTerrestre' data-nome='Terrestre' type='radio' name='frete' id='frete-terrestre' value='$pPrecoTerrestre'> <span>Frete Terrestre</span></label> <div class='valor'>Valor do frete: <span>{$valorDescrito}</span></div> <div class='prazo'>Prazo para entregar: <span>$pPrazoTerrestre dias úteis</span></div></div>";
            } else {
                if ($produtos_no_carrinho->num_rows)
                    $html .= "<div class='option-frete'><label><span>Frete Terrestre </span><div>Não entregamos no CEP informado!</div></label></div>";
            }

            if (!$result['negar_aereo'] && $produtoAereo) {
                if ($pPrecoAereo > 0)
                    $valorDescrito = "R$ " . $this->formataMoedaShow($pPrecoAereo);
                else
                    $valorDescrito = 'Frete Grátis';

                $html .= "<div class='option-frete'><label for='frete-aereo'><input data-prazo='$pPrazoAereo' data-nome='Aéreo' type='radio' name='frete' id='frete-aereo' value='$pPrecoAereo'> <span>Frete Aéreo</span></label> <div class='valor'>Valor do frete: <span>$valorDescrito</span></div> <div class='prazo'>Prazo para entregar: <span>$pPrazoAereo dias úteis</span></div></div>";
            } else {
                $html .= "<div class='option-frete'><label><span>Frete Aéreo </span><div>Não entregamos no CEP informado!</div></label></div>";
            }
        }
        return $html;
    }

    public function negarAereo($id) {
        $negar = $this->DB_fetch_array("SELECT C.*
            FROM tb_carrinho_produtos_historico A 
            INNER JOIN tb_produtos_produtos B ON B.id = A.id_produto 
            INNER JOIN tb_config_conjuntos_fretes_negar C ON C.id_conjunto = B.id_frete_aereo 
        WHERE A.id_pedido = $id");

        $resposta['negar'] = array();
        if ($negar->num_rows) {
            foreach ($negar->rows as $nega) {
                $resposta['negar'][] = $nega['id_estado'] . "-" . $nega['id_cidade'];
            }
        }
        return $resposta['negar'];
    }

    public function negarTerrestre($id) {
        $negar = $this->DB_fetch_array("SELECT C.*
            FROM tb_carrinho_produtos_historico A 
            INNER JOIN tb_produtos_produtos B ON B.id = A.id_produto 
            INNER JOIN tb_config_conjuntos_fretes_negar C ON C.id_conjunto = B.id_frete_terrestre 
        WHERE A.id_pedido = $id");

        $resposta['negar'] = array();
        if ($negar->num_rows) {
            foreach ($negar->rows as $nega) {
                $resposta['negar'][] = $nega['id_estado'] . "-" . $nega['id_cidade'];
            }
        }
        return $resposta['negar'];
    }

    private function naoNegarAereo($id, $cidades, $estados = null) {
        $permitir = $this->DB_fetch_array("SELECT C.*
            FROM tb_carrinho_produtos_historico A 
            INNER JOIN tb_produtos_produtos B ON B.id = A.id_produto 
            INNER JOIN tb_config_conjuntos_fretes_customizados C ON C.id_conjunto = B.id_frete_aereo
        WHERE A.id_pedido = $id");

        $array = array();
        if ($estados != null) {
            for ($i = 0; $i < count($estados); $i++) {
                if ($cidades[$i] == "") {
                    $array[] = $estados[$i];
                }
            }
        }

        if ($permitir->num_rows) {
            foreach ($permitir->rows as $permite) {
                if (in_array($permite['id_estado'], $array) && $permite['id_cidade'] == "") {
                    $result = true;
                }
            }
        }

        $cidades = array_filter($cidades);

        $result = false;

        if ($permitir->num_rows) {
            foreach ($permitir->rows as $permite) {
                if (in_array($permite['id_cidade'], $cidades) && $permite['id_cidade'] != "") {
                    $result = true;
                }
            }
        }

        return $result;
    }

    private function naoNegarTerrestre($id, $cidades, $estados = null) {
        $permitir = $this->DB_fetch_array("SELECT C.*
            FROM tb_carrinho_produtos_historico A 
            INNER JOIN tb_produtos_produtos B ON B.id = A.id_produto 
            INNER JOIN tb_config_conjuntos_fretes_customizados C ON C.id_conjunto = B.id_frete_terrestre
        WHERE A.id_pedido = $id");

        $array = array();
        if ($estados != null) {
            for ($i = 0; $i < count($estados); $i++) {
                if ($cidades[$i] == "") {
                    $array[] = $estados[$i];
                }
            }
        }

        if ($permitir->num_rows) {
            foreach ($permitir->rows as $permite) {
                if (in_array($permite['id_estado'], $array) && $permite['id_cidade'] == "") {
                    $result = true;
                }
            }
        }

        $cidades = array_filter($cidades);

        $result = false;

        if ($permitir->num_rows) {
            foreach ($permitir->rows as $permite) {
                if (in_array($permite['id_cidade'], $cidades) && $permite['id_cidade'] != "") {
                    $result = true;
                }
            }
        }

        return $result;
    }

    /*
     * Métodos padrões da classe
     */

    public function setModule($module) {
        $this->module = $module;
    }

    public function getModule() {
        return strtolower($this->module);
    }

    public static function setAction() {
        $sistema = new Bootstrap();

        #encontrar nome da classe pelo nome do arquivo e instanciá-la
        $class = explode(DIRECTORY_SEPARATOR, __FILE__);
        $class = str_replace(".php", "", end($class));
        $instance = new $class();

        #acionar o método da classe de acordo com o parâmetro da url
        $action = $sistema->getParameter(strtolower($class));
        $action = explode("?", $action);
        $newAction = $action[0] . "Action";

        #antes de acioná-lo, verifica se ele existe
        if (method_exists($instance, $newAction)) {
            $instance->setModule($class);
            $instance->$newAction();
        } else if ($newAction == "Action") {
            $instance->setModule($class);
            if (method_exists($instance, 'indexAction'))
                $instance->indexAction();
            else
                $sistema->renderView($instance->getModule(), "404");
        } else {
            $sistema->renderView($instance->getModule(), "404");
        }
    }
}