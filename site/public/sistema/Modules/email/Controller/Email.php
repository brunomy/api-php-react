<?php

use System\Core\Bootstrap;

Email::setAction();

class Email extends Bootstrap {

    public $module = "";
    public $permissao_ref = "lista-emails";
    public $table = "tb_emails_emails";

    function __construct() {
        parent::__construct();
        if (!isset($_SESSION['admin_logado'])) {
            header("Location: " . $this->system_path . "login");
            exit;
        }
        $this->getAllPermissions();

        $this->module_icon = "entypo-icon-email";
        $this->module_link = "email";
        $this->module_title = "E-mails";
        $this->retorno = "email";
    }

    private function indexAction() {
        if (!$this->permissions[$this->permissao_ref]['editar'])
            $this->noPermission();

        $this->list = $this->DB_fetch_array("SELECT * FROM $this->table A ORDER BY A.nome");

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
            $this->listas = $this->DB_fetch_array("SELECT A.*, B.id_lista FROM tb_listas_listas A LEFT JOIN tb_listas_listas_has_tb_emails_emails B ON B.id_lista = A.id AND B.id_email = 0 ORDER BY A.nome", "form");
        } else {
            //edit
            if (!$this->permissions[$this->permissao_ref]['editar'])
                $this->noPermission();

            $query = $this->DB_fetch_array("SELECT *, CASE WHEN nascimento IS NULL THEN nascimento ELSE DATE_FORMAT(nascimento, '%d/%m/%Y') END nascimento FROM $this->table WHERE id = $this->id");
            $this->registro = $query->rows[0];
            $this->listas = $this->DB_fetch_array("SELECT A.*, B.id_lista FROM tb_listas_listas A LEFT JOIN tb_listas_listas_has_tb_emails_emails B ON B.id_lista = A.id AND B.id_email = $this->id ORDER BY A.nome", "form");
        }


        $this->renderView($this->getModule(), "edit");
    }

    private function saveAction() {
        $formulario = $this->formularioObjeto($_POST);
        $validacao = $this->validaFormulario($formulario);
        if (!$validacao->return) {
            echo json_encode($validacao);
        } else {
            $resposta = new \stdClass();
            $data = $this->formularioObjeto($_POST, $this->table);

            if (!isset($data->id) || $data->id == "") {
                //criar
                if (!$this->permissions[$this->permissao_ref]['gravar'])
                    exit();

                if ($data->nascimento == "")
                    $data->nascimento = "NULL";
                else
                    $data->nascimento = $this->formataDataDeMascara($data->nascimento);

                foreach ($data as $key => $value) {
                    $fields[] = $key;
                    if ($value == "NULL")
                        $values[] = "$value";
                    else
                        $values[] = "'$value'";
                }

                $flag = false;
                $existe = $this->DB_fetch_array("SELECT * FROM $this->table WHERE email = '$data->email'");
                if ($existe->num_rows) {
                    $idEmail = $existe->rows[0]['id'];
                    $query = new \stdClass();
                    $query->query = true;
                    $flag = true;
                } else {
                    $query = $this->DB_insert($this->table, implode(',', $fields), implode(',', $values));
                    $idEmail = $query->insert_id;
                }

                if ($query->query) {
                    if (isset($formulario->id_lista)) {
                        $existe = $this->DB_fetch_array("SELECT * FROM tb_listas_listas_has_tb_emails_emails WHERE id_lista = $formulario->id_lista AND id_email = $idEmail");
                        if (isset($formulario->id_lista) && $formulario->id_lista != "" && !$existe->num_rows) {
                            $this->DB_insert("tb_listas_listas_has_tb_emails_emails", "id_lista, id_email", "$formulario->id_lista, $idEmail");
                            $this->inserirRelatorio("Relacionou e-mail: [" . $data->email . "] à lista de id: [$formulario->id_lista]");
                        }
                    }

                    if (isset($formulario->listas)) {
                        foreach ($formulario->listas as $lista) {
                            $existe = $this->DB_fetch_array("SELECT * FROM tb_listas_listas_has_tb_emails_emails WHERE id_lista = $lista AND id_email = $idEmail");
                            if (!$existe->num_rows)
                                $insertData[] = "($lista, $idEmail)";
                        }
                        if (isset($insertData)) {
                            $this->DB_connect();
                            $this->mysqli->query("INSERT INTO tb_listas_listas_has_tb_emails_emails (id_lista,id_email) VALUES " . implode(',', $insertData));
                            $this->DB_disconnect();
                        }
                    }

                    $resposta->type = "success";
                    $resposta->message = "Registro cadastrado com sucesso!";
                    if (!$flag)
                        $this->inserirRelatorio("Cadastrou e-mail: [" . $data->email . "]");
                } else {
                    $resposta->type = "error";
                    $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
                }
            } else {
                //alterar
                if (!$this->permissions[$this->permissao_ref]['editar'])
                    exit();

                $existe = $this->DB_fetch_array("SELECT * FROM $this->table WHERE email = '$data->email' AND id != $data->id");
                if ($existe->num_rows) {
                    $resposta->type = "error";
                    $resposta->message = "Este e-mail já está cadastrado!";
                    echo json_encode($resposta);
                    exit();
                }

                if ($data->nascimento == "")
                    $data->nascimento = "NULL";
                else
                    $data->nascimento = $this->formataDataDeMascara($data->nascimento);

                foreach ($data as $key => $value) {
                    if ($value == "NULL")
                        $fields_values[] = "$key=$value";
                    else
                        $fields_values[] = "$key='$value'";
                }

                $query = $this->DB_update($this->table, implode(',', $fields_values) . " WHERE id=" . $data->id);
                if ($query) {

                    $this->DB_delete("tb_listas_listas_has_tb_emails_emails", "id_email = $data->id");

                    if (isset($formulario->listas)) {
                        foreach ($formulario->listas as $lista) {
                            $insertData[] = "($lista, $data->id)";
                        }
                        $this->DB_connect();
                        $this->mysqli->query("INSERT INTO tb_listas_listas_has_tb_emails_emails (id_lista,id_email) VALUES " . implode(',', $insertData));
                        $this->DB_disconnect();
                    }

                    $resposta->type = "success";
                    $resposta->message = "Registro alterado com sucesso!";
                    $this->inserirRelatorio("Alterou e-email: [" . $data->email . "]");
                } else {
                    $resposta->type = "error";
                    $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
                }
            }

            echo json_encode($resposta);
        }
    }

    private function listasByIdEmail($id = null) {
        if (!$this->permissions[$this->permissao_ref]['ler'])
            $this->noPermission();

        if ($id != null) {
            $query = $this->DB_fetch_array("SELECT B.nome nome FROM tb_listas_listas_has_tb_emails_emails A INNER JOIN tb_listas_listas B ON B.id = A.id_lista WHERE A.id_email = $id");
            if ($query->num_rows) {
                $result = "";
                $complemento = "";
                foreach ($query->rows as $row) {
                    $result .= $complemento . $row['nome'];
                    $complemento = ", ";
                }
                return $result;
            }
        }
    }

    private function datatableEmailsAction() {

        if (!$this->permissions[$this->permissao_ref]['ler'])
            $this->noPermission();

        /*
         * CONFIGURAÇÕES INICIAIS
         */

        //defina os campos da tabela
        $aColumns = array('A.nome', 'A.email', 'DATE_FORMAT(A.nascimento, "%d/%m/%Y") nascimento', 'B.id_lista', 'A.id');

        //defina os campos que devem ser usados para a busca
        $aColumnsWhere = array('A.nome', 'A.email', 'DATE_FORMAT(A.nascimento, "%d/%m/%Y")', 'B.id_lista', 'A.id');

        //defina o coluna índice
        $sIndexColumn = "A.id";

        //defina o nome da tabela, ou faça aqui seu INNER JOIN, LEFT JOIN, RIGHT JOIN
        //Ex: "tb_emails_emails A INNER JOIN tb_admin_users B ON B.id=A.id"
        $sTable = "tb_emails_emails A LEFT JOIN tb_listas_listas_has_tb_emails_emails B ON B.id_email = A.id";

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
                $campo = str_replace(array("DATE_FORMAT(", ","), "", $campo);

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
            if ($_POST['bSearchable_' . $i] == "true" && $_POST['sSearch_' . $i] != '') {
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
            GROUP BY A.id
            $sOrder
            $sLimit");
        if ($sQuery->num_rows)
            $rResult = $sQuery->rows;


        $sQuery = $this->DB_num_rows(" SELECT $sIndexColumn
            FROM   $sTable
            $sWhere
                
            GROUP BY A.id
        ");
        $iFilteredTotal = $sQuery;


        $sQuery = $this->DB_num_rows(" SELECT $sIndexColumn
            FROM   $sTable
                
            GROUP BY A.id
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

                $indice = 4;

                $aColumns[$indice] = explode(".", $aColumns[$indice]);
                $aColumns[$indice] = end($aColumns[$indice]);
                $id = $aRow[$aColumns[$indice]];

                //NOME
                $row[] = '<div align="left"><a href="' . $this->getModule() . '/edit/id/' . $id . '">' . $aRow [$aColumns[0]] . '</a></div>';

                //E-MAIL
                $row[] = '<div align="left"><a href="' . $this->getModule() . '/edit/id/' . $id . '">' . $aRow [$aColumns[1]] . '</a></div>';

                //DATA DE NASCIMENTO
                $nascimento = explode(" ", $aColumns[2]);
                $nascimento = end($nascimento);
                $row[] = '<div align="left"><a href="' . $this->getModule() . '/edit/id/' . $id . '">' . $aRow [$nascimento] . '</a></div>';

                //LISTA
                $row[] = '<div align="left"><a href="' . $this->getModule() . '/edit/id/' . $id . '">' . $this->listasByIdEmail($id) . '</a></div>';

                //AÇÃO
                if ($this->permissions[$this->permissao_ref]['excluir']) {
                    $row[] = '<div align="center"><a href="' . $this->getModule() . '/edit/id/' . $id . '"><span class="icon12 icomoon-icon-pencil"></span></a> <span style="cursor:pointer" class="bt_system_delete" data-controller="' . $this->getModule() . '" data-id="' . $id . '"><span class="icon12 icomoon-icon-remove"></span></span> <input type="checkbox" id="del_' . $id . '" value="' . $id . '" class="del-this checkbox" /></div>';
                } else {
                    $row[] = '<div align="center"><a href="' . $this->getModule() . '/edit/id/' . $id . '"><span class="icon12 icomoon-icon-pencil"></span></a></div>';
                }

                $output['aaData'][] = $row;
            }
        }

        echo json_encode($output);
    }

    private function exportEmailsAction() {

        if (!$this->permissions[$this->permissao_ref]['ler'])
            $this->noPermission();

        $data = array();
        $query = $this->DB_fetch_array("SELECT nome, email, DATE_FORMAT(nascimento, '%d/%m/%Y') nascimento FROM tb_emails_emails ORDER BY nome");
        if ($query->num_rows) {
            $data = $query->rows;
        }

        if (count($data) < 1) {
            echo '<script>javascript:history.back() </script>';
            exit;
        }

        function array_para_csv(array &$array) {
            if (count($array) == 0) {
                return null;
                exit;
            }
            ob_end_clean();
            $df = fopen("php://output", 'w');
            //fputcsv($df, array_keys(reset($array)));
            foreach ($array as $row) {
                fputcsv($df, $row);
            }
            fclose($df);
            return ob_get_clean();
        }

        function cabecalho_download_csv($filename) {
            // desabilitar cache
            $now = gmdate("D, d M Y H:i:s");
            header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
            header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
            header("Last-Modified: {$now} GMT");

            // forçar download  
            header("Content-Type: application/force-download");
            header("Content-Type: application/octet-stream");
            header("Content-Type: application/download");

            // disposição do texto / codificação
            header("Content-Disposition: attachment;filename={$filename}");
            header("Content-Transfer-Encoding: binary");
        }

        cabecalho_download_csv("emails-" . date("Y-m-d") . ".csv");
        echo array_para_csv($data);
        die();
    }

    private function relateListAction() {
        if (!$this->permissions[$this->permissao_ref]['editar'])
            exit;

        $formulario = $this->formularioObjeto($_POST);

        $resposta = false;

        if (!$formulario->lista) {
            $resposta = "Selecione uma Lista";
        } else {
            foreach ($formulario->i as $idEmail) {
                $verifica = $this->DB_fetch_array("SELECT * FROM tb_listas_listas_has_tb_emails_emails A INNER JOIN tb_listas_listas B ON B.id = A.id_lista WHERE A.id_email = $idEmail AND A.id_lista = {$formulario->lista}");

                if (!$verifica->num_rows) {

                    $fields = array('id_lista', 'id_email');
                    $values = array("{$formulario->lista}", "$idEmail");
                    $query = $this->DB_insert('tb_listas_listas_has_tb_emails_emails', implode(',', $fields), implode(',', $values));

                    if ($query->query)
                        $query = true;
                    else
                        $query = "Aconteceu algum erro";
                }
            }
        }

        echo $resposta;
    }

    private function delAction() {
        if (!$this->permissions[$this->permissao_ref]['excluir'])
            exit();

        $id = $this->getParameter("id");
        $lista = $this->getParameter("lista");

        $dados = $this->DB_fetch_array("SELECT * FROM $this->table WHERE id = $id");

        if ($lista != "undefined" && $lista != "") {
            $this->inserirRelatorio("Tirou e-mail: [" . $dados->rows[0]['email'] . "] id: [$id] da lista de id: [$lista]");
            $this->DB_delete("tb_listas_listas_has_tb_emails_emails", "id_email=$id AND id_lista = $lista");
        } else {
            $this->inserirRelatorio("Apagou e-mail: [" . $dados->rows[0]['email'] . "] id: [$id]");
            $this->DB_delete($this->table, "id=$id");
        }

        if (isset($_POST['retorno']) && $_POST['retorno'] != "")
            echo $_POST['retorno'];
        else
            echo $this->getModule();
    }

    private function validaFormulario($form) {
        $resposta = new \stdClass();
        $resposta->return = true;

        if ($form->nome == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "nome_email";
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
        } else if ($form->nascimento != "" && $this->checkdate($form->nascimento) == 0) {
            $resposta->type = "validation";
            $resposta->message = "Data inválida";
            $resposta->field = "nascimento";
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
