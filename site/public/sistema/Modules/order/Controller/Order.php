<?php

use System\Core\Bootstrap;
use classes\Product;
use System\Libs\ContaAzul;
use System\Libs\IpTables;
use System\Libs\BlingV3;

Order::setAction();

class Order extends Bootstrap {

    public $module = "";
    public $permissao_ref = "pedidos-pedidos";
    public $table = "tb_pedidos_pedidos";
    public $table_remessas = "dp_remessas";
    public $table_ordens = "dp_ordens";
    public $table_requisitos = "dp_requisitos";
    public $table_dependencias = "dp_dependencias";

    function __construct() {
        parent::__construct();

        //hackzinho para quando faz busca pela datatable não bloquear
            if($this->getParameter(strtolower("order")) == "verificasessao"){
                if (!isset($_SESSION['admin_logado'])) {
                    echo "login";
                }
                $_SESSION['firsturl'] = $this->system_path.'order';
                exit();
            }


        if (!isset($_SESSION['admin_logado'])) {
            header("Location: " . $this->system_path . "login");
        }
        $this->getAllPermissions();

        //estava tendo bastante incidência de bloqueio de ip a partir do módulo dos pedidos. por isso estou definido qualquer ip como whitelist aqui.
        if(!isset($_SESSION['ip_whitelisted']) || $_SESSION['ip_whitelisted'] != $_SERVER['REMOTE_ADDR']){
            $ip = new IpTables($this);
            $ip->whitelist($_SERVER['REMOTE_ADDR']);
        }
        
        $this->arquivo = "";

        $this->module_icon = "icomoon-icon-cart-4";
        $this->module_link = "order";
        $this->module_title = "Pedidos";
        $this->retorno = "order";

        $this->product = new Product();

        $this->crop_sizes = array();
        array_push($this->crop_sizes, array("width" => 400, "height" => 1000, "best_fit" => true));
    }

    public function paymentType($string) {
        switch ($string) {
            case 'deposito':
                return 'Depósito';
                break;

            case 'boleto':
                return 'Boleto';
                break;

            case 'cielo':
                return 'Cielo';
                break;

            case 'pagseguro':
                return 'Pagseguro';
                break;

            case 'pokerstars':
                return 'Pokerstars';
                break;

            case 'rede_transparente':
                return 'eRede';
                break;

            case 'cielo_transparente':
                return 'Cielo Transparente';
                break;

            default:
                break;
        }
    }

    public function cieloBrands($int) {
        switch ($int) {
            case 1:
                return 'Visa';
                break;

            case 2:
                return 'Mastercard';
                break;

            case 3:
                return 'AmericanExpress';
                break;

            case 4:
                return 'Diners';
                break;

            case 5:
                return 'Elo';
                break;

            case 6:
                return 'Aura';
                break;

            case 7:
                return 'JCB';
                break;

            default:
                break;
        }
    }

    private function indexAction() {
        if (!$this->permissions[$this->permissao_ref]['ler']) {
            $this->noPermission();
        }
        if($_SESSION['admin_grupo'] == 2 || $_SESSION['admin_grupo'] == 3){
            //$this->list = $this->DB_fetch_array("SELECT A.*, A.data registro, DATE_FORMAT(A.data, '%d/%m/%Y às %H:%i') data_compra, DATE_FORMAT(A.prazo_entrega, '%d/%m/%Y') prazo_entrega, B.nome status, B.cor, IF(C.pessoa = 1, C.nome, C.razao_social) cliente, C.cpf, C.telefone, C.email, E.cidade, F.estado, G.nome vendedor, H.payment_method_brand, I.nome editando FROM $this->table A INNER JOIN tb_pedidos_status B ON B.id = A.id_status INNER JOIN tb_clientes_clientes C ON C.id = A.id_cliente INNER JOIN tb_pedidos_enderecos D ON D.id_pedido = A.id INNER JOIN tb_utils_cidades E ON E.id = D.id_cidade INNER JOIN tb_utils_estados F ON F.id = E.id_estado LEFT JOIN tb_admin_users G ON A.id_vendedor = G.id LEFT JOIN tb_pedidos_transacoes_cielo H ON H.checkout_cielo_order_number = A.metodo_pagamento_id LEFT JOIN tb_admin_users I ON A.usuario_editando_pedido = I.id  ORDER BY A.data DESC");
        }else{
            //SOMENTE OS PEDIDOS VINCULADOS AO VENDEDOR
            //$this->list = $this->DB_fetch_array("SELECT A.*, A.data registro, DATE_FORMAT(A.data, '%d/%m/%Y às %H:%i') data_compra, DATE_FORMAT(A.prazo_entrega, '%d/%m/%Y') prazo_entrega, B.nome status, B.cor, IF(C.pessoa = 1, C.nome, C.razao_social) cliente, C.cpf, C.telefone, C.email, E.cidade, F.estado, G.nome vendedor, H.payment_method_brand, I.nome editando FROM $this->table A INNER JOIN tb_pedidos_status B ON B.id = A.id_status INNER JOIN tb_clientes_clientes C ON C.id = A.id_cliente INNER JOIN tb_pedidos_enderecos D ON D.id_pedido = A.id INNER JOIN tb_utils_cidades E ON E.id = D.id_cidade INNER JOIN tb_utils_estados F ON F.id = E.id_estado LEFT JOIN tb_admin_users G ON A.id_vendedor = G.id LEFT JOIN tb_pedidos_transacoes_cielo H ON H.checkout_cielo_order_number = A.metodo_pagamento_id LEFT JOIN tb_admin_users I ON A.usuario_editando_pedido = I.id WHERE A.id_vendedor = {$_SESSION['admin_id']} ORDER BY A.data DESC");
        }

        //$this->last_order_id = $this->DB_fetch_array("SELECT MAX(id) id FROM $this->table");
        //$this->last_order_id = $this->last_order_id->rows[0]["id"];

        $this->renderView($this->getModule(), "index");

        //$this->updateCampanhasConversoes(4099);
    }

    private function index2Action() {
        if (!$this->permissions[$this->permissao_ref]['ler']) {
            $this->noPermission();
        }
        if($_SESSION['admin_grupo'] == 2 || $_SESSION['admin_grupo'] == 3){
            $this->list = $this->DB_fetch_array("SELECT A.*, A.data registro, DATE_FORMAT(A.data, '%d/%m/%Y às %H:%i') data_compra, DATE_FORMAT(A.prazo_entrega, '%d/%m/%Y') prazo_entrega, B.nome status, B.cor, IF(C.pessoa = 1, C.nome, C.razao_social) cliente, C.cpf, C.telefone, C.email, E.cidade, F.estado, G.nome vendedor, H.payment_method_brand, I.nome editando FROM $this->table A INNER JOIN tb_pedidos_status B ON B.id = A.id_status INNER JOIN tb_clientes_clientes C ON C.id = A.id_cliente INNER JOIN tb_pedidos_enderecos D ON D.id_pedido = A.id INNER JOIN tb_utils_cidades E ON E.id = D.id_cidade INNER JOIN tb_utils_estados F ON F.id = E.id_estado LEFT JOIN tb_admin_users G ON A.id_vendedor = G.id LEFT JOIN tb_pedidos_transacoes_cielo H ON H.checkout_cielo_order_number = A.metodo_pagamento_id LEFT JOIN tb_admin_users I ON A.usuario_editando_pedido = I.id  ORDER BY A.data DESC");
        }else{
            //SOMENTE OS PEDIDOS VINCULADOS AO VENDEDOR
            $this->list = $this->DB_fetch_array("SELECT A.*, A.data registro, DATE_FORMAT(A.data, '%d/%m/%Y às %H:%i') data_compra, DATE_FORMAT(A.prazo_entrega, '%d/%m/%Y') prazo_entrega, B.nome status, B.cor, IF(C.pessoa = 1, C.nome, C.razao_social) cliente, C.cpf, C.telefone, C.email, E.cidade, F.estado, G.nome vendedor, H.payment_method_brand, I.nome editando FROM $this->table A INNER JOIN tb_pedidos_status B ON B.id = A.id_status INNER JOIN tb_clientes_clientes C ON C.id = A.id_cliente INNER JOIN tb_pedidos_enderecos D ON D.id_pedido = A.id INNER JOIN tb_utils_cidades E ON E.id = D.id_cidade INNER JOIN tb_utils_estados F ON F.id = E.id_estado LEFT JOIN tb_admin_users G ON A.id_vendedor = G.id LEFT JOIN tb_pedidos_transacoes_cielo H ON H.checkout_cielo_order_number = A.metodo_pagamento_id LEFT JOIN tb_admin_users I ON A.usuario_editando_pedido = I.id WHERE A.id_vendedor = {$_SESSION['admin_id']} ORDER BY A.data DESC");
        }

        $this->renderView($this->getModule(), "index2");

        //$this->updateCampanhasConversoes(4099);
    }

    public function datatableAction() {
        if (!$this->permissions[$this->permissao_ref]['ler'])
            exit();

        //defina os campos da tabela
        $aColumns = array("A.id_cliente","A.metodo_pagamento","A.id","A.data registro","A.data_competencia","A.id","IF(G.nome IS NULL, 'sem vendedor', G.nome) vendedor", "DATE_FORMAT(A.data, '%d/%m/%Y às %H:%i') data_compra", "IF(C.pessoa = 1, C.nome, C.razao_social) cliente", "C.cpf", "C.email", "C.telefone", "E.cidade", "F.estado", "H.payment_method_brand", "B.nome status", "A.valor_final", "A.n_nota", "I.nome editando", "A.usuario_editando_ultimo_ping", "B.cor");


        //defina os campos que devem ser usados para a busca
        $aColumnsWhere = array("A.data","A.id","G.nome", "DATE_FORMAT(A.data, '%d/%m/%Y às %H:%i')", "IF(C.pessoa = 1, C.nome, C.razao_social)", "C.cpf", "C.email", "C.telefone", "E.cidade", "F.estado", "H.payment_method_brand", "A.valor_final", "A.n_nota", "I.nome", "B.nome", "G.nome, A.orc_status");

            // BUSCA ESPECÍFICA PARA ACHAR SOMENTE VENDENDOR CASO A BUSCA INICIE COM "V:"

            if($_POST['sSearch'] != "" && $_POST['sSearch'] > 1000 && $_POST['sSearch'] < 100000){
                // BUSCA ESPECÍFICA PARA ACHAR SOMENTE ID DO PEDIDO
                $aColumnsWhere = array("A.id");
            }else if($_POST['sSearch'] != "" && stripos($_POST['sSearch'], "v:") === 0){
                $_POST['sSearch'] = str_ireplace(array('v: ','v:'), '', $_POST['sSearch']);
                $aColumnsWhere = array("G.nome");
            }else if($_POST['sSearch'] != "" && stripos($_POST['sSearch'], "s:") === 0 ){
                // BUSCA ESPECÍFICA PARA ACHAR SOMENTE STATUS CASO A BUSCA INICIE COM "S:"
                $_POST['sSearch'] = str_ireplace(array('s: ','s:'), '', $_POST['sSearch']);
                $aColumnsWhere = array("B.nome");
            } else if($_POST['sSearch'] != "" && stripos($_POST['sSearch'], "c:") === 0 ){
                // BUSCA ESPECÍFICA PARA ACHAR SOMENTE CLIENTE CASO A BUSCA INICIE COM "C:"
                $_POST['sSearch'] = str_ireplace(array('c: ','c:'), '', $_POST['sSearch']);
                $aColumnsWhere = array("C.nome","C.razao_social");
            }


        //defina o coluna índice
        $sIndexColumn = "A.id";

        //defina o nome da tabela, ou faça aqui seu INNER JOIN, LEFT JOIN, RIGHT JOIN
        //Ex: "tb_emails_emails A INNER JOIN tb_admin_users B ON B.id=A.id"
        $sTable = "$this->table A INNER JOIN tb_pedidos_status B ON B.id = A.id_status INNER JOIN tb_clientes_clientes C ON C.id = A.id_cliente INNER JOIN tb_pedidos_enderecos D ON D.id_pedido = A.id INNER JOIN tb_utils_cidades E ON E.id = D.id_cidade INNER JOIN tb_utils_estados F ON F.id = E.id_estado LEFT JOIN tb_admin_users G ON A.id_vendedor = G.id LEFT JOIN tb_pedidos_transacoes_cielo H ON H.checkout_cielo_order_number = A.metodo_pagamento_id LEFT JOIN tb_admin_users I ON A.usuario_editando_pedido = I.id  ";

        //declarar condições extras
        //$sWhere = "WHERE A.id > ".$_POST['id_limit']." ";
        if($_POST['date_flag'])
            $sWhere = "";
        else
            $sWhere = "WHERE A.data > CURRENT_DATE - INTERVAL 120 DAY";

        // $sWhere = "WHERE (A.orc_status IS NULL OR A.orc_status = 'ganho')";
        // if(!$_POST['date_flag'])
        //     $sWhere .= " AND (A.data > CURRENT_DATE - INTERVAL 60 DAY)";

        if($_POST['sSearch'] == "sem vendedor"){
            if ($sWhere == "") $sWhere = "WHERE G.nome IS NULL";
            $sWhere .= " AND G.nome IS NULL";
            $_POST['sSearch'] = "";
        }

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

                if ($_POST['iSortCol_0'] == 0)
                    $campo = "A.data";
                else if ($_POST['iSortCol_0'] == 4)
                    $campo = "DATE_ADD(A.data, INTERVAL 5 MINUTE)";

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

        if(isset($_POST['id_cliente'])) {
            if($sWhere == "")
                $sWhere .= " where A.id_cliente = " . $_POST['id_cliente'];
            else
                $sWhere .= " and A.id_cliente = " . $_POST['id_cliente'];
        }

        $query = "SELECT SQL_CALC_FOUND_ROWS " . str_replace(" , ", " ", implode(", ", $aColumns)) . "
            FROM $sTable
            $sWhere GROUP BY A.id 
            $sOrder
            $sLimit";

        $rResult = array();
        $sQuery = $this->DB_fetch_array("SELECT SQL_CALC_FOUND_ROWS " . str_replace(" , ", " ", implode(", ", $aColumns)) . "
            FROM $sTable
            $sWhere GROUP BY A.id 
            $sOrder
            $sLimit");
        if ($sQuery->num_rows)
            $rResult = $sQuery->rows;

        $queryExport = "SELECT SQL_CALC_FOUND_ROWS " . str_replace(" , ", " ", implode(", ", $aColumns)) . "
            FROM $sTable
            $sWhere GROUP BY A.id 
            $sOrder
            $sLimit";

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
            foreach ($rResult as $aRow) {
                $row = array();


                //PEDIDO
                $data_array = explode(" ", $aColumns[0]);
                $data = end($data_array);
                $row[] = '<div align="left" style="padding-left:5px;border-left:7px solid '.$aRow['cor'].'"><a href="order/edit/id/'. $aRow['id'] .'">' . $aRow ['id'] . '</a></div>';

                //VENDEDOR
                $row[] = "<div align=left>" . $aRow ['vendedor'] . "</div>";

                //DATA
                $row[] = "<div align=left>" . $aRow ['data_compra'] . "</div>";

                //DATA COMPETÊNCIA
                $row[] = "<div align=left>" . $aRow ['data_competencia'] . "</div>";

                //CLIENTE
                $row[] = "<div align=left><a href='client/edit/id/".$aRow ['id_cliente']."' target='_blank'>" . $aRow ['cliente'] . "</a></div>";

                //TELEFONE
                $row[] = "<div align=left>" . $aRow ['telefone'] . "</div>";

                //CIDADE
                $row[] = "<div align=left>" . $aRow ['cidade'] . "</div>";

                //ESTADO
                $row[] = "<div align=left>" . $aRow ['estado'] . "</div>";

                //TIPO DE PAGAMENTO
                $metodo = "";
                if($aRow ['payment_method_brand'] != "") $metodo = " (".$this->cieloBrands($aRow ['payment_method_brand']).")";
                $row[] = "<div align=left>" . $this->paymentType($aRow ['metodo_pagamento']) .  $metodo . "</div>";

                //STATUS DO PEDIDO
                $row[] = "<div align=left style='padding-left:5px;border-left:7px solid ".$aRow['cor']."'>" . $aRow ['status'] . "</div>";

                //VALOR TOTAL
                $row[] = "<div align=left>" . $this->formataMoedaShow($aRow ['valor_final']) . "</div>";

                //EDITANDO

                 if($aRow['usuario_editando_ultimo_ping'] != '' AND (strtotime(date("Y-m-d H:i:s")) - strtotime($aRow['usuario_editando_ultimo_ping'])) < 60)
                    $editando= "<div align=left>" . $aRow ['editando'] . "</div>";
                    else
                        $editando= "<div align=left></div>";
                $row[] = $editando;


                //AÇÃO
                $excluir = "";
                if ($this->permissions[$this->permissao_ref]['excluir'])
                    $excluir = "<a class='bt_system_delete' data-controller='".$this->getModule()."' data-id='".$aRow['id']."' href='#'><i class='s12 icomoon-icon-remove'></i></a> <input type='checkbox' id='del_".$aRow['id']."' value='". $aRow['id']."' class='del-this'>";
                $row[] = '<div align="left"><a href="order/edit/id/'. $aRow['id'] .'/read/1"><i class="icon12 icomoon-icon-eye left"></i></a><a href="order/edit/id/'. $aRow['id'] .'"><i class="s12 icomoon-icon-pencil"></i></a> '.$excluir.'</div>';

                /*

                //CPF
                $row[] = "<div align=left>" . $aRow ['cpf'] . "</div>";

                //EMAIL
                $row[] = "<div align=left>" . $aRow ['email'] . "</div>";

                //NOTA FISCAL
                $row[] = "<div align=left>" . $aRow ['n_nota'] . "</div>";
                */

                $output['aaData'][] = $row;
            }
        }

        $output['queryExport'] = $queryExport;

        echo json_encode($output);
        //unset($_SESSION['admin_logado']);
        $_SESSION['firsturl'] = $this->system_path.'order';
    }

    public function getDeducoesByProduto($idPedido = null, $id = null) {
        if ($idPedido != null && $id != null) {
            $query = $this->DB_fetch_array("SELECT * FROM tb_pedidos_deducoes WHERE id_pedido = $idPedido AND id_produto_carrinho = $id ORDER BY descricao");
            return $query;
        }
    }

    private function saveDeducoesAction() {
        if (!$this->permissions[$this->permissao_ref]['editar'])
            exit();

        $query = new \stdClass;
        $query->query = true;

        $resposta = new \stdClass;

        $formulario = $this->formularioObjeto($_POST);
        $this->DB_delete("tb_pedidos_deducoes", "id_produto_carrinho = $formulario->id");

        if (isset($_POST['valores'])) {
            for ($i = 0; $i < count($_POST['valores']); $i++) {
                if ($_POST['valores'][$i] != "") {
                    $query = $this->DB_insert("tb_pedidos_deducoes", "id_pedido,id_produto_carrinho,descricao,valor", "'$formulario->id_pedido','$formulario->id','{$_POST['descricoes'][$i]}','{$this->formataMoedaBd($_POST['valores'][$i])}'");
                }
            }
        }

        if ($query->query) {
            $resposta->type = "success";
            $resposta->message = "Registros salvos com sucesso!";
            $this->inserirRelatorio("Alterou adição/dedução para cálculo de fábrica, id produto do carrinho: [" . $formulario->id . "] nº pedido: [$formulario->id_pedido]");
        } else {
            $resposta->type = "error";
            $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
        }

        echo json_encode($resposta);
    }

    private function getDeducaoTemplateAction() {
        if (!$this->permissions[$this->permissao_ref]['editar'])
            exit();
        $this->renderView($this->getModule(), "deducao");
    }

    private function editAction() {
        $this->id = $this->getParameter("id");
        $read = $this->getParameter("read");

        if ($this->id == "") {
            //new
            $this->noPermission();
        } else {
            //edit
            if (!$this->permissions[$this->permissao_ref]['editar'])
                $this->noPermission();

            try{
                $query = $this->DB_fetch_array("
                    SELECT 
                        A.*, 
                        A.data registro, 
                        DATE_FORMAT(A.data, '%d/%m/%Y às %H:%i') data_compra, 
                        DATE_FORMAT(A.data_competencia, '%d/%m/%Y às %H:%i') data_competencia, 
                        DATE_FORMAT(A.prazo_entrega, '%d/%m/%Y') prazo_entrega, 
                        B.nome status, 
                        C.nome cliente, 
                        C.telefone cliente_telefone, 
                        C.pessoa, 
                        C.razao_social, 
                        C.cpf as cliente_cpf, 
                        C.cnpj as cliente_cnpj, 
                        C.id_estado as cliente_estado, 
                        C.id_cidade as cliente_cidade, 
                        C.cep, 
                        C.endereco, 
                        C.numero, 
                        C.bairro, 
                        C.complemento, 
                        D.nome_fantasia,
                        D.cnpj,
                        E.nome_fantasia as empresa_faturado 
                            FROM $this->table A 
                            INNER JOIN tb_pedidos_status B ON B.id = A.id_status 
                            INNER JOIN tb_clientes_clientes C ON C.id = A.id_cliente 
                            LEFT JOIN tb_admin_empresas D ON D.id = A.id_empresa 
                            LEFT JOIN tb_admin_empresas E ON E.id = A.id_empresa_faturado
                    WHERE A.id = $this->id", "form");
            }
            catch (\Exception $e){
                $query = $this->DB_fetch_array("SELECT A.*, A.data registro, DATE_FORMAT(A.data, '%d/%m/%Y às %H:%i') data_compra, DATE_FORMAT(A.data_competencia, '%d/%m/%Y às %H:%i') data_competencia, DATE_FORMAT(A.prazo_entrega, '%d/%m/%Y') prazo_entrega, B.nome status, C.nome cliente, C.telefone cliente_telefone, C.pessoa, C.razao_social, D.nome_fantasia, D.cnpj FROM $this->table A INNER JOIN tb_pedidos_status B ON B.id = A.id_status INNER JOIN tb_clientes_clientes C ON C.id = A.id_cliente LEFT JOIN tb_admin_empresas D ON D.id = A.id_empresa WHERE A.id = $this->id", "form");
            }

            $this->registro = $query->rows[0];

            $this->documentos = $this->DB_fetch_array("SELECT * FROM tb_pedidos_documentos WHERE id_pedido = ".$this->id." ORDER BY id DESC");
            if($this->documentos->num_rows){
                foreach ($this->documentos->rows as $value) {
                    if($value['default']==1){
                        $this->documentos_padrao = $value;
                    }
                }
            }

            $this->rastreios = $this->DB_fetch_array("SELECT * FROM tb_pedidos_rastreios A INNER JOIN tb_pedidos_has_tb_rastreios B ON B.id_rastreio = A.id WHERE B.id_pedido = {$this->registro['id']} ORDER BY A.data DESC");

            $this->historicos = $this->DB_fetch_array("SELECT *, DATE_FORMAT(data, '%d/%m/%Y às %H:%i') registro FROM tb_pedidos_historicos WHERE id_pedido = {$this->registro['id']} ORDER BY data DESC");

            $this->historico_emails = $this->DB_fetch_array("SELECT *, DATE_FORMAT(data, '%d/%m/%Y %H:%i') registro, data data FROM tb_pedidos_emails_historicos WHERE id_pedido = '{$this->registro['id']}' ORDER BY data DESC");

            $this->historicosnav = $this->DB_fetch_array("SELECT DATE_FORMAT(B.date, '%d/%m/%Y às %H:%i:%s') registro, B.date, C.seo_title titulo, B.origem, CONCAT(B.cidade, ', ', B.estado, ' - ', B.pais) localizacao, B.pais, B.estado, B.cidade, B.dispositivo, B.ip, B.session FROM tb_pedidos_pedidos A LEFT JOIN tb_carrinho_produtos_historico H ON H.id_pedido = A.id LEFT JOIN tb_seo_acessos_historicos B ON B.session = H.session INNER JOIN tb_seo_paginas C ON C.id = B.id_seo WHERE H.session = '{$this->registro['session']}' ORDER BY B.date");

            $this->usuarios = $this->DB_fetch_array("SELECT A.* FROM tb_admin_users A INNER JOIN tb_admin_grupos B ON A.id_grupo = B.id WHERE A.stats = 1 AND B.stats = 1 AND B.id = 3");

            $this->avaliacoes = $this->DB_fetch_array("SELECT * FROM tb_produtos_avaliacoes WHERE id_pedido = ".$this->id);

            $this->extrato_financeiro = $this->DB_fetch_array("SELECT categoria, SUM(total_frete_embutido) total_frete, SUM(total_sem_frete) total_sem_frete FROM
                (SELECT *, (total_pago - total_frete_embutido) total_sem_frete FROM
                (SELECT *, ((valor_produto*quantidade) - (desconto*quantidade)) total_pago, (frete_embutido*quantidade) total_frete_embutido FROM 
                (SELECT a.id_produto, c.id id_categoria, c.nome categoria, a.custo, a.valor_produto, a.quantidade, COALESCE(a.desconto, 0) desconto, COALESCE(d.frete_embutido, 0) frete_embutido 
                FROM tb_carrinho_produtos_historico a 
                INNER JOIN tb_produtos_produtos_has_tb_produtos_categorias b ON a.id_produto = b.id_produto 
                INNER JOIN tb_produtos_categorias c ON b.id_categoria = c.id
                INNER JOIN tb_produtos_produtos d ON a.id_produto = d.id  
                WHERE id_pedido = {$this->id}) select1) select2) select3
                GROUP BY id_categoria");

            $this->extrato_financeiro2 = $this->extratoFinanceiro($this->id);

            $this->contas_bling = $this->DB_fetch_array("SELECT A.id, A.nome_fantasia FROM tb_admin_empresas A WHERE A.invoice_bling_autorization_code IS NOT NULL AND A.invoice_bling_autorization_code != ''");

            //Produção
            $this->remessas_ativas = $this->DB_fetch_array("SELECT * FROM dp_remessas WHERE id_status != 4;")->rows;

            $this->ordens_producao = $this->DB_fetch_array("SELECT 
                A.*, B.titulo as titulo_remessa, C.titulo as titulo_status
                FROM dp_ordens A
                LEFT JOIN dp_remessas B ON A.id_remessa = B.id
                LEFT JOIN dp_status C ON A.id_status = C.id
            WHERE A.id_pedido = ".$this->id);

            if($this->ordens_producao->num_rows){
                $titulosRemessa = array_unique(array_column($this->ordens_producao->rows, 'titulo_remessa'));
                $titulosRemessaEscapados = array_map(function($titulo) {
                    return "'" . addslashes($titulo) . "'";
                }, $titulosRemessa);
                $titulosRemessaString = implode(',', $titulosRemessaEscapados);
    
                $this->remessasDessePedido = $this->DB_fetch_array("SELECT 
                        A.*, B.estado, C.cidade,
                        DATE_FORMAT(A.entrega, '%d/%m/%Y') AS entrega_formatada,
                        DATE_FORMAT(A.saida, '%d/%m/%Y') AS saida_formatada
                    FROM dp_remessas A
                        LEFT JOIN tb_utils_estados B ON A.id_estado = B.id
                        LEFT JOIN tb_utils_cidades C ON A.id_cidade = C.id
                    WHERE titulo IN ($titulosRemessaString)")->rows;
    
                foreach($this->remessasDessePedido as $key => $remessa){
                    $ordensDaRemessa = $this->DB_fetch_array("SELECT * FROM dp_ordens WHERE id_remessa = ".$remessa['id'])->rows;
                    $this->remessasDessePedido[$key]['ordens'] = $ordensDaRemessa;
                }

                $this->data_producao = new \DateTime($this->ordens_producao->rows[0]['created_at']);
                $this->data_producao = $this->data_producao->format('d/m/Y');

                $nomes_remessas = $this->DB_fetch_array("SELECT 
                    A.titulo
                FROM dp_remessas A WHERE A.titulo LIKE '".$this->id."-%'");

                if($nomes_remessas->num_rows){
                    $codigoDisponivel = '';
                    foreach($nomes_remessas->rows as $remessa){
                        if($codigoDisponivel == ''){
                            $codigoDisponivel = $remessa['titulo'];
                        } else if($remessa['titulo'] > $codigoDisponivel){
                            $codigoDisponivel = $remessa['titulo'];
                        }
                    }
    
                    list($parte1, $parte2) = explode('-', $codigoDisponivel);
                    $parte2 = (int)$parte2 + 1; 
                    $this->nome_remessa_disponivel = $parte1 . '-' . $parte2;
                } else {
                    $this->nome_remessa_disponivel = $this->id.'-1';
                }
            }
            

            // SE NÃO EXISTE NINGUEM EDITANDO NO MOMENTO CONCEDER EDIÇÃO PARA ESTE USUÁRIO
            if($this->registro['usuario_editando_pedido'] == "" || $this->registro['usuario_editando_pedido'] == 0){
                $this->concederEdicao($this->registro['id']);
                $this->editor_permission = 1;
                $this->editor_name = $_SESSION['admin_nome'];
            }else{

                $this->editor_name = $this->DB_fetch_array("SELECT * FROM tb_admin_users WHERE id = ".$this->registro['usuario_editando_pedido']);
                $this->editor_name = $this->editor_name->rows[0]['nome'];

                //VERIFICA SE O ÚLTIMO PING É MAIOR QUE 1 MINUTO
                    $last_ping = strtotime($this->registro['usuario_editando_ultimo_ping']);
                    $now = strtotime(date("Y-m-d H:i:s"));
                    $interval = abs($now - $last_ping);
                    $difenca = round($interval / 60);

                    if($difenca > 1){
                        //SE FOR MAIOR QUE 1 MINUTO, CONCEDER NOVA EDIÇÃO PARA ESTE USUÁRIO
                            $this->concederEdicao($this->registro['id']);
                            $this->editor_permission = 1;
                            $this->editor_name = $_SESSION['admin_nome'];
                    }else if($_SESSION['admin_id'] == $this->registro['usuario_editando_pedido']){
                        //SE O USUÁRIO FOR O MESMO, CONCEDER NOVA EDIÇÃO PARA ELE
                            $this->concederEdicao($this->registro['id']);
                            $this->editor_permission = 1;
                    }else{
                        $this->editor_permission = 0;
                    }
            }

        }

        if($read==1){
            $this->editor_permission = 0;
            $this->read = 1;
        }


        $this->status = $this->DB_fetch_array("SELECT * FROM tb_pedidos_status ORDER BY ordem");

        $this->estados = $this->DB_fetch_array("SELECT * FROM tb_utils_estados ORDER BY estado");

        $this->renderView($this->getModule(), "edit");
    }

    private function cidadesAction() {
        if (!$this->permissions[$this->permissao_ref]['ler'])
            $this->noPermission();

        $query = $this->DB_fetch_array("SELECT * FROM tb_utils_cidades WHERE id_estado = {$_GET['id']}");
        if ($query->num_rows) {
            foreach ($query->rows as $row) {
                if (isset($_GET['cidade']) && $_GET['cidade'] == $row['id'])
                    echo '<option selected data-cidade="' . $row['cidade'] . '" value="' . $row['id'] . '">' . $row['cidade'] . '</option>';
                else
                    echo '<option data-cidade="' . $row['cidade'] . '" value="' . $row['id'] . '">' . $row['cidade'] . '</option>';
            }
        }
    }

    private function extratoFinanceiro($id) {
        return $this->DB_fetch_array("SELECT *, (total_pago - total_frete_embutido) total_sem_frete FROM
                (SELECT *, ((valor_produto*quantidade) - (desconto*quantidade)) total_pago, (frete_embutido*quantidade) total_frete_embutido, (valor_produto-desconto-frete_embutido) valor_unitario_sem_frete FROM 
                (SELECT d.nome, a.id_produto, c.id id_categoria, c.nome categoria, a.custo, a.valor_produto, a.valor_editado, a.quantidade, COALESCE(a.desconto, 0) desconto, COALESCE(d.frete_embutido, 0) frete_embutido, d.contaazul_id
                FROM tb_carrinho_produtos_historico a 
                INNER JOIN tb_produtos_produtos_has_tb_produtos_categorias b ON a.id_produto = b.id_produto 
                INNER JOIN tb_produtos_categorias c ON b.id_categoria = c.id
                INNER JOIN tb_produtos_produtos d ON a.id_produto = d.id  
                WHERE id_pedido = {$id}) select1) select2");
    }

    private function orderAction() {
        if (!$this->permissions[$this->permissao_ref]['excluir'])
            exit();
        $this->ordenarRegistros($_POST["array"], $this->table);
    }

    private function delAction() {
        if (!$this->permissions[$this->permissao_ref]['excluir'])
            exit();

        $id = $this->getParameter("id");

        $dados = $this->DB_fetch_array("SELECT * FROM $this->table WHERE id = $id");

        $fotos = $this->DB_fetch_array("SELECT C.id_carrinho_produto_historico, C.id_atributo, C.arquivo FROM tb_pedidos_pedidos A INNER JOIN tb_carrinho_produtos_historico B ON B.id_pedido = A.id INNER JOIN tb_carrinho_atributos_historico C ON C.id_carrinho_produto_historico = B.id WHERE A.id = $id");
        if ($fotos->num_rows) {
            foreach ($fotos->rows as $foto) {
                if ($foto['arquivo'])
                    $this->deleteFile('tb_carrinho_atributos_historico', "arquivo", "id_carrinho_produto_historico = {$foto['id_carrinho_produto_historico']} AND id_atributo = {$foto['id_atributo']}");
            }
        }


        $this->inserirRelatorio("Apagou pedido id: [$id] cliente id [{$dados->rows[0]['id_cliente']}");
        $this->DB_delete('tb_carrinho_produtos_historico', "id_pedido = $id");
        $this->DB_delete($this->table, "id=$id");

        echo $this->getModule();
    }

    private function saveAction() {
        $formulario = $this->formularioObjeto($_POST);
        $validacao = $this->validaFormulario($formulario);
        if (!$validacao->return) {
            echo json_encode($validacao);
        } else {
            $resposta = new \stdClass();

            //$data = $this->formularioObjeto($_POST, $this->table);

            $data = new \stdClass();

            if ($formulario->id == "") {
                //criar
                $this->noPermission(true);
            } else {
                //alterar
                if (!$this->permissions[$this->permissao_ref]['editar'])
                    exit();

                $data->data_competencia = $this->formataDataDeMascara($_POST['data_competencia']);

                $data->prazo_entrega = $this->formataDataDeMascara($_POST['prazo_entrega']);
                if($_POST['tipo_cliente'] != '') $data->tipo_cliente = $_POST['tipo_cliente'];

                $data->entrega_transportadora = $_POST['entrega_transportadora'];
                $data->entrega_cotacao = $_POST['entrega_cotacao'];
                $data->entrega_coleta = $_POST['entrega_coleta'];
                $data->entrega_valor = $_POST['entrega_valor'];
                $data->entrega_dia_coleta = $_POST['entrega_dia_coleta'];
                if(isset($_POST['observacoes_gerais'])) $data->observacoes_gerais = $_POST['observacoes_gerais'];
                $data->usuario_editando_pedido = 0;

                /*
                  $data->descontos = $this->formataMoedaBd($data->descontos);
                  $data->subtotal = $this->formataMoedaBd($data->subtotal);
                  $data->valor_final = $this->formataMoedaBd($data->valor_final);
                 *
                 */
                //VERIFICA SE TROCOU O VENDEDOR
                if(isset($_POST['id_vendedor']) && $_POST['id_vendedor'] != $_POST['id_vendedor_atual']){
                    if($_POST['agendor'] == 1 || $_POST['agendor'] == 2){
                        $data->agendor = 2; // AGENDOR SETOU VENDEDOR
                    }else{
                        $data->agendor = 0;
                    }
                    $data->id_vendedor = $_POST['id_vendedor'];
                    if($data->id_vendedor == ""){
                        $data->id_vendedor = 'null';

                    }
                }

                foreach ($data as $key => $value) {
                    if($value === 'null') {
                        $fields_values[] = "$key=null";
                    } else {
                        $fields_values[] = "$key='$value'";
                    }

                }
                $query = $this->DB_update($this->table, implode(',', $fields_values) . " WHERE id=" . $_POST['id']);
                if ($query) {
                    unset($fields_values);

                    $end = $this->formularioObjeto($_POST, "tb_pedidos_enderecos");

                    foreach ($end as $key => $value) {
                        $fields_values[] = "$key='$value'";
                    }

                    $query = $this->DB_update('tb_pedidos_enderecos', implode(',', $fields_values) . " WHERE id_pedido=" . $_POST['id']);

                    $this->DB_connect();
                    $this->mysqli->query("DELETE s.* FROM tb_pedidos_rastreios s
                        INNER JOIN tb_pedidos_has_tb_rastreios n ON s.id = n.id_rastreio
                        WHERE (n.id_pedido = {$_POST['id']})"
                    );

                    if (isset($_POST['link'])) {
                        for ($i = 0; $i < count($_POST['link']); $i++) {
                            if ($_POST['link'][$i] != "") {
                                $insert = $this->DB_insert("tb_pedidos_rastreios", "descricao,link,stats", "'{$_POST['descricao'][$i]}', '{$_POST['link'][$i]}', '{$_POST['rastreio_stats'][$i]}'");
                                if ($insert->query) {
                                    $this->DB_insert("tb_pedidos_has_tb_rastreios", "id_pedido,id_rastreio", "{$_POST['id']}, $insert->insert_id");
                                }
                            }
                        }
                    }

                    $this->alterarStatusPedido($_POST['id'], $_POST['id_status']);

                    $resposta->type = "success";
                    $resposta->message = "Registro alterado com sucesso!";
                    $this->inserirRelatorio("Alterou pedido: [" . $formulario->cliente . "] nº pedido: [{$_POST['id']}]");
                } else {
                    $resposta->type = "error";
                    $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
                }
            }

            echo json_encode($resposta);
        }
    }

    private function sendNotificationsAction() {
        if (!$this->permissions[$this->permissao_ref]['gravar'])
            exit();


        $to = array();
        $send = false;

        parse_str($_POST['data'], $data);

        if (!isset($data['alvo_notificacao'])) {
            echo json_encode(Array('status' => $send, 'message' => "Selecione Financeiro ou Fábrica!"));
            exit();
        }

        if (isset($data['email_notificacao'])) {

            if(isset($data['id_vendedor']) && $data['id_vendedor'] != '' && $data['id_vendedor'] != 0){
                $vendedor = $this->DB_fetch_array('SELECT * FROM tb_admin_users WHERE id = '.$data['id_vendedor']);
                if($vendedor->num_rows){
                    $setFrom = array(array('email'=>$vendedor->rows[0]['email'], 'nome'=>'Real Poker'));
                }
            }else{
                $setFrom = '';
            }

            for ($i = 0; $i < count($data['email_notificacao']); $i++) {

                if ($data['email_notificacao'][$i] != "" && isset($data['alvo_notificacao'][$i][0]) && $data['alvo_notificacao'][$i][0] == "financeiro") {
                    $assunto_financeiro = "Pedido n.: {$data['id']} - Cliente: {$data['cliente']}" . " - Financeiro - " . $this->_empresa['nome'];


                    $pdts = "";
                    if (isset($data['produtos'][$i])) {
                        $separador = "";
                        for ($f = 0; $f < count($data['produtos'][$i]); $f++) {
                            $pdts .= $separador . $data['produtos'][$i][$f];
                            $separador = ",";
                        }
                    }

                    $link = $this->site_url . "script/pedido/pdf/?pedido=" . $this->embaralhar($data['codigo'] . "|" . $pdts);


                    $mensagem_financeiro = "Olá, [{NOME}].<br><br>Acesse o pedido clicando <a target='_blank' href='$link'>aqui</a>!<br><br>Financeiro";

                    $this->DB_insert('tb_pedidos_emails_historicos', 'id_pedido,alvo,nome,email,link,usuario', "
                        '{$data['id']}',
                        'financeiro',
                        '{$data['nome_notificacao'][$i]}',
                        '{$data['email_notificacao'][$i]}',
                        '$link',
                        '{$_SESSION['admin_id']} - {$_SESSION['admin_nome']}'
                        ");



                    $to[] = array("email" => $data['email_notificacao'][$i], "nome" => utf8_decode($data['nome_notificacao'][$i]));

                    $assunto = $assunto_financeiro;

                    $body = $mensagem_financeiro;


                    $body = str_replace("[{NOME}]", $data['nome_notificacao'][$i], $body);

                    $send = $this->enviarEmail($to, $setFrom, utf8_decode($assunto), utf8_decode($body));

                    unset($to);
                }

                if ($data['email_notificacao'][$i] != "" && isset($data['alvo_notificacao'][$i][0]) && $data['alvo_notificacao'][$i][0] == "fabrica") {
                    $assunto_fabrica = "Pedido n.: {$data['id']} - Cliente: {$data['cliente']}" . " - Fábrica - " . $this->_empresa['nome'];

                    $pdts = "";
                    if (isset($data['produtos'][$i])) {
                        $separador = "";
                        for ($f = 0; $f < count($data['produtos'][$i]); $f++) {
                            $pdts .= $separador . $data['produtos'][$i][$f];
                            $separador = ",";
                        }
                    }

                    $link = $this->site_url . "script/pedido/pdf/?pedido=" . $this->embaralhar($data['codigo'] . "|" . $pdts . "|2");
                    $carta = $this->site_url . "/carta?p=" . md5($data['id']);

                    $mensagem_fabrica = "Olá, [{NOME}].<br><br>Acesse o pedido clicando <a target='_blank' href='$link'>aqui</a>!<br><br>Clique <a href='$carta'>aqui</a> para imprimir a carta do cliente<br><br>Fábrica";

                    $this->DB_insert('tb_pedidos_emails_historicos', 'id_pedido,alvo,nome,email,link,usuario', "
                        '{$data['id']}',
                        'fábrica',
                        '{$data['nome_notificacao'][$i]}',
                        '{$data['email_notificacao'][$i]}',
                        '$link',
                        '{$_SESSION['admin_id']} - {$_SESSION['admin_nome']}'
                        ");


                    $to[] = array("email" => $data['email_notificacao'][$i], "nome" => utf8_decode($data['nome_notificacao'][$i]));

                    $assunto = $assunto_fabrica;

                    $body = $mensagem_fabrica;


                    $body = str_replace("[{NOME}]", $data['nome_notificacao'][$i], $body);

                    $send = $this->enviarEmail($to, $setFrom, utf8_decode($assunto), utf8_decode($body));

                    unset($to);
                }

                if ($data['email_notificacao'][$i] != "" && isset($data['alvo_notificacao'][$i][1]) && $data['alvo_notificacao'][$i][1] == "financeiro") {
                    $assunto_financeiro = "Pedido n.: {$data['id']} - Cliente: {$data['cliente']}" . " - Financeiro - " . $this->_empresa['nome'];


                    $pdts = "";
                    if (isset($data['produtos'][$i])) {
                        $separador = "";
                        for ($f = 0; $f < count($data['produtos'][$i]); $f++) {
                            $pdts .= $separador . $data['produtos'][$i][$f];
                            $separador = ",";
                        }
                    }

                    $link = $this->site_url . "script/pedido/pdf/?pedido=" . $this->embaralhar($data['codigo'] . "|" . $pdts);


                    $mensagem_financeiro = "Olá, [{NOME}].<br><br>Acesse o pedido clicando <a target='_blank' href='$link'>aqui</a>!<br><br>Financeiro";

                    $this->DB_insert('tb_pedidos_emails_historicos', 'id_pedido,alvo,nome,email,link,usuario', "
                        '{$data['id']}',
                        'financeiro',
                        '{$data['nome_notificacao'][$i]}',
                        '{$data['email_notificacao'][$i]}',
                        '$link',
                        '{$_SESSION['admin_id']} - {$_SESSION['admin_nome']}'
                        ");


                    $to[] = array("email" => $data['email_notificacao'][$i], "nome" => utf8_decode($data['nome_notificacao'][$i]));

                    $assunto = $assunto_financeiro;

                    $body = $mensagem_financeiro;


                    $body = str_replace("[{NOME}]", $data['nome_notificacao'][$i], $body);

                    $send = $this->enviarEmail($to, $setFrom, utf8_decode($assunto), utf8_decode($body));

                    unset($to);
                }

                if ($data['email_notificacao'][$i] != "" && isset($data['alvo_notificacao'][$i][1]) && $data['alvo_notificacao'][$i][1] == "fabrica") {
                    $assunto_fabrica = "Pedido n.: {$data['id']} - Cliente: {$data['cliente']}" . " - Fábrica - " . $this->_empresa['nome'];

                    $pdts = "";
                    if (isset($data['produtos'][$i])) {
                        $separador = "";
                        for ($f = 0; $f < count($data['produtos'][$i]); $f++) {
                            $pdts .= $separador . $data['produtos'][$i][$f];
                            $separador = ",";
                        }
                    }

                    $link = $this->site_url . "script/pedido/pdf/?pedido=" . $this->embaralhar($data['codigo'] . "|" . $pdts . "|2");
                    $carta = $this->site_url . "/carta?p=" . md5($data['id']);

                    $mensagem_fabrica = "Olá, [{NOME}].<br><br>Acesse o pedido clicando <a target='_blank' href='$link'>aqui</a>!<br><br>Clique <a href='$carta'>aqui</a> para imprimir a carta do cliente<br><br>Fábrica";

                    $this->DB_insert('tb_pedidos_emails_historicos', 'id_pedido,alvo,nome,email,link,usuario', "
                        '{$data['id']}',
                        'fábrica',
                        '{$data['nome_notificacao'][$i]}',
                        '{$data['email_notificacao'][$i]}',
                        '$link',
                        '{$_SESSION['admin_id']} - {$_SESSION['admin_nome']}'
                        ");

                    $to[] = array("email" => $data['email_notificacao'][$i], "nome" => utf8_decode($data['nome_notificacao'][$i]));

                    $assunto = $assunto_fabrica;

                    $body = $mensagem_fabrica;


                    $body = str_replace("[{NOME}]", $data['nome_notificacao'][$i], $body);

                    $send = $this->enviarEmail($to, $setFrom, utf8_decode($assunto), utf8_decode($body));

                    unset($to);
                }
            }
        }



        if ($send) {
            $mensagem = "E-mails enviados com sucesso!";
        } else {
            $mensagem = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
        }

        echo json_encode(Array('status' => $send, 'message' => $mensagem));
    }

    private function createBlingInvoiceAction() {
        $resposta = new \stdClass();

        $query = $this->DB_fetch_array("SELECT * FROM tb_admin_empresas A WHERE A.id = {$_POST['emitente']}");
        $empresa = $query->rows[0];

        $query = $this->DB_fetch_object("SELECT A.* FROM tb_pedidos_pedidos A WHERE A.id = {$_POST['id']}");
        $pedido = $query->rows[0];

        $query = $this->DB_fetch_object("SELECT B.* FROM tb_pedidos_pedidos A INNER JOIN tb_clientes_clientes B ON B.id = A.id_cliente WHERE A.id = {$_POST['id']}");
        $cliente = $query->rows[0];

        $query = $this->DB_fetch_object("SELECT A.*, C.cidade, E.estado, E.uf FROM tb_pedidos_enderecos A INNER JOIN tb_utils_cidades C ON C.id = A.id_cidade INNER JOIN tb_utils_estados E ON E.id = C.id_estado WHERE A.id_pedido = {$_POST['id']}");
        $endereco = $query->rows[0];


        $valor_total = ($pedido->subtotal + $pedido->valor_frete) - $pedido->descontos;
        $cupom = $pedido->valor_cupom;
        $avista = 0;

        if($pedido->tipo_cupom == 1){
            $cupom = (($valor_total*$pedido->valor_cupom)/100);
        }

        if ($pedido->avista == 1) {
            $avista = ($valor_total - $cupom) * 5 / 100;
        }

        $desconto = $cupom+$avista;

        $produtos = $this->product->getCartProductsByPedido($_POST['id']);
        $peso = 0;

        $itens = [];
        
        foreach ($produtos->rows as $produto) {
            $peso = $peso + (float)$produto['peso'];
            $vlr_unit = ((float)$produto['valor_produto'] - (float)$produto['desconto']);

            //Calcular IPI reverso para notas dos produtos que tiver IPI registrado.
            if($empresa['invoice_bling_ipi_flag'] == 1 && $produto['porcentagem_ipi'] > 0){
                $vlr_unit = $this->calcularIPI(($pedido->subtotal-$pedido->descontos),$desconto,$pedido->valor_frete,$vlr_unit,$produto['quantidade'],$produto['porcentagem_ipi']);
                $vlr_unit = number_format($vlr_unit, 2, '.', '');
            }

            $itens[] = [
                'tipo' => 'P',
                'codigo' => $produto['sku'],
                'classificacaoFiscal' => $produto['ncm'],
                'origem' => 0,
                'unidade' => 'un',
                'quantidade' => $produto['quantidade'],
                'pesoBruto' => $produto['peso'],
                'pesoLiquido' => $produto['peso'],
                'valor' => $vlr_unit,
            ];
        }

        $data = [
            'tipo' => 1,
            'finalidade' => 1,
            'dataOperacao' => date('Y-m-d H:i:s'),
            'contato' => [
                'nome' => $cliente->pessoa == 1 ? $cliente->nome : $cliente->razao_social,
                'tipoPessoa' => $cliente->pessoa == 1 ? "F" : "J",
                'numeroDocumento' => $cliente->pessoa == 1 ? $this->numeros($cliente->cpf) : $this->numeros($cliente->cnpj),
                'telefone' => $cliente->telefone,
                'email' =>  $cliente->email,
                'contribuinte' => $cliente->pessoa == 1 ? 9 : 2,
                'endereco' => [
                    'endereco' => $endereco->endereco,
                    'numero' => $endereco->numero ?: 'SN',
                    'complemento' => $endereco->complemento,
                    'bairro' => $endereco->bairro,
                    'cep' => $this->formataCep($endereco->cep),
                    'municipio' => $endereco->cidade,
                    'uf' => $endereco->uf,
                ],
            ],
            'itens' => $itens,
            'desconto' => $desconto,
            'transporte' => [
                'fretePorConta' => 0,
                'frete' => $pedido->valor_frete,
                'volume' => [
                    'pesoBruto' => $peso,
                    'pesoLiquido' => $peso,
                ],
            ],
            'observacoes' => "Pedido #{$_POST['id']} - Forma de Pagamento: " . $this->paymentType($pedido->metodo_pagamento),
        ];

        if($cliente->inscricao_estadual){
            $data['contato']['ie'] = $cliente->inscricao_estadual;
            $data['contato']['contribuinte'] = 1;
        }

        try {
            $this->bling_company_id = $_POST['emitente'];
            $request = (string) (new BlingV3($this))->createNF($data);
            $result = json_decode($request);

            $query = $this->DB_update("tb_pedidos_pedidos", "id_empresa_faturado = '{$empresa['id']}', faturado_por = '{$empresa['nome_fantasia']}', n_nota = '{$result->data->numero}', idNotaFiscal = '{$result->data->id}' WHERE id = {$_POST['id']}");

            if ($query) {
                $resposta->numero = $result->data->numero;
                $resposta->idNotaFiscal = $result->data->id;
                $resposta->type = "success";
                $resposta->message = "Nota faturada com sucesso!";
                $this->inserirRelatorio("Faturou nota id: [" . $_POST['id'] . "]");
                $this->updateCampanhasConversoes($_POST['id']);
            } else {
                $resposta->type = "error";
                $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
            }

        } catch (Exception $e) {
            $resposta->type = "error";
            $resposta->message = $e->getMessage();
        }

        echo json_encode($resposta);

    }

    private function mountXmlAction() {
        $resposta = new \stdClass();

        $url = 'https://bling.com.br/Api/v2/notafiscal/json/';
        $xml = '<?xml version="1.0" encoding="UTF-8"?><pedido>';

        $query = $this->DB_fetch_object("SELECT A.* FROM tb_pedidos_pedidos A WHERE A.id = {$_POST['id']}");
        $pedido = $query->rows[0];

        $query = $this->DB_fetch_object("SELECT B.* FROM tb_pedidos_pedidos A INNER JOIN tb_clientes_clientes B ON B.id = A.id_cliente WHERE A.id = {$_POST['id']}");
        $cliente = $query->rows[0];

        $query = $this->DB_fetch_object("SELECT A.*, C.cidade, E.estado, E.uf FROM tb_pedidos_enderecos A INNER JOIN tb_utils_cidades C ON C.id = A.id_cidade INNER JOIN tb_utils_estados E ON E.id = C.id_estado WHERE A.id_pedido = {$_POST['id']}");
        $endereco = $query->rows[0];

        if ($cliente->pessoa == 1) {
            $pessoa = "F";
            $cpf = $this->numeros($cliente->cpf);
        } else if ($cliente->pessoa == 2) {
            $pessoa = "J";
            $cpf = $this->numeros($cliente->cnpj);
        }


        $valor_total = ($pedido->subtotal + $pedido->valor_frete) - $pedido->descontos;
        $cupom = $pedido->valor_cupom;
        $avista = 0;

        if($pedido->tipo_cupom == 1){
            $cupom = (($valor_total*$pedido->valor_cupom)/100);
        }

        if ($pedido->avista == 1) {
            $avista = ($valor_total - $cupom) * 5 / 100;
        }

        $desconto = $cupom+$avista;


        $xml .= '
        <cliente>
            <nome>' . (($cliente->pessoa == 1) ? $cliente->nome : $cliente->razao_social) . '</nome>
            <tipoPessoa>' . $pessoa . '</tipoPessoa>
            <cpf_cnpj>' . $cpf . '</cpf_cnpj>
            <ie_rg>' . $this->numeros($cliente->inscricao_estadual) . '</ie_rg>
            <endereco>' . $endereco->endereco . '</endereco>
            <numero>' . $endereco->numero . '</numero>
            <complemento>' . $endereco->complemento . '</complemento>
            <bairro>' . $endereco->bairro . '</bairro>
            <cep>' . $this->formataCep($endereco->cep) . '</cep>
            <cidade>' . $endereco->cidade . '</cidade>
            <uf>' . $endereco->uf . '</uf>
            <fone>' . $cliente->telefone . '</fone>
            <email>' . $cliente->email . '</email>
        </cliente>    
        ';

        $xml .= '<itens>';

        $produtos = $this->product->getCartProductsByPedido($_POST['id']);
        $peso = 0;

        foreach ($produtos->rows as $produto) {
            $peso = $peso + (float)$produto['peso'];
            $vlr_unit = ((float)$produto['valor_produto'] - (float)$produto['desconto']);

            //Calcular IPI reverso para notas somente da "Real Poker" dos produtos que tiver IPI registrado.
            if($_POST['emitente'] == 1 && $produto['porcentagem_ipi'] > 0){
                $vlr_unit = $this->calcularIPI(($pedido->subtotal-$pedido->descontos),$desconto,$pedido->valor_frete,$vlr_unit,$produto['quantidade'],$produto['porcentagem_ipi']);
                $vlr_unit = number_format($vlr_unit, 2, '.', '');
            }

            $xml .= '
            <item>
                <codigo>' . $produto['sku'] . '</codigo>
                <descricao>' . $produto['nome_produto'] . '</descricao>
                <un>un</un>
                <qtde>' . $produto['quantidade'] . '</qtde>
                <vlr_unit>' . $vlr_unit . '</vlr_unit>
                <tipo>P</tipo>
                <peso_bruto>' . $produto['peso'] . '</peso_bruto>
                <peso_liq>' . $produto['peso'] . '</peso_liq>
                <class_fiscal>' . $produto['ncm'] . '</class_fiscal>
                <origem>0</origem>
            </item>
            ';
        }

        $xml .= '</itens>';
        $xml .= '<transporte>';
        $xml .= '<peso_bruto>' . $peso . '</peso_bruto>';
        $xml .= '<peso_liquido>' . $peso . '</peso_liquido>';
        $xml .= '</transporte>';
        $xml .= '<vlr_frete>' . $pedido->valor_frete . '</vlr_frete>';
        $xml .= '<vlr_desconto>' . $desconto . '</vlr_desconto>';
        $xml .= '<obs>Forma de Pagamento: ' . $this->paymentType($pedido->metodo_pagamento) . '</obs>';
        $xml .= '</pedido>';

        switch ($_POST['emitente']) {

            case 1:
                $id_empresa_faturado = 1;
                $faturado_por = "Real Poker";
                $apikey = "9c367660a7de8850fc54055cbcc58970e3396a23bcee59752536007cb0fc1c522899b714";

                break;

            case 2:
                $id_empresa_faturado = 2;
                $faturado_por = "Real Acessórios";
                $apikey = "5d9270b2ac21fcafcf41bf04c00519c1344edaa11d76072c761b3d9048cab0e9da631e8e";

                break;

            case 3:
                $id_empresa_faturado = 3;
                $faturado_por = "RP";
                $apikey = "f83cafbf9a8cbe60c0ed587f60762ee8ed40572fdd90e00bbfa5086d6de87646ec06257a";

                break;

        }

        $posts = array(
            //"apikey" => "b7db6bf857787d283e543c378c84719ea63daf64",
            "apikey" => $apikey,
            "xml" => rawurlencode($xml)
        );


        $retorno = $this->executeSendFiscalDocument($url, $posts);
        $json = json_decode($retorno);

        if (isset($json->retorno->notasfiscais{0}->notaFiscal->numero)) {

            $numero = $json->retorno->notasfiscais{0}->notaFiscal->numero;
            $codigo_rastreamento = $json->retorno->notasfiscais{0}->notaFiscal->codigos_rastreamento->codigo_rastreamento;
            $idNotaFiscal = $json->retorno->notasfiscais{0}->notaFiscal->idNotaFiscal;

            $fields_values = "";
            if ($codigo_rastreamento != "")
                $fields_values = ",codigo_rastreamento = '$codigo_rastreamento'";

            $query = $this->DB_update("tb_pedidos_pedidos", "id_empresa_faturado = '$id_empresa_faturado', faturado_por = '$faturado_por', n_nota = '$numero', idNotaFiscal = '$idNotaFiscal' $fields_values WHERE id = {$_POST['id']}");

            if ($query) {
                $resposta->numero = $numero;
                $resposta->idNotaFiscal = $idNotaFiscal;
                $resposta->type = "success";
                $resposta->message = "Nota faturada com sucesso!";
                $this->inserirRelatorio("Faturou nota id: [" . $_POST['id'] . "]");
                $this->updateCampanhasConversoes($_POST['id']);
            } else {
                $resposta->type = "error";
                $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
            }

        } else {
            $resposta->type = "error";
            $resposta->message = $json->retorno->erros->erro->msg;
            $this->inserirRelatorio('Bling - Retorno de Erro: '.$retorno);
        }

        echo json_encode($resposta);
    }


    private function recalcularIPI($vlt_total,$vlr_desconto,$vlr_frete,$vlr_produto,$ipi,$incremento) {
        $vlr_produto = $vlr_produto  + $incremento;
        $proporcao = $vlr_produto / $vlt_total;
        $desconto_produto = $vlr_desconto * $proporcao;
        $frete_produto = $vlr_frete * $proporcao;
        $vlr_base = $vlr_produto + $frete_produto - $desconto_produto;
        $vlr_ipi = $vlr_base * $ipi / 100;
        //echo 'vlr_produto: '.$vlr_produto.' | frete_produto: '.$frete_produto.' | desconto_produto: '.$desconto_produto.' | valor_base: '.$vlr_base.' | ipi final: '.$vlr_ipi.PHP_EOL;
        return $vlr_produto - $vlr_ipi;
    }

    private function provaIPI($vlt_total,$vlr_produto,$vlr_desconto,$vlr_frete,$ipi){
        $proporcao = $vlr_produto / $vlt_total;
        $desconto_produto = $vlr_desconto * $proporcao;
        $frete_produto = $vlr_frete * $proporcao;
        $vlr_base = $vlr_produto + $frete_produto - $desconto_produto;
        $vlr_ipi = $vlr_base * $ipi / 100;
        return $vlr_produto + $vlr_ipi;
    }

    private function calcularIPI($vlt_total,$vlr_desconto,$vlr_frete,$vlr_unit,$qtde,$ipi) {
        $vlr_produtos = $vlr_unit * $qtde;

        $vlr_final = $this->recalcularIPI($vlt_total,$vlr_desconto,$vlr_frete,$vlr_produtos,$ipi,0);

        $prova = $this->provaIPI($vlt_total,$vlr_final,$vlr_desconto,$vlr_frete,$ipi) - $vlr_produtos;
        $incremento = abs($prova);
        $incr = 0;

        while(abs($prova) > 0.50){
            if($prova > 0) break;
            $incremento = $incr + $incremento;
            $vlr_final = $this->recalcularIPI($vlt_total,$vlr_desconto,$vlr_frete,$vlr_produtos,$ipi,$incremento);
            $prova = $this->provaIPI($vlt_total,$vlr_final,$vlr_desconto,$vlr_frete,$ipi) - $vlr_produtos;
            $incr = 0.50;
        }

        return $vlr_final / $qtde;

    }

    private function sendNotaAction() {

        $resposta = new \stdClass();

        $query = $this->DB_fetch_object("SELECT * FROM tb_clientes_clientes WHERE id = {$_POST['id_cliente']}");
        $cliente = $query->rows[0];

        $url = 'https://bling.com.br/Api/v2/notafiscal/json/';
        $posts = array(
            "apikey" => "b7db6bf857787d283e543c378c84719ea63daf64",
            "number" => $_POST['nota'],
            "serie" => 1,
            "sendEmail" => "$cliente->email"
        );
        $retorno = $this->executeSendFiscalDocument($url, $posts);
        $json = json_decode($retorno);

        if (isset($json->retorno->erros[0]->notafiscal->erro) && $json->retorno->erros[0]->notafiscal->erro != "") {
            $resposta->type = "error";
            $resposta->message = $json->retorno->erros[0]->notafiscal->erro;
        } else {
            $resposta->type = "success";
            $resposta->message = "Nota enviada com sucesso!";
            $this->inserirRelatorio("Enviou nota: [" . $_POST['nota'] . "]");
        }

        echo json_encode($resposta);
    }

    private function caProductsAction() {
        echo '<pre>';
        $ca = new ContaAzul($this);
        //print_r($ca->getCategoriesList());
        //print_r($ca->newProduct(212));
        //print_r($ca->delProduct('16adc25b-23ff-4cf9-a6c5-6603e70da9c1'));
    }

    private function contaazulAction() {
        
        $id = $this->getParameter('contaazul');
        $form = $this->formularioObjeto($_POST);
        $ca = new ContaAzul($this);


        //------------------------


        $query = $this->DB_fetch_array("SELECT A.*, A.data registro, DATE_FORMAT(A.data, '%d/%m/%Y às %H:%i') data_compra, DATE_FORMAT(A.data_competencia, '%Y-%m-%dT08:%i:%s.000Z') data_competencia, DATE_FORMAT(A.prazo_entrega, '%d/%m/%Y') prazo_entrega, B.nome status, C.nome cliente, C.telefone cliente_telefone, C.pessoa, C.razao_social, D.nome_fantasia, D.cnpj, E.contaazul_seller_id FROM $this->table A INNER JOIN tb_pedidos_status B ON B.id = A.id_status INNER JOIN tb_clientes_clientes C ON C.id = A.id_cliente LEFT JOIN tb_admin_empresas D ON D.id = A.id_empresa LEFT JOIN tb_admin_users E ON E.id = A.id_vendedor WHERE A.id = $id");

        $order = $query->rows[0];

        $desc_avista = $order['avista']==1 ? 0.95 : 1;
        $total_frete_embutido = 0;
        $cupom = 0;

        $valor_total = $order['subtotal'] + $order['valor_frete'] - $order['descontos'];

        if ($order['valor_cupom']) {
            if ($order['tipo_cupom'] != 1)
                $cupom = $order['valor_cupom'];
            else
                $cupom = (($valor_total*$order['valor_cupom'])/100);
        }

        $method = 'CASH';
        switch ($order['metodo_pagamento']) {
            case 'boleto':
                $method = 'BANKING_BILLET';
                break;

            case 'cielo':
            case 'pagseguro':
            case 'rede_transparente':
            case 'cielo_transparente':
                $method = 'CREDIT_CARD';
                break;
                
            case 'deposito':
                $method = 'BANKING_DEPOSIT';
                break;
                
            case 'pokerstars':
                $method = 'VIRTUAL_CREDIT';
                break;

        }

        $soma_individual = 0;
        
        $extrato_financeiro = $this->extratoFinanceiro($id);
        $products = array();

        foreach ($extrato_financeiro->rows as $key => $value) {
            $products[$key] = new stdClass();
            $products[$key]->product_id = $value['contaazul_id'];
            $products[$key]->quantity = $value['quantidade'];
            $products[$key]->description = $value['nome'];
            $products[$key]->value = number_format(($value['total_sem_frete']*$desc_avista)/$value['quantidade'], 6, '.', '');

            $soma_individual = number_format($soma_individual + ($products[$key]->value * $products[$key]->quantity), 6, '.', ''); 

            $total_frete_embutido = $total_frete_embutido + $value['total_frete_embutido'];
        }

        $sale = new stdClass();

        $sale->number = $id;
        $sale->emission = $order['data_competencia'];
        $sale->status = 'COMMITTED';
        $sale->customer_id = $order['pessoa']==1 ? $ca->getOrCreateCustomer($order['cliente']) : $ca->getOrCreateCustomer($order['cliente'], $order['razao_social']);
        if($order['contaazul_seller_id'] != '') $sale->seller_id = $order['contaazul_seller_id']; else $sale->seller_id = '3df9b04b-ebc8-46b4-bfcb-1ccf0c2c6175';
        $sale->payment = new stdClass();
        $sale->payment->type = $order['avista']==1 ? 'CASH' : 'TIMES';
        $sale->payment->method = $method;
        $sale->products = $products;
        $sale->shipping_cost = number_format((($total_frete_embutido + $order['valor_frete'])*$desc_avista), 2, '.', '');
        $sale->category_id = '09d17b02-bce8-43f9-904c-ef09d0cbe68a';
        $sale->discount = new stdClass();
        $sale->discount->measure_unit = "VALUE";
        $sale->discount->rate = number_format(($cupom*$desc_avista), 2, '.', '');

        $soma_individual = number_format($soma_individual + $sale->shipping_cost - $sale->discount->rate, 6, '.', ''); 

        $installment = new stdClass();
        $installment->number = 1;
        $installment->value = number_format(str_replace(',', '.', str_replace('.', '',$form->pedido_valor_total)), 2, '.', '');
        $installment->due_date = $order['data_competencia'];

        $sale->payment->installments = array($installment);

        $resposta = new \stdClass();
        $resposta->apagado = "";
        $resposta->soma_individual = $soma_individual;
        $resposta->valor_final = $installment->value;
        $resposta->sale = $sale;

        if($order['contaazul_id'] != "") $resposta->apagado = $ca->delSale($order['contaazul_id']);
        
        $result = $ca->newSale($sale);

        $resposta->return = true;

        if(isset($result->id)){
            $this->DB_update('tb_pedidos_pedidos', 'contaazul_id="'.$result->id.'", contaazul_number="'.$result->number.'" WHERE id='.$id);
            $resposta->type = "success";
            $resposta->message = "Venda registrada com sucesso!";
        } else if(isset($result->code) && $result->code == 'INTEGRATION_ERROR' && isset($result->message)) {
            $resposta->return = false;
            $resposta->type = "error";
            $resposta->message = $result->message;
        }  else {
            $resposta->return = false;
            $resposta->type = "error";
            $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
        } 

        $resposta->contaazul = $result;
        echo json_encode($resposta);

    }

    private function executeSendFiscalDocument($url, $data) {
        $curl_handle = curl_init();
        curl_setopt($curl_handle, CURLOPT_URL, $url);
        curl_setopt($curl_handle, CURLOPT_POST, count($data));
        curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl_handle, CURLOPT_VERBOSE, true);
        $verbose = fopen('php://temp', 'w+');
        curl_setopt($curl_handle, CURLOPT_STDERR, $verbose);
        $response = curl_exec($curl_handle);


        if ($response === FALSE) {
            printf("cUrl error (#%d): %s<br>\n", curl_errno($curl_handle),
                htmlspecialchars(curl_error($curl_handle)));
        }

        curl_close($curl_handle);
        return $response;
    }

    private function getRastreioTemplateAction() {
        if (!$this->permissions[$this->permissao_ref]['editar'])
            exit();
        $this->renderView($this->getModule(), "rastreio");
    }

    private function getNotificacaoTemplateAction() {
        if (!$this->permissions[$this->permissao_ref]['editar'])
            exit();
        $this->n = $_POST['id'];
        $this->nmore = $_POST['idmore'];
        $this->produtos = $this->product->getCartProductsByPedido($_POST['id_pedido']);
        $this->renderView($this->getModule(), "notificacao");
    }

    private function validaFormulario($form) {

        $resposta = new \stdClass();
        $resposta->return = true;

        if ($form->prazo_entrega == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "prazo_entrega";
            $resposta->return = false;
            return $resposta;
        } else if ($form->id_status == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "id_status";
            $resposta->return = false;
            return $resposta;
        } else {
            return $resposta;
        }
    }

    private function sendObservacaoAction() {
        if (!$this->permissions[$this->permissao_ref]['editar'])
            exit();

        $resposta = new \stdClass;

        $data = $this->formularioObjeto($_POST, 'tb_carrinho_produtos_historico');

        foreach ($data as $key => $value) {
            $fields_values[] = "$key='$value'";
        }

        $query = $this->DB_update('tb_carrinho_produtos_historico', implode(',', $fields_values) . " WHERE id = $data->id");
        if ($query) {
            $resposta->type = "success";
            $resposta->message = "Registro alterado com sucesso!";
            $this->inserirRelatorio("Alterou observação no produo do carrinho: [" . $data->id . "] nº pedido: [{$_POST['id_pedido']}]");
        } else {
            $resposta->type = "error";
            $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
        }


        echo json_encode($resposta);
    }

    private function uploadFileAction() {
        $resposta = new \stdClass();
        $resposta->return = true;

        $formulario = $this->formularioObjeto($_POST, "tb_carrinho_produtos_anexos");

        if (is_uploaded_file($_FILES["arquivo"]["tmp_name"])) {

            $upload = $this->uploadFile("arquivo", array("jpg", "jpeg", "gif", "png", "rar", "zip", "pdf", "tar"), '');
            if ($upload->return) {
                $this->arquivo = $upload->file_uploaded;
            }
        }

        if (!isset($upload)) {
            $resposta->type = "attention";
            $resposta->message = "Arquivo não selecionado.";
            $resposta->return = false;
        } else if (isset($upload) && !$upload->return) {
            $resposta->type = "attention";
            $resposta->message = $upload->message;
            $resposta->return = false;
        } else {

            if ($formulario->id_produto_historico != "") {
                if (!$this->permissions[$this->permissao_ref]['editar'])
                    exit();

                $dados = $this->DB_fetch_array("SELECT * FROM tb_carrinho_produtos_anexos WHERE id_produto_historico = $formulario->id_produto_historico");

                if ($dados->num_rows AND $dados->rows[0]['arquivo'] != "" AND $this->arquivo != "") {
                    $this->deleteFile("tb_carrinho_produtos_anexos", "arquivo", "id_produto_historico = $formulario->id_produto_historico", "");
                    $this->DB_delete("tb_carrinho_produtos_anexos", "id_produto_historico = $formulario->id_produto_historico");
                }

                $query = $this->DB_insert("tb_carrinho_produtos_anexos", "id_produto_historico,arquivo", "$formulario->id_produto_historico,'$this->arquivo'");
                if ($query) {
                    $resposta->type = "success";
                    $resposta->message = "Arquivo enviado com sucesso!";
                    $resposta->arquivo = $this->root_path . "uploads/" . $this->arquivo;
                    $resposta->return = false;

                    $this->inserirRelatorio("Alterou anexo de produto do carrinho id [" . $formulario->id_produto_historico . "]");
                } else {
                    $resposta->type = "error";
                    $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
                }
            }
        }


        echo json_encode($resposta);
    }

    private function uploadDocumentosAction() {
        $resposta = new \stdClass();
        $resposta->return = true;

        $formulario = $this->formularioObjeto($_POST);
        $crop_sizes = array(array("width"=>200,"height"=>200,"best_fit"=>true));
        $recorte = $crop_sizes;

        $documentos = array('cartao_frente','cartao_verso','documento_frente','documento_verso','selfie');
        $documento = "";

        foreach ($documentos as $value) {
            if (isset($_FILES[$value]) && is_uploaded_file($_FILES[$value]["tmp_name"])) {
                $arquivo = explode(".", $_FILES[$value]["name"]);
                if(end($arquivo)=="pdf") $recorte = "";
                $documento = $value;
                $upload = $this->uploadFile($value, array("jpg", "jpeg", "png", "pdf"), $recorte);
                if ($upload->return) {
                    $this->arquivo = $upload->file_uploaded;
                }
            }
        }

        if (!isset($upload)) {
            $resposta->type = "attention";
            $resposta->message = "Arquivo não selecionado.";
            $resposta->return = false;
        } else if (isset($upload) && !$upload->return) {
            $resposta->type = "attention";
            $resposta->message = $upload->message;
            $resposta->return = false;
        } else {

            if ($formulario->documento_padrao > 0) {
                if (!$this->permissions[$this->permissao_ref]['editar'])
                    exit();

                $dados = $this->DB_fetch_array("SELECT * FROM tb_pedidos_documentos WHERE id = $formulario->documento_padrao");

                if ($dados->num_rows AND $dados->rows[0][$documento] != "" AND $this->arquivo != "") {
                    $corte = $crop_sizes;
                    $arquivo = explode(".", $dados->rows[0][$documento]);
                    if(end($arquivo)=="pdf") $corte = "";
                    $this->deleteFile("tb_pedidos_documentos", $documento, "id = $formulario->documento_padrao", $corte);
                }

                $query = $this->DB_update("tb_pedidos_documentos", $documento."='".$this->arquivo."' WHERE id=".$formulario->documento_padrao);
                if ($query) {
                    $resposta->type = "success";
                    $resposta->message = "Documento enviado com sucesso!";
                    $resposta->id_padrao = $formulario->documento_padrao;
                    $resposta->arquivo_link = $this->root_path . "uploads/" . $this->arquivo;
                    if($recorte==""){
                        $resposta->arquivo_tipo = "pdf";
                    }else{
                        $resposta->arquivo_tipo = "foto";
                        $resposta->foto = $this->root_path . "uploads/" . $this->getImageFileSized($this->arquivo,200,200);
                    }
                    $resposta->return = false;

                    $this->inserirRelatorio("Alterou foto do $documento de pedido id [" . $formulario->id . "]");
                } else {
                    $resposta->type = "error";
                    $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
                }
            }else{
                $query = $this->DB_insert('tb_pedidos_documentos', 'id_pedido, `default`, '.$documento, $formulario->id.',1,"'.$this->arquivo.'"');

                $inserted_id = $query->insert_id;
                $resposta->query = $query;

                if($query->query){
                    $resposta->type = "success";
                    $resposta->time = 4000;
                    $resposta->message = "Documento enviado com sucesso!";
                    $resposta->arquivo_link = $this->root_path . "uploads/" . $this->arquivo;
                    if($recorte==""){
                        $resposta->arquivo_tipo = "pdf";
                    }else{
                        $resposta->arquivo_tipo = "foto";
                        $resposta->foto = $this->root_path . "uploads/" . $this->getImageFileSized($this->arquivo,200,200);
                    }
                    $resposta->id_padrao = $inserted_id;
                    $this->inserirRelatorio("Inseriu foto do $documento de pedido id [" . $formulario->id . "]");
                    $resposta->return = false;
                }else{
                    $resposta->type = "error";
                    $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
                }
            }
        }


        echo json_encode($resposta);
    }

    private function uploadFotoAction() {
        $resposta = new \stdClass();
        $resposta->return = true;

        $formulario = $this->formularioObjeto($_POST, "tb_carrinho_produtos_historico");

        if (is_uploaded_file($_FILES["foto_final"]["tmp_name"])) {

            $upload = $this->uploadFile("foto_final", array("jpg", "jpeg", "png"), $this->crop_sizes);
            if ($upload->return) {
                $this->arquivo = $upload->file_uploaded;
            }
        }

        if (!isset($upload)) {
            $resposta->type = "attention";
            $resposta->message = "Arquivo não selecionado.";
            $resposta->return = false;
        } else if (isset($upload) && !$upload->return) {
            $resposta->type = "attention";
            $resposta->message = $upload->message;
            $resposta->return = false;
        } else {

            if ($formulario->id != "") {
                if (!$this->permissions[$this->permissao_ref]['editar'])
                    exit();

                $dados = $this->DB_fetch_array("SELECT * FROM tb_carrinho_produtos_historico WHERE id = $formulario->id");

                if ($dados->num_rows AND $dados->rows[0]['foto_final'] != "" AND $this->arquivo != "") {
                    $this->deleteFile("tb_carrinho_produtos_historico", "foto_final", "id = $formulario->id", $this->crop_sizes);
                }

                $query = $this->DB_update("tb_carrinho_produtos_historico", "foto_final='".$this->arquivo."' WHERE id=".$formulario->id);
                if ($query) {
                    $resposta->type = "success";
                    $resposta->message = "Arquivo enviado com sucesso!";
                    $resposta->arquivo = $this->root_path . "uploads/" . $this->getImageFileSized($this->arquivo,400,1000);
                    $resposta->return = false;

                    $this->inserirRelatorio("Alterou foto final de produto do carrinho id [" . $formulario->id . "]");
                } else {
                    $resposta->type = "error";
                    $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
                }
            }
        }


        echo json_encode($resposta);
    }

    private function delArquivoAction() {
        if (!$this->permissions[$this->permissao_ref]['excluir'])
            $this->noPermission();

        $resposta = new \stdClass();

        $id = $_POST['id_produto_historico'];

        $dados = $this->DB_fetch_array("SELECT * FROM tb_carrinho_produtos_anexos WHERE id_produto_historico = $id");

        if ($dados->num_rows AND $dados->rows[0]['arquivo'] != "") {
            $this->deleteFile("tb_carrinho_produtos_anexos", "arquivo", "id_produto_historico=$id", '');
        }

        $query = $this->DB_delete("tb_carrinho_produtos_anexos", "id_produto_historico = $id");
        if ($query) {
            $resposta->type = "success";
            $resposta->message = "Arquivo removido com sucesso!";
            $this->inserirRelatorio("Removeu arquivo de produto do carrinho id: [$id]");
        } else {
            $resposta->type = "error";
            $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
        }

        echo json_encode($resposta);
    }

    public function updateCampanhasConversoes($idPedido) {
        if (!$this->permissions[$this->permissao_ref]['editar'])
            exit();

        $query = $this->DB_fetch_array("SELECT A.id, C.id id_acesso, C.faturado, C.faturamento, A.valor_final, B.session, DATE(A.data) utm_data FROM tb_pedidos_pedidos A INNER JOIN tb_carrinho_produtos_historico B ON B.id_pedido = A.id INNER JOIN tb_seo_acessos_historicos C ON C.session = B.session WHERE A.id = $idPedido AND C.compra IS NOT NULL AND C.faturado IS NULL GROUP BY A.id");
        if ($query->num_rows) {
            $new_faturado = 1 + (int)$query->rows[0]['faturado'];
            $new_faturamento = $query->rows[0]['valor_final'] + (float)$query->rows[0]['faturamento'];
            $updateSeoHistorico = $this->DB_update('tb_seo_acessos_historicos', "faturado = $new_faturado, faturamento = $new_faturamento WHERE id = {$query->rows[0]['id_acesso']}");
            if ($updateSeoHistorico) {
                $verifica = $this->DB_fetch_array("SELECT * FROM tb_seo_acessos WHERE id = {$query->rows[0]['id_acesso']}");
                if ($verifica->num_rows) {
                    $updateSeo = $this->DB_update('tb_seo_acessos', "faturado = $new_faturado, faturamento = $new_faturamento WHERE id = {$query->rows[0]['id_acesso']}");
                } else {
                    //cria histórico utm
                    $utm = $this->DB_fetch_array("SELECT date,utm,utm_source,utm_medium,utm_term,utm_content,utm_campaign FROM (
                    SELECT DATE(A.date) date, CONCAT('utm_source=',IFNULL(A.utm_source, ''),'&utm_medium=',IFNULL(A.utm_medium, ''),'&utm_term=',IFNULL(A.utm_term, ''),'&utm_content=',IFNULL(A.utm_content, ''),'&utm_campaign=',IFNULL(A.utm_campaign, '')) utm,
                    A.utm_source, A.utm_medium, A.utm_term, A.utm_content, A.utm_campaign, A.session
                    FROM tb_seo_acessos_historicos A WHERE A.session = '{$query->rows[0]['session']}' AND DATE(A.date) = '{$query->rows[0]['utm_data']}'
                    GROUP BY A.session
                    ) tab GROUP BY date, utm");

                    if ($utm->num_rows) {
                        $this->DB_insert('tb_dashboards_utms', 'date,utm_source,utm_medium,utm_term,utm_content,utm_campaign,cadastros,contatos,compras,faturados,faturamentos', "'{$utm->rows[0]['date']}','{$utm->rows[0]['utm_source']}','{$utm->rows[0]['utm_medium']}','{$utm->rows[0]['utm_term']}','{$utm->rows[0]['utm_content']}','{$utm->rows[0]['utm_campaign']}',0,0,0,1,'{$query->rows[0]['valor_final']}'");
                    }
                }
            }
        }
    }

    private function concederEdicao($idPedido){
        $this->DB_update("tb_pedidos_pedidos","usuario_editando_pedido={$_SESSION['admin_id']}, usuario_editando_ultimo_ping='".date("Y-m-d H:i:s")."' WHERE id = ".$idPedido);
    }

    private function pingAction(){
        $resposta = new StdClass();
        if(isset($_POST['id'])){
            $this->concederEdicao($_POST['id']);
        }
    }

    private function pongAction(){
        $resposta = new StdClass();
        if(isset($_POST['id'])){
            $registro = $this->DB_fetch_array('SELECT * FROM tb_pedidos_pedidos WHERE id='.$_POST['id']);
            $registro = $registro->rows[0];

            $last_ping = strtotime($registro['usuario_editando_ultimo_ping']);
            $now = strtotime(date("Y-m-d H:i:s"));
            $interval = abs($now - $last_ping);
            if($interval > 60){
                echo 1;
            }else{
                echo 0;
            }
        }
    }

    // Action chamada por AJAX pela Data Table que controla as observações
    private function obsAction() {
        // echo json_encode($_SESSION['admin_nome']);
        // return;

        $tb = 'tb_pedidos_observacoes';
        $resultado = [
            'aaData' => [],
        ];

        if ($_POST['action'] ?? false) {
            $data = $this->formularioObjeto($_POST, $tb);
            switch ($_POST['action']) {
                case 'new':
                    foreach ($data as $k => $v) {
                        $fields[] = $k;
                        $values[] = "'$v'";
                    }
                    $query = $this->DB_insert($tb, implode(',', $fields), implode(',', $values));
                    $data->id = $query->insert_id ?? null;
                    $data->ativo = true;
                    break;
                case 'del':
                    if ($data->id) {
                        $query = $this->DB_update($tb, "deletado_por = '{$data->deletado_por}', ativo = FALSE WHERE id = {$data->id}");
                    }
                    break;
            }
            if ($query != false) {
                $data->DT_RowClass = isset($data->ativo) ? '' : 'obs-inativo';
                $resultado['aaData'] = (array) $data;
            }
        } else if ($_POST['id_pedido'] ?? false) {
            $query = $this->DB_fetch_array("SELECT id, ativo, categoria, criado_por, deletado_por, REPLACE(texto, '\n', '<br />') texto, DATE_FORMAT(data, '%d/%m/%Y às %H:%i') data FROM tb_pedidos_observacoes WHERE id_pedido = {$_POST['id_pedido']} ORDER BY id");
            if ($query->num_rows) {
                $resultado['aaData'] = array_map(function ($row) {
                    $row['DT_RowClass'] = ($row['ativo']) ? '' : ' obs-inativo';
                    return $row;
                }, $query->rows);
            }
            $resultado['aaData'][] = [
                'id' => null,
                'categoria' => null,
                'texto' => null,
                'data' => null,
                'criado_por' => null,
                'deletado_por' => null,
            ];
        }

        echo json_encode($resultado);
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

    //FUNÇÕES DE PRODUÇÃO
    private function producaoAction() {
        $formulario = $this->formularioObjeto($_POST);
        $validacao = $this->validaProducao($formulario);
        $falha = false;
        
        if (!$validacao->return) {
            echo json_encode($validacao);
        } else {
            $resposta = new \stdClass();
            $data = new \stdClass();
            $query = true;

            $remessas = $formulario->remessa;

            foreach ($formulario->produto as $produto) {
                $titulo = $produto['titulo_remessa'];
        
                if (!isset($remessas[$titulo])) $remessas[$titulo] = [];
                if (!isset($remessas[$titulo]['ordens'])) $remessas[$titulo]['ordens'] = [];
                if (!isset($remessas[$titulo]['titulo'])) $remessas[$titulo]['titulo'] = '';

                $ordem = $produto;
                $remessas[$titulo]['ordens'][] = $ordem;
                $remessas[$titulo]['titulo'] = $titulo;
            }

            $new_remessas = [];
            $new_ordens = [];
            
            foreach ($remessas as $key => $remessa) {
                $criarRemessa = $this->criarRemessa($remessa);

                if ($criarRemessa['id']) {
                    $id_remessa = $criarRemessa['id'];

                    $new_remessas[] = (object)[
                        'new'    => $criarRemessa['new'],
                        'id'     => $criarRemessa['id'],
                    ];

                    foreach ($remessa['ordens'] as $key => $ordem) {
                        $falhaOrdem = $this->criarOrdem($ordem, $id_remessa);

                        if($falhaOrdem){
                            $falha = true;
                        }
                    }

                } else { $falha = true; }
            }

            if(!$falha) {
                $this->inserirRelatorio("Pedido: [".$formulario->id."] enviado para produção.");
                $resposta->type = "success";
                $resposta->message = "Pedido enviado para produção!";
            } else {
                $this->deletarRemessasOrdensNovas($new_remessas, $new_ordens);
                $resposta->type = "error";
                $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
            }

            ob_clean();
            echo json_encode($resposta);
            return;
        }
    }

    private function validaProducao($form) {
        $resposta = new \stdClass();
        $resposta->return = true;

        foreach ($form->remessa as $key => $remessa) {
            // if ($remessa['n_nota'] == "") {
            //     $resposta->type = "validation";
            //     $resposta->message = "Preencha este campo";
            //     $resposta->field = $remessa['titulo'].'-n_nota';
            //     $resposta->return = false;
            //     return $resposta;
            // } else 
            if($remessa['nome'] == ""){
                $resposta->type = "validation";
                $resposta->message = "Preencha este campo";
                $resposta->field = $remessa['titulo'].'-nome';
                $resposta->return = false;
                return $resposta;
            } else if($remessa['telefone'] == ""){
                $resposta->type = "validation";
                $resposta->message = "Preencha este campo";
                $resposta->field = $remessa['titulo'].'-telefone';
                $resposta->return = false;
                return $resposta;
            } else if($remessa['cpf_cnpj'] == ""){
                $resposta->type = "validation";
                $resposta->message = "Preencha este campo";
                $resposta->field = $remessa['titulo'].'-cpf_cnpj';
                $resposta->return = false;
                return $resposta;
            } else if($remessa['cep'] == ""){
                $resposta->type = "validation";
                $resposta->message = "Preencha este campo";
                $resposta->field = $remessa['titulo'].'-cep';
                $resposta->return = false;
                return $resposta;
            } else if($remessa['endereco'] == ""){
                $resposta->type = "validation";
                $resposta->message = "Preencha este campo";
                $resposta->field = $remessa['titulo'].'-endereco';
                $resposta->return = false;
                return $resposta;
            } else if($remessa['numero'] == ""){
                $resposta->type = "validation";
                $resposta->message = "Preencha este campo";
                $resposta->field = $remessa['titulo'].'-numero';
                $resposta->return = false;
                return $resposta;
            } else if($remessa['bairro'] == ""){
                $resposta->type = "validation";
                $resposta->message = "Preencha este campo";
                $resposta->field = $remessa['titulo'].'-bairro';
                $resposta->return = false;
                return $resposta;
            } else if($remessa['id_estado'] == ""){
                $resposta->type = "validation";
                $resposta->message = "Preencha este campo";
                $resposta->field = $remessa['titulo'].'-id_estado';
                $resposta->return = false;
                return $resposta;
            } else if($remessa['id_cidade'] == ""){
                $resposta->type = "validation";
                $resposta->message = "Preencha este campo";
                $resposta->field = $remessa['titulo'].'-id_cidade';
                $resposta->return = false;
                return $resposta;
            }
        }
        
        foreach ($form->produto as $key => $produto) {
            if ($produto['titulo_remessa'] == "") {
                $resposta->type = "validation";
                $resposta->message = "Existem produtos sem remessa";
                $resposta->field = 'alert_titulo_remessa';
                $resposta->return = false;
                return $resposta;
            }
        }

        return $resposta;
    }

    private function criarRemessa($remessa, $mudarCriar = false) {
        $id = -1;
        $new = null;
        $title = $remessa['titulo'];

        if (isset($remessa['ordens']) && is_array($remessa['ordens']) && count($remessa['ordens']) > 0 || $mudarCriar) {
            $remessa_existente = $this->DB_fetch_array('SELECT * 
                FROM dp_remessas WHERE titulo = "'.$remessa['titulo'].'" AND deleted_at IS NULL;')->rows;
            
            if(!$remessa_existente){
                $entrega_raw = $remessa['entrega'];
                $saida_raw   = $this->calcularDataSaida($remessa['ordens']);
                $remessa['entrega'] = DateTime::createFromFormat('d/m/Y', $entrega_raw)->format('Y-m-d');
                $remessa['saida'] = DateTime::createFromFormat('d/m/Y', $saida_raw)->format('Y-m-d');
    
                if($remessa['nova_entrega'] == ''){
                    $remessa['nova_entrega'] = 'NULL';
                } else {
                    $remessa['nova_entrega'] = DateTime::createFromFormat('d/m/Y', $remessa['nova_entrega'])->format('Y-m-d');
                }
    
                if($remessa['nova_saida'] == ''){
                    $remessa['nova_saida'] = 'NULL';
                } else {
                    $remessa['nova_saida'] = DateTime::createFromFormat('d/m/Y', $remessa['nova_saida'])->format('Y-m-d');
                }
    
                $fields = [];
                $values = [];
    
                foreach ($remessa as $key => $value) {
                    if ($key == 'ordens' || $key == 'id_ordem' || $key == 'id_remessa_old') continue;
                    $fields[] = $key;
    
                    if ($value == "NULL") $values[] = "{$value}";
                    else $values[] = "'{$value}'";
                }
    
                //ADICIONA UMA NOVA REMESSA
                $query = $this->DB_insert($this->table_remessas, implode(',', $fields), implode(',', $values));

                $id = $query->insert_id;
                $new = true;


            } else {
               $id = $remessa_existente[0]['id'];
               $new = false;
            }
        }

        return [
            'id' => $id,
            'new' => $new,
            'remessa' => $remessa
        ];
    }

    private function criarMudarRemessaAction() {
        $formulario = $this->formularioObjeto($_POST);
        $validacao = $this->validaNovaRemessa($formulario);
        $falha = false;

        if (!$validacao->return) {
            echo json_encode($validacao);
        } else {
            $resposta = new \stdClass();
            $data = new \stdClass();
            $query = true;

            $criarRemessa = $this->criarRemessa((Array) $formulario, true);

            if ($criarRemessa['id']){
                $updateOrder = $this->DB_update("dp_ordens", "id_remessa = ".$criarRemessa['id']." WHERE id = ".$formulario->id_ordem);

                if(!$updateOrder){
                    $falha = true;
                }
                $this->deletarRemessaVazia($formulario->id_remessa_old);
            } else {
                $falha = true;
            }

            if(!$falha) {
                $resposta->type = "success";
                $resposta->message = "Remessa criada!";
            } else {
                $resposta->type = "error";
                $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
            }

            ob_clean();
            echo json_encode($resposta);
            return;
        }
    }

    private function mudarRemessaAction() {
        $formulario = $this->formularioObjeto($_POST);
        $falha = false;

        $resposta = new \stdClass();
        $data = new \stdClass();
        $query = true;
        
        $updateOrder = $this->DB_update("dp_ordens", "id_remessa = ".$formulario->remessa_id." WHERE id = ".$formulario->id_ordem);

        $this->deletarRemessaVazia($formulario->id_remessa_old);

        if(!$updateOrder){
            $falha = true;
        }

        if(!$falha) {
            $resposta->type = "success";
            $resposta->message = "Remessa alterada!";
        } else {
            $resposta->type = "error";
            $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
        }

        ob_clean();
        echo json_encode($resposta);
        return;
    }

    private function deletarRemessaVazia($id_remessa) {
        $existe_ordem = $this->DB_fetch_array('SELECT * 
            FROM dp_ordens WHERE id_remessa = "'.$id_remessa.'" AND deleted_at IS NULL;')->rows;
    
        if(!$existe_ordem){
            $this->DB_delete("dp_remessas", "id = {$id_remessa}");
        }
    }

    private function validaNovaRemessa($form) {
        $resposta = new \stdClass();
        $resposta->return = true;

        if($form->nome == ""){
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = $form->id_pedido.'-0-nome';
            $resposta->return = false;
            return $resposta;
        } else if($form->telefone == ""){
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = $form->id_pedido.'-0-telefone';
            $resposta->return = false;
            return $resposta;
        } else if($form->cpf_cnpj == ""){
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = $form->id_pedido.'-0-cpf_cnpj';
            $resposta->return = false;
            return $resposta;
        } else if($form->cep == ""){
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = $form->id_pedido.'-0-cep';
            $resposta->return = false;
            return $resposta;
        } else if($form->endereco == ""){
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = $form->id_pedido.'-0-endereco';
            $resposta->return = false;
            return $resposta;
        } else if($form->numero == ""){
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = $form->id_pedido.'-0-numero';
            $resposta->return = false;
            return $resposta;
        } else if($form->bairro == ""){
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = $form->id_pedido.'-0-bairro';
            $resposta->return = false;
            return $resposta;
        } else if($form->id_estado == ""){
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = $form->id_pedido.'-0-id_estado';
            $resposta->return = false;
            return $resposta;
        } else if($form->id_cidade == ""){
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = $form->id_pedido.'-0-id_cidade';
            $resposta->return = false;
            return $resposta;
        }

        return $resposta;
    }

    private function criarOrdem($ordem, $id_remessa){
        $falha = false;
        $ordem['id_status'] = 0;
        $ordem['id_remessa'] = $id_remessa;

        $ordem['resumo'] = substr($ordem['resumo'], 0, 255);
        $ordem['observacao'] = substr($ordem['observacao'], 0, 255);

        $fields = [];
        $values = [];

        foreach ($ordem as $key => $value) {
            if ($key == 'titulo_remessa') continue;
            $fields[] = $key;

            if ($value == "NULL") $values[] = "{$value}";
            else $values[] = "'{$value}'";
        }

        //ADICIONA UMA NOVA ORDEM
        $query = $this->DB_insert($this->table_ordens, implode(',', $fields), implode(',', $values));

        if ($query->insert_id) {
            $atribuirFalha = $this->atribuirOrdemDepartamento($query->insert_id, $ordem['id_categoria']);

            if($atribuirFalha){
                $falha = true;
            }

            //Adicionar requisitos
            $id_ordem = $query->insert_id;
            $new_ordens[] = (object)[
                'id'     => $id_ordem,
            ];
            $id_categoria = (int) $ordem['id_categoria'];

            $requisitos = $this->DB_fetch_array('SELECT A.* 
                FROM tb_produtos_prerequisitos A
                LEFT JOIN tb_produtos_pre_cat B ON A.id = B.id_pre AND B.id_cat = '.$id_categoria.'
                WHERE A.deleted_at IS NULL AND A.stats = 1;');

            foreach ($requisitos->rows as $key => $requisito) {
                $criarRequisito = $this->criarRequisito($requisito, $id_ordem);

                if ($criarRequisito) {
                    $requisito_id = $criarRequisito;

                    $dependencias = $this->DB_fetch_array('SELECT A.* 
                        FROM tb_produtos_dependencias A
                        LEFT JOIN tb_produtos_pre_dep B ON A.id = B.id_dep AND B.id_pre = '.$requisito['id'].'
                        WHERE A.deleted_at IS NULL AND A.stats = 1;');

                    foreach ($dependencias->rows as $key => $dependencia) {
                        $criarDependencia = $this->criarDependencia($dependencia, $requisito_id);

                        if (!$criarDependencia) {
                            $falha = true;
                        }
                    }
                } else {
                    $falha = true;
                }
            }
        } else {
            $falha = true;
        }

        return $falha;
    }

    private function atribuirOrdemDepartamento($id_ordem, $id_categoria){
        $falha = false;
        $departamentos = $this->DB_fetch_array('SELECT A.id_departamento
            FROM dp_categoria_departamento A
            WHERE A.id_categoria = '.$id_categoria.';')->rows;

        foreach ($departamentos as $key => $departamento) {
            $query = $this->DB_insert('dp_ordem_departamento', 'id_departamento, id_ordem', implode(',', [$departamento['id_departamento'], $id_ordem]));
            if (!$query->insert_id) {
                $falha = true;
            }
        }

        return $falha;
    }

    private function criarRequisito($requisito, $id_ordem){
        //ADICIONA UM NOVO REQUISITO
        $fields = ['id_ordem', 'nome', 'descricao', 'ordem', 'status'];
        $values = [
            $id_ordem,
            "'" . (string) $requisito['nome'] . "'",
            "'" . (string) $requisito['descricao'] . "'",
            (int) $requisito['ordem'],
            0
        ];
        $query = $this->DB_insert($this->table_requisitos, implode(',', $fields), implode(',', $values));

        return $query->insert_id;
    }

    private function criarDependencia($dependencia, $requisito_id){
        //ADICIONA UMA NOVA DEPENDENCIA
        $fields = ['id_requisito', 'id_dependencia', 'status'];
        $values = [$requisito_id, $dependencia['id'], 0];

        $fields = ['id_requisito', 'nome', 'descricao', 'cor', 'status'];
        $values = [
            $requisito_id,
            "'" . (string) $dependencia['nome'] . "'",
            "'" . (string) $dependencia['descricao'] . "'",
            "'" . (string) $dependencia['cor'] . "'",
            0
        ];

        $query = $this->DB_insert($this->table_dependencias, implode(',', $fields), implode(',', $values));

        return $query->insert_id;
    }

    public function calcularDataSaida($ordens){
        $data = new DateTime();
        $dias = 0;
        foreach ($ordens as $ordem) {
            if((int) $ordem['prazo_producao'] > $dias){
                $dias = (int) $ordem['prazo_producao'];
            }
        }
        $data->modify("+{$dias} days"); 
        return $data->format('d/m/Y'); 
    }

    private function deletarRemessasOrdensNovas($remessas, $ordens){
        foreach ($ordens as $ordem) {
            $this->DB_delete($this->table_ordens, "id = {$ordem->id}");
        }
        foreach ($remessas as $remessa) {
            if($remessa->new){
                $this->DB_delete($this->table_remessas, "id = {$remessa->id}");
            }
        }

    }
}