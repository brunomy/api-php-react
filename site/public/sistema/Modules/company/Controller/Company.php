<?php

use System\Core\Bootstrap;

Company::setAction();

class Company extends Bootstrap {

    public $module = "";
    public $permissao_ref = "admin-empresa";
    public $table = "tb_admin_empresa";

    function __construct() {
        parent::__construct();
        if (!isset($_SESSION['admin_logado'])) {
            header("Location: " . $this->system_path . "login");
            exit;
        }
        $this->getAllPermissions();

        $this->module_icon = "icomoon-icon-office";
        $this->module_link = "company";
        $this->module_title = "Configurações da Empresa";
        $this->retorno = "company";

        $this->logomarca_uploaded = "";
        $this->favicon_uploaded = "";

        $this->crop_logomarca = array();
        array_push($this->crop_logomarca, array("width" => 370, "height" => 141, "best_fit" => true));

        $this->crop_favicon = array();
        array_push($this->crop_favicon, array("width" => 16, "height" => 16, "best_fit" => true));
    }

    private function indexAction() {
        $this->id = 1;

        if (!$this->permissions[$this->permissao_ref]['editar'])
            $this->noPermission();

        $query = $this->DB_fetch_array("SELECT * FROM $this->table WHERE id = $this->id");
        $this->registro = $query->rows[0];

        $query = $this->DB_fetch_array("SELECT * FROM $this->table A", "form");
        $this->registro = $query->rows[0];

        $this->renderView($this->getModule(), "edit");
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

                if ($data->id == "") {
                    //criar
                    if (!$this->permissions[$this->permissao_ref]['gravar'])
                        exit();

                    $resposta = new stdClass();
                    $resposta->type = "error";
                    $resposta->time = 4000;
                    $resposta->message = "Só é possível editar, para criar use o banco de dados!";
                } else {
                    //alterar
                    if (!$this->permissions[$this->permissao_ref]['editar'])
                        exit();

                    if ($this->logomarca_uploaded != "") {
                        $this->deleteFile($this->table, "logomarca", "id=" . $data->id, $this->crop_logomarca);
                        $data->logomarca = $this->logomarca_uploaded;
                    }

                    if ($this->favicon_uploaded != "") {
                        $this->deleteFile($this->table, "favicon", "id=" . $data->id, $this->crop_favicon);
                        $data->favicon = $this->favicon_uploaded;
                    }

                    foreach ($data as $key => $value) {
                        $fields_values[] = "$key='$value'";
                    }

                    $query = $this->DB_update($this->table, implode(',', $fields_values) . " WHERE id=" . $data->id);
                    if ($query) {
                        $resposta->type = "success";
                        $resposta->message = "Registro alterado com sucesso!";
                        $this->inserirRelatorio("Alterou empresa: [" . $data->nome . "]");
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
        } else if ($form->endereco == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "endereco";
            $resposta->return = false;
            return $resposta;
        } else if ($form->bairro == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "bairro";
            $resposta->return = false;
            return $resposta;
        } else if ($form->cep == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "cep";
            $resposta->return = false;
            return $resposta;
        } else if ($form->cidade == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "cidade";
            $resposta->return = false;
            return $resposta;
        } else if ($form->estado == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "estado";
            $resposta->return = false;
            return $resposta;
        } else if ($form->email == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "email";
            $resposta->return = false;
            return $resposta;
        } else if ($this->validaEmail($form->email) == 0) {
            $resposta->type = "validation";
            $resposta->message = "Formato de Email Incorreto";
            $resposta->field = "email";
            $resposta->return = false;
            return $resposta;
        } else if ($form->autenticado == 1 && $form->email_host == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "email_host";
            $resposta->return = false;
            return $resposta;
        } else if ($form->autenticado == 1 && $form->email_port == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "email_port";
            $resposta->return = false;
            return $resposta;
        } else if ($form->autenticado == 1 && $form->email_user == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "email_user";
            $resposta->return = false;
            return $resposta;
        } else if ($form->autenticado == 1 && $form->email_password == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "email_password";
            $resposta->return = false;
            return $resposta;
        } else if ($form->autenticado == 1 && $form->email_padrao == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "email_padrao";
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

            $upload_logomarca = $this->uploadFile("fileupload", array("jpg", "jpeg", "gif", "png"), $this->crop_logomarca);
            if ($upload_logomarca->return) {
                $this->logomarca_uploaded = $upload_logomarca->file_uploaded;
            }
        }

        if (is_uploaded_file($_FILES["favicon"]["tmp_name"])) {

            $upload_favicon = $this->uploadFile("favicon", array("png"), $this->crop_favicon);
            if ($upload_favicon->return) {
                $this->favicon_uploaded = $upload_favicon->file_uploaded;
            }
        }


        if (isset($upload_logomarca) && !$upload_logomarca->return) {
            $resposta->type = "attention";
            $resposta->message = $upload_logomarca->message;
            $resposta->return = false;
            return $resposta;
        } else if (isset($upload_favicon) && !$upload_favicon->return) {
            $resposta->type = "attention";
            $resposta->message = $upload_favicon->message;
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
