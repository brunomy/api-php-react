<?php

use System\Core\Bootstrap;

Coupon::setAction();

class Coupon extends Bootstrap {

    public $module = "";
    public $permissao_ref = "cupons";
    public $table = "tb_cupons_cupons";

    function __construct() {
        parent::__construct();
        if (!isset($_SESSION['admin_logado'])) {
            header("Location: " . $this->system_path . "login");
            exit;
        }
        $this->getAllPermissions();

        $this->module_icon = "icomoon-icon-transmission";
        $this->module_link = "coupon";
        $this->module_title = "Cupons";
        $this->retorno = "coupon";
    }

    private function indexAction() {

        if (!$this->permissions[$this->permissao_ref]['ler']) {
            $this->noPermission();
        }

        //$this->list = $this->DB_fetch_array("SELECT A.* FROM $this->table A ORDER BY A.data");

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
        } else {
            //edit
            if (!$this->permissions[$this->permissao_ref]['editar'])
                $this->noPermission();

            $query = $this->DB_fetch_array("SELECT A.* FROM $this->table A WHERE A.id = $this->id");
            $this->registro = $query->rows[0];
        }


        $this->renderView($this->getModule(), "edit");
    }

    private function delAction() {
        if (!$this->permissions[$this->permissao_ref]['excluir'])
            exit();

        $id = $this->getParameter("id");

        $dados = $this->DB_fetch_array("SELECT * FROM $this->table WHERE id = $id");

        $this->inserirRelatorio("Apagou código: [" . $dados->rows[0]['codigo'] . "] id: [$id]");
        $this->DB_delete($this->table, "id=$id");

        echo $this->getModule();
    }

    private function saveAction() {
        $formulario = $this->formularioObjeto($_POST);
        $validacao = $this->validaFormulario($formulario);
        if (!$validacao->return) {
            echo json_encode($validacao);
        } else {


            if ($_POST['valor'] != "")
                $_POST['valor'] = $this->formataMoedaBd($_POST['valor']);
            else
                $_POST['valor'] = "NULL";


            $resposta = new \stdClass();

            $data = $this->formularioObjeto($_POST, $this->table);


            if ($formulario->id == "") {
                //criar
                if (!$this->permissions[$this->permissao_ref]['gravar'])
                    exit();

                foreach ($data as $key => $value) {
                    $fields[] = $key;
                    if ($value == "NULL")
                        $values[] = "$value";
                    else
                        $values[] = "'$value'";
                }

                $query = $this->DB_insert($this->table, implode(',', $fields), implode(',', $values));
                $idDesconto = $query->insert_id;
                if ($query->query) {
                    $resposta->type = "success";
                    $resposta->message = "Registro cadastrado com sucesso!";
                    $this->inserirRelatorio("Cadastrou código: [" . $data->codigo . "]");
                } else {
                    $resposta->type = "error";
                    $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
                }
            } else {
                //alterar

                if (!$this->permissions[$this->permissao_ref]['editar'])
                    exit();

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
                    $this->inserirRelatorio("Alterou código: [" . $data->codigo . "]");
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

        if ($form->codigo == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "codigo";
            $resposta->return = false;
            return $resposta;
        } else if ($form->porcentagem == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "porcentagem";
            $resposta->return = false;
            return $resposta;
        } else if ($form->valor == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "valor";
            $resposta->return = false;
            return $resposta;
        } else {
            return $resposta;
        }
    }

    private function datatableAction() {
        if (!$this->permissions[$this->permissao_ref]['ler'])
            exit();
        
        //defina os campos da tabela
        $aColumns = array('A.data', 'A.id', 'A.codigo', 'A.mensagem', 'A.porcentagem', 'A.stats');

        //defina os campos que devem ser usados para a busca
        $aColumnsWhere = array('A.id', 'A.codigo', 'A.mensagem', 'A.porcentagem');

        //defina o coluna índice
        $sIndexColumn = "A.id";

        //defina o nome da tabela, ou faça aqui seu INNER JOIN, LEFT JOIN, RIGHT JOIN
        //Ex: "tb_emails_emails A INNER JOIN tb_admin_users B ON B.id=A.id"
        $sTable = "$this->table A";

        //declarar condições extras
        $sWhere = "";

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

                //CÓDIGO
                $row[] = "<div align=left>" . $aRow ['codigo'] . "</div>";

                //MENSAGEM
                $row[] = "<div align=left>" . $aRow ['mensagem'] . "</div>";

                //TIPO
                if ($aRow ['porcentagem']) $tipo="Porcentagem"; else $tipo="Fixo";
                $row[] = "<div align=left>" .  $tipo . "</div>";

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
                $row[] = '<div align="left"><a href="coupon/edit/id/'. $aRow['id'] .'"><i class="s12 icomoon-icon-pencil"></i></a> '.$excluir.'</div>';

                $output['aaData'][] = $row;
            }
        }
        
        $output['queryExport'] = $queryExport;

        echo json_encode($output);
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
