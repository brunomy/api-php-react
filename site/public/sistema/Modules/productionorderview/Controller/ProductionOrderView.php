<?php

use System\Core\Bootstrap;

ProductionOrderView::setAction();

class ProductionOrderView extends Bootstrap {

    public $module = "";
    public $permissao_ref = "producao-view-pedidos";
    public $table = "tb_pedidos_pedidos";

    function __construct() {
        parent::__construct();
        if (!isset($_SESSION['admin_logado'])) {
            header("Location: " . $this->system_path . "login");
            exit;
        }
        $this->getAllPermissions();

        $this->module_icon = "icomoon-icon-cart-4";
        $this->module_link = "productionorderview";
        $this->module_title = "Visualização de Pedidos";
        $this->retorno = "productionorderview";
    }

    private function indexAction() {

        if (!$this->permissions[$this->permissao_ref]['ler']) {
            $this->noPermission();
        }

        $this->renderView($this->getModule(), "index");
    }

    public function getPedidoAction(){
        $id = $this->getParameter("id");

        $query = $this->DB_fetch_array("SELECT A.id, B.nome status, C.nome comprador, D.cidade, E.uf, F.nome vendedor
            FROM tb_pedidos_pedidos A
            JOIN tb_pedidos_status B ON A.id_status = B.id
            JOIN tb_clientes_clientes C ON A.id_cliente = C.id
            LEFT JOIN tb_utils_cidades D ON C.id_cidade = D.id
            LEFT JOIN tb_utils_estados E ON C.id_estado = E.id
            left JOIN tb_admin_users F ON A.id_vendedor = F.id
            WHERE A.id = $id");

        if(!$query->num_rows) die("Pedido não encontrado");
        $this->dados_do_pedido = $query->rows[0];


        $query = $this->DB_fetch_array("SELECT A.id id_carrinho_produto, A.nome_produto, A.quantidade, A.observacao
            FROM tb_carrinho_produtos_historico A 
            WHERE A.id_pedido=$id
            ORDER BY A.id");

        $this->produtos = $query->rows;


        $query = $this->DB_fetch_array("SELECT A.id id_carrinho_produto, B.nome_conjunto, B.nome_atributo, B.texto, IF(C.ordem IS NOT NULL, C.ordem, 10000) ordem
            FROM tb_carrinho_produtos_historico A 
            JOIN tb_carrinho_atributos_historico B ON A.id = B.id_carrinho_produto_historico
            LEFT JOIN tb_produtos_conjuntos_atributos C ON C.id = B.id_conjunto_atributo
            WHERE A.id_pedido = $id
            ORDER BY ordem");

        if($query->num_rows) $this->atributos = $query->rows;

        $query = $this->DB_fetch_array("SELECT criado_por, deletado_por, REPLACE(texto, '\n', '<br />') texto, DATE_FORMAT(data, '%d/%m/%Y às %H:%i') data FROM tb_pedidos_observacoes WHERE id_pedido = {$id} AND ativo = 1 AND categoria = 'Transporte' ORDER BY id");
        $this->observacoes = [];

        if($query->num_rows) $this->observacoes = $query->rows;

        $this->renderView($this->getModule(), "pedido");
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
