<?php

use System\Core\Bootstrap;

CassinoUser::setAction();

class CassinoUser extends Bootstrap {

    public $module = "";
    public $permissao_ref = "cassino-admin";
    public $table = "tb_admin_users";

    function __construct() {
        parent::__construct();
        if (!isset($_SESSION['admin_logado'])) {
            header("Location: " . $this->system_path . "login");
            exit;
        }
        $this->getAllPermissions();


        $this->module_icon = "icomoon-icon-users";
        $this->module_link = "user";
        $this->module_title = "Cassino Clientes";
        $this->retorno = "user";
        $this->avatar_uploaded = "";


        $this->crop_sizes = array();
        array_push($this->crop_sizes, array("width" => 38, "height" => 33));
        array_push($this->crop_sizes, array("width" => 40, "height" => 40));
    }

    private function indexAction() {

        if (!$this->permissions[$this->permissao_ref]['ler']) {
            $this->noPermission();
        }

        $this->list = $this->DB_fetch_array("SELECT B.nome grupo, A.* FROM $this->table A INNER JOIN tb_admin_grupos B ON B.id = A.id_grupo WHERE B.id = 7 ORDER BY A.nome");

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

            $query = $this->DB_fetch_array("SELECT * FROM $this->table WHERE id = $this->id");
            $this->registro = $query->rows[0];

        }

        $this->renderView($this->getModule(), "edit");
    }

    private function delAction() {
        if (!$this->permissions[$this->permissao_ref]['excluir'])
            exit();


        $id = $this->getParameter("id");
        if ($id == 1) {
            echo "error";
            exit();
        }

        $dados = $this->DB_fetch_array("SELECT * FROM $this->table WHERE id = $id");
        if ($dados->rows[0]['avatar'] != "") {
            $this->deleteFile($this->table, "avatar", "id=$id", $this->crop_sizes);
        }

        $this->inserirRelatorio("Apagou cliente cassino: [" . $dados->rows[0]['nome'] . "] id: [$id]");
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

                if ($this->avatar_uploaded != "") {
                    $data->avatar = $this->avatar_uploaded;
                    if ($formulario->id != "")
                        $this->deleteFile($this->table, "avatar", "id=" . $data->id, $this->crop_sizes);
                }

                if ($formulario->id == "") {
                    //criar
                    if (!$this->permissions[$this->permissao_ref]['gravar'])
                        exit();

                    $data->senha = $this->embaralhar($data->senha);

                    foreach ($data as $key => $value) {
                        $fields[] = $key;
                        $values[] = "'$value'";
                    }

                    $query = $this->DB_insert($this->table, implode(',', $fields), implode(',', $values));
                    if ($query->query) {
                        $resposta->type = "success";
                        $resposta->message = "Registro cadastrado com sucesso!";
                        $this->inserirRelatorio("Cadastrou cliente cassino: [" . $data->nome . "]");
                    } else {
                        $resposta->type = "error";
                        $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
                    }
                } else {
                    //alterar

                    if (!$this->permissions[$this->permissao_ref]['editar'])
                        exit();

                    if ($data->senha != "")
                        $data->senha = $this->embaralhar($data->senha);
                    else
                        unset($data->senha);

                    foreach ($data as $key => $value) {
                        $fields_values[] = "$key='$value'";
                    }

                    $query = $this->DB_update($this->table, implode(',', $fields_values) . " WHERE id=" . $data->id);
                    if ($query) {
                        $this->DB_delete("tb_user_permissoes", "id_usuario=" . $data->id);
                        if (isset($formulario->pi) && $formulario->pi != "")
                            $this->setPermissions($data->id, $_POST);
                        $resposta->type = "success";
                        $resposta->message = "Registro alterado com sucesso!";
                        $this->inserirRelatorio("Alterou cliente cassino: [" . $data->nome . "]");
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
        } else if ($form->email == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "email";
            $resposta->return = false;
            return $resposta;
        } else if ($form->telefone == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "telefone";
            $resposta->return = false;
            return $resposta;
        } else if ($form->usuario == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "usuario";
            $resposta->return = false;
            return $resposta;
        } else if ($form->id == "" && $form->senha = "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "senha";
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
                $this->avatar_uploaded = $upload->file_uploaded;
                $_SESSION['admin_avatar'] = $this->getImageFileSized($upload->file_uploaded, 38, 33);
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
