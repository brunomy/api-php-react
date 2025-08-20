<?php

use System\Core\Bootstrap;

ProductCustom::setAction();

class ProductCustom extends Bootstrap {

    public $module = "";
    public $permissao_ref = "produtos-produtos";
    public $table = "tb_produtos_personalizados";

    function __construct() {
        parent::__construct();
        if (!isset($_SESSION['admin_logado'])) {
            header("Location: " . $this->system_path . "login");
        }
        $this->getAllPermissions();

        $this->module_icon = "minia-icon-pencil-2";
        $this->module_link = "productcustom";
        $this->module_title = "Produtos Personalizados";
        $this->retorno = "productcustom";

        $this->imagem_capa = "";
        $this->googleshop_img = "";

        $this->crop_capa_sizes = array();
        array_push($this->crop_capa_sizes, array("width" => 700, "height" => 385));
        //array_push($this->crop_capa_sizes, array("width" => 730, "height" => 540));
        array_push($this->crop_capa_sizes, array("width" => 790, "height" => 435));
        array_push($this->crop_capa_sizes, array("width" => 455, "height" => 335));

        $this->crop_quadrado = array();
        array_push($this->crop_quadrado, array("width" => 385, "height" => 385));
    }

    private function indexAction() {
        if (!$this->permissions[$this->permissao_ref]['ler']) {
            $this->noPermission();
        }

        $this->list = $this->DB_fetch_array("SELECT A.* FROM $this->table A WHERE A.apagado != 1 ORDER BY A.ordem");

        $this->produtos = $this->DB_fetch_array("SELECT * FROM tb_produtos_produtos WHERE apagado != 1 ORDER BY nome");

        $this->renderView($this->getModule(), "index");
    }

    public function getCategoriasByIdProduto($idProduto) {
        if (!$this->permissions[$this->permissao_ref]['ler']) {
            $this->noPermission();
        }

        $result = "";
        $separador = "";

        $query = $this->DB_fetch_array("SELECT A.* FROM tb_produtos_categorias A INNER JOIN tb_produtos_produtos_has_tb_produtos_categorias B ON B.id_categoria = A.id WHERE B.id_produto = $idProduto ORDER BY A.ordem");
        if ($query->num_rows) {
            foreach ($query->rows as $row) {
                $result .= $separador . $row['nome'];
                $separador = ", ";
            }
        }

        return $result;
    }

    private function editAction() {
        $this->id = $this->getParameter("id");
        if ($this->id == "") {
            //new            
            $this->noPermission();
        } else {
            //edit
            if (!$this->permissions[$this->permissao_ref]['editar'])
                $this->noPermission();

            $query = $this->DB_fetch_array("SELECT A.*, IFNULL(B.custo,'0') custo, IFNULL(B.frete_embutido,'0') frete_embutido FROM $this->table A INNER JOIN tb_produtos_produtos B ON B.id = A.id_produto  WHERE A.id = $this->id", "form");
            $this->registro = $query->rows[0];

            $query = $this->DB_fetch_array("SELECT * FROM tb_seo_paginas WHERE id = " . $this->registro['id_seo'], "form");
            $this->seo = $query->rows[0];

            $this->conjuntos = $this->DB_fetch_array("SELECT * FROM tb_produtos_conjuntos_atributos A WHERE A.id_produto = {$this->registro['id_produto']} ORDER BY ordem");
        }

        $this->renderView($this->getModule(), "edit");
    }

    private function delAction() {
        if (!$this->permissions[$this->permissao_ref]['excluir'])
            exit();

        $id = $this->getParameter("id");

        $dados = $this->DB_fetch_array("SELECT * FROM $this->table WHERE id = $id");

        $this->DB_update($this->table, "apagado = 1 WHERE id = $id");

        $this->DB_delete("tb_produtos_destaques", "id_personalizado = $id");
        $this->DB_delete("tb_produtos_destaques_personalizados", "id_personalizado = $id");

        /*
          if ($dados->rows[0]['imagem'] != "") {
          $produto = $this->DB_fetch_array("SELECT * FROM tb_produtos_produtos WHERE imagem = '{$dados->rows[0]['imagem']}'");
          if (!$produto->num_rows)
          $this->deleteFile($this->table, "imagem", "id=$id", $this->crop_capa_sizes);
          }
         * 
         */

        $this->inserirRelatorio("Apagou produto personalizado: [" . $dados->rows[0]['nome'] . "] id: [$id]");
        /*
          $this->DB_delete($this->table, "id=$id");
          $this->DB_delete($this->_seo_table, "id=" . $dados->rows[0]['id_seo']);
         */

        echo $this->getModule();
    }

    private function saveAction() {
        $seo = new stdClass();
        $seo->breadcrumbs = "produto/";
        $seo->pagina = "produto";

        $formulario = $this->formularioObjeto($_POST);
        $validacao = $this->validaFormulario($formulario);
        if (!$validacao->return) {
            echo json_encode($validacao);
        } else {
            $validacao = $this->validaUpload($formulario);

            if (!$validacao->return) {
                echo json_encode($validacao);
            } else {

                if (isset($_POST['seo_url_breadcrumbs']) && $_POST['seo_url_breadcrumbs'] != "") {
                    $seo->breadcrumbs = $this->formataBreadcrumbs($_POST['seo_url_breadcrumbs']);
                }

                $seo->url_secundaria = $formulario->nome;
                $seo->seo_scripts = $formulario->seo_scripts;

                if ($_POST["seo_title"] == "")
                    $_POST["seo_title"] = $formulario->nome;

                require_once __sys_path__ . "System/includes/seo.php";

                if ($seo_request->response) {
                    $resposta = new \stdClass();

                    $data = $this->formularioObjeto($_POST, $this->table);


                    if ($formulario->id == "") {
                        //criar
                        if (!$this->permissions[$this->permissao_ref]['gravar'])
                            exit();

                        $resposta->type = "error";
                        $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
                    } else {
                        //alterar
                        if (!$this->permissions[$this->permissao_ref]['editar'])
                            exit();

                        if ($this->imagem_capa != "" || $this->googleshop_img != "") {
                            $dados = $this->DB_fetch_array("SELECT imagem, googleshop_img FROM tb_produtos_personalizados WHERE id = $data->id");

                            if ($this->imagem_capa != ""){
                                $data->imagem = $this->imagem_capa;
                                $produto = $this->DB_fetch_array("SELECT * FROM tb_produtos_produtos WHERE imagem = '{$dados->rows[0]['imagem']}'");
                                if (!$produto->num_rows)
                                    $this->deleteFile($this->table, "imagem", "id=" . $data->id, $this->crop_capa_sizes);
                            }
                            if ($this->googleshop_img != "") {
                                $data->googleshop_img = $this->googleshop_img;
                                $produto = $this->DB_fetch_array("SELECT * FROM tb_produtos_personalizados WHERE googleshop_img = '{$dados->rows[0]['googleshop_img']}' AND id != $data->id");
                                if (!$produto->num_rows)
                                    $this->deleteFile($this->table, "googleshop_img", "id=" . $data->id, $this->crop_quadrado);
                            }
                            if($this->imagem_capa != "" && $dados->rows[0]['googleshop_img']=="" && $this->googleshop_img==""){
                                foreach($this->crop_quadrado as $i => $val){
                                    $this->crop_quadrado[$i]["overlaped"] = true;
                                }
                                $upload = $this->duplicateFile($this->imagem_capa, array("jpg", "jpeg", "gif", "png"), $this->crop_quadrado);
                                if ($upload->return) {
                                    $data->googleshop_img = $upload->file_uploaded;
                                }
                            }
                        }

                        foreach ($data as $key => $value) {
                            $fields_values[] = "$key='$value'";
                        }

                        $query = $this->DB_update($this->table, implode(',', $fields_values) . " WHERE id=" . $data->id);
                        if ($query) {

                            $atributos = $this->DB_fetch_array("SELECT A.* FROM tb_produtos_atributos A INNER JOIN tb_produtos_conjuntos_atributos B ON B.id = A.id_conjunto_atributo WHERE B.id_produto = $formulario->id_produto GROUP BY id_conjunto_atributo");

                            if ($atributos->num_rows) {
                                $this->DB_delete("tb_produtos_personalizados_has_tb_produtos_atributos", "id_produto_personalizado = $formulario->id ");
                                foreach ($atributos->rows as $atributo) {
                                    if (isset($_POST[$atributo['id_conjunto_atributo']]) && $_POST[$atributo['id_conjunto_atributo']] != "")
                                        $this->DB_insert("tb_produtos_personalizados_has_tb_produtos_atributos", "id_produto_personalizado,id_atributo,selecionado", "$formulario->id,{$_POST[$atributo['id_conjunto_atributo']]},1");
                                }
                            }

                            $resposta->type = "success";
                            $resposta->message = "Registro alterado com sucesso!";
                            $this->inserirRelatorio("Alterou categoria de decorações: [" . $data->nome . "], id: [$data->id]");
                        } else {
                            $resposta->type = "error";
                            $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
                        }
                    }
                } else {
                    $resposta->type = "error";
                    $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
                }
                echo json_encode($resposta);
            }
        }
    }

    private function orderAction() {
        if (!$this->permissions[$this->permissao_ref]['excluir'])
            exit();
        $this->ordenarRegistros($_POST["array"], $this->table);
    }

    private function addCustomAction() {
        if (!$this->permissions[$this->permissao_ref]['gravar'])
            exit();

        $id = $_POST['id'];

        $seo = new stdClass();
        $seo->breadcrumbs = "produto/";
        $seo->pagina = "produto";

        $resposta = new \stdClass();

        $seo->url_secundaria = '';
        $seo->seo_scripts = '';

        $produto = $this->DB_fetch_array("SELECT * FROM tb_produtos_produtos A INNER JOIN tb_seo_paginas B ON B.id = A.id_seo WHERE A.id = $id");

        if ($produto->num_rows) {

            foreach ($produto->rows[0] as $key => $value) {
                $_POST[$key] = $value;
            }

            $_POST['id'] = "";
            $_POST['id_seo'] = "";

            $_POST["seo_title"] = $produto->rows[0]['seo_title'];

            require_once __sys_path__ . "System/includes/seo.php";

            if ($seo_request->response) {

                $ordem = $this->DB_fetch_array("SELECT MAX(id) ordem FROM $this->table");
                if ($ordem->num_rows) {
                    $ordem = $ordem->rows[0]['ordem'] + 1;
                } else {
                    $ordem = 0;
                }

                $query = $this->DB_insert("tb_produtos_personalizados", "id_produto, id_seo, nome, resumo, texto, imagem, ordem", "$id, $seo_request->id,'{$_POST['nome']}','{$_POST['resumo']}','{$_POST['texto']}','{$_POST['imagem']}', $ordem");
                if ($query->query) {

                    $atributos = $this->DB_fetch_array("SELECT B.id, B.selecionado FROM tb_produtos_conjuntos_atributos A INNER JOIN tb_produtos_atributos B ON B.id_conjunto_atributo = A.id WHERE A.id_produto = $id AND B.selecionado = 1 ORDER BY B.ordem");
                    if ($atributos->num_rows) {
                        foreach ($atributos->rows as $atributo) {
                            $this->DB_insert("tb_produtos_personalizados_has_tb_produtos_atributos", "id_produto_personalizado,id_atributo,selecionado", "$query->insert_id,{$atributo['id']},{$atributo['selecionado']}");
                        }
                    }

                    $resposta->result = true;
                    $resposta->id = $query->insert_id;
                } else {
                    $resposta->result = false;
                }
            } else {
                $resposta->result = false;
            }
        } else {
            $resposta->result = false;
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
            return $resposta;
        } else {
            return $resposta;
        }
    }

    private function validaUpload($form) {

        $resposta = new \stdClass();
        $resposta->return = true;

        if (is_uploaded_file($_FILES["fileupload"]["tmp_name"])) {

            $upload = $this->uploadFile("fileupload", array("jpg", "jpeg", "gif", "png"), $this->crop_capa_sizes);
            if ($upload->return) {
                $this->imagem_capa = $upload->file_uploaded;
            }
        }

        if (is_uploaded_file($_FILES["fileupload2"]["tmp_name"])) {

            $upload2 = $this->uploadFile("fileupload2", array("jpg", "jpeg", "gif", "png"), $this->crop_quadrado);
            if ($upload2->return) {
                $this->googleshop_img = $upload2->file_uploaded;
            }
        }

        if (isset($upload) && !$upload->return) {
            $resposta->type = "attention";
            $resposta->message = $upload->message;
            $resposta->return = false;
            return $resposta;
        } else if (isset($upload2) && !$upload2->return) {
            $resposta->type = "attention";
            $resposta->message = $upload2->message;
            $resposta->return = false;
            return $resposta;
        } else {
            return $resposta;
        }
    }

    public function getAtributosByIdConjunto($idConjunto,$idPersonalizado) {
        $query = $this->DB_fetch_array("SELECT A.*, B.id_atributo FROM tb_produtos_atributos A  LEFT JOIN tb_produtos_personalizados_has_tb_produtos_atributos B ON B.id_atributo = A.id AND B.id_produto_personalizado = $idPersonalizado WHERE A.id_conjunto_atributo = $idConjunto GROUP BY A.id ORDER BY A.ordem");
        if ($query->num_rows)
            return $query->rows;
        else
            return false;
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
