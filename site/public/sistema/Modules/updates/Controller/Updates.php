<?php

use System\Core\Bootstrap;

Updates::setAction();

class Updates extends Bootstrap {

    public $module = "";
    public $permissao_ref = "system-updates";

    function __construct() {
        parent::__construct();
        if (!isset($_SESSION['admin_logado'])) {
            header("Location: " . $this->system_path . "login");
            exit;
        }
        
        $this->getAllPermissions();

        $this->table = "tb_system_updates";
        $this->module_title = "Atualizações do sistema";
        $this->module_icon = "";
        $this->module_link = "updates";
        $this->retorno = "updates";

        // $this->model = new Model();
    }

    public function indexAction() {
        if (!$this->permissions[$this->permissao_ref]['ler']) {
            $this->noPermission();
        }

        $this->list = $this->DB_fetch_array("SELECT S.*, U.nome name FROM tb_system_updates S LEFT JOIN tb_admin_users U ON U.id = S.initialized_by_user ORDER BY id DESC");

        $this->renderView($this->getModule(), "index");
    }

    public function executeAction() {
        if (!empty($_POST) && isset($_POST['id'])) {
            $id = (int) $_POST['id'];

            $data = "status = 'waiting', initialized_by_user = " . $_SESSION['admin_id'] . " WHERE id = " . $id;
            
            $result = $this->DB_update('tb_system_updates', $data);

            if ($result) {
                die(json_encode([
                    'status' => 'success',
                    'message' => 'A atualização será aplicada em breve!',
                    'data' => 'waiting'
                ]));
            }

            die(json_encode([
                'status' => 'error',
                'message' => 'Não foi possível atualizar o registro!'
            ]));
        }

        die(json_encode([
            'status' => 'error',
            'message' => 'Falta parametros!'
        ]));
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

?>