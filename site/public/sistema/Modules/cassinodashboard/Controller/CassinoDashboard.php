<?php

use System\Core\Bootstrap;

CassinoDashboard::setAction();

class CassinoDashboard extends Bootstrap {

    public $module = "";
    public $permissao_ref = "cassino-clientes";
    public $custom = "";
    public $customTitle = "";

    function __construct() {
        parent::__construct();
        if (!isset($_SESSION['admin_logado'])) {
            header("Location: " . $this->system_path . "login");
            exit;
        }
        $this->getAllPermissions();

        $this->custom = "produto";
        $this->customTitle = "Produtos mais visitados";
    }

    private function indexAction() {
        $this->module_icon = "icomoon-icon-screen-2";
        $this->module_link = "dashboard";
        $this->module_title = "Painel";

        if (!$this->permissions[$this->permissao_ref]['ler']) {
            //$this->noPermission();
            header("Location: " . $this->system_path . "order");
            exit;
        }

        if ($_SESSION['admin_grupo'] != 7) {
            $query = $this->DB_fetch_array("SELECT * FROM tb_admin_users WHERE id_grupo = 7 ORDER BY nome");
            if($query->num_rows) $this->usuarios = $query->rows;

            if(isset($_POST['user'])){
                $query = $this->DB_fetch_array("SELECT * FROM tb_cassino_mesas WHERE id_user = ".$_POST['user']);
                if($query->num_rows) $this->charts_estatistica_rodadas = $query->rows;
                $this->user_id = $_POST['user'];
            }else{
                $query = $this->DB_fetch_array("SELECT * FROM tb_cassino_mesas WHERE id_user = ".$this->usuarios[0]['id']);
                if($query->num_rows) $this->charts_estatistica_rodadas = $query->rows;
                $this->user_id = $this->usuarios[0]['id'];
            }

        }else{
            $query = $this->DB_fetch_array("SELECT * FROM tb_cassino_mesas WHERE id_user = ".$_SESSION['admin_id']);
            if($query->num_rows) $this->charts_estatistica_rodadas = $query->rows;
        }

        $this->renderView($this->getModule(), "index");
    }


    /*
     * Métodos padrões da classe
     */

    private function setModule($module) {
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
