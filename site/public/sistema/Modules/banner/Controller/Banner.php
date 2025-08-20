<?php

use System\Core\Bootstrap;

Banner::setAction();

class Banner extends Bootstrap {

    public $module = "";
    public $permissao_ref = "banners";
    public $table = "tb_banners_banners";

    function __construct() {
        parent::__construct();
        if (!isset($_SESSION['admin_logado'])) {
            header("Location: " . $this->system_path . "login");
        }
        $this->getAllPermissions();

        $this->module_icon = "icomoon-icon-image-3";
        $this->module_link = "banner";
        $this->module_title = "Banners";
        $this->retorno = "banner";
        $this->banner_uploaded = "";

        $this->crop_sizes = array();

        array_push($this->crop_sizes, array("width" => 930, "height" => 405, "best_fit" => true));
        array_push($this->crop_sizes, array("width" => 2000, "height" => 400, "best_fit" => true));
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

        $dados = $this->DB_fetch_array("SELECT * FROM $this->table WHERE id = $id");
        if ($dados->rows[0]['imagem'] != "") {
            $this->deleteFile($this->table, "imagem", "id=$id", $this->crop_sizes);
        }

        $this->inserirRelatorio("Apagou banner: [" . $dados->rows[0]['nome'] . "] id: [$id]");
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

                $data = $this->formularioObjeto($_POST, $this->table);

                if ($this->banner_uploaded != "") {
                    $data->imagem = $this->banner_uploaded;
                    if ($formulario->id != "")
                        $this->deleteFile($this->table, "imagem", "id=" . $data->id, $this->crop_sizes);
                }

                if ($formulario->id == "") {
                    //criar
                    if (!$this->permissions[$this->permissao_ref]['gravar'])
                        exit();

                    foreach ($data as $key => $value) {
                        $fields[] = $key;
                        $values[] = "'$value'";
                    }

                    $query = $this->DB_insert($this->table, implode(',', $fields), implode(',', $values));
                    if ($query->query) {
                        $resposta->type = "success";
                        $resposta->message = "Registro cadastrado com sucesso!";
                        $this->inserirRelatorio("Cadastrou banner: [" . $data->nome . "]");
                    } else {
                        $resposta->type = "error";
                        $resposta->message = "Aconteceu um erro no sistemaa, favor tente novamente mais tarde!";
                        print_r($query);
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
                        $this->inserirRelatorio("Alterou banner: [" . $data->nome . "]");
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

            $upload = $this->uploadFile("fileupload", array("jpg", "jpeg", "gif", "png"), $this->crop_sizes);
            if ($upload->return) {
                $this->banner_uploaded = $upload->file_uploaded;
            }
        }

        if ((!isset($form->id) || $form->id == "") && !isset($upload)) {
            $resposta->type = "attention";
            $resposta->message = "Imagem não selecionada.";
            $resposta->return = false;
            return $resposta;
        } else if (isset($upload) && !$upload->return) {
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
