<?php

use System\Core\Bootstrap;

blogfixed::setAction();

class blogfixed extends Bootstrap {

    public $module = "";
    public $permissao_ref = "blog";
    public $table = "tb_institucional_paginas";
    public $table2 = "tb_institucional_fotos";

    function __construct() {
        parent::__construct();
        if (!isset($_SESSION['admin_logado'])) {
            header("Location: " . $this->system_path . "login");
        }
        $this->getAllPermissions();

        $this->module_icon = "icomoon-icon-paragraph-left";
        $this->module_link = "blogfixed";
        $this->module_title = "Página Fixa do Blog";
        $this->retorno = "blogfixed";

        $this->imagem_uploaded = "";

        $this->crop_imagem_sizes = array();
        array_push($this->crop_imagem_sizes, array("width" => 559, "height" => 280));

        $this->crop_sizes = array();

        array_push($this->crop_sizes, array("width" => 75, "height" => 75));
        array_push($this->crop_sizes, array("width" => 125, "height" => 100));
        array_push($this->crop_sizes, array("width" => 592, "height" => 300));
        array_push($this->crop_sizes, array("width" => 375, "height" => 275));
    }

    private function indexAction() {
        $this->id = 10;
        if ($this->id == "") {
            //new            
            if (!$this->permissions[$this->permissao_ref]['gravar'])
                $this->noPermission();

            $campos = $this->DB_columns($this->table);
            foreach ($campos as $campo) {
                $this->registro[$campo] = "";
            }

            $this->seo['seo_title'] = "";
            $this->seo['seo_description'] = "";
            $this->seo['seo_keywords'] = "";
            $this->seo['seo_url'] = "";
            $this->seo['seo_scripts'] = "";
            
            $this->seo['seo_url_breadcrumbs'] = "";
        } else {
            //edit
            if (!$this->permissions[$this->permissao_ref]['editar'])
                $this->noPermission();

            $query = $this->DB_fetch_array("SELECT * FROM $this->table  WHERE id = $this->id", "form");
            $this->registro = $query->rows[0];

            $this->fotos = $this->DB_fetch_array("SELECT * FROM $this->table2 WHERE id_pagina = $this->id", "form");

            $query = $this->DB_fetch_array("SELECT * FROM tb_seo_paginas WHERE id = " . $this->registro['id_seo'], "form");
            $this->seo = $query->rows[0];
        }

        $this->categorias = $this->DB_fetch_array("SELECT * FROM tb_institucional_categorias ORDER BY nome");


        $this->renderView($this->getModule(), "edit");
    }


    private function fotosAction() {
        if (!$this->permissions[$this->permissao_ref]['ler']) {
            $this->noPermission();
        }

        $this->id = $this->getParameter("id");

        $this->fotos = $this->DB_fetch_array("SELECT * FROM $this->table2 WHERE id_pagina = $this->id order by ordem");

        $this->renderAjax($this->getModule(), "fotos");
    }

    private function fotoAction() {
        if (!$this->permissions[$this->permissao_ref]['ler'])
            $this->noPermission(false);

        $this->id = $this->getParameter("id");
        $this->id_pagina = $this->getParameter("id_pagina");

        $foto = $this->DB_fetch_array("SELECT * FROM $this->table2 WHERE id = $this->id AND id_pagina = $this->id_pagina");
        $this->foto = $foto->rows[0];

        $this->renderAjax($this->getModule(), "foto");
    }

    private function delAction() {
        if (!$this->permissions[$this->permissao_ref]['excluir'])
            exit();

        $id = $this->getParameter("id");

        $dados = $this->DB_fetch_array("SELECT * FROM $this->table WHERE id = $id");
        if ($dados->rows[0]['imagem'] != "") {
            $this->deleteFile($this->table, "imagem", "id=$id", $this->crop_imagem_sizes);
        }

        $fotos = $this->DB_fetch_array("SELECT * FROM $this->table2 WHERE id_pagina = $id");
        if ($fotos->num_rows) {
            foreach ($fotos->rows as $foto) {
                $this->deleteFile($this->table2, "url", "id=" . $foto['id'], $this->crop_sizes);
            }
        }

        $this->inserirRelatorio("Apagou página institucional: [" . $dados->rows[0]['nome'] . "] id: [$id]");
        $this->DB_delete($this->table, "id=$id");
        $this->DB_delete($this->_seo_table, "id=" . $dados->rows[0]['id_seo']);

        echo $this->getModule();
    }

    private function saveAction() {
        $seo = new stdClass();
        $seo->pagina = "institucional";

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

                $seo->url_secundaria = $formulario->nome;
                $seo->seo_scripts = $formulario->seo_scripts;

                $data = $this->formularioObjeto($_POST, $this->table);

                if ($this->imagem_uploaded != "") {
                    $data->imagem = $this->imagem_uploaded;
                    if ($formulario->id != "")
                        $this->deleteFile($this->table, "imagem", "id=" . $data->id, $this->crop_imagem_sizes);
                }

                if (isset($_POST['seo_url_breadcrumbs']) && $_POST['seo_url_breadcrumbs'] != "") {
                    $seo->breadcrumbs = $this->formataBreadcrumbs($_POST['seo_url_breadcrumbs']);
                } else {
                    $seo->breadcrumbs = "";
                }

                require_once __sys_path__ . "System/includes/seo.php";

                if ($seo_request->response) {



                    if ($formulario->id == "") {
                        //criar
                        if (!$this->permissions[$this->permissao_ref]['gravar'])
                            exit();

                        $data->id_seo = $seo_request->id;


                        foreach ($data as $key => $value) {
                            $fields[] = $key;
                            $values[] = "'$value'";
                        }

                        $query = $this->DB_insert($this->table, implode(',', $fields), implode(',', $values));
                        if ($query->query) {
                            $resposta->type = "success";
                            $resposta->message = "Registro cadastrado com sucesso!";
                            $this->inserirRelatorio("Cadastrou institucional: [" . $data->nome . "]");
                        } else {
                            $resposta->type = "error";
                            $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
                        }
                    } else {
                        //alterar
                        if (!$this->permissions[$this->permissao_ref]['editar'])
                            exit();


                        foreach ($data as $key => $value) {
                            $fields_values[] = "$key='$value'";
                        }

                        $query = $this->DB_update($this->table, implode(',', $fields_values) . " WHERE id=" . $data->id);
                        if ($query) {
                            $resposta->type = "success";
                            $resposta->message = "Registro alterado com sucesso!";
                            $this->inserirRelatorio("Alterou institucional: [" . $data->nome . "]");
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

    private function validaFormulario($form) {

        $resposta = new \stdClass();
        $resposta->return = true;

        if ($form->id_categoria == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "id_categoria";
            $resposta->return = false;
            return $resposta;
        } else if ($form->nome == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "nome";
            $resposta->return = false;
            return $resposta;
        } else if ($form->texto == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "texto";
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

            $upload = $this->uploadFile("fileupload", array("jpg", "jpeg", "gif", "png"), $this->crop_imagem_sizes);
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

    private function uploadAction() {

        if ($this->getParameter("id") && $this->getParameter("session")) {

            $consulta = $this->DB_fetch_array("SELECT * FROM tb_admin_users WHERE session = '{$this->getParameter('session')}'");
            if ($consulta->num_rows) {

                $this->id = $this->getParameter("id");

                if (is_uploaded_file($_FILES['file']['tmp_name'])) {

                    echo $_FILES['file']['tmp_name'];

                    $upload = $this->uploadFile("file", array("jpg", "jpeg", "gif", "png"), $this->crop_sizes);
                    if ($upload->return) {

                        $file_uploaded = $upload->file_uploaded;

                        $dados = $this->DB_fetch_array("SELECT id, MAX(ordem) as ordem FROM $this->table2 WHERE id_pagina=" . $this->id . " GROUP BY id_pagina");
                        if ($dados->num_rows == 0) {
                            $ordem = 1;
                        } else {
                            $ordem = $dados->rows[0]['ordem'];
                            $ordem++;
                        }

                        $fields = array('id_pagina', 'stats', 'ordem', 'url');
                        $values = array($this->id, '1', "'" . $ordem . "'", "'" . $file_uploaded . "'");

                        $query = $this->DB_insert($this->table2, implode(',', $fields), implode(',', $values));
                        $idFoto = $query->insert_id;
                        if ($query->query) {
                            $_SESSION['admin_id'] = $consulta->rows[0]['id'];
                            $_SESSION['admin_nome'] = $consulta->rows[0]['nome'];
                            $this->inserirRelatorio("Cadastrou imagem institucional id página: [" . $this->id . "], id foto: [" . $idFoto . "]");
                        }

                        echo '{"jsonrpc" : "2.0", "result" : null, "id" : "id"}';
                    }
                }
            }
        }
    }

    private function uploadDelAction() {
        if (!$this->permissions[$this->permissao_ref]['excluir'])
            $this->noPermission(false);

        $this->id = $this->getParameter("id");
        $fotos = $this->DB_fetch_array("SELECT * FROM $this->table2 WHERE id = $this->id");
        if ($fotos->num_rows) {
            foreach ($fotos->rows as $foto) {
                $this->deleteFile($this->table2, "url", "id=" . $foto['id'], $this->crop_sizes);
            }
        }
        $this->inserirRelatorio("Apagou imagem instiucional legenda: [" . $fotos->rows[0]['legenda'] . "] id: [$this->id]");
        $this->DB_delete($this->table2, "id=$this->id");
    }

    private function orderAction() {

        if (!$this->permissions[$this->permissao_ref]['editar'])
            $this->noPermission(false);

        $this->ordenarRegistros($_POST["array"], $this->table2);
    }

    private function editPhotoAction() {
        if (!$this->permissions[$this->permissao_ref]['editar'])
            $this->noPermission(false);

        $id = $_POST['id'];
        $legenda = $_POST['legenda'];

        $fields_values = array(
            "legenda='" . $legenda . "'"
        );

        $query = $this->DB_update($this->table2, implode(',', $fields_values) . " WHERE id=" . $id);

        $resposta = new stdClass();

        if ($query) {
            $resposta->type = "success";
            $this->inserirRelatorio("Editou imagem institucional legenda: [" . $legenda . "] id: [" . $id . "]");
        } else {
            $resposta->type = "error";
            $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
        }

        echo json_encode($resposta);
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
