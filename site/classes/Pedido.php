<?php

namespace classes;

if (!isset($_SESSION["seo_session"])) {
    $_SESSION["seo_session"] = uniqid();
}

use System\Core\Bootstrap;
use classes\Product;


$p = new Pedido();

if ($p->getParameter('pedido')) {
    $p->setAction();
}

class Pedido extends Bootstrap {

    private $descontos = array();

    function __construct() {

        parent::__construct();

        $this->pedido = "";

        $this->module = "";
    }

    //'PDF'
    private function pdfAction() {
        if (!isset($_GET['pedido'])) {
            header("Location: $this->root_path");
        } else {
            
            
            $pedido = $_GET['pedido'];
            $pedido = str_replace(" ", "+", $pedido);
           
            $new_code = explode("|", $this->desembaralhar($pedido));
         
            if (!isset($new_code[0]) && !isset($new_code[1])) {
                header("Location: $this->root_path");
            } else {

                $query = $this->DB_fetch_array("SELECT B.*, B.nome comprador, A.* FROM tb_pedidos_pedidos A INNER JOIN tb_clientes_clientes B ON B.id = A.id_cliente WHERE A.code = '{$new_code[0]}' OR A.code = '{$_GET['pedido']}'");
                if ($query->num_rows) {
                    $id = $query->rows[0]['id'];
                    $this->pedido = $query->rows[0];
                    //1 é para financeiro
                    //2 é para fábrica
                    if (isset($new_code[2])) {
                        $this->getPdf($id, $new_code[1], 2);
                    } else {
                        $this->getPdf($id, $new_code[1], 1);
                    }
                } else {
                    
            
                    header("Location: $this->root_path");
                }
            }
        }
    }

    private function getPdf($id, $produtos, $local) {

        $this->local = $local;
        $p = new Product();
        $this->produtos = $p->getCartProductsByPedido($id, $produtos);
        $query = $p->getEnderecoByPedido($id);
        $this->endereco = $query->rows[0];

        $this->renderSiteView("pedido");
    }

    public function renderSiteView($view) {
        if (file_exists(__DIR__ . "/view/$view.phtml")) {
            require_once __DIR__ . "/view/$view.phtml";
        } else {
            require_once "layouts/404.phtml";
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

    public function setAction() {

        #encontrar nome da classe pelo nome do arquivo e instanciá-la
        $class = explode(DIRECTORY_SEPARATOR, __FILE__);
        $class = str_replace(".php", "", end($class));

        #acionar o método da classe de acordo com o parâmetro da url
        $action = $this->getParameter(strtolower($class));
        $action = explode("?", $action);
        $newAction = $action[0] . "Action";

        #antes de acioná-lo, verifica se ele existe
        if (method_exists($this, $newAction)) {
            $this->setModule($class);
            $this->$newAction();
        } else if ($newAction == "Action") {
            $this->setModule($class);
            if (method_exists($this, 'indexAction'))
                $this->indexAction();
            else
                $this->errorAction();
        } else {
            $this->errorAction();
        }
    }

}
