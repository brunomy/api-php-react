<?php

use System\Core\Bootstrap;

Group::setAction();

class Group extends Bootstrap {

    public $module = "";
    public $permissao_ref = "admin-grupos";
    public $table = "tb_admin_grupos";

    function __construct() {
        parent::__construct();
        if (!isset($_SESSION['admin_logado'])) {
            header("Location: " . $this->system_path . "login");
            exit;
        }
        $this->getAllPermissions();


        $this->module_icon = "icomoon-icon-users-2";
        $this->module_link = "group";
        $this->module_title = "Grupos de Usuário";
        $this->retorno = "group";
    }

    private function indexAction() {

        if (!$this->permissions[$this->permissao_ref]['ler']) {
            $this->noPermission();
        }

        $this->list = $this->DB_fetch_array("SELECT * FROM $this->table WHERE id != 1 ORDER BY nome");

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

            $this->funcoes = $this->DB_fetch_array("SELECT * FROM tb_admin_funcoes ORDER BY nome");
        } else {
            //edit
            if (!$this->permissions[$this->permissao_ref]['editar'])
                $this->noPermission();

            $query = $this->DB_fetch_array("SELECT * FROM $this->table WHERE id = $this->id");
            $this->registro = $query->rows[0];

            $this->funcoes = $this->DB_fetch_array("SELECT A.id, A.nome, B.ler, B.gravar, B.excluir, B.editar FROM tb_admin_funcoes A LEFT JOIN (SELECT * FROM tb_admin_permissoes WHERE id_grupo = $this->id) B ON A.id=B.id_funcao ORDER BY A.nome");
        }

        $this->renderView($this->getModule(), "edit");
    }

    private function delAction() {
        if (!$this->permissions[$this->permissao_ref]['excluir'])
            exit();



        $id = $this->getParameter("id");
        if ($id == 1 or $id == 2) {
            echo "error";
            exit();
        }

        $dados = $this->DB_fetch_array("SELECT * FROM $this->table WHERE id = $id");

        $this->inserirRelatorio("Apagou grupo: [" . $dados->rows[0]['nome'] . "] id: [$id]");
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

                foreach ($data as $key => $value) {
                    $fields[] = $key;
                    $values[] = "'$value'";
                }

                $query = $this->DB_insert($this->table, implode(',', $fields), implode(',', $values));
                if ($query->query) {
                    $this->setPermissions($query->insert_id, $_POST);
                    $resposta->type = "success";
                    $resposta->message = "Registro cadastrado com sucesso!";
                    $this->inserirRelatorio("Cadastrou grupo: [" . $data->nome . "]");
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
                    $this->DB_delete("tb_admin_permissoes", "id_grupo=" . $data->id);
                    $this->setPermissions($data->id, $_POST);
                    $resposta->type = "success";
                    $resposta->message = "Registro alterado com sucesso!";
                    $this->inserirRelatorio("Alterou grupo: [" . $data->nome . "]");
                } else {
                    $resposta->type = "error";
                    $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
                }
            }

            echo json_encode($resposta);
        }
    }

    private function setPermissions($idGrupo, $post) {
        $funcoes = $this->DB_fetch_array("SELECT * FROM tb_admin_funcoes");

        $insertData = array();
        foreach ($funcoes->rows as $funcao) {
            $ler = 0;
            if (isset($post['ler'])) {
                foreach ($post['ler'] as $value) {
                    if ($value == $funcao['id']) {
                        $ler = 1;
                    }
                }
            }
            $gravar = 0;
            if (isset($post['gravar'])) {
                foreach ($post['gravar'] as $value) {
                    if ($value == $funcao['id']) {
                        $gravar = 1;
                    }
                }
            }
            $editar = 0;
            if (isset($post['editar'])) {
                foreach ($post['editar'] as $value) {
                    if ($value == $funcao['id']) {
                        $editar = 1;
                    }
                }
            }
            $excluir = 0;
            if (isset($post['excluir'])) {
                foreach ($post['excluir'] as $value) {
                    if ($value == $funcao['id']) {
                        $excluir = 1;
                    }
                }
            }

            $insertData[] = "(" . $idGrupo . "," . $funcao['id'] . ",$ler,$gravar,$editar,$excluir)";
            //$this->DB_insert("tb_admin_permissoes", "id_grupo,id_funcao,ler,gravar,editar,excluir", $formulario->id.",".$funcao['id'].",$ler,$gravar,$editar,$excluir");
        }

        $this->DB_connect();
        $this->mysqli->query("INSERT INTO tb_admin_permissoes (id_grupo,id_funcao,ler,gravar,editar,excluir) VALUES " . implode(',', $insertData));
        $this->DB_disconnect();
    }

    public function validaFormulario($form) {

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
