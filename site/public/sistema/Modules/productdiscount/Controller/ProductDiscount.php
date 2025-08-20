<?php

use System\Core\Bootstrap;

ProductDiscount::setAction();

class ProductDiscount extends Bootstrap {

    public $module = "";
    public $permissao_ref = "produtos-descontos";
    public $table = "tb_produtos_descontos";

    function __construct() {
        parent::__construct();
        if (!isset($_SESSION['admin_logado'])) {
            header("Location: " . $this->system_path . "login");
            exit;
        }
        $this->getAllPermissions();

        $this->module_icon = "entypo-icon-price";
        $this->module_link = "productdiscount";
        $this->module_title = "Descontos de Produtos";
        $this->retorno = "productdiscount";
    }

    private function indexAction() {

        if (!$this->permissions[$this->permissao_ref]['ler']) {
            $this->noPermission();
        }

        $this->list = $this->DB_fetch_array("SELECT A.*  FROM $this->table A ORDER BY A.descricao");

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
            $this->categorias = $this->DB_fetch_array("SELECT * FROM tb_produtos_categorias A LEFT JOIN tb_produtos_descontos_has_tb_produtos_categorias B ON B.id_categoria = A.id AND B.id_desconto = 0 ORDER BY A.nome");
        } else {
            //edit
            if (!$this->permissions[$this->permissao_ref]['editar'])
                $this->noPermission();

            $query = $this->DB_fetch_array("SELECT A.*, DATE_FORMAT(A.data, '%d/%m/%Y %H:%i') data FROM $this->table A WHERE A.id = $this->id");

            $this->registro = $query->rows[0];

            $this->produtos = $this->DB_fetch_array("SELECT * FROM tb_produtos_produtos A LEFT JOIN tb_produtos_has_tb_descontos B ON B.id_produto = A.id AND B.id_desconto = {$this->registro['id']} ORDER BY A.nome");
            $this->categorias = $this->DB_fetch_array("SELECT * FROM tb_produtos_categorias A LEFT JOIN tb_produtos_descontos_has_tb_produtos_categorias B ON B.id_categoria = A.id AND B.id_desconto = {$this->registro['id']} ORDER BY A.nome");
        }

        $this->renderView($this->getModule(), "edit");
    }

    private function delAction() {
        if (!$this->permissions[$this->permissao_ref]['excluir'])
            exit();

        $id = $this->getParameter("id");

        $dados = $this->DB_fetch_array("SELECT * FROM $this->table WHERE id = $id");

        $this->inserirRelatorio("Apagou desconto de produtos: [" . $dados->rows[0]['descricao'] . "] id: [$id]");
        $this->DB_delete("tb_produtos_has_tb_descontos", "id_desconto=$id");
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

            if ($_POST['valor'] != "")
                $_POST['valor'] = $this->formataMoedaBd($_POST['valor']);
            else
                $_POST['valor'] = "NULL";
            
            if ($_POST['porcentagem_fabrica'] != "")
                $_POST['porcentagem_fabrica'] = $this->formataMoedaBd($_POST['porcentagem_fabrica']);
            else
                $_POST['porcentagem_fabrica'] = "NULL";
            
            
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
                $idDesconto = $query->insert_id;
                if ($query->query) {


                    if ($_POST['categoria'] != 1) {
                        if (isset($_POST['produtos'])) {
                            foreach ($_POST['produtos'] as $produto) {
                                $this->DB_insert("tb_produtos_has_tb_descontos", "id_produto,id_desconto", "$produto,$idDesconto");
                            }
                        }
                    } else {

                        if (isset($_POST['categorias'])) {
                            foreach ($_POST['categorias'] as $categoria) {
                                $this->DB_insert("tb_produtos_descontos_has_tb_produtos_categorias", "id_categoria,id_desconto", "$categoria,$idDesconto");

                                $cat = $this->DB_fetch_array("SELECT * FROM tb_produtos_produtos A INNER JOIN tb_produtos_produtos_has_tb_produtos_categorias B ON B.id_produto = A.id WHERE B.id_categoria = $categoria GROUP BY A.id");
                                if ($cat->num_rows) {
                                    foreach ($cat->rows as $cat) {
                                        $existe = $this->DB_fetch_array("SELECT * FROM tb_produtos_has_tb_descontos A WHERE A.id_produto = {$cat['id_produto']} AND A.id_desconto = $idDesconto");
                                        if (!$existe->num_rows)
                                            $this->DB_insert("tb_produtos_has_tb_descontos", "id_produto,id_desconto", "{$cat['id_produto']},$idDesconto");
                                    }
                                }
                            }
                        }
                    }


                    $resposta->type = "success";
                    $resposta->message = "Registro cadastrado com sucesso!";
                    $this->inserirRelatorio("Cadastrou desconto de produtos: [" . $data->descricao . "]");
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

                    $this->DB_delete("tb_produtos_has_tb_descontos", "id_desconto = $data->id");

                    $this->DB_delete("tb_produtos_descontos_has_tb_produtos_categorias", "id_desconto = $data->id");
                    $verificar = $this->DB_fetch_array("SELECT C.* FROM tb_produtos_produtos A INNER JOIN tb_produtos_produtos_has_tb_produtos_categorias B ON B.id_produto = A.id INNER JOIN tb_produtos_has_tb_descontos C ON C.id_produto = B.id_produto WHERE C.id_desconto = $data->id GROUP BY A.id");
                    if ($verificar->num_rows) {
                        foreach ($verificar->rows as $verifica) {
                            if (!in_array($verifica['id_produto'], $_POST['produtos']))
                                $this->DB_delete("tb_produtos_has_tb_descontos", "id_desconto = $data->id AND id_produto = {$verifica['id_produto']}");
                        }
                    }

                    if ($_POST['categoria'] != 1) {

                        if (isset($_POST['produtos'])) {
                            foreach ($_POST['produtos'] as $produto) {
                                $this->DB_insert("tb_produtos_has_tb_descontos", "id_produto,id_desconto", "$produto,$data->id");
                            }
                        }
                    } else {

                        if (isset($_POST['categorias'])) {
                            foreach ($_POST['categorias'] as $categoria) {
                                $this->DB_insert("tb_produtos_descontos_has_tb_produtos_categorias", "id_categoria,id_desconto", "$categoria,$data->id");

                                $cat = $this->DB_fetch_array("SELECT * FROM tb_produtos_produtos A INNER JOIN tb_produtos_produtos_has_tb_produtos_categorias B ON B.id_produto = A.id WHERE B.id_categoria = $categoria GROUP BY A.id");
                                if ($cat->num_rows) {
                                    foreach ($cat->rows as $cat) {
                                        $existe = $this->DB_fetch_array("SELECT * FROM tb_produtos_has_tb_descontos A WHERE A.id_produto = {$cat['id_produto']} AND A.id_desconto = $data->id");
                                        if (!$existe->num_rows)
                                            $this->DB_insert("tb_produtos_has_tb_descontos", "id_produto,id_desconto", "{$cat['id_produto']},$data->id");
                                    }
                                }
                            }
                        }
                    }

                    $resposta->type = "success";
                    $resposta->message = "Registro alterado com sucesso!";
                    $this->inserirRelatorio("Alterou desconto de produtos: [" . $data->descricao . "]");
                } else {
                    $resposta->type = "error";
                    $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
                }
            }

            echo json_encode($resposta);
        }
    }

    private function getProdutosByCategoriaAction() {
        if (!$this->permissions[$this->permissao_ref]['ler'])
            exit();

        $ids = "";
        $id = $_POST['ids'];
        $separador = "";
        foreach ($id as $id) {
            $query = $this->DB_fetch_array("SELECT B.id_produto FROM tb_produtos_produtos A INNER JOIN tb_produtos_produtos_has_tb_produtos_categorias B ON B.id_produto = A.id WHERE B.id_categoria = $id GROUP BY A.id");
            if ($query->num_rows) {
                foreach ($query->rows as $row) {
                    $ids .= $separador . $row['id_produto'];
                    $separador = ",";
                }
            }
        }

        echo $ids;
    }

    private function validaFormulario($form) {

        $resposta = new \stdClass();
        $resposta->return = true;

        if ($form->descricao == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "descricao";
            $resposta->return = false;
            return $resposta;
        } else if ($form->porcentagem == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "porcentagem";
            $resposta->return = false;
            return $resposta;
        } else if ($form->valor == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "valor";
            $resposta->return = false;
            return $resposta;
        } else if ($form->quantidade == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "quantidade";
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
