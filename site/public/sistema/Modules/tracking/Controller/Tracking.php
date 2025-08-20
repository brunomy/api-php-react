<?php

use System\Core\Bootstrap;

Tracking::setAction();

class Tracking extends Bootstrap {

    public $module = "";
    public $permissao_ref = "pedidos-pedidos";
    public $table = "tb_pedidos_rastreios";

    function __construct() {
        parent::__construct();
        if (!isset($_SESSION['admin_logado'])) {
            header("Location: " . $this->system_path . "login");
        }
        $this->getAllPermissions();

        $this->module_icon = "icomoon-icon-truck";
        $this->module_link = "tracking";
        $this->module_title = "Rastreios";
        $this->retorno = "tracking";
    }

    private function indexAction() {
        if (!$this->permissions[$this->permissao_ref]['ler']) {
            $this->noPermission();
        }

        $this->list = $this->DB_fetch_array("SELECT a.id, c.id pedido, a.link, a.descricao, a.data, c.prazo_entrega, d.nome, d.telefone, e.cidade, f.estado, a.stats FROM tb_pedidos_rastreios a INNER JOIN tb_pedidos_has_tb_rastreios b ON b.id_rastreio = a.id INNER JOIN tb_pedidos_pedidos c ON b.id_pedido = c.id INNER JOIN tb_clientes_clientes d ON c.id_cliente = d.id INNER JOIN tb_utils_cidades e ON d.id_cidade = e.id INNER JOIN tb_utils_estados f ON d.id_estado = f.id WHERE c.prazo_entrega > CURRENT_DATE - INTERVAL 120 DAY ORDER BY a.stats, c.prazo_entrega ASC");

        $this->renderView($this->getModule(), "index");
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
