<?php

use System\Core\Bootstrap;
use System\Libs\Notificacoes;
use classes\Product;
use classes\Cliente;
use classes\Frete;

Proposal2::setAction();

class Proposal2 extends Bootstrap
{

    public $module = "";
    public $permissao_ref = "orcamentos";
    public $table = "tb_pedidos_pedidos";

    function __construct()
    {


        parent::__construct();
        if (!isset($_SESSION['admin_logado'])) {
            header("Location: " . $this->system_path . "login");
            exit;
        }
        $this->getAllPermissions();

        $this->module_icon = "icomoon-icon-copy";
        $this->module_link = "proposal";
        $this->module_title = "Orçamentos2.0";
        $this->retorno = "proposal";

        $this->id_pedidos_faturados = ['3', '5', '10', '11', '12', '13', '14', '15', '18', '19', '20', '21'];
        $this->id_pedidos_cancelados = ['4', '6', '7', '8','17', '22'];

        $this->etapas = [
            'orcamento' => 'Orçamento',
            'followup' => 'Follow-Up',
            'fechamento' => 'Fechamento',
            'desativado' => 'Desativado',
        ];

        $this->prioridadeEtapas = [
            'desativado' => 0,
            'orcamento' => 1,
            'followup' => 2,
            'fechamento' => 3,
        ];

        $this->status = [
            'aberto' => 'Aberto',
            'ganho' => 'Ganho',
            'perda' => 'Perda',
        ];

        $this->gateway_pagamento = $this->DB_fetch_array("SELECT servico_pagamento_padrao FROM tb_admin_empresas WHERE stats = 1");
        $this->gateway_pagamento = $this->gateway_pagamento->rows[0]['servico_pagamento_padrao'];

        $this->product = new Product();
    }

    private function indexAction()
    {
        if (!$this->permissions[$this->permissao_ref]['ler']) {
            $this->noPermission();
        }
        // pesquisa
        $this->categorias_selecionadas = [];
        if(isset($_POST['categorias'])) {
            $this->categorias_selecionadas = count(explode(',',$_POST['categorias'])) ? explode(',',$_POST['categorias']) : array($_POST['categorias']);
        }

        if(!isset($_POST['ordenacao'])) {
            $this->ordenacao = 'data-asc';
        }else {
            $this->ordenacao = $_POST['ordenacao'];
        }
        $this->categrias_string = isset($_POST['categorias']) ? $_POST['categorias'] : '';
        $this->id_vendedor = isset($_POST['id_vendedor']) ? $_POST['id_vendedor'] : $_SESSION['admin_id'];
        $this->pesquisa = isset($_POST['pesquisa']) ? $_POST['pesquisa'] : '';

        $ordenacaoCrm = explode('-',$this->ordenacao)[1];

        if($this->id_vendedor == 'todos') {
            $whereVendedor = "";
        } else {
            $whereVendedor = 'and id_user = ' . $this->id_vendedor;
        }

        $having = isset($_POST['categorias']) ? 'HAVING ' . implode(' OR ', array_map(function($c) {
                return "cat_ids LIKE '%{$c}%'";
            }, explode(',', ($_POST['categorias'])))) : '';


        $aColumnsWhereCrm = array('tb_crm_crm.nome', 'tb_crm_crm.telefone', 'tb_crm_crm.email', 'tb_crm_crm.cpf_cnpj');
        $whereCrm = '';

        if($this->pesquisa) {
            for ($i = 0; $i < count($aColumnsWhereCrm); $i++) {
                $whereCrm .= $aColumnsWhereCrm[$i] . " LIKE '%" . $this->pesquisa . "%' OR ";
            }
            $whereCrm = ' and (' .substr_replace($whereCrm, "", -3) . ')';
        }


        // Estados para o modal de criação de usuários
        $query = $this->DB_fetch_array("SELECT * FROM tb_utils_estados ORDER BY estado");
        $this->estados = ($query->num_rows) ? $query->rows : [];

        // Categorias de produtos para os checkbox de filtro
        $query = $this->DB_fetch_array("SELECT id, nome FROM tb_produtos_categorias WHERE stats <> 0");
        $this->categorias = ($query->num_rows) ? $query->rows : [];


        // motivos desistencia
        $query = $this->DB_fetch_array("SELECT id, descricao FROM tb_pedidos_motivos_desistencia");
        $this->movitos_desistencia = ($query->num_rows) ? $query->rows : [];

        // BUSCA TODOS OS CONTATOS EM ABERTO NO CRM
        $query = $this->DB_fetch_array("SELECT 
                                                    C.usuario as vendedor,
                                                    DATE_FORMAT(tb_crm_crm.data, '%d/%m/%Y às %H:%i') as data_crm,
                                                    TIMESTAMPDIFF(DAY,date(tb_crm_crm.data), date(now())) as crm_quantidade_dias, 
                                                    tb_crm_crm.*,tb_clientes_clientes.*,  
                                                    tb_crm_crm.id as id_crm,
                                                    TIMESTAMPDIFF(DAY,date(ultimo_contato), date(now())) as dias_ultimo_contato, 
                                                    TIMESTAMPDIFF(DAY,date(tb_crm_crm.data), date(now())) as data_futura,   
                                                    DATE_FORMAT(tb_crm_crm.data, '%d/%m/%Y') AS data_formatada,
                                                    DATE_FORMAT(ultimo_contato,'%d/%m/%Y às %H:%i') ultimo_contato,
                                                    (SELECT DATE_FORMAT(data,'%d/%m/%Y às %H:%i') FROM (SELECT data FROM tb_clientes_contatos WHERE id_cliente = tb_clientes_clientes.id ORDER BY data DESC LIMIT 2) sub ORDER BY data ASC LIMIT 1) penultimo_contato
                                                FROM
                                                    tb_crm_crm
                                                        LEFT JOIN
                                                    tb_clientes_clientes ON tb_clientes_clientes.id = tb_crm_crm.id_cliente
                                                    INNER JOIN tb_admin_users C ON C.id = tb_crm_crm.id_user
                                                WHERE
                                                    possui_orcamento = 0 and finalizado is null 
                                                    $whereVendedor  
                                                    $whereCrm                                                  
                                                    order by tb_crm_crm.data $ordenacaoCrm");
        $this->crm_contatos = $query->num_rows ? $query->rows : [];

        // PEGA TODOS OS IDS DE CLIENTES
        $idClientes = implode(',', array_filter(array_values(array_column($this->crm_contatos, 'id_cliente'))));

        $idClientes = $idClientes != "" ? $idClientes : 0;

        $query = $this->DB_fetch_array("SELECT 
                                                    *
                                                FROM
                                                    tb_crm_crm
                                                WHERE id_cliente in ($idClientes) and  possui_orcamento = 0 and finalizado = 1 ");
        $this->crm_contatos_finalizados = $query->num_rows ? $query->rows : [];

        // BUSCA TODOS OS PEDIDOS DOS CLIENTES
        $query = $this->DB_fetch_array("SELECT 
                                                    *
                                                FROM
                                                    tb_pedidos_pedidos A
                                                WHERE A.id_cliente in ($idClientes)");

        $this->todosPedidos = $query->num_rows ? $query->rows : [];

        $query = $this->DB_fetch_array("SELECT * FROM tb_admin_users WHERE stats = 1 AND id_grupo IN (2,3,4)");
        $this->users = $query;

        $query = $this->DB_fetch_array("SELECT * FROM tb_origem_lead WHERE stats = 1 AND deleted_at IS NULL");
        $this->origins = $query;

        // BUSCANDO OS CONTATOS DO CRM
        // VINCULA TODOS OS PEDIDOS A CADA CONTATO DO CRM
        foreach ($this->todosPedidos as $pedido) {
            $id = array_search($pedido['id_cliente'], array_column($this->crm_contatos, 'id_cliente'));

            $possuiCompra = in_array($pedido['id_status'], $this->id_pedidos_faturados);
            $possuiCompraCancelada = in_array($pedido['id_status'], $this->id_pedidos_cancelados);

            $pode_reativar = false;
            if($pedido['orc_etapa'] == 'desativado' or $pedido['orc_status'] == 'perda') {
                $pode_reativar = true;
            }

            $date = strtotime($pedido['data']);
            $this->crm_contatos[$id]['pedidos'][] = [
                'id' => $pedido['id'],
                'possui_compra' => $possuiCompra,
                'possui_compra_cancelada' => $possuiCompraCancelada,
                'data' => date('d/m/Y', $date),
                'datetime' => $date,
                'possui_orcamento' => true,
                'tipo_cliente' => $pedido['tipo_cliente'],
                'pode_reativa' =>  $pode_reativar,
                'quantidade_dias' => round((time() - $date) / (60 * 60 * 24))
            ];



            if(isset($this->crm_contatos[$id]['data_ultimo_pedido'])){
                if($date >  $this->crm_contatos[$id]['data_ultimo_pedido']){
                    $this->crm_contatos[$id]['data_ultimo_pedido'] = $date;
                    $this->crm_contatos[$id]['tipo_cliente'] = $pedido['tipo_cliente'];
                }

            }else {
                $this->crm_contatos[$id]['data_ultimo_pedido'] = $date;
                $this->crm_contatos[$id]['tipo_cliente'] = $pedido['tipo_cliente'];
            }



            if ($possuiCompra) {
                $this->crm_contatos[$id]['possui_compra'] = $possuiCompra;
            }
            if ($possuiCompraCancelada) {
                $this->crm_contatos[$id]['possui_compra_cancelada'] = $possuiCompraCancelada;
            }
        }
        // VINCULA TODOS OS CRM SEM ORÇAMENTO E FINALIZADO A CADA CONTATO DO CRM
        foreach ($this->crm_contatos_finalizados as $crm) {
            $id = array_search($crm['id_cliente'], array_column($this->crm_contatos, 'id_cliente'));
            $date = strtotime($crm['data']);

            $this->crm_contatos[$id]['pedidos'][] = [
                'possui_compra' => false,
                'possui_compra_cancelada' => false,
                'data' => date('d/m/Y', $date),
                'datetime' => $date,
                'tipo_cliente' => '',
                'possui_orcamento' => 0,
                'quantidade_dias' => round((time() - $date) / (60 * 60 * 24))
            ];
        }
        // ORDENA TODOS OS PEDIDOS / CRM PARA DATA MAIS RECENTE
        foreach ($this->crm_contatos as $key => $contato) {
            if (!isset($this->crm_contatos[$key]['pedidos'])) {
                $this->crm_contatos[$key]['pedidos'] = [];
                $this->crm_contatos[$key]['tipo_cliente'] =  '';
            }

            if (!isset($this->crm_contatos[$key]['possui_compra'])) {
                $this->crm_contatos[$key]['possui_compra'] = false;
            }

            if (!isset($this->crm_contatos[$key]['possui_compra_cancelada'])) {
                $this->crm_contatos[$key]['possui_compra_cancelada'] = false;
            }

            usort($this->crm_contatos[$key]['pedidos'], function ($a, $b) {
                $t1 = $a['datetime'];
                $t2 = $b['datetime'];

                return $t2 - $t1;
            });
        }

        list($field, $order) = explode('-', $this->ordenacao);
        if($field == 'data') {
            $field = 'tb_crm_crm.data';
        }
        // BUSCANDO OS PEDIDOS EM ABERTO
        // BUSCA TODOS OS CONTATOS DO CRM QUE POSSUI ORÇAMENTO
        $query = $this->DB_fetch_array("SELECT 
                                                    C.usuario as vendedor,
                                                    tb_crm_crm.id as id_crm,
                                                    'sem etapa' as etapa,
                                                    DATE_FORMAT(tb_crm_crm.data, '%d/%m/%Y às %H:%i') as data_crm,
                                                    tb_crm_crm.id_cliente,
                                                    tb_crm_crm.possui_orcamento,
                                                    tb_clientes_clientes.nome,
                                                    tb_clientes_clientes.telefone,
                                                    tb_clientes_clientes.observacao,
                                                    tb_clientes_clientes.engajamento,
                                                    tb_clientes_clientes.poder_aquisitivo,
                                                    tb_clientes_clientes.email,
                                                    TIMESTAMPDIFF(DAY,
                                                        date(ultimo_contato),
                                                        date(NOW())) AS dias_ultimo_contato,

                                                    DATE_FORMAT(tb_clientes_clientes.ultimo_contato,'%d/%m/%Y às %H:%i') ultimo_contato,
                                                    (SELECT DATE_FORMAT(data,'%d/%m/%Y às %H:%i') FROM (SELECT data FROM tb_clientes_contatos WHERE id_cliente = tb_clientes_clientes.id ORDER BY data DESC LIMIT 2) sub ORDER BY data ASC LIMIT 1) penultimo_contato
                                                FROM
                                                    tb_crm_crm
                                                        LEFT JOIN
                                                    tb_clientes_clientes ON tb_clientes_clientes.id = tb_crm_crm.id_cliente
                                                     LEFT JOIN
                                                     tb_pedidos_pedidos on tb_pedidos_pedidos.orc_id_crm = tb_crm_crm.id
                                                     INNER JOIN 
                                                     tb_admin_users C ON C.id = tb_crm_crm.id_user
                                                WHERE
                                                    possui_orcamento = 1
                                                    AND finalizado IS NULL
                                                    $whereVendedor  
                                                    $whereCrm
                                                    group by tb_crm_crm.id_cliente order by $field $order");
        $this->orcamentos = $query->num_rows ? $query->rows : [];

        $idClientes = implode(',', array_filter(array_values(array_column($this->orcamentos, 'id_cliente'))));
        $idClientes = $idClientes != "" ? $idClientes : 0;

        // BUSCA TODOS OS PEDIDOS DOS CLIENTES


        $query = $this->DB_fetch_array("SELECT                                                 
                                                  *,
                                                  A.data as data_pedido,
                                                    A.code as codigo_pedido,
                                                   C.usuario as vendedor
                                                FROM
                                                    tb_pedidos_pedidos A
                                                        INNER JOIN
                                                    (SELECT 
                                                        X.id_pedido,
                                                            GROUP_CONCAT(DISTINCT Z.nome
                                                                SEPARATOR ', ') categorias,
                                                            GROUP_CONCAT(DISTINCT Z.id) cat_ids,
                                                            GROUP_CONCAT(DISTINCT X.id) prod_ids
                                                    FROM
                                                        tb_carrinho_produtos_historico X
                                                    INNER JOIN tb_produtos_produtos_has_tb_produtos_categorias Y ON X.id_produto = Y.id_produto
                                                    INNER JOIN tb_produtos_categorias Z ON Y.id_categoria = Z.id
                                              
                                                    WHERE
                                                        X.id_pedido IS NOT NULL
                                                    GROUP BY X.id_pedido
                                                    $having
                                                    ) C ON C.id_pedido = A.id
                                                        LEFT JOIN
                                                    tb_pedidos_status ON tb_pedidos_status.id = A.id_status
                                                        left join 
                                                    tb_crm_crm on tb_crm_crm.id = A.orc_id_crm 
                                                        left JOIN 
                                                    tb_admin_users C ON C.id = tb_crm_crm.id_user   
                                                WHERE
                                                    A.id_cliente IN ($idClientes) and
                                                    possui_orcamento = 1
                                                    AND finalizado IS NULL 
                                                ORDER BY A.data DESC");
        $this->pedidos = $query->num_rows ? $query->rows : [];



        // VINCULA TODOS OS PEDIDOS A CADA CONTATO DO CRM
        foreach ($this->pedidos as $pedido) {
            $id = array_search($pedido['id_cliente'], array_column($this->orcamentos, 'id_cliente'));

            $possuiCompra = in_array($pedido['id_status'], $this->id_pedidos_faturados);
            $possuiCompraCancelada = in_array($pedido['id_status'], $this->id_pedidos_cancelados);

            $date = strtotime($pedido['data_pedido']);
            $novoPedido = [
                'possui_compra' => $possuiCompra,
                'categorias' => $pedido['categorias'],
                'id_pedido' => $pedido['id_pedido'],
                'valor_final' => $pedido['valor_final'],
                'possui_compra_cancelada' => $possuiCompraCancelada,
                'data' => date('d/m/Y', $date),
                'datetime' => $date,
                'possui_orcamento' => true,
                'quantidade_dias' => round((time() - $date) / (60 * 60 * 24)),
                'orc_status' => $pedido['orc_status'],
                'orc_etapa' => $pedido['orc_etapa'],
                'status_pedido' => $pedido['nome'],
                'id_cliente' => $pedido['id_cliente'],
                'code' => strtoupper(dechex($pedido['codigo_pedido'])),
                'valor_final' => $this->formataMoedaShow($pedido['valor_final']),
                'orc_id_crm' => $pedido['orc_id_crm']
            ];
            if($pedido['orc_status'] == 'aberto'){
                if($pedido['orc_etapa']){
                    if(!isset($this->orcamentos[$id]['prioridade_etapa'])){
                        $this->orcamentos[$id]['prioridade_etapa'] =  $this->prioridadeEtapas[$pedido['orc_etapa']];
                        $this->orcamentos[$id]['etapa'] =  $pedido['orc_etapa'];
                        $this->orcamentos[$id]['tipo_cliente'] =  $pedido['tipo_cliente'];
                    } else {
                        if($this->prioridadeEtapas[$pedido['orc_etapa']] > $this->orcamentos[$id]['prioridade_etapa']){
                            $this->orcamentos[$id]['prioridade_etapa'] =  $this->prioridadeEtapas[$pedido['orc_etapa']];
                            $this->orcamentos[$id]['etapa'] =  $pedido['orc_etapa'];
                            $this->orcamentos[$id]['tipo_cliente'] =  $pedido['tipo_cliente'];
                        }
                    }

                }
                if($pedido['orc_etapa'] == 'desativado') {
                    $this->orcamentos[$id]['pedido_desativado'][] = $novoPedido;
                } else {
                    $this->orcamentos[$id]['pedidos'][] = $novoPedido;
                }

            }else {
                $this->orcamentos[$id]['pedidos_finalizados'][] = $novoPedido;
            }

            if(!isset($this->orcamentos[$id]['possui_compra']))
                $this->orcamentos[$id]['possui_compra'] = false;

            if(!isset($this->orcamentos[$id]['possui_compra_cancelada']))
                $this->orcamentos[$id]['possui_compra_cancelada'] = false;

            if($possuiCompra)
                $this->orcamentos[$id]['possui_compra'] = $possuiCompra;

            if($possuiCompraCancelada)
                $this->orcamentos[$id]['possui_compra'] = $possuiCompraCancelada;
        }

        // BUSCA TODOS OS PEDIDOS DOS CLIENTES
        $query = $this->DB_fetch_array("SELECT 
                                                    *
                                                FROM
                                                    tb_pedidos_pedidos A
                                                WHERE A.id_cliente in ($idClientes)");

        $this->todosPedidos = $query->num_rows ? $query->rows : [];

        foreach ($this->todosPedidos as $pedido) {

            $id = array_search($pedido['id_cliente'], array_column($this->orcamentos, 'id_cliente'));

            $possuiCompra = in_array($pedido['id_status'], $this->id_pedidos_faturados);

            $possuiCompraCancelada = in_array($pedido['id_status'], $this->id_pedidos_cancelados);

            if ($possuiCompra) {
                $this->orcamentos[$id]['possui_compra'] = $possuiCompra;
            }
            if ($possuiCompraCancelada) {
                $this->orcamentos[$id]['possui_compra_cancelada'] = $possuiCompraCancelada;
            }
        }

        $this->totais = [
            'TOTAL' => [
                'contato' => 0,
                'orcamento' => 0,
                'followup' => 0,
                'fechamento' => 0,
            ],
        ];

        // PREENCHENDO TABELA TOTAAIS SÓ CRM
        $now = date("Y-m-d");
        foreach($this->crm_contatos as $contato) {
            if($contato['crm_quantidade_dias'] >= 0) {
                if(!isset($this->totais[$contato['vendedor']])){
                    $this->totais[$contato['vendedor']] = ['contato' => 0,'orcamento' => 0,'followup' => 0,'fechamento' => 0];

                }
                $this->totais[$contato['vendedor']]['contato']++;
                $this->totais['TOTAL']['contato']++;
            }
        }
        // PREENCHENDO TABELA TOTAAIS PEDIDOS
        foreach($this->pedidos as $pedido) {
            if($pedido['orc_status'] == 'aberto' and $pedido['orc_etapa'] != 'desativado') {
                if (!isset($this->totais[$pedido['vendedor']])) {
                    $this->totais[$pedido['vendedor']] = [
                        'contato' => 0,
                        'orcamento' => 0,
                        'followup' => 0,
                        'fechamento' => 0
                    ];
                }
                $this->totais[$pedido['vendedor']][$pedido['orc_etapa']] += $pedido['valor_final'];
                $this->totais['TOTAL'][$pedido['orc_etapa']] += $pedido['valor_final'];
            }
        }

        $this->renderView($this->getModule(), "index");
    }

    private function saveCrmNaoPossuiClienteAction()
    {
        $this->tableCrm = 'tb_crm_crm';
        $this->tableCliente = 'tb_clientes_clientes';
        $this->tableClientesContatos = 'tb_clientes_contatos';

        $formulario = $this->formularioObjeto($_POST);
        $validacao = $this->validaFormularioCrmNaoPossuiCliente($formulario);

        if (!$validacao->return) {
            echo json_encode($validacao);
        } else {

            $resposta = new \stdClass();

            $data = $this->formularioObjeto($_POST, $this->tableCliente);
            if ($_POST['cpf_cnpj']) {
                strlen($_POST['cpf_cnpj']) == 14 ? $data->cpf = $_POST['cpf_cnpj'] : $data->cnpj = $_POST['cpf_cnpj'];
            }

            foreach ($data as $key => $value) {
                $fields[] = $key;
                if ($value == "NULL") {
                    $values[] = "$value";
                } else {
                    $values[] = "'$value'";
                }
            }

            $query = $this->DB_insert($this->tableCliente, implode(',', $fields), implode(',', $values));


            if ($query->query) {
                $idCliente = $query->insert_id;

                unset($fields);
                unset($values);
                $data = $this->formularioObjeto($_POST, $this->tableCrm);
                $data->id_cliente = $idCliente;
                foreach ($data as $key => $value) {
                    $fields[] = $key;
                    if ($value == "NULL") {
                        $values[] = "$value";
                    } else {
                        $values[] = "'$value'";
                    }
                }

                $query = $this->DB_insert($this->tableCrm, implode(',', $fields), implode(',', $values));

                if($_POST['email'] && $query->query) {
                    $cliente = new Cliente();
                    $cliente->adicionarEmailBase($query->insert_id);
                }

                $date = date('Y-m-d H:i:s');

                // atualizando ultima data de contato do usuário
                $fields_values[] = "ultimo_contato='$date'";
                $query = $this->DB_update($this->tableCliente,
                    implode(',', $fields_values) . " WHERE id=" . $idCliente);

                // inserindo na tabela tb_clientes_contato
                unset($fields);
                unset($values);
                $fields[] = "data";
                $fields[] = "id_cliente";
                $values[] = "'$date'";
                $values[] = $idCliente;
                $this->DB_insert($this->tableClientesContatos, implode(',', $fields), implode(',', $values));

                // buscando usuario
                $query = $this->DB_fetch_array("select * from $this->tableCliente where id = $idCliente");
                $nome = isset($query->num_rows) ? $query->rows[0]['nome'] : '';

                $resposta->type = "success";
                $resposta->message = "Registro cadastrado com sucesso!";
                $this->inserirRelatorio("Cadastrou CRM: [" . $nome . "]");
            } else {
                $resposta->type = "error";
                $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
            }

            if (ob_get_length()) {
                ob_end_clean();
            }
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($resposta);
            exit;
        }
    }

    private function saveCrmPossuiClienteAction()
    {
        $this->tableCrm = 'tb_crm_crm';
        $this->tableCliente = 'tb_clientes_clientes';
        $this->tableClientesContatos = 'tb_clientes_contatos';

        $formulario = $this->formularioObjeto($_POST);
        $validacao = $this->validaFormularioCrmPossuiCliente($formulario);

        if (!$validacao->return) {
            echo json_encode($validacao);
        } else {

            $resposta = new \stdClass();
            $data = $this->formularioObjeto($_POST, $this->tableCrm);
            $query = $this->DB_fetch_array("select * from $this->tableCliente where id = $data->id_cliente");
            if($query->num_rows){
                $data->nome = $query->rows[0]['nome'];
                $data->email = $query->rows[0]['email'];
                $data->cpf_cnpj = isset($query->rows[0]['cpf']) ? $query->rows[0]['cpf'] : $query->rows[0]['cnpj'];
                $data->telefone = $query->rows[0]['telefone'];
            }

            //busca dados do cliente para preencher crm

            foreach ($data as $key => $value) {
                $fields[] = $key;
                if ($value == "NULL") {
                    $values[] = "$value";
                } else {
                    $values[] = "'$value'";
                }
            }

            $query = $this->DB_insert($this->tableCrm, implode(',', $fields), implode(',', $values));

            unset($fields);
            unset($values);

            if ($query->query) {
                $date = date('Y-m-d H:i:s');

                // atualizando ultima data de contato do usuário
                $fields_values[] = "ultimo_contato='$date'";
                $query = $this->DB_update($this->tableCliente,
                    implode(',', $fields_values) . " WHERE id=" . $data->id_cliente);
                // inserindo na tabela tb_clientes_contato
                $fields[] = "data";
                $fields[] = "id_cliente";
                $values[] = "'$date'";
                $values[] = $data->id_cliente;
                $this->DB_insert($this->tableClientesContatos, implode(',', $fields), implode(',', $values));

                // buscando usuario
                $query = $this->DB_fetch_array("select * from $this->tableCliente where id = $data->id_cliente");
                $nome = isset($query->num_rows) ? $query->rows[0]['nome'] : '';

                $resposta->type = "success";
                $resposta->message = "Registro cadastrado com sucesso!";
                $this->inserirRelatorio("Cadastrou CRM: [" . $nome . "]");
            } else {
                $resposta->type = "error";
                $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
            }

            echo json_encode($resposta);
        }
    }

    private function agendarCrmAction()
    {
        $this->tableCrm = 'tb_crm_crm';
        $this->tableCliente = 'tb_clientes_clientes';


        $formulario = $this->formularioObjeto($_POST);
        $validacao = $this->validaFormularioAgendarCrm($formulario);

        if (!$validacao->return) {
            echo json_encode($validacao);
        } else {

            $resposta = new \stdClass();


            $data = $this->formularioObjeto($_POST, $this->tableCrm);
            list($dia, $mes, $ano) = explode('/', $data->data);
            $data->data = "$ano-$mes-$dia";

            $fields_values[] = "finalizado=1";
            $query = $this->DB_update($this->tableCrm, implode(',', $fields_values) . " WHERE id=" . $data->id);
            unset($data->id);
            $queryCliente = $this->DB_fetch_array("select * from $this->tableCliente where id = 3886");

            $data->nome = $queryCliente->rows[0]['nome'];
            $data->email = $queryCliente->rows[0]['email'];
            $data->telefone = $queryCliente->rows[0]['telefone'];
            $data->cpf_cnpj = $queryCliente->rows[0]['cpf'] ? $queryCliente->rows[0]['cpf'] : $queryCliente->rows[0]['cnpj'];

            foreach ($data as $key => $value) {
                $fields[] = $key;
                if ($value == "NULL") {
                    $values[] = "$value";
                } else {
                    $values[] = "'$value'";
                }
            }

            $query = $this->DB_insert($this->tableCrm, implode(',', $fields), implode(',', $values));

            if ($query->query) {
                $nome = isset($queryCliente->num_rows) ? $queryCliente->rows[0]['nome'] : '';

                $resposta->type = "success";
                $resposta->message = "Registro cadastrado com sucesso!";
                $this->inserirRelatorio("Cadastrou CRM: [" . $nome . "]");
            } else {
                $resposta->type = "error";
                $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
            }

            echo json_encode($resposta);
        }
    }

    private function finalizarCrmAction()
    {
        $resposta = new \stdClass();
        $this->tableCrm = 'tb_crm_crm';

        $data = $this->formularioObjeto($_POST, $this->tableCrm);

        $fields_values[] = "finalizado=1";
        $query = $this->DB_update($this->tableCrm, implode(',', $fields_values) . " WHERE id=" . $data->id);

        if ($query) {
            $resposta->type = "success";
            $resposta->message = "Contato Finalizado com sucesso!";
            $this->inserirRelatorio("Finalizou CRM: [" . $data->id . "]");
        } else {
            $resposta->type = "error";
            $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
        }

        echo json_encode($resposta);

    }

    private function validaFormularioAgendarCrm($form)
    {

        $resposta = new \stdClass();
        $resposta->return = true;

        if ($form->descricao == "") {
            $resposta->type = "attention";
            $resposta->message = "Insira uma descrição";
            $resposta->field = "descricao";
            $resposta->return = false;
            return $resposta;
        } else {
            if ($form->data == "") {
                $resposta->type = "attention";
                $resposta->message = "Insira uma data";
                $resposta->field = "data";
                $resposta->return = false;
                return $resposta;
            } else {
                return $resposta;
            }
        }
    }


    private function validaFormularioCrmNaoPossuiCliente($form)
    {
        $resposta = new \stdClass();
        $resposta->return = true;
        $where = [];
        $whereOr = '';
        if($form->email){
            $where[] = "email = '$form->email'";
        }
        if($form->cpf_cnpj){
            $where[] = "cpf = '$form->cpf_cnpj'";
        }
        if($form->cpf_cnpj){
            $where[] = "cnpj = '$form->cpf_cnpj'";
        }
        if($form->telefone){
            $where[] = "telefone = '$form->telefone'";

            list($ddd, $numero) =  explode(' ', $form->telefone);

            $numero = str_replace('_', '', $numero);
            if(strlen($numero) == 9){
                $newNumberFormat = $ddd . ' ' . substr($numero, 1);
            } else if(strlen($numero) == 8){
                $newNumberFormat = $ddd . ' ' . '9' . $numero;
            }
            $where[] = "telefone = '$newNumberFormat'";
        }
        if(!empty($where)){
            $whereOr = ' WHERE ' . implode(' or ', $where);
        }


        $query = $this->DB_fetch_array("select * from tb_clientes_clientes $whereOr limit 1");

        if ($form->nome == "") {
            $resposta->type = "attention";
            $resposta->message = "Preencha o campo nome";
            $resposta->field = "nome";
            $resposta->return = false;
            return $resposta;
        } else {
            if ($form->id_user == "") {
                $resposta->type = "attention";
                $resposta->message = "Selecione um vendedor";
                $resposta->field = "id_user";
                $resposta->return = false;
                return $resposta;
            } else {
                if ($form->email == "" && $form->telefone == "" && $form->cpf_cnpj == "") {
                    $resposta->type = "attention";
                    $resposta->message = "Você precisa preencher ao menos um campo entre email, telelfone, cpf ou cnpj";
                    $resposta->return = false;
                    $resposta->time = 5000;
                    return $resposta;
                } else {
                    if ($form->email != "" && $this->validaEmail($form->email) == 0) {
                        $resposta->type = "attention";
                        $resposta->message = "Formato de Email Incorreto";
                        $resposta->field = "email";
                        $resposta->return = false;
                        return $resposta;
                    }
                }
            }
        }
        if($query->num_rows){
            if($form->email != '' and $form->email  == $query->rows[0]['email']){
                $resposta->type = "attention";
                $resposta->message = "Email já cadastrado";
                $resposta->field = "Email";
                $resposta->return = false;
                return $resposta;
            }
            if($form->cpf_cnpj != '' and $form->cpf_cnpj == $query->rows[0]['cpf']){
                $resposta->type = "attention";
                $resposta->message = "CPF já cadastrado";
                $resposta->field = "cpf_cnpj";
                $resposta->return = false;
                return $resposta;
            }
            if($form->cpf_cnpj != '' and $form->cpf_cnpj == $query->rows[0]['cnpj']){
                $resposta->type = "attention";
                $resposta->message = "CNPJ já cadastrado";
                $resposta->field = "cpf_cnpj";
                $resposta->return = false;
                return $resposta;
            }

            if($form->telefone != '' and ($form->telefone == $query->rows[0]['telefone'] or $newNumberFormat == $query->rows[0]['telefone'])){
                $resposta->type = "attention";
                $resposta->message = "Telefone já cadastrado";
                $resposta->field = "telefone";
                $resposta->return = false;
                return $resposta;
            }
        }

        return $resposta;
    }

    //-------------- Editar Descontos -----------------//

    private function editaDescontosAction(){
        $id = $this->getParameter('id');
        #$id = 25358;
        $query = $this->DB_fetch_array("SELECT B.mensagem_cupom, A.id, A.id_pedido, A.nome_produto, COALESCE(A.valor_editado, A.valor_produto) valor_editado, A.custo, A.valor_produto, A.quantidade, A.desconto, A.desconto_fabrica, A.descricao_desconto FROM tb_carrinho_produtos_historico A JOIN tb_pedidos_pedidos B ON A.id_pedido=B.id WHERE A.id_pedido = $id");

        if($query->num_rows){
            $this->produtos = $query->rows;
        }


        $this->renderView($this->getModule(), "edita_descontos");
    }

    private function saveDescontosAction(){

        $pedido = $_POST['pedido'];
        $carrinho = $this->DB_fetch_array("SELECT c.id, c.valor_produto, COALESCE(c.desconto, 0) desconto, c.quantidade FROM tb_carrinho_produtos_historico c WHERE c.id_pedido =".$pedido['id']);
        $carrinho = $carrinho->rows;

        $valor_cupom = 0;

        foreach ($_POST['produtos'] as $editado) {
            foreach ($carrinho as $atual) {
                if($atual['id']==$editado['id'] && $atual['valor_produto'] != $editado['valor_editado']){
                    $query = $this->DB_update("tb_carrinho_produtos_historico", "valor_editado = ".$editado['valor_editado']." WHERE id = ".$editado['id']);

                    //o valor do cupom é a diferença do desconto que possa estar existente no produto e o valor editado.
                    $valor_cupom = $valor_cupom + (($atual['valor_produto'] - $atual['desconto'] - $editado['valor_editado']) * $atual['quantidade']);
                }

                if($atual['id']==$editado['id'] && ($atual['valor_produto']-$atual['desconto']) == $editado['valor_editado']){
                    $query = $this->DB_update("tb_carrinho_produtos_historico", "valor_editado = NULL WHERE id = ".$editado['id']);
                }

            }
        }

        if($valor_cupom){
            $query = $this->DB_update("tb_pedidos_pedidos", "mensagem_cupom = 'desconto especial', valor_cupom = ".$valor_cupom.", valor_final = ".$pedido['valor_final']." WHERE id = ".$pedido['id']);
        }else{
            $query = $this->DB_update("tb_pedidos_pedidos", "mensagem_cupom = NULL, valor_cupom = 0, valor_final = ".$pedido['valor_final']." WHERE id = ".$pedido['id']);
        }

    }

    private function validaFormularioCrmPossuiCliente($form)
    {
        $resposta = new \stdClass();
        $resposta->return = true;

        if ($form->id_cliente == "") {
            $resposta->type = "validation";
            $resposta->message = "Selecione um cliente";
            $resposta->field = "id_cliente";
            $resposta->return = false;
            return $resposta;
        }

        if ($form->id_user == "") {
            $resposta->type = "validation";
            $resposta->message = "Selecione um vendedor";
            $resposta->field = "id_user";
            $resposta->return = false;
            return $resposta;
        }

        if($form->aceita_outro_vendedor == 0) {

            $query = $this->DB_fetch_array("select C.* from tb_crm_crm   left JOIN tb_admin_users C ON C.id = tb_crm_crm.id_user    where finalizado is null
                                                            and id_cliente =  $form->id_cliente
                                                            and id_user <> " . $form->id_user . ' limit 1');
            if($query->num_rows) {
                $resposta->type = "modal";
                $resposta->message = $query->rows[0]['nome'];
                $resposta->return = false;
                return $resposta;
            }
        }


        return $resposta;
    }

    //--------------- Buscar lista de usuários ------------------//

    private function searchClientsCompleteAction()
    {
        $termo = $_GET['q'] ?? '';
        $limite = $_GET['per_page'] ?? 10;
        $ultimo = $_GET['last_id'] ?? 0;

        $resposta = [
            'results' => [],
            'more' => false
        ];

        // Busca um a mais para saber se ainda existem mais resultados
        $lim = $limite + 1;

        $query = $this->DB_fetch_array("SELECT 
                                                     id, nome, cnpj,cpf,email,telefone,razao_social
                                                FROM
                                                    tb_clientes_clientes
                                                WHERE
                                                    (nome LIKE '%{$termo}%' or                                                    
                                                    cnpj LIKE '%{$termo}%' or                                                    
                                                    cpf LIKE '%{$termo}%' or                                                    
                                                    email LIKE '%{$termo}%' or                                                    
                                                    razao_social LIKE '%{$termo}%' or                                                    
                                                    telefone LIKE '%{$termo}%') And                                                    
                                                    id > {$ultimo} 
                                                ORDER BY id
                                                LIMIT {$lim}");
        if ($query->num_rows) {
            // Adiciona os resultados
            foreach ($query->rows as $i => $row) {
                if ($i >= $limite) {
                    break;
                }
                $resposta['results'][] = [
                    'id' => $row['id'],
                    'text' => "#{$row['id']} - {$row['nome']} - {$row['email']} - {$row['telefone']} - {$row['cpf']} - {$row['cnpj']} - {$row['razao_social']}",
                ];
            }
            // Seta a flag de paginação
            if ($query->num_rows > $limite) {
                $resposta['more'] = true;
            }
        }

        echo json_encode($resposta);
    }

    private function getClientOriginByIdAction()
    {
        $id = $_GET['id'] ?? 0;

        $resposta = ['success' => false];

        if ($id) {
            $cliente = $this->DB_fetch_array("SELECT id_origem FROM tb_clientes_clientes WHERE id = {$id}", "form");
            
            if ($cliente->num_rows) {
                $resposta['success'] = true;
                $resposta['data'] = $cliente->rows[0];
            }
        }

        echo json_encode($resposta);
    }

    //--------------- Altera o status de um orçamento ------------------//

    private function saveAction()
    {
        if (!$this->permissions[$this->permissao_ref]['editar']) {
            exit();
        }

        $formulario = $this->formularioObjeto($_POST);
        $resposta = new \stdClass();

        // Validação
        $queryPedidos = $this->DB_fetch_array("SELECT * FROM {$this->table} inner join tb_clientes_clientes on tb_clientes_clientes.id = tb_pedidos_pedidos.id_cliente WHERE tb_pedidos_pedidos.id = {$formulario->id_orcamento} LIMIT 1");

        if (!$queryPedidos->num_rows) {
            $resposta->type = "error";
            $resposta->message = "Identificador de orçamento inválido!";
            echo json_encode($resposta);
            return;
        } else {
            if (!in_array($formulario->etapa, ['orcamento', 'followup', 'fechamento', 'desativado'])) {
                $resposta->type = 'validation';
                $resposta->message = 'Selecione uma etapa válida';
                $resposta->field = 'etapa';
                echo json_encode($resposta);
                return;
            } else {
                if (!in_array($formulario->status, ['aberto', 'ganho', 'perda'])) {
                    $resposta->type = 'validation';
                    $resposta->message = 'Selecione um status válido';
                    $resposta->field = 'status';
                    echo json_encode($resposta);
                    return;
                } else {
                    if ($formulario->etapa !='desativado' and $formulario->status == 'perda' and !isset($formulario->motivo)) {
                        $resposta->type = 'attention';
                        $resposta->message = 'Selecione um motivo';
                        $resposta->field = 'status';
                        echo json_encode($resposta);
                        return;
                    }
                }
            }
        }


        if(isset($formulario->id_crm) and $formulario->id_crm > 0 ) {
            $fields_values[] = "orc_id_crm = '{$formulario->id_crm}'";
        }
        // Atualiza os valores

        $fields_values[] = "orc_etapa = '{$formulario->etapa}'";
        $fields_values[] = "orc_status = '{$formulario->status}'";
        $fields_values[] = "data = NOW()";

        // Atualiza o valor no banco de dados
        $query = $this->DB_update($this->table, implode(',', $fields_values) . " WHERE id = {$formulario->id_orcamento}");

        if ($query) {

            if(isset($formulario->motivo)){
                    $this->DB_insert('tb_pedidos_desistencias', "id_pedido,id_motivo",
                    "'$formulario->id_orcamento','$formulario->motivo'");
            }

            $idCrm = $queryPedidos->rows[0]['orc_id_crm'];
            if($formulario->status != 'aberto'  and $queryPedidos->rows[0]['orc_id_crm']){
                $fields_values_update[] = "finalizado=1";
            } else {
                if($formulario->id_crm) {
                    $idCrm = $formulario->id_crm;
                    $fields_values_update[] = "possui_orcamento=1";
                }
                $fields_values_update[] = "finalizado=NULL";
            }
            $query = $this->DB_update('tb_crm_crm', implode(',', $fields_values_update) . " WHERE id = $idCrm");

            $resposta->id_cliente =  $queryPedidos->rows[0]['id_cliente'];
            $resposta->nome =  $queryPedidos->rows[0]['nome'];
            $resposta->id_crm =  $queryPedidos->rows[0]['orc_id_crm'];
            $resposta->status =  'perda';
            $resposta->type = "success";
            $resposta->message = "Alterado com sucesso!";
        } else {
            $resposta->type = "error";
            $resposta->message = "Aconteceu um erro no sistema, por favor tente novamente mais tarde!";
        }
        echo json_encode($resposta);
    }

    //--------------- Transforma um orçamento em pedido ------------------//

    private function transferAction()
    {
        global $sistema;
        
        if (!$this->permissions[$this->permissao_ref]['editar']) {
            exit();
        }

        $formulario = $this->formularioObjeto($_POST);
        $resposta = new \stdClass();
        $resposta->return = false;

        $validacao = $this->validaFormulario($formulario);

        if (!$validacao->return) {
            echo json_encode($validacao);
            return;
        }

        $table = 'tb_clientes_clientes';
        $dataCliente = $this->formularioObjeto($_POST, $table);

        $dataCliente->senha = $this->embaralhar($dataCliente->senha);

        $query = $this->DB_fetch_array("SELECT * FROM tb_clientes_clientes WHERE email = '$dataCliente->email' and id <> $formulario->id_cliente");
        if ($query->num_rows) {
            $resposta->type = "attention";
            $resposta->message = "Este e-mail já está cadastrado em nossa base.";
            echo json_encode($resposta);
            exit();
        }


        foreach ($dataCliente as $key => $value) {
            if ($value == "NULL")
                $fields_values[] = "$key=$value";
            else
                $fields_values[] = "$key='$value'";
        }

        $query = $this->DB_update('tb_clientes_clientes', implode(',', $fields_values) . " WHERE id=" . $formulario->id_cliente);
        unset($fields_values);
        if ($query) {

            $verifica = $this->DB_num_rows("SELECT * FROM tb_emails_emails A INNER JOIN tb_listas_listas_has_tb_emails_emails B ON B.id_email = A.id AND A.email = '$formulario->email' AND B.id_lista = 3");
            if (!$verifica) {
                $verifica = $this->DB_fetch_array("SELECT * FROM tb_emails_emails WHERE email = '$formulario->email'");
                if (!$verifica->num_rows) {
                    $addEmail = $this->DB_insert('tb_emails_emails', "nome,email",
                        "'$formulario->nome','$formulario->email'");
                    $idEmail = $addEmail->insert_id;
                } else {
                    $idEmail = $verifica->rows[0]['id'];
                }
                $addListaHasEmail = $this->DB_insert('tb_listas_listas_has_tb_emails_emails',
                    "id_lista,id_email", "3,$idEmail");
            }

            $this->inserirRelatorio("Cliente: [" . $formulario->email . "] Id: [" . $formulario->id_cliente . "]");
        } else {
            $resposta->type = "error";
            $resposta->message = "Aconteceu um erro ao atualizar o usuário";
            echo json_encode($resposta);
            return;
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
        unset($endereco->id);
        unset($endereco->data);
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
        if (isset($formulario->frete_prazo) && $formulario->frete_prazo != "") {
            $data->prazo_entrega = date('Y-m-d', strtotime("+$formulario->frete_prazo days", strtotime(date('Y-m-d'))));
            $data->dias_entrega = $formulario->frete_prazo;
            $temp = "(tipo1)";
        } else {
            $data->prazo_entrega = date('Y-m-d');
            $data->dias_entrega = 0;
            $temp = "(tipo2)";
        }

        if ($data->dias_entrega == 0) {
            $resposta->type = "error";
            $resposta->message = "Dias para entrega do pedido está zerado. Tente novamente. ".$temp;
            $sistema->inserirRelatorio("Erro frete zerado, pedido ".$formulario->id_orcamento.", cliente ".$formulario->id_cliente.", frete ".$data->frete.", valor do frete ".$data->valor_frete." ".$temp);
            $sistema->enviarEmail('jfelipesilva@gmail.com', 'contato@realpoker.com', 'Prazo de entrega zerado', utf8_decode("Erro frete zerado, pedido ".$formulario->id_orcamento.", cliente ".$formulario->id_cliente.", frete ".$data->frete.", valor do frete ".$data->valor_frete. " ".$temp));
            echo json_encode($resposta);
            return;
        }



        // TODO: FIX - Cupom de porcentagem tem o valor alterado pelo frete
        // Recalcula o valor final (aplicando cupons, o novo frete, e método de pagamento)
        $valor_final = $data->subtotal + $data->valor_frete - $data->descontos;

        if ($data->tipo_cupom != 1) {
            $valor_cupom = $data->valor_cupom;
        } else {
            $valor_cupom = (($valor_final * $data->valor_cupom) / 100);
        }

        $valor_final = $valor_final - $valor_cupom;

        if ($data->avista == 1) {
            $valor_final = $valor_final - (($valor_final * 5) / 100);
        }

        $data->valor_final = $valor_final;

        // Prepara campos para a query de update
        foreach ($data as $key => $value) {
            if ($value == "NULL") {
                $fields_values[] = "$key=$value";
            } else {
                $fields_values[] = "$key='$value'";
            }
        }
        // Atualiza timestamp (data)
        $fields_values[] = "data = NOW()";

        $query = $this->DB_update($this->table, implode(',', $fields_values) . " WHERE id = $formulario->id_orcamento");


        unset($fields_values);
        if ($query) {
            $endereco = false;
            $enderecos = $this->DB_fetch_array("SELECT A.*, C.cidade, E.estado, E.uf FROM tb_pedidos_enderecos A INNER JOIN tb_utils_cidades C ON C.id = A.id_cidade INNER JOIN tb_utils_estados E ON E.id = C.id_estado WHERE A.id_pedido = $data->id");
            if ($enderecos->num_rows) {
                $endereco = $enderecos->rows[0];
            }

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

            if (isset($vendedor) && $vendedor->num_rows) {
                $to[] = array(
                    "email" => $vendedor->rows[0]['email'],
                    "nome" => utf8_decode($vendedor->rows[0]['nome'])
                );
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

                            <br /><br /><span style='color: #008000; font-size: 14pt;'><a href='{$this->site_url}minha-conta' target='_blank'><span style='color: #008000;'><strong>https://www.realpoker.com.br/minha-conta</strong> </span></a></span><strong><br /><br />
                            ";

            if ($data->metodo_pagamento == "pokerstars") {
                $frt = $data->valor_frete / $sys->cotacao_dollar;
            } else {
                $frt = $data->valor_frete;
            }

            $mensagem .= "
                            <b>Nome:</b> {$cliente['nome']}<br>
                            " . (
                ($cliente['cnpj'] != "") ?
                    "
                            <b>Razão Social:</b> {$cliente['razao_social']}<br>
                            <b>CNPJ:</b> {$cliente['cnpj']}<br>
                            <b>Inscrição Estadual:</b> {$cliente['inscricao_estadual']}<br>
                        " :
                    "
                            <b>CPF:</b> {$cliente['cpf']}<br> 
                        "
                ) . "<b>E-mail:</b> {$cliente['email']}<br>
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
            } else {
                if ($data->metodo_pagamento == "boleto") {
                    $forma_pagamento = "Boleto";
                } else {
                    if ($data->metodo_pagamento == "cielo") {
                        $forma_pagamento = "Cielo";
                    } else {
                        if ($data->metodo_pagamento == "cielo_transparente") {
                            $forma_pagamento = "Cartão de Crédito";
                        } else {
                            if ($data->metodo_pagamento == "rede_transparente") {
                                $forma_pagamento = "Cartão de Crédito";
                            } else {
                                if ($data->metodo_pagamento == "pagseguro") {
                                    $forma_pagamento = "Pagseguro";
                                } else {
                                    if ($data->metodo_pagamento == "pokerstars") {
                                        $forma_pagamento = "Pokerstars";
                                    }
                                }
                            }
                        }
                    }
                }
            }

            $mensagem .= "<b>Forma de Pagamento:</b> $forma_pagamento<br><br>";

            if ($data->avista == 1) {
                $mensagem .= "<b>Você ganhou 5% de desconto por escolher um pagamento à vista.</b><br><br>";
            }

            if ($data->tipo_cupom != 0) {
                $mensagem .= "<b>{$data->mensagem_cupom}</b><br><br>";
            }


            if ($data->metodo_pagamento == "pokerstars") {
                $mensagem .= "<b>Valor:</b> US$ {$this->formataMoedaShow($data->valor_final / $sys->cotacao_dollar)}<br><br><br>";
            } else {
                $mensagem .= "<b>Valor:</b> R$ {$this->formataMoedaShow($data->valor_final)}<br><br><br>";
            }


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
                if ($produto['desconto'] == "") {
                    $produto['desconto'] = 0;
                }
                $mensagem .= "<br><br><b>Quantidade:</b> {$produto['quantidade']}<br>";
                if ($data->metodo_pagamento == "pokerstars") {
                    $mensagem .= "<b>Total:</b> R$ {$this->formataMoedaShow((($produto['valor_produto'] * $produto['quantidade']) - ($produto['desconto'] * $produto['quantidade'])) / $sys->cotacao_dollar)}<br><br>";
                } else {
                    $mensagem .= "<b>Total:</b> R$ {$this->formataMoedaShow(($produto['valor_produto'] * $produto['quantidade']) - ($produto['desconto'] * $produto['quantidade']))}<br><br>";
                }
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
                $xmctags = 'X-MC-Tags: ' . implode(',', $xmctagscategorias);
            }

            if (isset($vendedor) && $vendedor->num_rows) {
                $setFrom = array(array('email' => $vendedor->rows[0]['email'], 'nome' => 'Real Poker'));
            } else {
                $setFrom = '';
            }

            $fields_values_update[] = "finalizado=1";
            $query = $this->DB_update('tb_crm_crm', implode(',', $fields_values_update) . " WHERE id = {$formulario->orc_id_crm}");

            $queryCrm = $this->DB_fetch_array("SELECT * FROM tb_crm_crm where tb_crm_crm.id = {$formulario->orc_id_crm} LIMIT 1");

            $this->enviarEmail($to, $setFrom, utf8_decode($assunto), utf8_decode($mensagem), '', utf8_decode($xmctags));

            //verifica se o frete é à consultar e dispara alerta
            if(stripos(mb_strtolower($data->frete), 'consultar')){
                $vars = new stdClass();
                $vars->PEDIDO = $data->id;
                $vars->DATAHORA = date('d/m/Y').' às '.date('H:m');
                $vars->CLIENTE = $cliente['nome'];
                $vars->CEP = $endereco['cep'];
                $vars->ENDERECO = $endereco['endereco'] . ', nº '. $endereco['numero'] . ', '. $endereco['complemento'] . ' - '. $endereco['bairro'];
                $vars->CIDADE = $endereco['cidade'];
                $vars->ESTADO = $endereco['uf'];
                (new Notificacoes($sistema, $vars, 'cotacao-frete'))->disparaNotificao();
            }


            $resposta->id_cliente =  $queryCrm->rows[0]['id_cliente'];
            $resposta->nome =  $queryCrm->rows[0]['nome'];
            $resposta->id_crm =  $queryCrm->rows[0]['id'];
            $resposta->type = "success";
            $resposta->message = "Registro transferido com sucesso!";
            echo json_encode($resposta);
        } else {
            $resposta->type = "error";
            $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
            echo json_encode($resposta);
        }
    }

    function newsletter($cliente)
    {
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
                    $addListaHasEmail = $sistema->DB_insert('tb_listas_listas_has_tb_emails_emails',
                        "id_lista,id_email", "2,$idEmail");
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

    private function datatableAction()
    {
        if (!$this->permissions[$this->permissao_ref]['ler']) {
            exit();
        }

        // IDs de categorias de produto para aplicar o filtro
        $sHaving = ($_POST['categories_array']) ? 'HAVING ' . implode(' OR ', array_map(function ($c) {
                return "cat_ids LIKE '%{$c}%'";
            }, explode(',', ($_POST['categories_array'])))) : '';

        //defina os campos da tabela
        $aColumns = array(
            'B.nome',
            'B.telefone',
            'A.id',
            'E.nome vendedor',
            'DATE_FORMAT(A.orc_data, CONCAT("%d/%m/%Y (", DATEDIFF(NOW(), A.orc_data), IF(DATEDIFF(NOW(), A.orc_data) = 1, " dia", " dias"), ")")) data',
            'C.categorias',
            'A.valor_final',
            'A.orc_etapa',
            'A.orc_status',
            'A.code',
            'A.orc_id_crm',
            'C.prod_ids',
            'D.maxID'
        );

        //defina os campos que devem ser usados para a busca
        $aColumnsWhere = array(
            'B.nome',
            'B.telefone',
            'A.id',
            'E.nome',
            'DATE_FORMAT(A.orc_data, CONCAT("%d/%m/%Y (", DATEDIFF(NOW(), A.orc_data), IF(DATEDIFF(NOW(), A.orc_data) = 1, " dia", " dias"), ")"))',
            'C.categorias',
            'A.valor_final',
            'A.orc_etapa',
            'A.orc_status'
        );

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
                $tmp = $from;
                $from = $to;
                $to = $tmp;
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
                if ($_POST['iSortCol_0'] == 2) {
                    $campo = "D.maxID DESC, A.id";
                }

                // Ordena por data da última modificação
                if ($_POST['iSortCol_0'] == 3) {
                    $campo = "A.orc_data";
                }

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
        if (isset($sQuery->num_rows)) {
            $rResult = $sQuery->rows;
        }

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
                    $options = implode(' ', array_map(function ($etapa, $label) use ($aRow) {
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

    private function validaFormulario($form)
    {

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
        } else {
            if ($form->pessoa == 2) {
                if ($form->razao_social == "") {
                    $resposta->type = "validation";
                    $resposta->message = "Preencha este campo corretamente";
                    $resposta->field = "razao_social";
                    $resposta->return = false;
                    return $resposta;
                } else {
                    if ($form->cnpj == "") {
                        $resposta->type = "validation";
                        $resposta->message = "Preencha este campo corretamente";
                        $resposta->field = "cnpj";
                        $resposta->return = false;
                        return $resposta;
                    } else {
                        if (!$sistema->validaCNPJ($form->cnpj)) {
                            $resposta->type = "validation";
                            $resposta->message = "Preencha este campo corretamente";
                            $resposta->field = "cnpj";
                            $resposta->return = false;
                            return $resposta;
                        } else {
                            return $this->validaFormularioContinuacao($form);
                        }
                    }
                }
            } else {
                return $this->validaFormularioContinuacao($form);
            }
        }
    }

    private function validaFormularioContinuacao($form)
    {
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
        } else {
            if ($form->pessoa == 1 AND $form->cpf == "") {
                $resposta->type = "validation";
                $resposta->message = "Preencha este campo corretamente";
                $resposta->field = "cpf";
                $resposta->return = false;
                return $resposta;
            } else {
                if ($form->pessoa == 1 AND !$sistema->validaCPF($form->cpf)) {
                    $resposta->type = "validation";
                    $resposta->message = "Preencha este campo corretamente";
                    $resposta->field = "cpf";
                    $resposta->return = false;
                    return $resposta;
                } else {
                    if ($form->email == "") {
                        $resposta->type = "validation";
                        $resposta->message = "Preencha este campo corretamente";
                        $resposta->field = "email";
                        $resposta->return = false;
                        return $resposta;
                    } else {
                        if ($sistema->validaEmail($form->email) == 0) {
                            $resposta->type = "validation";
                            $resposta->message = "Preencha este campo com um E-mail válido";
                            $resposta->field = "email";
                            $resposta->return = false;
                            return $resposta;
                        } else {
                            if ($form->telefone == "") {
                                $resposta->type = "validation";
                                $resposta->message = "Preencha este campo corretamente";
                                $resposta->field = "telefone";
                                $resposta->return = false;
                                return $resposta;
                            } else {
                                if ($form->senha == "") {
                                    $resposta->type = "validation";
                                    $resposta->message = "Preencha este campo corretamente";
                                    $resposta->field = "senha";
                                    $resposta->return = false;
                                    return $resposta;
                                } else {
                                    if ($form->cep == "") {
                                        $resposta->type = "validation";
                                        $resposta->message = "Preencha este campo corretamente";
                                        $resposta->field = "cep";
                                        $resposta->return = false;
                                        return $resposta;
                                    } else {
                                        if (strlen($form->cep) != 10) {
                                            $resposta->type = "validation";
                                            $resposta->message = "Preencha todo o campo";
                                            $resposta->field = "cep";
                                            $resposta->return = false;
                                            return $resposta;
                                        } else {
                                            if ($form->endereco == "") {
                                                $resposta->type = "validation";
                                                $resposta->message = "Preencha este campo corretamente";
                                                $resposta->field = "endereco";
                                                $resposta->return = false;
                                                return $resposta;
                                            } else {
                                                if ($form->bairro == "") {
                                                    $resposta->type = "validation";
                                                    $resposta->message = "Preencha este campo corretamente";
                                                    $resposta->field = "bairro";
                                                    $resposta->return = false;
                                                    return $resposta;
                                                } else {
                                                    if ($form->id_estado == "") {
                                                        $resposta->type = "validation";
                                                        $resposta->message = "Preencha este campo corretamente";
                                                        $resposta->field = "id_estado";
                                                        $resposta->return = false;
                                                        return $resposta;
                                                    } else {
                                                        if ($form->id_cidade == "") {
                                                            $resposta->type = "validation";
                                                            $resposta->message = "Preencha este campo corretamente";
                                                            $resposta->field = "id_cidade";
                                                            $resposta->return = false;
                                                            return $resposta;
                                                        } else {
                                                            if (!isset($form->frete) || $form->frete == "") {
                                                                $resposta->type = "attention";
                                                                $resposta->message = "Selecione o método de envio [FRETE]";
                                                                $resposta->field = "frete";
                                                                $resposta->return = false;
                                                                return $resposta;
                                                            } else {
                                                                if ($form->frete_nome == "") {
                                                                    $resposta->type = "attention";
                                                                    $resposta->message = "Selecione o método de envio [FRETE]";
                                                                    $resposta->field = "frete_nome";
                                                                    $resposta->return = false;
                                                                    return $resposta;
                                                                } else {
                                                                    if ($form->metodo_pagamento == "") {
                                                                        $resposta->type = "validation";
                                                                        $resposta->message = "Selecione um método de pagamento";
                                                                        $resposta->field = "metodo_pagamento_cli";
                                                                        $resposta->return = false;
                                                                        return $resposta;
                                                                    } else {
                                                                        return $resposta;
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    //--------------- Funções para cálculo opções de frete ------------------//

    private function freteAction()
    {
        $id = $_POST['id_orcamento'] ?? 0;
        $cep = preg_replace('/\D/', '', $_POST['cep'] ?? '00000000');
        $result = $this->freteOptions($id, $cep);
        if (!$id || !$result) {
            $result = '<div><p>Não foi possível carregar as opções de frete:</p><p>Orçamento ou CEP inválido</p></div>';
        }
        echo $result;
    }

    private function freteOptions($id, $cep)
    {
        $frete = new Frete();
        $cotacao_manual = 0;

        $locais = $this->DB_fetch_array("SELECT id_estado, id_cidade FROM (
            (SELECT A.id id_estado, NULL id_cidade FROM tb_config_estados A WHERE A.cep_init <= '$cep' AND A.cep_end >= '$cep')
            UNION
            (SELECT A.id_estado, A.id id_cidade FROM tb_config_cidades A WHERE A.cep_init <= '$cep' AND A.cep_end >= '$cep')
        ) TAB  ORDER BY id_cidade DESC");
        if (!$locais->num_rows) {
            return false;
        }

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
        if ($nao_negar_aereo) {
            $result['negar_aereo'] = false;
        }

        $nao_negar_terrestre = $this->naoNegarTerrestre($id, $cidades, $estados);
        if ($nao_negar_terrestre) {
            $result['negar_terrestre'] = false;
        }

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
                if ($pBase == $pa['id']) {
                    $pa['quantidade'] = $pa['quantidade'] - 1;
                }
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
                    $a[] = $frete->getInfoFrete($aereo['id_frete_aereo'], $estados, $cidades,
                        $aereo['frete_embutido'], $aereo['quantidade']);
                    $produtoAereo = $frete->entregaAereo($aereo['id_frete_aereo'], $estados, $cidades);
                } else {
                    $produtoAereo = false;
                }
            }
            foreach ($a as $a) {
                if($a['cotacao_manual']) $cotacao_manual = 1;
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
                    $a[] = $frete->getInfoFrete($terrestre['id_frete_terrestre'], $estados, $cidades,
                        $terrestre['frete_embutido'], $terrestre['quantidade']);
                }
            }

            foreach ($a as $a) {
                $preco_terrestre = $preco_terrestre + $a['preco'];
                if($a['cotacao_manual']) $cotacao_manual = 1;
                if ($a['prazo'] > $prazo_terrestre) {
                    $prazo_terrestre = $a['prazo'];
                }
            }
        }

        $pPrazoTerrestre = $pPadraoNewCep + $prazo_terrestre;
        $pPrecoTerrestre = $preco_terrestre;

        $produtos_no_carrinho = $this->DB_fetch_array("SELECT * FROM tb_carrinho_produtos_historico A WHERE A.id_pedido = $id");

        $html = '';
        if($cotacao_manual){

            $html .= "
            <div class='option-frete'>
                <label for='frete-terrestre'>
                    <input data-prazo='$pPrazoTerrestre' data-nome='A Consultar' type='radio' name='frete' id='frete-consultar' value='0'> 
                    <span> Á Consultar</span>
                </label>
                <div>Frete à consultar, pagamento realizado separadamente</div>
            </div>";

        } else {
            if ($produtos_no_carrinho->num_rows) {
                if (!$result['negar_terrestre']) {
                    if ($pPrecoTerrestre > 0) {
                        $valorDescrito = "R$ " . $this->formataMoedaShow($pPrecoTerrestre);
                    } else {
                        $valorDescrito = 'Frete Grátis';
                    }

                    $html .= "<div class='option-frete'><label for='frete-terrestre'><input data-prazo='$pPrazoTerrestre' data-nome='Terrestre' type='radio' name='frete' id='frete-terrestre' value='$pPrecoTerrestre'> <span>Frete Terrestre</span></label> <div class='valor'>Valor do frete: <span>{$valorDescrito}</span></div> <div class='prazo'>Prazo para entregar: <span>$pPrazoTerrestre dias úteis</span></div></div>";
                } else {
                    if ($produtos_no_carrinho->num_rows) {
                        $html .= "<div class='option-frete'><label><span>Frete Terrestre </span><div>Não entregamos no CEP informado!</div></label></div>";
                    }
                }

                if (!$result['negar_aereo'] && $produtoAereo) {
                    if ($pPrecoAereo > 0) {
                        $valorDescrito = "R$ " . $this->formataMoedaShow($pPrecoAereo);
                    } else {
                        $valorDescrito = 'Frete Grátis';
                    }

                    $html .= "<div class='option-frete'><label for='frete-aereo'><input data-prazo='$pPrazoAereo' data-nome='Aéreo' type='radio' name='frete' id='frete-aereo' value='$pPrecoAereo'> <span>Frete Aéreo</span></label> <div class='valor'>Valor do frete: <span>$valorDescrito</span></div> <div class='prazo'>Prazo para entregar: <span>$pPrazoAereo dias úteis</span></div></div>";
                } else {
                    $html .= "<div class='option-frete'><label><span>Frete Aéreo </span><div>Não entregamos no CEP informado!</div></label></div>";
                }
            }
        }
        return $html;
    }

    public function negarAereo($id)
    {
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

    public function negarTerrestre($id)
    {
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

    private function naoNegarAereo($id, $cidades, $estados = null)
    {
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

    private function naoNegarTerrestre($id, $cidades, $estados = null)
    {
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

    public function setModule($module)
    {
        $this->module = $module;
    }

    public function getModule()
    {
        return strtolower($this->module);
    }

    public static function setAction()
    {
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
        } else {
            if ($newAction == "Action") {
                $instance->setModule($class);
                if (method_exists($instance, 'indexAction')) {
                    $instance->indexAction();
                } else {
                    $sistema->renderView($instance->getModule(), "404");
                }
            } else {
                $sistema->renderView($instance->getModule(), "404");
            }
        }
    }
}
