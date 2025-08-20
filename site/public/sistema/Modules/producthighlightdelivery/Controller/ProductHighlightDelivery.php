<?php

use System\Core\Bootstrap;

ProductHighlightDelivery::setAction();

class ProductHighlightDelivery extends Bootstrap {

    public $module = "";
    public $permissao_ref = "produtos-produtos";
    public $table = "tb_produtos_destaques_pronto_entrega";

    function __construct() {
        parent::__construct();
        if (!isset($_SESSION['admin_logado'])) {
            header("Location: " . $this->system_path . "login");
        }
        $this->getAllPermissions();

        $this->module_icon = "minia-icon-checked";
        $this->module_link = "producthighlightdelivery";
        $this->module_title = "Destaque de Produtos a Pronto Entrega";
        $this->retorno = "producthighlightdelivery";
    }

    private function indexAction() {
        if (!$this->permissions[$this->permissao_ref]['ler']) {
            $this->noPermission();
        }

        $this->list = $this->DB_fetch_array("SELECT A.id, B.nome produto FROM tb_produtos_destaques_pronto_entrega A INNER JOIN tb_produtos_produtos B ON A.id_produto = B.id ORDER BY A.ordem");


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

            $this->produtos = $this->DB_fetch_array("SELECT A.id, A.nome, D.id id_destaque FROM tb_produtos_produtos A INNER JOIN tb_produtos_produtos_has_tb_produtos_categorias B ON B.id_produto = A.id INNER JOIN tb_produtos_categorias C ON C.id = B.id_categoria LEFT JOIN tb_produtos_destaques_pronto_entrega D ON D.id_produto = A.id AND D.id = 0 WHERE C.id = 1 AND A.apagado != 1 GROUP BY A.id ORDER BY A.nome");
        } else {
            //edit
            if (!$this->permissions[$this->permissao_ref]['editar'])
                $this->noPermission();

            $query = $this->DB_fetch_array("SELECT * FROM $this->table  WHERE id = $this->id", "form");
            $this->registro = $query->rows[0];

            $this->produtos = $this->DB_fetch_array("SELECT A.id, A.nome, D.id id_destaque FROM tb_produtos_produtos A INNER JOIN tb_produtos_produtos_has_tb_produtos_categorias B ON B.id_produto = A.id INNER JOIN tb_produtos_categorias C ON C.id = B.id_categoria LEFT JOIN tb_produtos_destaques_pronto_entrega D ON D.id_produto = A.id AND D.id = {$this->registro['id']} WHERE C.id = 1 AND A.apagado != 1 GROUP BY A.id ORDER BY A.nome");
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

        $dados = $this->DB_fetch_array("SELECT * FROM $this->table WHERE id = $id");

        $this->inserirRelatorio("Apagou destaque de produto a pronto entrega id: [$id]");
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
                    $resposta->type = "success";
                    $resposta->message = "Registro cadastrado com sucesso!";
                    $this->inserirRelatorio("Cadastrou destaque de produto a pronto entrega id [$query->insert_id]");
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
                    $this->inserirRelatorio("Alterou destaque de produto a pronto entrega id: [$data->id]");
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

        if (isset($form->id_produto) && $form->id_produto != "") {
            if ($form->id == "") {
                $verifica = $this->DB_fetch_array("SELECT * FROM tb_produtos_destaques_pronto_entrega WHERE id_produto = {$_POST['id_produto']} ");
            } else {
                $verifica = $this->DB_fetch_array("SELECT * FROM tb_produtos_destaques_pronto_entrega WHERE id_produto = {$_POST['id_produto']} AND id != $form->id ");
            }
        }

        if ($form->id_produto == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "id_produto";
            $resposta->return = false;
            return $resposta;
        } else if ($verifica->num_rows) {
            $resposta->type = "validation";
            $resposta->message = "Este produto já está como destaque, por favor escolha outro!";
            $resposta->field = "id_personalizado";
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
