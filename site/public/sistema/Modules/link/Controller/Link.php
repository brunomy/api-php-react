<?php

use System\Core\Bootstrap;

Link::setAction();

class Link extends Bootstrap {

    public $module = "";
    public $permissao_ref = "links";
    public $table = "tb_links_links";

    function __construct() {
        parent::__construct();
        if (!isset($_SESSION['admin_logado'])) {
            header("Location: " . $this->system_path . "login");
        }
        $this->getAllPermissions();

        $this->module_icon = "iconic-icon-link";
        $this->module_link = "link";
        $this->module_title = "Menu Links";
        $this->retorno = "link";

        $this->imagem_uploaded = "";

        $this->crop_sizes = array();

        array_push($this->crop_sizes, array("width" => 300, "height" => 165));
    }

    private function indexAction() {
        if (!$this->permissions[$this->permissao_ref]['ler']) {
            $this->noPermission();
        }

        $this->list = $this->DB_fetch_array("SELECT * FROM $this->table ORDER BY ordem");

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
            $this->links = $this->DB_fetch_array("SELECT * FROM tb_links_links ORDER BY ordem");
        } else {
            //edit
            if (!$this->permissions[$this->permissao_ref]['editar'])
                $this->noPermission();

            $query = $this->DB_fetch_array("SELECT * FROM $this->table  WHERE id = $this->id", "form");
            $this->registro = $query->rows[0];

            if ($this->getProdutosCategorias($this->id))
                $where = $this->getProdutosCategorias($this->id);

            $this->links = $this->DB_fetch_array("SELECT * FROM tb_links_links $where ORDER BY ordem");
        }

        $this->urls = $this->DB_fetch_array("
            SELECT nome, link FROM
            (
            (SELECT CONCAT('Produto: ', A.seo_title) nome, CONCAT('produto/',A.seo_url) link FROM tb_seo_paginas A INNER JOIN tb_produtos_produtos B ON B.id_seo = A.id)
            UNION
            (SELECT CONCAT('Produto Personalizado: ', A.seo_title) nome, CONCAT('produto/',A.seo_url) link FROM tb_seo_paginas A INNER JOIN tb_produtos_personalizados B ON B.id_seo = A.id)
            UNION
            (SELECT CONCAT('Página Fixa: ', A.seo_title) nome, A.seo_url link FROM tb_seo_paginas A WHERE A.seo_pagina_dinamica = 0)
            UNION
            (SELECT CONCAT('Categoria de Produto: ', A.nome) nome, CONCAT('produtos/categoria/',A.nome) link FROM tb_produtos_categorias A)            
            UNION
            (SELECT nome, link FROM
            ((SELECT CONCAT('Produtos Personalizados: ', C.seo_title) nome, CONCAT('produtos-personalizados/',C.seo_url) link FROM tb_produtos_produtos A INNER JOIN tb_produtos_personalizados B ON B.id_produto = A.id INNER JOIN tb_seo_paginas C ON C.id = B.id_seo  GROUP BY B.id_produto)
            UNION
            (SELECT CONCAT('Produtos Personalizados: ', C.seo_title) nome, CONCAT('produtos-personalizados/',C.seo_url) link FROM tb_produtos_produtos A INNER JOIN tb_produtos_personalizados B ON B.id_produto = A.id INNER JOIN tb_seo_paginas C ON C.id = A.id_seo GROUP BY B.id_produto))
            TAB ORDER BY nome)
            UNION
            (SELECT 'Produtos Personalizados' as nome, 'produtos-personalizados')
            )
            SUB ORDER BY nome
        ");

        $this->renderView($this->getModule(), "edit");
    }

    public function getCategoriaNome($id = null) {
        $cat = "";
        if ($id != null) {
            $query = $this->DB_fetch_array("SELECT id_pai, nome FROM tb_links_links WHERE id = $id");
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

        $query = $this->DB_fetch_array("SELECT id FROM tb_links_links WHERE id_pai = $id");
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

        $this->inserirRelatorio("Apagou link: [" . $dados->rows[0]['nome'] . "] id: [$id]");
        $this->DB_delete($this->table, "id=$id");

        echo $this->getModule();
    }

    private function saveAction() {
        $formulario = $this->formularioObjeto($_POST);
        $validacao = $this->validaFormulario($formulario);
        if (!$validacao->return) {
            echo json_encode($validacao);
        } else {

            $validacao = $this->validaUpload($formulario);

            if (!$validacao->return) {
                echo json_encode($validacao);
            } else {
                $resposta = new \stdClass();

                if (!isset($_POST['id_pai']) || $_POST['id_pai'] == "")
                    $_POST['id_pai'] = "NULL";

                $data = $this->formularioObjeto($_POST, $this->table);

                if ($this->imagem_uploaded != "") {
                    $data->imagem = $this->imagem_uploaded;
                    if ($formulario->id != "")
                        $this->deleteFile($this->table, "imagem", "id=" . $data->id, $this->crop_sizes);
                }

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
                        $this->inserirRelatorio("Cadastrou link: [" . $data->nome . "]");
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
                        $this->inserirRelatorio("Alterou link: [" . $data->nome . "]");
                    } else {
                        $resposta->type = "error";
                        $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
                    }
                }

                echo json_encode($resposta);
            }
        }
    }

    private function validaFormulario($form) {

        $resposta = new \stdClass();
        $resposta->return = true;

        $limita = false;
        if (isset($form->id_pai) && $form->id_pai != "") {
            $query = $this->DB_fetch_array("SELECT * FROM tb_links_links A INNER JOIN tb_links_links B ON B.id = A.id_pai INNER JOIN tb_links_links C ON C.id = B.id_pai WHERE A.id = $form->id_pai");
            if ($query->num_rows)
                $limita = true;
        }

        if ($form->nome == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "nome";
            $resposta->return = false;
            return $resposta;
        } else if ($limita) {
            $resposta->type = "validation";
            $resposta->message = "O Menu Link escolhido atingiu o limite de Submenus";
            $resposta->field = "id_pai";
            $resposta->return = false;
            return $resposta;
        } else {
            return $resposta;
        }
    }

    private function validaUpload($form) {

        $resposta = new \stdClass();
        $resposta->return = true;

        if (is_uploaded_file($_FILES["fileupload"]["tmp_name"])) {

            $upload = $this->uploadFile("fileupload", array("jpg", "jpeg", "gif", "png"), $this->crop_sizes);
            if ($upload->return) {
                $this->imagem_uploaded = $upload->file_uploaded;
            }
        }

        if (isset($upload) && !$upload->return) {
            $resposta->type = "attention";
            $resposta->message = $upload->message;
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
