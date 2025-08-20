<?php

use System\Core\Bootstrap;

ProductDependences::setAction();

class ProductDependences extends Bootstrap {

    public $module = "";
    public $permissao_ref = "produtos-categorias";
    public $table = "tb_produtos_dependencias";
    public $table2 = "tb_produtos_pre_dep";

    function __construct() {
        parent::__construct();
        if (!isset($_SESSION['admin_logado'])) {
            header("Location: " . $this->system_path . "login");
        }
        $this->getAllPermissions();

        $this->module_icon = "icomoon-icon-tree";
        $this->module_link = "productdependences";
        $this->module_title = "Dependência de pré-requisitos";
        $this->retorno = "productdependences";
    }

    private function indexAction() {
        if (!$this->permissions[$this->permissao_ref]['ler']) {
            $this->noPermission();
        }

        $this->list = $this->DB_fetch_array("SELECT 
                A.*,
                GROUP_CONCAT(B.nome ORDER BY B.nome SEPARATOR ', ') AS requisitos
            FROM tb_produtos_dependencias A 
                LEFT JOIN tb_produtos_pre_dep ab ON ab.id_dep = A.id
                LEFT JOIN tb_produtos_prerequisitos B ON B.id = ab.id_pre
                    AND B.stats = 1
                    AND B.deleted_at IS NULL
            WHERE A.deleted_at IS NULL 
            GROUP BY A.id
        ORDER BY A.ordem");

        $this->renderView($this->getModule(), "index");
    }

    private function editAction() {
        $this->id = $this->getParameter("id");
        $this->requisitos = $this->DB_fetch_array("SELECT * FROM tb_produtos_prerequisitos WHERE stats = 1 AND deleted_at IS NULL ORDER BY nome");

        if ($this->id == "") {
            //new            
       
            if (!$this->permissions[$this->permissao_ref]['gravar'])
                $this->noPermission();

            $campos = $this->DB_columns($this->table);

            foreach ($campos as $campo) {
                $this->registro[$campo] = "";
            }

            $this->requisitos_selecionados = [];

        } else {
            //edit
            if (!$this->permissions[$this->permissao_ref]['editar'])
                $this->noPermission();

            $query = $this->DB_fetch_array("SELECT * FROM $this->table  WHERE id = $this->id", "form");
            $this->registro = $query->rows[0];

            $where = "";

            $this->requisitos_selecionados = $this->DB_fetch_array("SELECT * FROM tb_produtos_pre_dep WHERE id_dep = $this->id");
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
            $this->inserirRelatorio("Apagou a Dependência de produtos: [" . $dados->rows[0]['nome'] . "] id: [$id]");
        }

        echo $this->getModule();
    }

    private function saveAction() {
        $formulario = $this->formularioObjeto($_POST);
        $validacao = $this->validaFormulario($formulario);
        $requisitos_selected = $_POST['requisitos_selected'] ?? [];

        if (!$validacao->return) {
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
                    $fields = ['id_pre', 'id_dep'];

                    foreach ($requisitos_selected as $key => $value) {
                        $query2 = $this->DB_insert($this->table2, implode(',', $fields), implode(',', [$value, $query->insert_id]));

                        if(!$query2->query){
                            $resposta->type = "error";
                            $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
                            echo json_encode($resposta);
                            return;
                        }
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
                    $fields = ['id_pre', 'id_dep'];

                    $this->DB_delete($this->table2, 'id_dep = '.$data->id.';');

                    foreach ($requisitos_selected as $key => $value) {
                        $query2 = $this->DB_insert($this->table2, implode(',', $fields), implode(',', [$value, $data->id]));

                        if(!$query2->query){
                            $resposta->type = "error";
                            $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
                            echo json_encode($resposta);
                            return;
                        }
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
