<?php

use System\Core\Bootstrap;

OrderStatus::setAction();

class OrderStatus extends Bootstrap {

    public $module = "";
    public $permissao_ref = "pedidos-status";
    public $table = "tb_pedidos_status";

    function __construct() {
        parent::__construct();
        if (!isset($_SESSION['admin_logado'])) {
            header("Location: " . $this->system_path . "login");
        }
        $this->getAllPermissions();

        $this->module_icon = "icomoon-icon-checkmark";
        $this->module_link = "orderstatus";
        $this->module_title = "Status de Pagamento";
        $this->retorno = "orderstatus";
    }

    private function indexAction() {
        if (!$this->permissions[$this->permissao_ref]['ler']) {
            $this->noPermission();
        }

        $this->list = $this->DB_fetch_array("SELECT * FROM $this->table ORDER BY ordem");

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
            $this->cielo = $this->DB_fetch_array("SELECT * FROM tb_pedidos_status_cielo A LEFT JOIN tb_pedidos_status_has_tb_pedidos_status_cielo B ON B.id_cielo_status = A.id AND B.id_pedido_status = 0 ORDER BY A.nome");
            $this->pagseguro = $this->DB_fetch_array("SELECT * FROM tb_pedidos_status_pagseguro A LEFT JOIN tb_pedidos_status_has_tb_pedidos_status_pagseguro B ON B.id_pagseguro_status = A.id AND B.id_pedido_status = 0 ORDER BY A.nome");
            $this->notificacoes = $this->DB_fetch_array("SELECT A.id, A.nome, A.email, B.* FROM tb_admin_users A LEFT JOIN tb_pedidos_status_has_users_notification B ON B.id_usuario = A.id AND B.id_pedido_status = 0 ORDER BY A.nome");
        } else {
            //edit
            if (!$this->permissions[$this->permissao_ref]['editar'])
                $this->noPermission();

            $query = $this->DB_fetch_array("SELECT * FROM $this->table  WHERE id = $this->id", "form");
            $this->registro = $query->rows[0];

            $this->cielo = $this->DB_fetch_array("SELECT * FROM tb_pedidos_status_cielo A LEFT JOIN tb_pedidos_status_has_tb_pedidos_status_cielo B ON B.id_cielo_status = A.id AND B.id_pedido_status = {$this->registro['id']} ORDER BY A.nome");
            $this->pagseguro = $this->DB_fetch_array("SELECT * FROM tb_pedidos_status_pagseguro A LEFT JOIN tb_pedidos_status_has_tb_pedidos_status_pagseguro B ON B.id_pagseguro_status = A.id AND B.id_pedido_status = {$this->registro['id']} ORDER BY A.nome");
            $this->notificacoes = $this->DB_fetch_array("SELECT A.id, A.nome, A.email, B.* FROM tb_admin_users A LEFT JOIN tb_pedidos_status_has_users_notification B ON B.id_usuario = A.id AND B.id_pedido_status = {$this->registro['id']} ORDER BY A.nome");
        }

        $this->renderView($this->getModule(), "edit");
    }

    private function orderAction() {
        if (!$this->permissions[$this->permissao_ref]['excluir'])
            exit();
        $this->ordenarRegistros($_POST["array"], $this->table);
    }

    private function delAction() {
        if (!$this->permissions[$this->permissao_ref]['excluir'])
            exit();

        $id = $this->getParameter("id");

        if ($id <= 1) {
            echo 'error';
            exit;
        }
        $dados = $this->DB_fetch_array("SELECT * FROM $this->table WHERE id = $id");

        $verificar = $this->DB_fetch_array("SELECT * FROM tb_pedidos_pedidos WHERE id_status = $id");
        if ($verificar->num_rows) {
            echo "error";
            exit();
        }

        $this->inserirRelatorio("Apagou status de pedido: [" . $dados->rows[0]['nome'] . "] id: [$id]");
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

                $ordem = $this->DB_fetch_array("SELECT MAX(id) ordem FROM $this->table");
                if ($ordem->num_rows) {
                    $data->ordem = $ordem->rows[0]['ordem'] + 1;
                } else {
                    $data->ordem = 0;
                }

                foreach ($data as $key => $value) {
                    $fields[] = $key;
                    $values[] = "'$value'";
                }

                $query = $this->DB_insert($this->table, implode(',', $fields), implode(',', $values));
                $idStatus = $query->insert_id;
                if ($query->query) {

                    if (isset($_POST['cielo'])) {
                        foreach ($_POST['cielo'] as $cielo) {
                            $this->DB_insert("tb_pedidos_status_has_tb_pedidos_status_cielo", "id_pedido_status, id_cielo_status", "$idStatus, $cielo");
                        }
                    }

                    if (isset($_POST['pagseguro'])) {
                        foreach ($_POST['pagseguro'] as $pagseguro) {
                            $this->DB_insert("tb_pedidos_status_has_tb_pedidos_status_pagseguro", "id_pedido_status, id_pagseguro_status", "$idStatus, $pagseguro");
                        }
                    }

                    if (isset($_POST['notificacoes'])) {
                        foreach ($_POST['notificacoes'] as $notificacao) {
                            $this->DB_insert("tb_pedidos_status_has_users_notification", "id_pedido_status, id_usuario", "$idStatus, $notificacao");
                        }
                    }

                    $resposta->type = "success";
                    $resposta->message = "Registro cadastrado com sucesso!";
                    $this->inserirRelatorio("Cadastrou status de pedido: [" . $data->nome . "]");
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

                    $this->DB_delete("tb_pedidos_status_has_tb_pedidos_status_cielo", "id_pedido_status = $data->id");
                    if (isset($_POST['cielo'])) {
                        foreach ($_POST['cielo'] as $cielo) {
                            $this->DB_insert("tb_pedidos_status_has_tb_pedidos_status_cielo", "id_pedido_status, id_cielo_status", "$data->id, $cielo");
                        }
                    }

                    $this->DB_delete("tb_pedidos_status_has_tb_pedidos_status_pagseguro", "id_pedido_status = $data->id");
                    if (isset($_POST['pagseguro'])) {
                        foreach ($_POST['pagseguro'] as $pagseguro) {
                            $this->DB_insert("tb_pedidos_status_has_tb_pedidos_status_pagseguro", "id_pedido_status, id_pagseguro_status", "$data->id, $pagseguro");
                        }
                    }

                    $this->DB_delete("tb_pedidos_status_has_users_notification", "id_pedido_status = $data->id");
                    if (isset($_POST['notificacoes'])) {
                        foreach ($_POST['notificacoes'] as $notificacao) {
                            $this->DB_insert("tb_pedidos_status_has_users_notification", "id_pedido_status, id_usuario", "$data->id, $notificacao");
                        }
                    }

                    $resposta->type = "success";
                    $resposta->message = "Registro alterado com sucesso!";
                    $this->inserirRelatorio("Alterou status de pedido: [" . $data->nome . "]");
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
