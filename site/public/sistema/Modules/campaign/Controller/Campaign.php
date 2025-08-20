<?php

use System\Core\Bootstrap;

Campaign::setAction();

class Campaign extends Bootstrap {

    public $module = "";
    public $permissao_ref = "pedidos-pedidos";
    public $table = "tb_pedidos_pedidos";

    function __construct() {
        parent::__construct();
        if (!isset($_SESSION['admin_logado'])) {
            header("Location: " . $this->system_path . "login");
        }
        $this->getAllPermissions();

        $this->module_icon = "icomoon-icon-call-outgoing ";
        $this->module_link = "campaign";
        $this->module_title = "Campanhas Whatsapp";
        $this->retorno = "campaign";

    }

    private function indexAction() {
        if (!$this->permissions[$this->permissao_ref]['ler']) {
            $this->noPermission();
        }

        $statuses = $this->DB_fetch_array("SELECT A.nome FROM tb_pedidos_status A ORDER BY A.ordem ASC");
        $this->statuses = array();
        
        foreach ($statuses->rows as $value) {
            array_push($this->statuses, '"'.$value['nome'].'"');
        }

        $categorias = $this->DB_fetch_array("SELECT A.nome FROM tb_produtos_categorias A ORDER BY A.ordem ASC");
        $this->categorias = array();
        
        foreach ($categorias->rows as $value) {
            array_push($this->categorias, '"'.$value['nome'].'"');
        }


        $this->renderView($this->getModule(), "index");
    }

    function searchAction() {
        if (!$this->permissions[$this->permissao_ref]['ler']) {
            $this->noPermission();
        }

        $pedidos_de = date('Y-m-d', strtotime('-30 days'));
        $pedidos_ate = date('Y-m-d');

        if(!empty($_POST['pedidos_de']) && !empty($_POST['pedidos_de'])){
            $pedidos_de = $_POST['pedidos_de'];
            $pedidos_ate = $_POST['pedidos_ate'];
        }


        $valor_de = '';
        $valor_ate = '';
        if(isset($_POST['valor_de'])) $valor_de = ' AND A.valor_final >= '.$this->formataMoedaBd($_POST['valor_de']);
        if(isset($_POST['valor_ate'])) $valor_ate = 'AND A.valor_final <= '.$this->formataMoedaBd($_POST['valor_ate']);

        $status = implode("','", explode(',',$_POST['status']));
        $categorias = implode("','", explode(',',$_POST['categorias']));

        $query = "SELECT A.id pedido, IF(B.nome IS NULL, 'Sem Vendedor', B.nome) vendedor, DATE_FORMAT(A.data, '%Y-%m-%d %H:%i') data, DATE_FORMAT(A.data, '%d/%m/%Y') data2, IF(C.pessoa = 1, C.nome, C.razao_social) cliente, C.telefone, D.cidade, E.estado, F.nome status, F.cor, A.valor_final, A.metodo_pagamento, A.marcado
            FROM tb_pedidos_pedidos A 
            LEFT JOIN tb_admin_users B ON B.id = A.id_vendedor 
            INNER JOIN tb_clientes_clientes C ON A.id_cliente = C.id 
            INNER JOIN tb_utils_cidades D ON D.id = C.id_cidade INNER JOIN tb_utils_estados E ON E.id = C.id_estado
            INNER JOIN tb_pedidos_status F ON F.id = A.id_status
            INNER JOIN tb_carrinho_produtos_historico G ON G.id_pedido = A.id
            INNER JOIN tb_produtos_produtos_has_tb_produtos_categorias H ON H.id_produto = G.id_produto
            INNER JOIN tb_produtos_categorias I ON I.id = H.id_categoria
            WHERE DATE(A.data) BETWEEN '$pedidos_de' AND '$pedidos_ate' AND F.nome IN ('$status') AND I.nome IN ('$categorias') $valor_de $valor_ate
            GROUP BY A.id
            ORDER BY A.id DESC";

        //echo $query;
        
        $this->list = $this->DB_fetch_array($query);

        $this->renderAjax($this->getModule(), "table");

    }

    function toggleCheckAction(){

        $return = new stdClass();

        if(isset($_POST['action']) && isset($_POST['id'])){
            $check = 0;
            if($_POST['action'] == 'check'){
                $check = 1;
            }
            $query = $this->DB_update("tb_pedidos_pedidos", " marcado=".$check." WHERE id=" . $_POST['id']);
        }

    }

    function uncheckAllAction(){

        if(isset($_POST['uncheck']) && $_POST['uncheck']){
            $check = 0;
            if($_POST['action'] == 'check'){
                $check = 1;
            }
            $query = $this->DB_update("tb_pedidos_pedidos", " marcado=NULL ");
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
