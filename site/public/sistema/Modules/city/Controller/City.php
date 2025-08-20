<?php

use System\Core\Bootstrap;

City::setAction();

class City extends Bootstrap {

    public $module = "";
    public $permissao_ref = "configuracoes";
    public $table = "tb_config_cidades";

    function __construct() {
        parent::__construct();
        if (!isset($_SESSION['admin_logado'])) {
            header("Location: " . $this->system_path . "login");
            exit;
        }
        $this->getAllPermissions();

        $this->module_icon = "icomoon-icon-location-2";
        $this->module_link = "city";
        $this->module_title = "Cidades";
        $this->retorno = "city";
    }

    private function indexAction() {

        if (!$this->permissions[$this->permissao_ref]['ler']) {
            $this->noPermission();
        }

        $this->list = $this->DB_fetch_array("SELECT A.*, B.nome estado, CASE A.capital WHEN 1 THEN 'Sim' ELSE 'Não' END capital FROM $this->table A INNER JOIN tb_config_estados B ON B.id = A.id_estado ORDER BY A.nome");

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

            $this->produtos = $this->DB_fetch_array("SELECT * FROM tb_produtos_produtos A LEFT JOIN tb_produtos_has_tb_descontos B ON B.id_produto = A.id AND B.id_desconto = 0 ORDER BY A.nome");
        } else {
            //edit
            if (!$this->permissions[$this->permissao_ref]['editar'])
                $this->noPermission();

            $query = $this->DB_fetch_array("SELECT A.*, DATE_FORMAT(A.data, '%d/%m/%Y %H:%i') data FROM $this->table A WHERE A.id = $this->id");

            $this->registro = $query->rows[0];

            $this->produtos = $this->DB_fetch_array("SELECT * FROM tb_produtos_produtos A LEFT JOIN tb_produtos_has_tb_descontos B ON B.id_produto = A.id AND B.id_desconto = {$this->registro['id']} ORDER BY A.nome");
        }

        $this->estados = $this->DB_fetch_array("SELECT * FROM tb_config_estados ORDER BY nome");

        $this->renderView($this->getModule(), "edit");
    }

    private function delAction() {
        if (!$this->permissions[$this->permissao_ref]['excluir'])
            exit();

        $id = $this->getParameter("id");

        $dados = $this->DB_fetch_array("SELECT * FROM $this->table WHERE id = $id");

        $this->inserirRelatorio("Apagou cidade: [" . $dados->rows[0]['nome'] . "] id: [$id]");
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

            $data->cep_init = str_replace(Array(".", "-"), "", $data->cep_init);
            $data->cep_end = str_replace(Array(".", "-"), "", $data->cep_end);

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
                    $this->inserirRelatorio("Cadastrou cidade: [" . $data->nome . "]");
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
                    $this->inserirRelatorio("Alterou cidade: [" . $data->nome . "]");
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

        if (isset($form->id_estado) && $form->id_estado != "") {
            if ($form->id == "")
                $form->id = 0;
            $verifica = $this->DB_fetch_array("SELECT * FROM tb_config_cidades WHERE id_estado = $form->id_estado AND capital = 1 AND id != $form->id");
        }

        if ($form->cep_init != "" && $form->cep_end != "" && $form->id_estado != "") {
            $cep_init = str_replace(array(".", "-"), "", $form->cep_init);
            $cep_end = str_replace(array(".", "-"), "", $form->cep_end);
            $verificafaixa = $this->DB_fetch_array("SELECT * FROM tb_config_estados A WHERE A.cep_init <= '$cep_init' AND A.cep_end >= '$cep_end' AND A.id = $form->id_estado");
        }

        if ($form->id_estado == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "id_estado";
            $resposta->return = false;
            return $resposta;
        } else if ($verifica->num_rows AND $form->capital == 1) {
            $resposta->type = "attention";
            $resposta->message = "A cidade de [{$verifica->rows[0]['nome']}] já está como capital para este Estado";
            $resposta->return = false;
            $resposta->time = 5000;
            return $resposta;
        } else if ($form->nome == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "nome";
            $resposta->return = false;
            return $resposta;
        } else if ($form->cep_init == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "cep_init";
            $resposta->return = false;
            return $resposta;
        } else if ($form->cep_end == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "cep_end";
            $resposta->return = false;
            return $resposta;
        } else if (!$verificafaixa->num_rows) {
            $resposta->type = "validation";
            $resposta->message = "A faixa de CEP digitada não está dentro da faixa de CEP do Estado selecionado";
            $resposta->field = "cep_init";
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
