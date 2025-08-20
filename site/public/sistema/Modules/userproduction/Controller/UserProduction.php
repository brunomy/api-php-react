<?php

use System\Core\Bootstrap;
use System\Libs\ContaAzul;

UserProduction::setAction();

class UserProduction extends Bootstrap {

    public $module = "";
    public $permissao_ref = "departamentos";
    public $table = "dp_users";
    public $table2 = "dp_departamentos";
    public $table_table2 = "dp_user_departamento";

    function __construct() {
        parent::__construct();
        if (!isset($_SESSION['admin_logado'])) {
            header("Location: " . $this->system_path . "login");
            exit;
        }
        $this->getAllPermissions();

        $this->module_icon = "icomoon-icon-users";
        $this->module_link = "userproduction";
        $this->module_title = "Usuários de Produção";
        $this->retorno = "userproduction";
    }

    private function indexAction() {

        if (!$this->permissions[$this->permissao_ref]['ler']) {
            $this->noPermission();
        }

        $this->list = $this->DB_fetch_array("SELECT 
                A.*, 
                GROUP_CONCAT(B.nome ORDER BY B.nome SEPARATOR ', ') AS departamentos
            FROM $this->table A
            LEFT JOIN $this->table_table2 AB ON A.id = AB.id_user
            LEFT JOIN $this->table2 B ON AB.id_departamento = B.id
            WHERE A.deleted_at IS NULL AND B.deleted_at IS NULL AND B.stats = 1
            GROUP BY A.id
        ");

        $this->renderView($this->getModule(), "index");
    }

    private function editAction() {
        $this->id = $this->getParameter("id");

        $this->departamentos = $this->DB_fetch_array("SELECT id, nome FROM $this->table2 WHERE deleted_at IS NULL AND stats = 1 ORDER BY ordem");

        if ($this->id == "") {
            //new            
            if (!$this->permissions[$this->permissao_ref]['gravar'])
                $this->noPermission();

            $campos = $this->DB_columns($this->table);
            $this->senha = '';

            foreach ($campos as $campo) {
                $this->registro[$campo] = "";
            }

        } else {
            //edit
            if (!$this->permissions[$this->permissao_ref]['editar'] && $_SESSION['admin_id'] != $this->id)
                $this->noPermission();

            $query = $this->DB_fetch_array("SELECT * FROM $this->table WHERE id = $this->id");
            $this->registro = $query->rows[0];

            $this->senha = $this->desembaralhar($this->registro['senha']);

            $this->departamentos_usuario = $this->DB_fetch_array("SELECT id_departamento FROM $this->table_table2 WHERE id_user = $this->id", "form");
        }

        $this->grupos = $this->DB_fetch_array("SELECT * FROM tb_admin_grupos WHERE id <> 1", "form");

        $this->renderView($this->getModule(), "edit");
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

        if ($dados->num_rows) {
            $query = $this->DB_update($this->table, "deleted_at = NOW() WHERE id ='{$id}'");
            $this->inserirRelatorio("Apagou usuário: [" . $dados->rows[0]['nome'] . "] id: [$id]");
        }

        echo $this->getModule();
    }

    private function saveAction() {
        $formulario = $this->formularioObjeto($_POST);
        $validacao = $this->validaFormulario($formulario);
        if (!$validacao->return) {
            ob_clean();
            echo json_encode($validacao);
        } else {
            $resposta = new \stdClass();
            $data = $this->formularioObjeto($_POST, $this->table);

            if ($formulario->id == "") {
                //criar
                if (!$this->permissions[$this->permissao_ref]['gravar'])
                    exit();

                $data->senha = $this->embaralhar($data->senha);

                foreach ($data as $key => $value) {
                    $fields[] = $key;
                    $values[] = "'$value'";
                }

                $query = $this->DB_insert($this->table, implode(',', $fields), implode(',', $values));
                if ($query->query) {
                    foreach ($formulario->departamentos as $departamento) {
                        $this->DB_insert($this->table_table2, "id_departamento, id_user", $departamento . "," . $query->insert_id);
                    }

                    $resposta->type = "success";
                    $resposta->message = "Registro cadastrado com sucesso!";
                    $this->inserirRelatorio("Cadastrou usuário: [" . $data->nome . "]");
                } else {
                    $resposta->type = "error";
                    $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
                }
            } else {
                //alterar

                if (!$this->permissions[$this->permissao_ref]['editar'] && $_SESSION['admin_id'] != $formulario->id)
                    exit();

                $data->senha = $this->embaralhar($data->senha);

                foreach ($data as $key => $value) {
                    $fields_values[] = "$key='$value'";
                }

                $query = $this->DB_update($this->table, implode(',', $fields_values) . " WHERE id=" . $data->id);
                if ($query) {
                    $this->DB_delete($this->table_table2, "id_user=" . $data->id);
                    foreach ($formulario->departamentos as $departamento) {
                        $this->DB_insert($this->table_table2, "id_departamento, id_user", $departamento . "," . $data->id);
                    }

                    $resposta->type = "success";
                    $resposta->message = "Registro alterado com sucesso!";
                    $this->inserirRelatorio("Alterou usuário: [" . $data->nome . "]");
                } else {
                    $resposta->type = "error";
                    $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
                }
            }

            ob_clean();
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
        } else if ($form->descricao == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "descricao";
            $resposta->return = false;
            return $resposta;
        } else if ($form->email == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "email";
            $resposta->return = false;
            return $resposta;
        } else if ($form->telefone == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "telefone";
            $resposta->return = false;
            return $resposta;
        } else if ($form->usuario == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "usuario";
            $resposta->return = false;
            return $resposta;
        } else if ($form->id == "" && $form->senha = "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "senha";
            $resposta->return = false;
            return $resposta;
        } else if(count($form->departamentos) == 0) {
            $resposta->type = "error";
            $resposta->message = "Preencha o campo Departamento";
            $resposta->field = "departamentos";
            $resposta->return = false;
            return $resposta;
        } else if($form->permissao == null) {
            $resposta->type = "error";
            $resposta->message = "Preencha o campo Permissões";
            $resposta->field = "permissao";
            $resposta->return = false;
            return $resposta;
        } 
        
        else {
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
