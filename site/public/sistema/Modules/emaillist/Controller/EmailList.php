<?php

use System\Core\Bootstrap;

EmailList::setAction();

class EmailList extends Bootstrap {

    public $module = "";
    public $permissao_ref = "lista-emails";
    public $table = "tb_listas_listas";

    function __construct() {
        parent::__construct();
        if (!isset($_SESSION['admin_logado'])) {
            header("Location: " . $this->system_path . "login");
            exit;
        }
        $this->getAllPermissions();

        $this->module_icon = "minia-icon-list-3";
        $this->module_link = "emaillist";
        $this->module_title = "Minhas Listas";
        $this->retorno = "emailList";
    }

    private function indexAction() {
        if (!$this->permissions[$this->permissao_ref]['ler'])
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
        } else {
            //edit
            if (!$this->permissions[$this->permissao_ref]['editar'])
                $this->noPermission();

            $query = $this->DB_fetch_array("SELECT * FROM $this->table WHERE id = $this->id");
            $this->registro = $query->rows[0];

            $this->listas = $this->DB_fetch_array("SELECT * FROM tb_listas_listas WHERE id <> $this->id", "form");
        }

        $this->renderView($this->getModule(), "edit");
    }

    private function datatableEmailsAction() {

        if (!$this->permissions[$this->permissao_ref]['ler'])
            $this->noPermission();

        $lista = $this->getParameter("lista");

        /*
         * CONFIGURAÇÕES INICIAIS
         */

        //defina os campos da tabela
        $aColumns = array('A.nome', 'A.email', 'DATE_FORMAT(A.nascimento, "%d/%m/%Y") nascimento', 'A.id');

        //defina os campos que devem ser usados para a busca
        $aColumnsWhere = array('A.nome', 'A.email', 'DATE_FORMAT(A.nascimento, "%d/%m/%Y")', 'A.id');

        //defina o coluna índice
        $sIndexColumn = "A.id";

        //defina o nome da tabela, ou faça aqui seu INNER JOIN, LEFT JOIN, RIGHT JOIN
        //Ex: "tb_emails_emails A INNER JOIN tb_admin_users B ON B.id=A.id"
        $sTable = "tb_emails_emails A INNER JOIN tb_listas_listas_has_tb_emails_emails B ON B.id_email = A.id AND B.id_lista = $lista";

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
            $sOrder
            $sLimit");
        if ($sQuery->num_rows)
            $rResult = $sQuery->rows;


        $sQuery = $this->DB_num_rows(" SELECT $sIndexColumn
            FROM   $sTable
            $sWhere
        ");
        $iFilteredTotal = $sQuery;


        $sQuery = $this->DB_num_rows(" SELECT $sIndexColumn
            FROM   $sTable
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

                $indice = 3;

                $aColumns[$indice] = explode(".", $aColumns[$indice]);
                $aColumns[$indice] = end($aColumns[$indice]);
                $id = $aRow[$aColumns[$indice]];

                //NOME
                $row[] = '<div align="left"><a href="email/edit/id/' . $id . '">' . $aRow [$aColumns[0]] . '</a></div>';

                //E-MAIL
                $row[] = '<div align="left"><a href="email/edit/id/' . $id . '">' . $aRow [$aColumns[1]] . '</a></div>';

                //DATA DE NASCIMENTO
                $nascimento = explode(" ", $aColumns[2]);
                $nascimento = end($nascimento);
                $row[] = '<div align="left"><a href="email/edit/id/' . $id . '">' . $aRow [$nascimento] . '</a></div>';

                //AÇÃO
                if ($this->permissions[$this->permissao_ref]['excluir']) {
                    $row[] = '<div align="center"><a href="email/edit/id/' . $id . '"><span class="s12 icomoon-icon-pencil"></span></a> <span style="cursor:pointer" class="bt_system_delete" data-retorno="' . $this->getModule() . '/edit/id/' . $lista . '" data-id="' . $id . '" data-id-lista="' . $lista . '" data-controller="email"><span class="s12 icomoon-icon-remove"></span></span> <input data-controller="email" data-id-lista="' . $lista . '"  type="checkbox" id="del_' . $id . '" value="' . $id . '" class="del-this checkbox" /></div>';
                } else {
                    $row[] = '<div align="center"><a href="email/edit/id/' . $id . '"><span class="s12 icomoon-icon-pencil"></span></a></div>';
                }

                $output['aaData'][] = $row;
            }
        }

        echo json_encode($output);
    }

    private function importListAction() {
        if (!$this->permissions[$this->permissao_ref]['gravar'])
            $this->noPermission();

        $formulario = $this->formularioObjeto($_POST);

        $resposta = new \stdClass ();

        if ($formulario->id_lista_import == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "id_lista_import";
            $resposta->return = false;
        } else {
            $query = $this->DB_fetch_array("SELECT id_email FROM tb_listas_listas_has_tb_emails_emails A INNER JOIN tb_listas_listas B ON B.id = A.id_lista WHERE A.id_lista = $formulario->id_lista_import");
            if ($query->num_rows) {
                foreach ($query->rows as $email) {

                    $verifica = $this->DB_fetch_array("SELECT * FROM tb_listas_listas_has_tb_emails_emails WHERE id_email = {$email['id_email']} AND id_lista = $formulario->id_lista");
                    if (!$verifica->num_rows) {
                        $insertData [] = "($formulario->id_lista, {$email['id_email']})";
                    }
                }
                $this->DB_connect();
                $query = $this->mysqli->query("INSERT INTO tb_listas_listas_has_tb_emails_emails (id_lista,id_email) VALUES " . implode(',', $insertData));
                $this->DB_disconnect();
            }

            if ($query) {
                $resposta->type = "success";
                $this->inserirRelatorio("Importou lista para lista: [" . $formulario->id_lista_import . "]");
                $resposta->message = "Lista importada com sucesso!";
            } else {
                $resposta->type = "error";
                $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
            }
        }

        echo json_encode($resposta);
    }

    private function exportListAction() {
        if (!$this->permissions[$this->permissao_ref]['ler'])
            $this->noPermission();


        $data = array();
        $lista = "";

        $id = $this->getParameter("id");

        if ($id) {
            $query = $this->DB_fetch_array("SELECT A.nome, A.email, DATE_FORMAT(A.nascimento, '%d/%m/%Y') nascimento, C.nome lista FROM tb_emails_emails A INNER JOIN tb_listas_listas_has_tb_emails_emails B ON B.id_email = A.id INNER JOIN tb_listas_listas C ON C.id = B.id_lista WHERE B.id_lista = $id ORDER BY A.nome");
            if ($query->num_rows) {
                $data = $query->rows;
                $lista = $query->rows[0]['lista'];
            }
        }

        if ($lista == "") {
            echo '<script>javascript:history.back() </script>';
            exit;
        }

        //FUNÇÃO TRANSFORMA OBJETO EM ARRAY
        function objectToArray($object) {
            $arr = array();
            for ($i = 0; $i < count($object); $i++) {

                $arr[] = get_object_vars($object[$i]);
            }
            return $arr;
        }

        //RETIRAR ID E LISTA DO ARRAY
        foreach ($data as $data) {
            unset($data['id'], $data['lista']);
            //RECONSTROI ARRAY PARA EXPORTAÇÃO
            $registros[] = $data;
        }

        function array_para_csv(array &$array) {
            if (count($array) == 0) {
                return null;
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

        cabecalho_download_csv($this->formataUrlAmiga($lista) . "-emails-" . date("Y-m-d") . ".csv");
        echo array_para_csv($registros);
        die();
    }

    private function importEmailAction() {

        $formulario = $this->formularioObjeto($_POST);
        $flag = true;
        $resposta = new \stdClass ();

        if ($_FILES ['csv'] ['tmp_name'] == "") {
            $resposta->type = "error";
            $resposta->message = 'Escolha um arquivo do formato CSV!';
            echo json_encode($resposta);
            exit();
        }

        $handle = @fopen($_FILES ['csv'] ['tmp_name'], "r");

        $upload = $this->uploadFile("csv", array(
            "csv"
        ));
        if ($upload->return) {

            // SE OS CAMPOS DO ARQUIVO CSV FOREM SEPARADOS POR VÍRGULA
            while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
                if (isset($data [0]) && isset($data [1])) {

                    if ($this->validaEmail($data [0]) != 0) {
                        $emailCSV = $data [0];
                        $nomeCSV = $data [1];
                    } else {
                        $nomeCSV = $data [0];
                        $emailCSV = $data [1];
                    }

                    if (isset($data [2]) && $data[2] != "") {
                        if ($this->checkdate($data [2]))
                            $nascimento = $data [2];
                    }

                    $fields = array(
                        'nome',
                        'email'
                    );
                    $values = array(
                        "'$nomeCSV'",
                        "'$emailCSV'"
                    );

                    if (isset($nascimento) && $nascimento != "") {
                        array_push($fields, 'nascimento');
                        array_push($values, "'" . $this->formataDataDeMascara($nascimento) . "'");
                    }

                    if ($this->validaEmail($emailCSV)) {

                        // VERIFICA SE E-MAIL EXISTE, CASO NÃO EXISTA INSERE, CASO EXISTA ATRIBUI SUA ID A $idEmail
                        $verifica = $this->DB_fetch_array("SELECT * FROM tb_emails_emails WHERE email = '$emailCSV'");
                        if (!$verifica->num_rows) {
                            $query = $this->DB_insert('tb_emails_emails', implode(',', $fields), implode(',', $values));
                            $idEmail = $query->insert_id;
                        } else {
                            $idEmail = $verifica->rows [0] ['id'];
                        }

                        if (isset($formulario->id_lista)) {
                            // VERIFICA SE EMAIL JÁ ESTÁ RELACIONADO A LISTA ATUAL, CASO ESTEJA NÃO FAZ NADA, CASO NÃO ESTEJA RELACIONA
                            $verifica = $this->DB_fetch_array("SELECT * FROM tb_listas_listas_has_tb_emails_emails WHERE id_email = $idEmail AND id_lista = $formulario->id_lista");
                            if (!$verifica->num_rows) {
                                $fields = array(
                                    'id_lista',
                                    'id_email'
                                );
                                $values = array(
                                    "'{$formulario->id_lista}'",
                                    "'{$idEmail}'"
                                );
                                $query = $this->DB_insert('tb_listas_listas_has_tb_emails_emails', implode(',', $fields), implode(',', $values));
                            }
                        }

                        unset($fields, $values, $nomeCSV, $emailCSV, $nascimento);
                    }

                    $resposta->type = "success";
                    $resposta->message = 'E-mails importados com sucesso!';
                    $this->inserirRelatorio("Importou lista de emails do arquivo: [" . $_FILES ['csv'] ['name'] . "]");
                    if (isset($formulario->retorno))
                        $resposta->retorno = $formulario->retorno;
                } else {
                    // SE FALHAR VERIFICAREMOS SE OS CAMPOS SÃO SEPARADOS POR PONTO E VÍRGULA
                    $flag = false;
                }
            }

            @fclose($handle);

            // SE OS CAMPOS DO ARQUIVO CSV FOREM SEPARADOS POR PONTO E VÍRGULA
            $handle = fopen($_FILES ['csv'] ['tmp_name'], "r");
            if ($flag == false) {
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    if (isset($data [0]) && isset($data [1])) {

                        if ($this->validaEmail($data [0]) != 0) {
                            $emailCSV = $data [0];
                            $nomeCSV = $data [1];
                        } else {
                            $nomeCSV = $data [0];
                            $emailCSV = $data [1];
                        }

                        if (isset($data [2]) && $data[2] != "") {
                            if ($this->checkdate($data [2]))
                                $nascimento = $data [2];
                        }

                        $fields = array(
                            'nome',
                            'email'
                        );
                        $values = array(
                            "'$nomeCSV'",
                            "'$emailCSV'"
                        );

                        if (isset($nascimento) && $nascimento != "") {
                            array_push($fields, 'nascimento');
                            array_push($values, "'" . $this->formataDataDeMascara($nascimento) . "'");
                        }

                        if ($this->validaEmail($emailCSV)) {

                            // VERIFICA SE E-MAIL EXISTE, CASO NÃO EXISTA INSERE, CASO EXISTA ATRIBUI SUA ID A $idEmail
                            $verifica = $this->DB_fetch_array("SELECT * FROM tb_emails_emails WHERE email = '$emailCSV'");
                            if (!$verifica->num_rows) {
                                $query = $this->DB_insert('tb_emails_emails', implode(',', $fields), implode(',', $values));
                                $idEmail = $query->insert_id;
                            } else {
                                $idEmail = $verifica->rows [0] ['id'];
                            }

                            // VERIFICA SE EMAIL JÁ ESTÁ RELACIONADO A LISTA ATUAL, CASO ESTEJA NÃO FAZ NADA, CASO NÃO ESTEJA RELACIONA
                            $verifica = $this->DB_fetch_array("SELECT * FROM tb_listas_listas_has_tb_emails_emails WHERE id_email = $idEmail AND id_lista = $formulario->id");
                            if (!$verifica->num_rows) {
                                $fields = array(
                                    'id_lista',
                                    'id_email'
                                );
                                $values = array(
                                    "'{$formulario->id}'",
                                    "'{$idEmail}'"
                                );
                                $query = $this->DB_insert('tb_listas_listas_has_tb_emails_emails', implode(',', $fields), implode(',', $values));
                            }

                            unset($fields, $values, $nomeCSV, $emailCSV, $nascimento);
                        }
                        $resposta->type = "success";
                        $resposta->message = 'E-mails importados com sucesso!';
                        $this->inserirRelatorio("Importou lista de emails do arquivo: [" . $_FILES ['csv'] ['name'] . "]");
                        if (isset($formulario->retorno))
                            $resposta->retorno = $formulario->retorno;
                    } else {
                        $resposta->type = "error";
                        $resposta->message = 'Não foi possível importar o arquivo!';
                        echo json_encode($resposta);
                        exit();
                    }
                }
            }

            @fclose($handle);

            echo json_encode($resposta);
        } else {
            $resposta->type = "error";
            $resposta->message = "Formato inválido!";
            echo json_encode($resposta);
        }
    }

    private function delAction() {
        if (!$this->permissions[$this->permissao_ref]['excluir'])
            exit();

        $id = $this->getParameter("id");
        if ($id == 1) {
            echo "error";
            exit();
        }

        $dados = $this->DB_fetch_array("SELECT * FROM $this->table WHERE id = $id");

        $this->inserirRelatorio("Apagou lista de e-mails: [" . $dados->rows[0]['nome'] . "] id: [$id]");
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

            if ($data->id == "") {
                //criar
                if (!$this->permissions[$this->permissao_ref]['gravar'])
                    exit();

                foreach ($data as $key => $value) {
                    $fields[] = $key;
                    $values[] = "'$value'";
                }

                $query = $this->DB_insert($this->table, implode(',', $fields), implode(',', $values));
                if ($query->query) {
                    $resposta->type = "success";
                    $resposta->message = "Registro cadastrado com sucesso!";
                    $this->inserirRelatorio("Cadastrou lista de e-mails: [" . $data->nome . "]");
                } else {
                    $resposta->type = "error";
                    $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
                }
            } else {
                //alterar
                if (!$this->permissions[$this->permissao_ref]['editar'])
                    exit();

                foreach ($data as $key => $value) {
                    $fields_values[] = "$key='$value'";
                }

                $query = $this->DB_update($this->table, implode(',', $fields_values) . " WHERE id=" . $data->id);
                if ($query) {
                    $resposta->type = "success";
                    $resposta->message = "Registro alterado com sucesso!";
                    $this->inserirRelatorio("Alterou lista de e-emails: [" . $data->nome . "]");
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
