<?php

use System\Core\Bootstrap;

EletronicPoint::setAction();

class EletronicPoint extends Bootstrap {

    public $module = "";
    public $permissao_ref = "ponto-eletronico";
    public $table = "tb_ponto_eletronico";

    function __construct() {
        parent::__construct();
        if (!isset($_SESSION['admin_logado'])) {
            header("Location: " . $this->system_path . "login");
            exit;
        }
        $this->getAllPermissions();

        $this->module_icon = "icomoon-icon-clock-2";
        $this->module_link = "eletronicpoint";
        $this->module_title = "Ponto Eletrônico";
        $this->retorno = "eletronicpoint";
    }

    private function indexAction() {

        if (!$this->permissions[$this->permissao_ref]['ler']) {
            $this->noPermission();
        }

        $this->funcionarios = $this->DB_fetch_array("SELECT * FROM tb_admin_users A ORDER BY A.nome");

        $this->data = array();

        if($search = $this->getParameter("search")){
            $search = explode("_", $search);
            $this->data["de"] = (isset($search[0])) ? $this->formatDateUrl($search[0],1) : "";
            $this->data["ate"] = (isset($search[1])) ?$this->formatDateUrl($search[1],1) : "";
        }else{
            $this->data["de"] = "";
            $this->data["ate"] = "";
        }
        $this->data["id_user"] = ($this->getParameter("user")) ? $this->getParameter("user") : "";

        $this->renderView($this->getModule(), "index");
    }

    private function editAction() {
        $this->id = $this->getParameter("id");
        if ($this->id == "" AND $_SERVER['REQUEST_METHOD'] != 'POST') {
            $this->noPermission();
        } else if($_SERVER['REQUEST_METHOD'] == 'POST'){
            //edit
            if (!$this->permissions[$this->permissao_ref]['editar'])
                $this->noPermission();

            $this->id = $this->getParameter("id");

            $this->retorno .= "/index/user/".$_POST["user_id"];

            $campos = $this->DB_columns($this->table);
            foreach ($campos as $campo) {
                $this->registro[$campo] = "";
            }

            $this->registro["funcionario"] = $_POST["user_name"];
            $this->registro["id_user"] = $_POST["user_id"];
            $this->registro["data"] = $_POST["date"];
            $this->registro["almoco"] = $_POST["lunch"];
            $this->registro["minutos_dia"] = $_POST["minutos_dia"];
            $this->registro["hora_contrato"] =  $_POST["hora_contrato"];
            $this->registro["identificador"] =  $_POST["identificador"];
            $this->registro["ip"] = "Lançado manualmente";
            $this->registro["hora"] = "";

        } else {
            //edit
            if (!$this->permissions[$this->permissao_ref]['editar'])
                $this->noPermission();

            if($user = $this->getParameter("user")){
                $this->retorno .= "/index/user/$user";
                if($search = $this->getParameter("search"))
                    $this->retorno .= "/search/$search";
            }


            $query = $this->DB_fetch_array("SELECT A.*, DATE_FORMAT(A.data, '%d/%m/%Y') data, DATE_FORMAT(A.data, '%H:%i:%s') hora, B.nome funcionario FROM $this->table A INNER JOIN tb_admin_users B ON A.id_user = B.id WHERE A.id = $this->id");

            $this->registro = $query->rows[0];

            $this->referencias = ($this->registro["almoco"]) 
            ? array("entrada" => "Entrada", "saida_almoco" => "Ida ao almoço", "volta_almoco" => "Volta do almoço", "saida" => "Saída")
            : array("entrada" => "Entrada", "saida" => "Saída");
        }

        $this->renderView($this->getModule(), "edit");
    }

    private function delAction() {
        if (!$this->permissions[$this->permissao_ref]['excluir'])
            exit();

        $id = $this->getParameter("id");

        $dados = $this->DB_fetch_array("SELECT *, DATE_FORMAT(data, '%d/%m/%Y') data FROM $this->table WHERE id = $id");

        $this->inserirRelatorio("Apagou ponto eletrônico: [" . $dados->rows[0]['data'] . "] id: [$id] id_user [" . $dados->rows[0]['id_user'] . "]");
        $this->DB_delete($this->table, "id=$id");

        echo (isset($_POST["pontoeletronico"]) AND $_POST["retorno"] != "") ? $_POST["retorno"] : $this->getModule();
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

                $data->data = "{$this->formataDataDeMascara($formulario->data)} $formulario->hora";
                if($data->identificador == "Saída")
                    $data->identificador = "saida";
                else if($data->identificador == "Volta do Almoço")
                    $data->identificador = "volta_almoco";

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
                    $this->inserirRelatorio("Cadastrou ponto eletrônico: [". $query->insert_id ."]");
                } else {
                    $resposta->type = "error";
                    $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
                }
            } else {
                //alterar

                if (!$this->permissions[$this->permissao_ref]['editar'])
                    exit();

                $data->data = "{$this->formataDataDeMascara($formulario->data)} $formulario->hora";

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
                    $this->inserirRelatorio("Alterou ponto eletrônico: [" . $data->id . "]");
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

        if ($form->data == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "data";
            $resposta->return = false;
            return $resposta;
        } else if ($this->checkdate($form->data) == false) {
            $resposta->type = "validation";
            $resposta->message = "Data inválida";
            $resposta->field = "data";
            $resposta->return = false;
            return $resposta;
        } else if ($form->hora == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "hora";
            $resposta->return = false;
            return $resposta;
        } else if ($this->checktime($form->hora) == false) {
            $resposta->type = "validation";
            $resposta->message = "Hora inválida";
            $resposta->field = "hora";
            $resposta->return = false;
            return $resposta;
        } else {
            return $resposta;
        }
    }

    public function getViewAction(){
        $this->renderAjax($this->getModule(), "eletronicpoint");
    }

    public function formatDateUrl($date, $get = 0){
        return ($get) ? str_replace("-", "/", $date) : str_replace("/", "-", $date);
    }

    private function exportAction() {
        if (!$this->permissions[$this->permissao_ref]['ler'])
            $this->noPermission();

        $this->data = $this->formularioObjeto($_POST);

        header('Content-type: application/x-msdownload');
        header("Content-type: application/vnd.ms-excel");
        header("Content-type: application/force-download");
        header("Content-Disposition: attachment; filename=ponto-eletronico-" . date("Y-m-d") . ".xls");
        header("Pragma: no-cache");

        $this->renderExport($this->getModule(), "eletronicpoint");
    }

    public function horaParaMinutos($hora) {

        list($h, $m, $s) = explode(':', $hora);
        $hours = $h * 60;
        $mins = $m + $hours;

        if ($s > 30)
            $mins = $mins + 1;

        return $mins;
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
