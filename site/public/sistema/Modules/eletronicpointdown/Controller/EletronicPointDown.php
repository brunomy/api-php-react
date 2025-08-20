<?php

use System\Core\Bootstrap;

EletronicPointDown::setAction();

class EletronicPointDown extends Bootstrap {

    public $module = "";
    public $permissao_ref = "ponto-eletronico";
    public $table = "tb_ponto_eletronico_abates";

    function __construct() {
        parent::__construct();
        if (!isset($_SESSION['admin_logado'])) {
            header("Location: " . $this->system_path . "login");
            exit;
        }
        $this->getAllPermissions();

        $this->module_icon = "icomoon-icon-pushpin";
        $this->module_link = "eletronicpointdown";
        $this->module_title = "Ponto Eletrônico - Abates de horas";
        $this->retorno = "eletronicpointdown";
    }

    private function indexAction() {

        if (!$this->permissions[$this->permissao_ref]['ler']) {
            $this->noPermission();
        }

        $this->list = $this->DB_fetch_array("SELECT A.*, DATE_FORMAT(A.data, '%d/%m/%Y') data, B.nome FROM $this->table A INNER JOIN tb_admin_users B ON B.id = A.id_user ORDER BY B.nome, A.data");

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

            $query = $this->DB_fetch_array("SELECT *, DATE_FORMAT(A.data, '%d/%m/%Y') data, DATE_FORMAT(A.data_inicio, '%d/%m/%Y') data_inicio, DATE_FORMAT(A.data_fim, '%d/%m/%Y') data_fim FROM $this->table A INNER JOIN tb_admin_users B ON B.id = A.id_user WHERE A.id = $this->id");

            $this->registro = $query->rows[0];
        }

        $this->users = $this->DB_fetch_array("SELECT * FROM tb_admin_users ORDER BY nome");

        $this->renderView($this->getModule(), "edit");
    }

    private function delAction() {
        if (!$this->permissions[$this->permissao_ref]['excluir'])
            exit();

        $id = $this->getParameter("id");

        $dados = $this->DB_fetch_array("SELECT *, B.nome FROM $this->table A INNER JOIN tb_admin_users B ON B.id = A.id_user WHERE A.id = $id");

        $this->inserirRelatorio("Apagou abate de horas: [" . $dados->rows[0]['nome'] . "] id: [$id]");
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

            $funcionario = $this->DB_fetch_array("SELECT * FROM tb_admin_users A WHERE A.id = $data->id_user")->rows[0];

            if ($funcionario["almoco"] == 1) 
                $hour = $this->DB_fetch_array("SELECT SEC_TO_TIME((((TIME_TO_SEC( A.hora_almoco ) - TIME_TO_SEC( A.hora_entrada )) + (TIME_TO_SEC( A.hora_saida ) - TIME_TO_SEC( A.hora_retorno ))))) hora FROM tb_admin_users  A WHERE A.id = $data->id_user");
            else    
                $hour = $this->DB_fetch_array("SELECT SEC_TO_TIME((((TIME_TO_SEC( A.hora_saida ) - TIME_TO_SEC( A.hora_entrada ))))) hora FROM tb_admin_users  A WHERE A.id = $data->id_user");

            $minutos_dia = (int) $this->horaParaMinutos($hour->rows[0]['hora']);

            if($data->identificador == 'falta'){
                $data->data = $this->formataDataDeMascara($data->data);
                $data->minutos = ($data->justificada) ? 0 : $minutos_dia;
                unset($data->data_inicio);
                unset($data->data_fim);
            }else if($data->identificador == 'folga'){
                $data->data_inicio = $this->formataDataDeMascara($data->data_inicio);
                $data->data_fim = $this->formataDataDeMascara($data->data_fim);
                $datediff = strtotime($data->data_fim) - strtotime($data->data_inicio);
                $days = floor($datediff / (60 * 60 * 24));
                $days++;
                $data->minutos = $days*$minutos_dia;
                unset($data->data);
            }else{
                unset($data->data);
                unset($data->data_inicio);
                unset($data->data_fim);
            }

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
                if ($query->query) {
                    $resposta->type = "success";
                    $resposta->message = "Registro cadastrado com sucesso!";
                    $this->inserirRelatorio("Cadastrou abate de horas(" . $formulario->identificador . ") relacionado ao usuário id: [" . $formulario->id_user . "]");
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
                    $this->inserirRelatorio("Alterou abate de horas(" . $formulario->identificador . ") relacionado ao usuário id: [" . $formulario->id_user . "]");
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

        if ($form->id_user == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "id_user";
            $resposta->return = false;
            return $resposta;
        } else if (($form->identificador == "venda") AND $form->minutos == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "minutos";
            $resposta->return = false;
            return $resposta;
        } else if ($form->identificador == "folga" AND $form->data_inicio == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "data_inicio";
            $resposta->return = false;
            return $resposta;
        } else if ($form->identificador == "folga" AND $form->data_fim == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "data_fim";
            $resposta->return = false;
            return $resposta;
        } else if ($form->identificador == "venda" AND $form->valor == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "valor";
            $resposta->return = false;
            return $resposta;
        } else {
            return $resposta;
        }
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
