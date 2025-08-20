<?php

use System\Core\Bootstrap;

LeadOrigin::setAction();

class LeadOrigin extends Bootstrap {

    public $module = "";
    public $permissao_ref = "produtos-categorias";
    public $table = "tb_origem_lead";

    function __construct() {
        parent::__construct();
        if (!isset($_SESSION['admin_logado'])) {
            header("Location: " . $this->system_path . "login");
        }
        $this->getAllPermissions();

        $this->module_icon = "icomoon-icon-location";
        $this->module_link = "leadorigin";
        $this->module_title = "Origens de lead";
        $this->retorno = "leadorigin";
    }

    private function indexAction() {
        if (!$this->permissions[$this->permissao_ref]['ler']) {
            $this->noPermission();
        }

        $this->list = $this->DB_fetch_array("SELECT * FROM $this->table WHERE deleted_at IS NULL");

        $this->renderView($this->getModule(), "index");
    }

    private function editAction() {
        $this->id = $this->getParameter("id");
        $this->requisitos = $this->DB_fetch_array("SELECT * FROM tb_produtos_prerequisitos WHERE stats = 1 ORDER BY nome");

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
    
        $this->inserirRelatorio("Soft delete em Origens do lead: [" . $dados->rows[0]['nome'] . "] id: [$id]");
    
        // Soft delete: marca o registro com a data atual
        $this->DB_update($this->table, "deleted_at = NOW() WHERE id = $id");
    
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

                    $resposta->type = "success";
                    $resposta->message = "Registro alterado com sucesso!";
                    $this->inserirRelatorio("Alterou dependência de produtos: [" . $data->nome . "], id: [$data->id]");
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
        }
        else {
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
    
    public function getPrerequisitoNome($id = null) {
        $cat = "";
        if ($id != null) {
            $query = $this->DB_fetch_array("SELECT id_pai, nome FROM tb_produtos_prerequisitos WHERE id = $id");
            if ($query->num_rows) {
                $prerequisitos = $query->rows;

                foreach ($prerequisitos as $prerequisito) {
                    $cat .= $prerequisito['nome'] . ' , ';
                    if ($prerequisito['id_pai']) {
                        $cat .= $this->getPrerequisitoNome($prerequisito['id_pai']);
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
