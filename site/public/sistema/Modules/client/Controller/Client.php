<?php

use System\Core\Bootstrap;
use classes\Cliente;;

Client::setAction();

class Client extends Bootstrap {

    public $module = "";
    public $permissao_ref = "clientes";
    public $table = "tb_clientes_clientes";

    function __construct() {
        parent::__construct();
        if (!isset($_SESSION['admin_logado'])) {
            header("Location: " . $this->system_path . "login");
            exit;
        }
        $this->getAllPermissions();

        $this->module_icon = "icomoon-icon-users";
        $this->module_link = "client";
        $this->module_title = "Clientes";
        $this->retorno = "client";

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
    }

    private function indexAction() {

        if (!$this->permissions[$this->permissao_ref]['ler']) {
            $this->noPermission();
        }



        //$this->list = $this->DB_fetch_array("SELECT A.*, IF(A.pessoa = 1, A.nome, A.razao_social) nome, DATE_FORMAT(A.ultimo_acesso, '%d/%m/%Y às %H:%i') ultimo_acesso, B.origem, B.utm_source, A.id, A.data registro, DATE_FORMAT(A.data, '%d/%m/%Y às %H:%i') data  FROM $this->table A LEFT JOIN tb_seo_acessos_historicos B ON B.session = A.session WHERE A.nome <> 'anonimo' GROUP BY A.id");

        $this->renderView($this->getModule(), "index");
    }

    private function editAction() {
        $this->id = $this->getParameter("id");

        if ($this->id == "") {
            //new
            if (!$this->permissions[$this->permissao_ref]['gravar'])
                $this->noPermission();

            $campos = $this->DB_columns($this->table);
            foreach ($campos as $campo) {
                $this->registro[$campo] = "";
            }

            $this->registro['origem'] = "";
            $this->registro['utm_source'] = "";
            $this->registro['utm_medium'] = "";
            $this->registro['utm_term'] = "";
            $this->registro['utm_content'] = "";
            $this->registro['utm_campaign'] = "";

            $this->registro['cidade'] = "";
            $this->registro['estado'] = "";
        } else {
            //edit
            if (!$this->permissions[$this->permissao_ref]['editar'])
                $this->noPermission();

            $query = $this->DB_fetch_array("SELECT A.*, DATE_FORMAT(A.data, '%d/%m/%Y %H:%i') data, DATE_FORMAT(A.ultimo_acesso, '%d/%m/%Y') ultimo_acesso, B.origem, B.utm_source,B.utm_medium,B.utm_term,B.utm_content,B.utm_campaign, A.id, C.cidade, E.estado FROM $this->table A LEFT JOIN tb_seo_acessos_historicos B ON B.session = A.session LEFT JOIN tb_utils_cidades C ON C.id = A.id_cidade LEFT JOIN tb_utils_estados E ON E.id = A.id_estado WHERE A.id = $this->id");

            $this->registro = $query->rows[0];
        }

        $this->registro['utm'] = "";
        if ($this->registro['utm_source'] != "") {
            $this->registro['utm'] = "utm_source={$this->registro['utm_source']}";
        }
        if ($this->registro['utm_medium'] != "") {
            $this->registro['utm'] .= "&utm_medium={$this->registro['utm_medium']}";
        }
        if ($this->registro['utm_term'] != "") {
            $this->registro['utm'] .= "&utm_term={$this->registro['utm_term']}";
        }
        if ($this->registro['utm_content'] != "") {
            $this->registro['utm'] .= "&utm_content={$this->registro['utm_content']}";
        }
        if ($this->registro['utm_campaign'] != "") {
            $this->registro['utm'] .= "&utm_campaign={$this->registro['utm_campaign']}";
        }

        $this->estados = $this->DB_fetch_array("SELECT * FROM tb_utils_estados ORDER BY estado");

        // Categorias de produtos para os checkbox de filtro
        $query = $this->DB_fetch_array("SELECT id, nome FROM tb_produtos_categorias WHERE stats <> 0");
        $this->categorias = ($query->num_rows) ? $query->rows : [];

        $this->renderView($this->getModule(), "edit");
    }



    private function showAction(){

        $this->id = $this->getParameter("id");

        if (!$this->permissions[$this->permissao_ref]['editar'])
            $this->noPermission();

        $query = $this->DB_fetch_array("select * FROM $this->table where id = $this->id");

        $this->registro = $query->rows[0];

        $this->registro['senha'] =  $this->desembaralhar($this->registro['senha']);

        echo  json_encode($this->registro) ;
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

    private function delAction() {
        if (!$this->permissions[$this->permissao_ref]['excluir'])
            exit();

        $id = $this->getParameter("id");

        $dados = $this->DB_fetch_array("SELECT * FROM $this->table WHERE id = $id");

        $this->inserirRelatorio("Apagou cliente: [" . $dados->rows[0]['nome'] . "] id: [$id]");
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
            $data = $this->formularioObjeto($_POST, $this->table);

            if ($formulario->id == "") {
                //criar
                if (!$this->permissions[$this->permissao_ref]['gravar'])
                    exit();

                if (isset($data->senha) && $data->senha != "")
                    $data->senha = $this->embaralhar($data->senha);
                else
                    unset($data->senha);


                foreach ($data as $key => $value) {
                    $fields[] = $key;
                    if ($value == "NULL")
                        $values[] = "$value";
                    else
                        $values[] = "'$value'";
                }

                $query = $this->DB_insert($this->table, implode(',', $fields), implode(',', $values));
                if ($query->query) {
                    $resposta->type = "success";
                    $resposta->message = "Registro cadastrado com sucesso!";
                    $this->inserirRelatorio("Cadastrou cliente: [" . $data->nome . "]");
                } else {
                    $resposta->type = "error";
                    $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
                }
            } else {
                //alterar

                if (!$this->permissions[$this->permissao_ref]['editar'])
                    exit();

                if (isset($data->senha) && $data->senha != "")
                    $data->senha = $this->embaralhar($data->senha);
                else
                    unset($data->senha);



                foreach ($data as $key => $value) {
                    if ($value == "NULL")
                        $fields_values[] = "$key=$value";
                    else
                        $fields_values[] = "$key='$value'";
                }

                $query = $this->DB_update($this->table, implode(',', $fields_values) . " WHERE id=" . $data->id);
                if ($query) {
                    $resposta->type = "success";
                    $resposta->message = "Registro alterado com sucesso!";
                    $this->inserirRelatorio("Alterou cliente: [" . $data->nome . "]");
                } else {
                    $resposta->type = "error";
                    $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
                }
            }

            echo json_encode($resposta);
        }
    }

    private function validaFormulario($form) {

        $resposta = new \stdClass();
        $resposta->return = true;

        if ($form->nome == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "nome";
            $resposta->return = false;
            return $resposta;
        } else if ($form->email == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "email";
            $resposta->return = false;
            return $resposta;
        } else if ($this->validaEmail($form->email) == 0) {
            $resposta->type = "validation";
            $resposta->message = "Formato de Email Incorreto";
            $resposta->field = "email";
            $resposta->return = false;
            return $resposta;
        } else if ($form->senha == "" && $form->id == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "senha";
            $resposta->return = false;
            return $resposta;
        } else {
            return $resposta;
        }
    }

    private function updateFieldAction(){
        $data = $this->formularioObjeto($_POST);
        $resposta = new \stdClass();
        $valid = true;
        if($data->field == 'email'){
            $email = $data->value;
            if ($this->validaEmail($email) == 0) {
                $resposta->type = "error";
                $resposta->message = "Formato de Email Incorreto";
                $valid = false;
            }
        }

        if (!$this->permissions[$this->permissao_ref]['editar'])
            exit();

       if($valid) {
           $fields_values[] = "$data->field='$data->value'";

           $query = $this->DB_update($this->table, implode(',', $fields_values) . " WHERE id=" . $data->id);

           if($data->field == 'email' or $data->field == 'telefone') {
               $query = $this->DB_update('tb_crm_crm', implode(',', $fields_values) . " WHERE id=" . $data->id_crm);

               if($data->field == 'email') {
                   $cliente = new Cliente();
                   $cliente->adicionarEmailBase($data->id_crm);
               }
           }

           if ($query) {

               if($data->field == 'ultimo_contato'){
                   $fields[] = "data";
                   $fields[] = "id_cliente";
                   $values[] = "'$data->value'";
                   $values[] = $data->id;
                   $this->DB_insert('tb_clientes_contatos', implode(',', $fields), implode(',', $values));

                   $fields_crm[] = "ultima_atualizacao='" . date('Y-m-d H:i:s') . "'";
                   $query = $this->DB_update('tb_crm_crm', implode(',', $fields_crm) . " WHERE id_cliente=" . $data->id . ' and finalizado is null and possui_orcamento is not null');
               }

               $resposta->type = "success";
               $resposta->message = "Registro alterado com sucesso!";
               $this->inserirRelatorio("Alterou cliente: [" . $data->id . "]");
           } else {
               $resposta->type = "error";
               $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
           }
       }
        echo json_encode($resposta);
    }

    private function exportClientsAction() {

        if (!$this->permissions[$this->permissao_ref]['ler'])
            $this->noPermission();

        header('Content-type: application/x-msdownload');
        header("Content-type: application/vnd.ms-excel");
        header("Content-type: application/force-download");
        header("Content-Disposition: attachment; filename=clientes-" . date("Y-m-d") . ".xls");
        header("Pragma: no-cache");

        $this->dados = $this->DB_fetch_array("SELECT A.*, DATE_FORMAT(A.data, '%d/%m/%Y') data_registro, DATE_FORMAT(A.ultimo_acesso, '%d/%m/%Y %Hh:%i') ultimo_acesso, B.origem, B.utm_source,B.utm_medium,B.utm_term,B.utm_content,B.utm_campaign, C.cidade, E.estado FROM tb_clientes_clientes A LEFT JOIN tb_seo_acessos_historicos B ON B.session = A.session LEFT JOIN tb_utils_cidades C ON C.id = A.id_cidade LEFT JOIN tb_utils_estados E ON E.id = A.id_estado GROUP BY A.id ORDER BY A.nome ASC");

        $this->renderView($this->getModule(), "clients");
    }

    private function datatableAction() {
        if (!$this->permissions[$this->permissao_ref]['ler'])
            exit();

        //defina os campos da tabela
        $aColumns = array('DATE_FORMAT(A.data, "%d/%m/%Y %H:%i") data', 'A.id', 'A.nome', 'A.email', 'A.ultimo_acesso', 'A.stats', 'B.origem', 'B.utm_source', 'DATE_FORMAT(A.ultimo_acesso, "%d/%m/%Y às %H:%i") ultimo_acesso');

        //defina os campos que devem ser usados para a busca
        $aColumnsWhere = array('A.nome', 'A.email', 'B.origem', 'B.utm_source');

        //defina o coluna índice
        $sIndexColumn = "A.id";

        //defina o nome da tabela, ou faça aqui seu INNER JOIN, LEFT JOIN, RIGHT JOIN
        //Ex: "tb_emails_emails A INNER JOIN tb_admin_users B ON B.id=A.id"
        $sTable = "$this->table A LEFT JOIN tb_seo_acessos_historicos B ON B.session = A.session";

        //declarar condições extras
        $sWhere = "WHERE A.nome <> 'anonimo'";

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

                if ($_POST['iSortCol_0'] == 0)
                    $campo = "A.data";
                else if ($_POST['iSortCol_0'] == 4)
                    $campo = "DATE_ADD(A.date, INTERVAL 5 MINUTE)";

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
            foreach ($rResult as $aRow) {
                $row = array();

                //ID
                $row[] = "<div align=left><a href='client/edit/id/". $aRow['id'] ."'>" . $aRow ['id'] . "</a></div>";

                //DATA
                $row[] = "<div align=left><a href='client/edit/id/". $aRow['id'] ."'>" . $aRow ['data'] . "</a></div>";

                //NOME
                $row[] = "<div align=left><a href='client/edit/id/". $aRow['id'] ."'>" . $aRow ['nome'] . "</a></div>";

                //EMAIL
                $row[] = "<div align=left><a href='client/edit/id/". $aRow['id'] ."'>" . $aRow ['email'] . "</a></div>";

                //ULTIMO ACESSO
                $row[] = "<div align=left><a href='client/edit/id/". $aRow['id'] ."'>" . $aRow ['ultimo_acesso'] . "</a></div>";

                //ORIGEM
                $row[] = "<div align=left><a href='client/edit/id/". $aRow['id'] ."'>" . $aRow ['origem'] . "</a></div>";

                //UTM SOURCE
                $row[] = "<div align=left><a href='client/edit/id/". $aRow['id'] ."'>" . $aRow ['utm_source'] . "</a></div>";

                //STATUS
                if($aRow['stats']==0){
                    $row[] = '<a href="#" class="bt_system_stats" data-permit="'.$this->permissao_ref.'" data-table="'.$this->table.'" data-action="ativar" data-id="'.$aRow['id'].'"><img src="images/status_vermelho.png" alt="Ativar"></a>';
                }else{
                    $row[] = '<a href="#" class="bt_system_stats" data-permit="'.$this->permissao_ref.'" data-table="'.$this->table.'" data-action="desativar" data-id="'.$aRow['id'].'"><img src="images/status_verde.png" alt="Desativar"></a>';
                }

                //AÇÃO
                $excluir = "";
                if ($this->permissions[$this->permissao_ref]['excluir'])
                    $excluir = "<a class='bt_system_delete' data-controller='".$this->getModule()."' data-id='".$aRow['id']."' href='#'><i class='s12 icomoon-icon-remove'></i></a> <input type='checkbox' id='del_".$aRow['id']."' value='". $aRow['id']."' class='del-this'>";
                $row[] = '<div align="left"><a href="client/edit/id/'. $aRow['id'] .'"><i class="s12 icomoon-icon-pencil"></i></a> '.$excluir.'</div>';

                $output['aaData'][] = $row;
            }
        }

        $output['queryExport'] = $queryExport;

        echo json_encode($output);
    }

    private function datatablePedidosAction() {

        $this->table = 'tb_pedidos_pedidos';

        if (!$this->permissions[$this->permissao_ref]['ler'])
            exit();

        //defina os campos da tabela
        $aColumns = array("A.id_cliente","A.metodo_pagamento","A.id","A.data registro","A.id","IF(G.nome IS NULL, 'sem vendedor', G.nome) vendedor", "DATE_FORMAT(A.data, '%d/%m/%Y às %H:%i') data_compra", "IF(C.pessoa = 1, C.nome, C.razao_social) cliente", "C.cpf", "C.email", "C.telefone", "E.cidade", "F.estado", "H.payment_method_brand", "B.nome status", "A.valor_final", "A.n_nota", "I.nome editando", "A.usuario_editando_ultimo_ping", "B.cor");


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
            $sWhere = "WHERE A.data > CURRENT_DATE - INTERVAL 60 DAY";

        // $sWhere = "WHERE (A.orc_status IS NULL OR A.orc_status = 'ganho')";
        // if(!$_POST['date_flag'])
        //     $sWhere .= " AND (A.data > CURRENT_DATE - INTERVAL 60 DAY)";

        if($_POST['sSearch'] == "sem vendedor"){
            //$sWhere = "WHERE G.nome IS NULL";
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



        $rResult = array();
        $sQuery = $this->DB_fetch_array("SELECT SQL_CALC_FOUND_ROWS " . str_replace(" , ", " ", implode(", ", $aColumns)) . "
            FROM $sTable
            $sWhere
            $sOrder
            $sLimit");
        if ($sQuery->num_rows)
            $rResult = $sQuery->rows;

        $queryExport = "SELECT SQL_CALC_FOUND_ROWS " . str_replace(" , ", " ", implode(", ", $aColumns)) . "
            FROM $sTable
            $sWhere
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
