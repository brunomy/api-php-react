<?php

use System\Core\Bootstrap;

ProductCategory::setAction();

class ProductCategory extends Bootstrap {

    public $module = "";
    public $permissao_ref = "produtos-categorias";
    public $table = "tb_produtos_categorias";
    public $table2 = "tb_produtos_pre_cat";
    public $table3 = "dp_categoria_departamento";

    function __construct() {
        parent::__construct();
        if (!isset($_SESSION['admin_logado'])) {
            header("Location: " . $this->system_path . "login");
        }
        $this->getAllPermissions();

        $this->module_icon = "icomoon-icon-tree";
        $this->module_link = "productcategory";
        $this->module_title = "Categorias de Produtos";
        $this->retorno = "productcategory";
    }

    private function indexAction() {
        if (!$this->permissions[$this->permissao_ref]['ler']) {
            $this->noPermission();
        }

        $this->list = $this->DB_fetch_array("SELECT 
                A.*,
                GROUP_CONCAT(DISTINCT B.nome ORDER BY B.nome SEPARATOR ', ') AS requisitos,
                GROUP_CONCAT(DISTINCT C.nome ORDER BY C.nome SEPARATOR ', ') AS departamentos
            FROM tb_produtos_categorias A 
                LEFT JOIN tb_produtos_pre_cat ab ON ab.id_cat = A.id
                LEFT JOIN tb_produtos_prerequisitos B ON B.id = ab.id_pre 
                    AND B.stats = 1 
                    AND B.deleted_at IS NULL
                LEFT JOIN dp_categoria_departamento ac ON ac.id_categoria = A.id
                LEFT JOIN dp_departamentos C ON C.id = ac.id_departamento 
                    AND C.stats = 1 
                    AND C.deleted_at IS NULL
            WHERE A.deleted_at IS NULL
            GROUP BY A.id
        ORDER BY A.ordem");

        $this->renderView($this->getModule(), "index");
    }

    private function editAction() {
        $this->id = $this->getParameter("id");
        $this->requisitos = $this->DB_fetch_array("SELECT * FROM tb_produtos_prerequisitos WHERE stats = 1 AND deleted_at IS NULL ORDER BY nome");
        $this->departamentos = $this->DB_fetch_array("SELECT * FROM dp_departamentos WHERE stats = 1 AND deleted_at IS NULL ORDER BY nome");

        if ($this->id == "") {
            //new            
            if (!$this->permissions[$this->permissao_ref]['gravar'])
                $this->noPermission();

            $campos = $this->DB_columns($this->table);
            foreach ($campos as $campo) {
                $this->registro[$campo] = "";
            }

            $this->categorias = $this->DB_fetch_array("SELECT * FROM tb_produtos_categorias ORDER BY nome");
            $this->departamentos_selecionados = [];
            $this->requisitos_selecionados = [];

        } else {
            //edit
            if (!$this->permissions[$this->permissao_ref]['editar'])
                $this->noPermission();

            $query = $this->DB_fetch_array("SELECT * FROM $this->table  WHERE id = $this->id", "form");
            $this->registro = $query->rows[0];

            $where = "";

            if ($this->getProdutosCategorias($this->id))
                $where = $this->getProdutosCategorias($this->id);

            $this->categorias = $this->DB_fetch_array("SELECT * FROM tb_produtos_categorias $where ORDER BY nome");
            $this->departamentos_selecionados = $this->DB_fetch_array("SELECT * FROM dp_categoria_departamento WHERE id_categoria = $this->id") ?: [];
            $this->requisitos_selecionados = $this->DB_fetch_array("SELECT * FROM tb_produtos_pre_cat WHERE id_cat = $this->id") ?: [];
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

        $existeProduto = $this->DB_num_rows("SELECT * FROM tb_produtos_produtos A WHERE A.id_categoria = $id");
        if ($existeProduto) {
            echo "error";
            exit;
        }

        $this->inserirRelatorio("Apagou categoria de produtos: [" . $dados->rows[0]['nome'] . "] id: [$id]");
        $this->DB_delete($this->table, "id=$id");

        echo $this->getModule();
    }

    private function saveAction() {
        $formulario = $this->formularioObjeto($_POST);
        $validacao = $this->validaFormulario($formulario);
        $departamentos_selected = $_POST['departamentos_selected'] ?? [];
        $requisitos_selected = $_POST['requisitos_selected'] ?? [];

        if (!$validacao->return) {
            echo json_encode($validacao);
        } else {
            $resposta = new \stdClass();
            
            if (!isset($_POST['id_pai']) || $_POST['id_pai'] == "") {
                $_POST['id_pai'] = "NULL";
            }

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
                    $fields = ['id_pre', 'id_cat'];

                    foreach ($requisitos_selected as $key => $value) {
                        $query2 = $this->DB_insert($this->table2, implode(',', $fields), implode(',', [$value, $query->insert_id]));

                        if(!$query2->query){
                            $resposta->type = "error";
                            $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
                            echo json_encode($resposta);
                            return;
                        }
                    }

                    $fields = ['id_departamento', 'id_categoria'];
                    foreach ($departamentos_selected as $key => $value) {
                        $query3 = $this->DB_insert($this->table3, implode(',', $fields), implode(',', [$value, $query->insert_id]));

                        if(!$query3->query){
                            $resposta->type = "error";
                            $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
                            echo json_encode($resposta);
                            return;
                        }
                    }

                    $resposta->type = "success";
                    $resposta->message = "Registro cadastrado com sucesso!";
                    $this->inserirRelatorio("Cadastrou categoria de produtos: [" . $data->nome . "], id [$query->insert_id]");
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
                    $fields = ['id_pre', 'id_cat'];
                    $this->DB_delete($this->table2, 'id_cat = '.$data->id.';');
                    foreach ($requisitos_selected as $key => $value) {
                        $query2 = $this->DB_insert($this->table2, implode(',', $fields), implode(',', [$value, $data->id]));

                        if(!$query2->query){
                            $resposta->type = "error";
                            $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
                            echo json_encode($resposta);
                            return;
                        }
                    }

                    $fields = ['id_departamento', 'id_categoria'];
                    $this->DB_delete($this->table3, 'id_categoria = '.$data->id.';');
                    foreach ($departamentos_selected as $key => $value) {
                        $query3 = $this->DB_insert($this->table3, implode(',', $fields), implode(',', [$value, $data->id]));

                        if(!$query3->query){
                            $resposta->type = "error";
                            $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
                            echo json_encode($resposta);
                            return;
                        }
                    }

                    $resposta->type = "success";
                    $resposta->message = "Registro alterado com sucesso!";
                    $this->inserirRelatorio("Alterou categoria de produtos: [" . $data->nome . "], id: [$data->id]");
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

    public function getCategoriaNome($id = null) {
        $cat = "";
        if ($id != null) {
            $query = $this->DB_fetch_array("SELECT id_pai, nome FROM tb_produtos_categorias WHERE id = $id");
            if ($query->num_rows) {
                $categorias = $query->rows;

                foreach ($categorias as $categoria) {
                    $cat .= $categoria['nome'] . ' , ';
                    if ($categoria['id_pai']) {
                        $cat .= $this->getCategoriaNome($categoria['id_pai']);
                    }
                }
            }

            $array = explode(",", $cat);
            $array = array_reverse($array);

            $cat = "";

            foreach ($array as $value) {
                $cat .= $value;
            }

            unset($query);

            echo $cat . " / ";
        } else {
            echo $cat;
        }
    }

    /*
     * PERCORRE PRODUTOS-CATEGORIAS PROCURANDO ID DE SUBCATEGORIAS
     */

    public function getProdutosSubCategorias($id) {
        $ids = "";

        $query = $this->DB_fetch_array("select id from tb_produtos_categorias where id_pai = $id");
        if ($query->num_rows) {
            $categoria = $query->rows;

            foreach ($categoria as $categoria) {
                $ids .= $categoria['id'] . ',';
                $ids .= $this->getProdutosSubCategorias($categoria['id']);
            }
        }


        return $ids;
    }

    /*
     * CRIA CONDIÇÃO COM AS IDS ENCONTRADAS EM PRODUTOS-CATEGORIAS    
     */

    public function getProdutosCategorias($id) {
        $ids = "";

        $where = " WHERE id <> $id ";

        $ids .= $this->getProdutosSubCategorias($id);

        $ids = explode(',', $ids);

        foreach ($ids as $id) {
            if ($id)
                $where .= " AND id <> $id ";
        }

        return $where;
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
