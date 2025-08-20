<?php

use System\Core\Bootstrap;

BannerSeveral::setAction();

class BannerSeveral extends Bootstrap {

    public $module = "";
    public $permissao_ref = "banners";
    public $table = "tb_banners_avulsos";

    function __construct() {
        parent::__construct();
        if (!isset($_SESSION['admin_logado'])) {
            header("Location: " . $this->system_path . "login");
        }
        $this->getAllPermissions();

        $this->module_icon = "icomoon-icon-image-3";
        $this->module_link = "bannerseveral";
        $this->module_title = "Banners Avulsos sem Título";
        $this->retorno = "bannerseveral";

        $this->id = 2;

        $this->banner1_uploaded = "";
        $this->crop_image1_sizes = array();
        array_push($this->crop_image1_sizes, array("width" => 560, "height" => 150));

        $this->banner2_uploaded = "";
        $this->crop_image2_sizes = array();
        array_push($this->crop_image2_sizes, array("width" => 560, "height" => 150));
    }

    private function indexAction() {
        if ($this->id == "") {
            //new            
            $this->noPermission();
        } else {
            //edit
            if (!$this->permissions[$this->permissao_ref]['editar'])
                $this->noPermission();

            $query = $this->DB_fetch_array("SELECT * FROM $this->table  WHERE id = $this->id", "form");
            $this->registro = $query->rows[0];
        }

        $this->renderView($this->getModule(), "edit");
    }

    private function saveAction() {
        $formulario = $this->formularioObjeto($_POST);

        $validacao = $this->validaUpload($formulario);

        if (!$validacao->return) {
            echo json_encode($validacao);
        } else {
            $resposta = new \stdClass();

            $data = $this->formularioObjeto($_POST, $this->table);

            if ($this->banner1_uploaded != "") {
                $data->imagem1 = $this->banner1_uploaded;
                if ($formulario->id != "")
                    $this->deleteFile($this->table, "imagem1", "id=" . $data->id, $this->crop_image1_sizes);
            }

            if ($this->banner2_uploaded != "") {
                $data->imagem2 = $this->banner2_uploaded;
                if ($formulario->id != "")
                    $this->deleteFile($this->table, "imagem2", "id=" . $data->id, $this->crop_image2_sizes);
            }

            if ($formulario->id == "") {
                //criar
                if (!$this->permissions[$this->permissao_ref]['gravar'])
                    exit();

                $resposta->type = "error";
                $resposta->message = "Esse módulo não existe opção para criação!";
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
                    $this->inserirRelatorio("Alterou banner avulso sem título");
                } else {
                    $resposta->type = "error";
                    $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
                }
            }

            echo json_encode($resposta);
        }
    }

    private function validaUpload($form) {

        $resposta = new \stdClass();
        $resposta->return = true;

        if (is_uploaded_file($_FILES["fileupload1"]["tmp_name"])) {
            $upload1 = $this->uploadFile("fileupload1", array("jpg", "jpeg", "gif", "png"), $this->crop_image1_sizes);
            if ($upload1->return) {
                $this->banner1_uploaded = $upload1->file_uploaded;
            }
        }

        if (is_uploaded_file($_FILES["fileupload2"]["tmp_name"])) {
            $upload2 = $this->uploadFile("fileupload2", array("jpg", "jpeg", "gif", "png"), $this->crop_image2_sizes);
            if ($upload2->return) {
                $this->banner2_uploaded = $upload2->file_uploaded;
            }
        }

        if (isset($upload1) && !$upload1->return) {
            $resposta->type = "attention";
            $resposta->message = $upload1->message;
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
