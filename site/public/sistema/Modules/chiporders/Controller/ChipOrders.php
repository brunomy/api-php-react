<?php

use System\Core\Bootstrap;

ChipOrders::setAction();

class ChipOrders extends Bootstrap {

    public $module = "";
    public $permissao_ref = "fichas-modelo";
    public $table = "tb_fichas_pedidos";

    function __construct() {
        parent::__construct();
        if (!isset($_SESSION['admin_logado'])) {
            header("Location: " . $this->system_path . "login");
        }
        $this->getAllPermissions();

        $this->module_icon = "icomoon-icon-tree";
        $this->module_link = "chipsmodel";
        $this->module_title = "Cadastro";
        $this->retorno = "chiporders";

        $this->crop_sizes = array();
        array_push($this->crop_sizes, array("width" => 75, "height" => 75));
        array_push($this->crop_sizes, array("width" => 160, "height" => 160));

    }

    private function indexAction() {
        if (!$this->permissions[$this->permissao_ref]['ler']) {
            $this->noPermission();
        }

        $this->list = $this->DB_fetch_array("SELECT A.id, A.id_pedido, A.foto, B.cidade, C.uf FROM tb_fichas_pedidos A JOIN tb_utils_cidades B ON A.id_cidade=B.id JOIN tb_utils_estados C ON A.id_estado=C.id");

        $this->renderView($this->getModule(), "index");
    }

    private function editAction() {
        $this->id = $this->getParameter("id");
        $estado = "";
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

            $query = $this->DB_fetch_array("SELECT * FROM $this->table  WHERE id = $this->id");
            $this->registro = $query->rows[0];

            $query = $this->DB_fetch_array("SELECT * FROM tb_utils_cidades");
            $this->cidades = $query->rows;

        }

        $query = $this->DB_fetch_array("SELECT * FROM tb_fichas_modelos");
        $this->modelos = array();
        if($query->num_rows) $this->modelos = $query->rows;

        $query = $this->DB_fetch_array("SELECT * FROM tb_utils_estados");
        $this->estados = $query->rows;



        $this->renderView($this->getModule(), "edit");
    }

    private function delAction() {
        if (!$this->permissions[$this->permissao_ref]['excluir'])
            exit();

        $id = $this->getParameter("id");
        
        $dados = $this->DB_fetch_array("SELECT * FROM $this->table WHERE id = $id");

        $this->deleteFile($this->table, "foto", "id=" . $id, $this->crop_sizes);
        $this->inserirRelatorio("Apagou ficha do pedido: [" . $dados->rows[0]['id_pedido'] . "] id: [$id]");
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
            $pedido = $this->DB_fetch_array("SELECT B.id_cidade, B.id_estado FROM tb_pedidos_pedidos A JOIN tb_clientes_clientes B ON A.id_cliente=B.id WHERE A.id = ".$formulario->id_pedido);

            $data->id_estado = $pedido->rows[0]['id_estado'];
            $data->id_cidade = $pedido->rows[0]['id_cidade'];

            if ($formulario->id == "") {
                //criar
                if (!$this->permissions[$this->permissao_ref]['gravar'])
                    exit();

                $data->foto = $this->foto;

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
                    $this->inserirRelatorio("Cadastrou ficha do pedido: [" . $data->id_pedido . "], id [$query->insert_id]");
                } else {
                    $resposta->type = "error";
                    $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
                }
            } else {
                //alterar
                if (!$this->permissions[$this->permissao_ref]['editar'])
                    exit();

                if ($this->foto != "") {
                    $data->foto = $this->foto;
                    if (isset($data->id) && $data->id != "")
                        $this->deleteFile($this->table, "foto", "id=" . $data->id, $this->crop_sizes);
                }

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
                    $this->inserirRelatorio("Alterou ficha do pedido: [" . $data->id_pedido . "], id: [$data->id]");
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
        $this->foto = "";
        if (is_uploaded_file($_FILES["foto"]["tmp_name"])) {
            $upload = $this->uploadFile("foto", array("jpg", "jpeg", "gif", "png"), $this->crop_sizes);
            if ($upload->return) {
                $this->foto = $upload->file_uploaded;
            }
        }

        if ((!isset($form->id) || $form->id == "") && !isset($upload)) {
            $resposta->type = "attention";
            $resposta->message = "Imagem não selecionada.";
            $resposta->return = false;
        } else if (isset($upload) && !$upload->return) {
            $resposta->type = "attention";
            $resposta->message = $upload->message;
            $resposta->return = false;
        } else if ($form->id_pedido == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "id_pedido";
            $resposta->return = false;
        } else if ($form->id_pedido == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "id_pedido";
            $resposta->return = false;
        } else if ($form->id_modelo == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "id_pedido";
            $resposta->return = false;
        }

        $query = $this->DB_fetch_array("SELECT A.id FROM tb_pedidos_pedidos A WHERE A.id = ".$form->id_pedido);

        if(!$query->num_rows){
            $resposta->type = "validation";
            $resposta->message = "Este pedido não existe";
            $resposta->field = "id_pedido";
            $resposta->return = false;
        }

        return $resposta;
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
