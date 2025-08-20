<?php

use System\Core\Bootstrap;

Contact::setAction();

class Contact extends Bootstrap {

    public $module = "";
    public $permissao_ref = "contatos";
    public $table = "tb_contatos_contatos";

    function __construct() {
        parent::__construct();
        if (!isset($_SESSION['admin_logado'])) {
            header("Location: " . $this->system_path . "login");
            exit;
        }
        $this->getAllPermissions();

        $this->module_icon = "entypo-icon-email";
        $this->module_link = "contact";
        $this->module_title = "Contatos";
        $this->retorno = "contact";
    }

    private function indexAction() {

        if (!$this->permissions[$this->permissao_ref]['ler']) {
            $this->noPermission();
        }

        $this->list = $this->DB_fetch_array("SELECT *, DATE_FORMAT(A.data, '%d/%m/%Y %H:%i') registro FROM $this->table A");

        $this->renderView($this->getModule(), "index");
    }

    private function datatableAction() {
        if (!$this->permissions[$this->permissao_ref]['ler'])
            exit();
        
        //defina os campos da tabela
        $aColumns = array('A.id','A.data','A.nome','A.email','A.fone');

        //defina os campos que devem ser usados para a busca
        $aColumnsWhere = array('A.nome');

        //defina o coluna índice
        $sIndexColumn = "A.id";

        //defina o nome da tabela, ou faça aqui seu INNER JOIN, LEFT JOIN, RIGHT JOIN
        //Ex: "tb_emails_emails A INNER JOIN tb_admin_users B ON B.id=A.id"
        $sTable = "$this->table A ";

        //declarar condições extras
        $sWhere = "";

        $sGrouby = "";

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
        //echo $mainQuery;
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
                $row[] = "<div align=left><a href='contact/edit/id/". $aRow['id'] ."'>" . $aRow ['id'] . "</a></div>";

                //NOME
                $row[] = "<div align=left><a href='contact/edit/id/". $aRow['id'] ."'>" . $aRow ['data'] . "</a></div>";

                //NOME
                $row[] = "<div align=left><a href='contact/edit/id/". $aRow['id'] ."'>" . $aRow ['nome'] . "</a></div>";

                //EMAIL
                $row[] = "<div align=left><a href='contact/edit/id/". $aRow['id'] ."'>" . $aRow ['email'] . "</a></div>";

                //TELEFONE
                $row[] = "<div align=left><a href='contact/edit/id/". $aRow['id'] ."'>" . $aRow ['fone'] . "</a></div>";

                //AÇÃO
                $excluir = "";
                if ($this->permissions[$this->permissao_ref]['excluir']) 
                    $excluir = "<a class='bt_system_delete' data-controller='".$this->getModule()."' data-id='".$aRow['id']."' href='#'><i class='s12 icomoon-icon-remove'></i></a> <input type='checkbox' id='del_".$aRow['id']."' value='". $aRow['id']."' class='del-this'>";
                $row[] = '<div align="left"><a href="contact/edit/id/'. $aRow['id'] .'"><i class="s12 icomoon-icon-pencil"></i></a> '.$excluir.'</div>';

                $output['aaData'][] = $row;
            }
        }
        
        $output['queryExport'] = $queryExport;

        echo json_encode($output);
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
        } else {
            //edit
            if (!$this->permissions[$this->permissao_ref]['editar'])
                $this->noPermission();

            $query = $this->DB_fetch_array("SELECT *, DATE_FORMAT(data, '%d/%m/%Y %H:%i') data FROM $this->table WHERE id = $this->id");
            $this->registro = $query->rows[0];
        }

        $this->historicos = $this->DB_fetch_array("SELECT DATE_FORMAT(B.date, '%d/%m/%Y às %H:%i:%s') registro, B.date, C.seo_title titulo, B.origem, CONCAT(B.cidade, ', ', B.estado, ' - ', B.pais) localizacao, B.pais, B.estado, B.cidade, B.dispositivo, B.ip, B.session FROM tb_contatos_contatos A LEFT JOIN tb_seo_acessos_historicos B ON B.session = A.session INNER JOIN tb_seo_paginas C ON C.id = B.id_seo WHERE A.session = '{$this->registro['session']}' ORDER BY B.date");

        $this->renderView($this->getModule(), "edit");
    }

    private function exportContactsAction() {
        if (!$this->permissions[$this->permissao_ref]['ler'])
            $this->noPermission();
     
        header('Content-type: application/x-msdownload');
        header("Content-type: application/vnd.ms-excel");
        header("Content-type: application/force-download");
        header("Content-Disposition: attachment; filename=contatos-" . date("Y-m-d") . ".xls");
        header("Pragma: no-cache");
      

        $this->dados = $this->DB_fetch_array("SELECT *, DATE_FORMAT(data, '%d/%m/%Y %H:%i') data FROM tb_contatos_contatos");

        $this->renderExport($this->getModule(), "contacts");
    }

    private function delAction() {
        if (!$this->permissions[$this->permissao_ref]['excluir'])
            exit();

        $id = $this->getParameter("id");
        $dados = $this->DB_fetch_array("SELECT * FROM $this->table WHERE id = $id");

        $this->inserirRelatorio("Apagou contato: [" . $dados->rows[0]['nome'] . "] id: [$id]");
        $this->DB_delete($this->table, "id=$id");

        echo $this->getModule();
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
