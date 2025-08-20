<?php

use System\Core\Bootstrap;

ProductReview::setAction();

class ProductReview extends Bootstrap {

    public $module = "";
    public $permissao_ref = "produtos-avaliacoes";
    public $table = "tb_produtos_avaliacoes";

    function __construct() {
        parent::__construct();
        if (!isset($_SESSION['admin_logado'])) {
            header("Location: " . $this->system_path . "login");
            exit;
        }
        $this->getAllPermissions();

        $this->module_icon = "entypo-icon-chat";
        $this->module_link = "productreview";
        $this->module_title = "Avaliações";
        $this->retorno = "productreview";
    }

    private function indexAction() {

        if (!$this->permissions[$this->permissao_ref]['ler']) {
            $this->noPermission();
        }

        //$this->list = $this->DB_fetch_array("SELECT A.*, B.nome produto, DATE_FORMAT(A.data, '%d/%m/%Y às %H:%i') data, DATE_FORMAT(A.data, '%Y/%m/%d') date FROM $this->table A INNER JOIN tb_produtos_produtos B ON A.id_produto = B.id ORDER BY A.data");

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

            $this->registro['id_cidade'] = "";
            $this->registro['id_estado'] = "";

            $this->registro['id_pedido'] = $this->getParameter("id_pedido");
            $this->registro['id_produto'] = $this->getParameter("id_produto");

            if ($this->registro['id_pedido'] != '') {
                $cliente = $this->DB_fetch_array('SELECT a.data, b.nome, b.id_cidade, b.id_estado FROM tb_pedidos_pedidos a INNER JOIN tb_clientes_clientes b ON a.id_cliente = b.id  WHERE a.id = '.$this->registro['id_pedido']);
                if($cliente->num_rows){
                    $this->registro['nome'] = $cliente->rows[0]['nome'];
                    $this->registro['id_cidade'] = $cliente->rows[0]['id_cidade'];
                    $this->registro['id_estado'] = $cliente->rows[0]['id_estado'];
                    $this->registro['data'] = $cliente->rows[0]['data'];
                }
            }

        } else {
            //edit
            if (!$this->permissions[$this->permissao_ref]['editar'])
                $this->noPermission();

            $query = $this->DB_fetch_array("SELECT A.*,B.id_estado FROM $this->table A LEFT JOIN tb_utils_cidades B ON B.id = A.id_cidade LEFT JOIN tb_utils_estados C ON C.id = B.id_estado WHERE A.id = $this->id");
            $this->registro = $query->rows[0];

        }

        $this->estados = $this->DB_fetch_array("SELECT * FROM tb_utils_estados ORDER BY estado");
        $this->produtos = $this->DB_fetch_array("SELECT * FROM tb_produtos_produtos WHERE apagado = 0 AND stats = 1 ORDER BY nome");
        $this->renderView($this->getModule(), "edit");
    }

    private function delAction() {
        if (!$this->permissions[$this->permissao_ref]['excluir'])
            exit();

        $id = $this->getParameter("id");

        $dados = $this->DB_fetch_array("SELECT * FROM $this->table WHERE id = $id");

        $this->inserirRelatorio("Apagou avaliação: [" . $id . "]");
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

                //unset($data->id);
                foreach ($data as $key => $value) {
                    $fields[] = $key;
                    if ($value == "NULL")
                        $values[] = "$value";
                    else
                        $values[] = "'$value'";
                }


                $query = $this->DB_insert($this->table, implode(',', $fields), implode(',', $values));
                $idReview = $query->insert_id;
                if ($query->query) {
                    $resposta->type = "success";
                    $resposta->message = "Registro cadastrado com sucesso!";
                    $this->inserirRelatorio("Cadastrou avaliação: [" . $idReview . "]");
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
                    $this->inserirRelatorio("Alterou avaliação: [" . $data->id . "]");
                } else {
                    $resposta->type = "error";
                    $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
                }
            }

            echo json_encode($resposta);
        }
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

    private function datatableAction() {
        if (!$this->permissions[$this->permissao_ref]['ler'])
            exit();
        
        //defina os campos da tabela
        $aColumns = array('A.id', 'DATE_FORMAT(A.data, "%d/%m/%Y às %H:%i") data','A.nome','A.chamada','B.nome produto','A.nota', 'A.stats', 'DATE_FORMAT(A.data, "%Y/%m/%d") date');

        //defina os campos que devem ser usados para a busca
        $aColumnsWhere = array('A.nome', 'A.avalicao', 'A.chamada', 'B.nome');

        //defina o coluna índice
        $sIndexColumn = "A.id";

        //defina o nome da tabela, ou faça aqui seu INNER JOIN, LEFT JOIN, RIGHT JOIN
        //Ex: "tb_emails_emails A INNER JOIN tb_admin_users B ON B.id=A.id"
        $sTable = "$this->table A INNER JOIN tb_produtos_produtos B ON A.id_produto = B.id ";

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
                $row[] = "<div align=left><a href='productreview/edit/id/". $aRow['id'] ."'>" . $aRow ['date'] . "</a></div>";

                //DATA
                $row[] = "<div align=left><a href='productreview/edit/id/". $aRow['id'] ."'>" . $aRow ['data'] . "</a></div>";

                //NOME
                $row[] = "<div align=left><a href='productreview/edit/id/". $aRow['id'] ."'>" . $aRow ['nome'] . "</a></div>";

                //CHAMADA
                $row[] = "<div align=left><a href='productreview/edit/id/". $aRow['id'] ."'>" . $aRow ['chamada'] . "</a></div>";

                //PRODUTO
                $row[] = "<div align=left><a href='productreview/edit/id/". $aRow['id'] ."'>" . $aRow ['produto'] . "</a></div>";

                //NOTA
                $row[] = "<div align=left><a href='productreview/edit/id/". $aRow['id'] ."'>" . $aRow ['nota'] . "</a></div>";

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
                $row[] = '<div align="left"><a href="productreview/edit/id/'. $aRow['id'] .'"><i class="s12 icomoon-icon-pencil"></i></a> '.$excluir.'</div>';

                $output['aaData'][] = $row;
            }
        }
        
        $output['queryExport'] = $queryExport;

        echo json_encode($output);
    }

    private function validaFormulario($form) {

        $resposta = new \stdClass();
        $resposta->return = true;

        if ($form->id_pedido != "")
            $pedido = $this->DB_num_rows('SELECT * FROM tb_pedidos_pedidos WHERE id = '.$form->id_pedido);

        if ($form->id == "" && $form->id_pedido != "" && $form->id_produto != "")
            $pedido_produto = $this->DB_num_rows('SELECT * FROM tb_produtos_avaliacoes WHERE id_produto = '.$form->id_produto.' AND id_pedido = '.$form->id_pedido);


        if ($form->id_produto == "") {
            $resposta->type = "validation";
            $resposta->message = "Escolha um produto";
            $resposta->field = "id_produto";
            $resposta->return = false;
            return $resposta;
        } else if ($form->id_pedido == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "id_pedido";
            $resposta->return = false;
            return $resposta;
        } else if(!$pedido){
            $resposta->type = "attention";
            $resposta->message = "O nº do pedido é inválido";
            $resposta->return = false;
            return $resposta;
        } else if(isset($pedido_produto) && $pedido_produto){
            $resposta->type = "attention";
            $resposta->message = "Já existe uma avaliação para esse produto neste pedido!";
            $resposta->time = 5000;
            $resposta->return = false;
            return $resposta;
        } else if ($form->nome == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "nome";
            $resposta->return = false;
            return $resposta;
        } else if ($form->chamada == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "chamada";
            $resposta->return = false;
            return $resposta;
        } else if ($form->avaliacao == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "avaliacao";
            $resposta->return = false;
            return $resposta;
        } else if ($form->nota == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "nota";
            $resposta->return = false;
            return $resposta;
        } else if ($form->id_estado == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "id_estado";
            $resposta->return = false;
            return $resposta;
        } else if ($form->id_cidade == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "id_cidade";
            $resposta->return = false;
            return $resposta;
        } else {
            return $resposta;
        }
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
