<?php

use System\Core\Bootstrap;

Department::setAction();

class Department extends Bootstrap {

    public $module = "";
    public $permissao_ref = "departamentos";
    public $table = "dp_departamentos";
    public $table_table2 = "dp_categoria_departamento";
    public $table2 = "tb_produtos_categorias";

    function __construct() {
        parent::__construct();
        if (!isset($_SESSION['admin_logado'])) {
            header("Location: " . $this->system_path . "login");
        }
        $this->getAllPermissions();

        $this->module_icon = "icomoon-icon-office";
        $this->module_link = "department";
        $this->module_title = "Departamentos de Produção";
        $this->retorno = "department";
    }

    private function indexAction() {
        if (!$this->permissions[$this->permissao_ref]['ler']) {
            $this->noPermission();
        }

        $this->list = $this->DB_fetch_array("SELECT 
                A.*,
                GROUP_CONCAT(DISTINCT B.nome ORDER BY B.nome SEPARATOR ', ') AS categorias
            FROM {$this->table} A 
                LEFT JOIN {$this->table_table2} ab ON ab.id_departamento = A.id
                LEFT JOIN {$this->table2} B ON B.id = ab.id_categoria 
                    AND B.stats = 1 
                    AND B.deleted_at IS NULL
            WHERE A.deleted_at IS NULL
            GROUP BY A.id
        ORDER BY A.ordem");

        $this->renderView($this->getModule(), "index");
    }

    private function editAction() {
        $this->id = $this->getParameter("id");
        $this->categorias = $this->DB_fetch_array("SELECT id, nome FROM {$this->table2} WHERE deleted_at IS NULL AND stats = 1 ORDER BY ordem");

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

            $query = $this->DB_fetch_array("SELECT * FROM $this->table  WHERE id = $this->id", "form");
            $this->registro = $query->rows[0];

            $where = "";

            $this->categorias_departamento = $this->DB_fetch_array("SELECT id_categoria FROM $this->table_table2 WHERE id_departamento = $this->id", "form");
        }

        $this->renderView($this->getModule(), "edit");
    }

    private function orderAction() {
        if (!$this->permissions[$this->permissao_ref]['excluir'])
            exit();
        $this->ordenarRegistros($_POST["array"], $this->table);
    }

    private function delAction() {
        if (!$this->permissions[$this->permissao_ref]['excluir'])
            exit();

        $id = $this->getParameter("id");
        
        if ($id == 1) {
            echo "error";
            exit;
        }

        $dados = $this->DB_fetch_array("SELECT * FROM $this->table WHERE id = $id");

        if ($dados->num_rows) {
            $query = $this->DB_update($this->table, "deleted_at = NOW() WHERE id ='{$id}'");

            $this->inserirRelatorio("Apagou o Departamento: [" . $dados->rows[0]['nome'] . "] id: [$id]");
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

                $ordem = $this->DB_fetch_array("SELECT MAX(id) ordem FROM $this->table");
                if ($ordem->num_rows) {
                    $data->ordem = $ordem->rows[0]['ordem'] + 1;
                } else {
                    $data->ordem = 0;
                }

                foreach ($data as $key => $value) {
                    $fields[] = $key;
                    if ($value == "NULL")
                        $values[] = "$value";
                    else
                        $values[] = "'$value'";
                }

                $query = $this->DB_insert($this->table, implode(',', $fields), implode(',', $values));
                if ($query->query) {
                    foreach ($formulario->categorias as $categoria) {
                        $this->DB_insert($this->table_table2, "id_departamento, id_categoria", $query->insert_id . "," . $categoria);
                    }

                    $resposta->type = "success";
                    $resposta->message = "Registro cadastrado com sucesso!";
                    $this->inserirRelatorio("Cadastrou dependência de produtos: [" . $data->nome . "], id [$query->insert_id]");
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
                    $this->DB_delete($this->id_departamento, "id_user=" . $data->id);

                    foreach ($formulario->categorias as $categoria) {
                        $this->DB_insert($this->table_table2, "id_departamento, id_categoria", $data->id . "," . $categoria);
                    }

                    $resposta->type = "success";
                    $resposta->message = "Registro alterado com sucesso!";
                    $this->inserirRelatorio("Alterou dependência de produtos: [" . $data->nome . "], id: [$data->id]");
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
