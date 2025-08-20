<?php

use System\Core\Bootstrap;

Shipment::setAction();

class Shipment extends Bootstrap {
    public $module = "";
    public $permissao_ref = "expedicoes";
    public $table = "tb_expedicoes_transportadoras";

    function __construct() {
        parent::__construct();
        if (!isset($_SESSION['admin_logado'])) {
            header("Location: " . $this->system_path . "login");
            exit;
        }
        $this->getAllPermissions();

        $this->module_icon = "icomoon-icon-office";
        $this->module_link = "shipment";
        $this->module_title = "Remessas";
        $this->retorno = "shipment";
    }

    private function indexAction() {
        if (!$this->permissions[$this->permissao_ref]['ler']) {
            $this->noPermission();
        }

        $this->list = $this->DB_fetch_array("SELECT * FROM {$this->table} ORDER BY nome ASC");
        $this->renderView($this->getModule(), "index");
    }

    private function editAction() {
        $this->id = $this->getParameter("id");

        if ($this->id == "") {
            //new
            if (!$this->permissions[$this->permissao_ref]['gravar']) {
                $this->noPermission();
            }

            $campos = $this->DB_columns($this->table);
            foreach ($campos as $campo) {
                $this->registro[$campo] = "";
            }
        } else {
            //edit
            if (!$this->permissions[$this->permissao_ref]['editar']) {
                $this->noPermission();
            }

            $query = $this->DB_fetch_array(
                "SELECT * FROM {$this->table} WHERE id = {$this->id}"
            );
            $this->registro = ($query->num_rows) ? $query->rows[0] : [];
        }

        $this->renderView($this->getModule(), "edit");
    }

    private function delAction() {
        if (!$this->permissions[$this->permissao_ref]['excluir']) {
            exit();
        }

        $id = $this->getParameter("id");
        $dados = $this->DB_fetch_array("SELECT * FROM {$this->table} WHERE id = {$id} AND NOT EXISTS (SELECT 1 FROM tb_expedicoes_expedicoes WHERE id_transportadora = {$id})");
        if ($dados->num_rows) {
            $this->DB_delete($this->table, "id = {$id}");
            $this->inserirRelatorio("Apagou a remessa: [{$dados->rows[0]['nome']}] id: [{$id}]");
            echo $this->getModule();
        } else {
            echo json_encode([
                'error' => "ID {$id} não pode ser removido ou não existe"
            ]);
        }
    }

    private function saveAction() {
        $formulario = $this->formularioObjeto($_POST);
        $validacao = $this->validaFormulario($formulario);
        if (!$validacao->return) {
            echo json_encode($validacao);
            return;
        }

        $resposta = new \stdClass();
        $data = $this->formularioObjeto($_POST, $this->table);

        if ($formulario->id == "") {
            //new
            if (!$this->permissions[$this->permissao_ref]['gravar']) {
                exit();
            }

            foreach ($data as $key => $value) {
                $fields[] = $key;
                if ($value == "NULL") $values[] = "{$value}";
                else $values[] = "'{$value}'";
            }

            $query = $this->DB_insert($this->table, implode(',', $fields), implode(',', $values));
            if ($query->query) {
                $resposta->type = "success";
                $resposta->message = "Registro cadastrado com sucesso!";
                $this->inserirRelatorio("Cadastrou transportadora: [{$data->nome}]");
            } else {
                $resposta->type = "error";
                $resposta->message = "Aconteceu um erro no sistema, favor tentar novamente mais tarde!";
            }
        } else {
            //edit
            if (!$this->permissions[$this->permissao_ref]['editar']) {
                exit();
            }

            foreach ($data as $key => $value) {
                if ($value == "NULL") $fields_values[] = "{$key}={$value}";
                else $fields_values[] = "{$key}='{$value}'";
            }

            $query = $this->DB_update($this->table, implode(',', $fields_values) . " WHERE id={$data->id}");
            if ($query) {
                $resposta->type = "success";
                $resposta->message = "Registro alterado com sucesso!";
                $this->inserirRelatorio("Alterou a remessa: [{$data->nome}]");
            } else {
                $resposta->type = "error";
                $resposta->message = "Aconteceu um erro no sistema, favor tentar novamente mais tarde!";
            }
        }
        echo json_encode($resposta);
    }

    private function validaFormulario($form) {
        $resposta = new \stdClass();
        $resposta->return = true;

        if ($form->nome == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "nome";
            $resposta->return = false;
        }
        return $resposta;
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
