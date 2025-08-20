<?php

use System\Core\Bootstrap;

Crm::setAction();

class Crm extends Bootstrap {

    public $module = "";
    public $permissao_ref = "clientes";
    public $table = "tb_crm_crm";

    function __construct() {
        parent::__construct();
        if (!isset($_SESSION['admin_logado'])) {
            header("Location: " . $this->system_path . "login");
            exit;
        }
        $this->getAllPermissions();

        $this->module_icon = "icomoon-icon-users";
        $this->module_link = "crm";
        $this->module_title = "CRM";
        $this->retorno = "crm";
    }

    private function indexAction() {

        if (!$this->permissions[$this->permissao_ref]['ler']) {
            $this->noPermission();
        }

        $this->list = $this->DB_fetch_array("SELECT A.*, DATE_FORMAT(A.ultima_atualizacao, '%d/%m/%Y às %H:%i') ultima, DATE_FORMAT(A.data, '%d/%m/%Y às %H:%i') data, B.nome vendedor  FROM $this->table A INNER JOIN tb_admin_users B ON B.id = A.id_user");

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

            $query = $this->DB_fetch_array("SELECT A.*, DATE_FORMAT(A.ultima_atualizacao, '%d/%m/%Y às %H:%i') ultima_atualizacao, DATE_FORMAT(A.data, '%d/%m/%Y às %H:%i') data  FROM $this->table A INNER JOIN tb_admin_users B ON B.id = A.id_user WHERE A.id = $this->id");

            $this->registro = $query->rows[0];
        }

        $query = $this->DB_fetch_array("SELECT * FROM tb_admin_users WHERE stats=1");
        $this->users = $query;

        $this->renderView($this->getModule(), "edit");
    }

    private function delAction() {
        if (!$this->permissions[$this->permissao_ref]['excluir'])
            exit();

        $id = $this->getParameter("id");

        $dados = $this->DB_fetch_array("SELECT * FROM $this->table WHERE id = $id");

        $this->inserirRelatorio("Apagou CRM: [" . $dados->rows[0]['nome'] . "] id: [$id]");
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

                unset($data->id);
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
                    $this->inserirRelatorio("Cadastrou CRM: [" . $data->nome . "]");
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

                $fields_values[] = "ultima_atualizacao=NOW()";

                $query = $this->DB_update($this->table, implode(',', $fields_values) . " WHERE id=" . $data->id);
                if ($query) {
                    $resposta->type = "success";
                    $resposta->message = "Registro alterado com sucesso!";
                    $this->inserirRelatorio("Alterou CRM: [" . $data->nome . "]");
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
        } else if ($form->id_user == "") {
            $resposta->type = "validation";
            $resposta->message = "Selecione um vendedor";
            $resposta->field = "id_user";
            $resposta->return = false;
            return $resposta;
        } else if ($form->email == "" && $form->telefone == "" && $form->cpf_cnpj == "") {
            $resposta->type = "attention";
            $resposta->message = "Você precisa preencher ao menos um campo entre email, telelfone, cpf ou cnpj";
            $resposta->return = false;
            $resposta->time = 5000;
            return $resposta;
        } else if ($form->email != "" && $this->validaEmail($form->email) == 0) {
            $resposta->type = "validation";
            $resposta->message = "Formato de Email Incorreto";
            $resposta->field = "email";
            $resposta->return = false;
            return $resposta;
        } else {
            return $resposta;
        }
    }

    private function exportCrmAction() {

        if (!$this->permissions[$this->permissao_ref]['ler'])
            $this->noPermission();

        header('Content-type: application/x-msdownload');
        header("Content-type: application/vnd.ms-excel");
        header("Content-type: application/force-download");
        header("Content-Disposition: attachment; filename=crm-" . date("Y-m-d") . ".xls");
        header("Pragma: no-cache");

        $this->dados = $this->DB_fetch_array("SELECT A.*, B.nome usuario, DATE_FORMAT(A.ultima_atualizacao, '%d/%m/%Y às %H:%i') ultima_atualizacao, DATE_FORMAT(A.data, '%d/%m/%Y às %H:%i') data  FROM $this->table A INNER JOIN tb_admin_users B ON B.id = A.id_user");

        $this->renderView($this->getModule(), "crm");
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
