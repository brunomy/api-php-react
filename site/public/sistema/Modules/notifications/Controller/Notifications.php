<?php

use System\Core\Bootstrap;

Notifications::setAction();

class Notifications extends Bootstrap {

    public $module = "";
    public $permissao_ref = "admin-notificacoes";
    public $table = "tb_admin_forms";

    function __construct() {
        parent::__construct();
        if (!isset($_SESSION['admin_logado'])) {
            header("Location: " . $this->system_path . "login");
            exit;
        }
        $this->getAllPermissions();


        $this->module_icon = "icomoon-icon-bubble-notification";
        $this->module_link = "notifications";
        $this->module_title = "Notificações";
        $this->retorno = "notifications";
    }

    private function indexAction() {

        if (!$this->permissions[$this->permissao_ref]['ler']) {
            $this->noPermission();
        }

        $this->list = $this->DB_fetch_array("SELECT * FROM $this->table A");

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

        $this->users = $this->DB_fetch_array("SELECT * FROM tb_admin_users A LEFT JOIN (SELECT * FROM tb_admin_email_notification WHERE id_form = $this->id) B ON A.id=B.id_user", "form");

        $this->nome = $this->getById($this->id);
        $this->nome = $this->nome['nome'];

        $this->renderView($this->getModule(), "edit");
    }

    public function getById($id) {
        $query = $this->DB_fetch_array("SELECT * FROM $this->table WHERE id = $id");
        if ($query->num_rows)
            return $query->rows[0];
    }

    private function saveAction() {
        $resposta = new \stdClass();
        $data = $this->formularioObjeto($_POST, $this->table);


        if ($data->id == "") {
            //criar
            if (!$this->permissions[$this->permissao_ref]['gravar'])
                exit();

            $resposta = new stdClass();
            $resposta->type = "error";
            $resposta->time = 4000;
            $resposta->message = "Só é possível criar formulários manualmente, use o banco de dados!";
        } else {
            //alterar
            if (!$this->permissions[$this->permissao_ref]['editar'])
                exit();

            $this->DB_delete("tb_admin_email_notification", "id_form=" . $data->id);

            $funcoes = $this->DB_fetch_array("SELECT * FROM tb_admin_funcoes");

            if (isset($_POST['emails'])) {
                foreach ($_POST['emails'] as $email) {
                    $insertData[] = "(" . $data->id . "," . $email . ")";
                }
            }

            $this->DB_connect();
            if (isset($insertData))
                $this->mysqli->query("INSERT INTO tb_admin_email_notification (id_form,id_user) VALUES " . implode(',', $insertData));

            $this->DB_disconnect();

            $resposta = new stdClass();
            $resposta->type = "success";
            $resposta->message = "Registro alterado com sucesso!";
            $this->inserirRelatorio("Alterou notificações: id formulário: [" . $data->id . "]");
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
