<?php

use System\Core\Bootstrap;

Expedition::setAction();

class Expedition extends Bootstrap {
    public $module = "";
    public $permissao_ref = "expedicoes";
    public $table = "tb_expedicoes_expedicoes";

    function __construct() {
        parent::__construct();
        if (!isset($_SESSION['admin_logado'])) {
            header("Location: " . $this->system_path . "login");
            exit;
        }
        $this->getAllPermissions();

        $this->module_icon = "icomoon-icon-barcode";
        $this->module_link = "expedition";
        $this->module_title = "Etiquetas";
        $this->retorno = "expedition";

        $this->departamentos = [
            'comunicacao' => 'Comunicação Visual',
            'fichas' => 'Fichas',
            'marcenaria' => 'Marcenaria',
        ];
    }

    private function indexAction() {
        if (!$this->permissions[$this->permissao_ref]['ler']) {
            $this->noPermission();
        }

        $this->list = $this->DB_fetch_array("SELECT A.*, B.nome AS transportadora, C.nome AS user FROM {$this->table} A INNER JOIN tb_expedicoes_transportadoras B ON B.id = A.id_transportadora LEFT JOIN tb_admin_users C ON C.id = A.id_user WHERE A.coleta_confirmada IS NULL OR DATE(A.coleta_confirmada) > date_sub(NOW(),INTERVAL 1 MONTH) ORDER BY id DESC");
        $this->renderView($this->getModule(), "index");
    }

    private function editAction() {
        $this->id = $this->getParameter("id");

        if ($this->id == "") {
            //new
            if (!$this->permissions[$this->permissao_ref]['gravar']) {
                $this->noPermission();
            }

            $campos = $this->DB_columns($this->table);
            foreach ($campos as $campo) {
                $this->registro[$campo] = "";
            }
        } else {
            //edit
            if (!$this->permissions[$this->permissao_ref]['editar']) {
                $this->noPermission();
            }

            $query = $this->DB_fetch_array(
                "SELECT * FROM {$this->table} WHERE id = {$this->id}"
            );
            $this->registro = ($query->num_rows) ? $query->rows[0] : [];
        }
        $query = $this->DB_fetch_array("SELECT * FROM tb_expedicoes_transportadoras ORDER BY nome");
        $this->transportadoras = ($query->num_rows) ? $query->rows : [];

        $query = $this->DB_fetch_array("SELECT * FROM tb_utils_estados ORDER BY estado");
        $this->estados = ($query->num_rows) ? $query->rows : [];

        $this->renderView($this->getModule(), "edit");
    }

    private function orderAction() {
        if (!$this->permissions[$this->permissao_ref]['ler']) {
            $this->noPermission();
        }

        $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_STRING) ?? 0;
        $query = $this->DB_fetch_array("SELECT A.id AS id_pedido, A.n_nota, B.cep, B.endereco, B.numero, B.bairro, B.complemento, B.id_cidade, C.id_estado, D.nome, D.telefone, CASE WHEN D.cnpj IS NOT NULL THEN D.cnpj ELSE D.cpf END AS cpf_cnpj FROM tb_pedidos_pedidos A INNER JOIN tb_pedidos_enderecos B ON B.id_pedido = A.id INNER JOIN tb_utils_cidades C ON C.id = B.id_cidade INNER JOIN tb_clientes_clientes D ON D.id = A.id_cliente WHERE A.id = {$id} LIMIT 1");

        if ($query->num_rows) {
            echo json_encode($query->rows[0]);
        } else {
            http_response_code(404);
        }
    }

    private function delAction() {
        if (!$this->permissions[$this->permissao_ref]['excluir']) {
            exit();
        }

        $id = $this->getParameter("id");
        $dados = $this->DB_fetch_array("SELECT * FROM {$this->table} WHERE id = {$id} AND coleta_confirmada IS NULL");
        if ($dados->num_rows) {
            $this->DB_delete($this->table, "id = {$id}");
            $this->inserirRelatorio("Apagou transportadora: [{$dados->rows[0]['nome']}] id: [{$id}]");
            echo $this->getModule();
        } else {
            echo json_encode([
                'error' => "ID {$id} não pode ser removido ou não existe"
            ]);
        }
    }

    private function saveAction() {
        $formulario = $this->formularioObjeto($_POST);
        $validacao = $this->validaFormulario($formulario);
        if (!$validacao->return) {
            echo json_encode($validacao);
            return;
        }

        $resposta = new \stdClass();
        if (isset($_POST['data_coleta'])) {
            $_POST['data_coleta'] = $this->formataDataDeMascara($_POST['data_coleta']);
        }
        $data = $this->formularioObjeto($_POST, $this->table);

        if ($formulario->id == "") {
            //new
            if (!$this->permissions[$this->permissao_ref]['gravar']) {
                exit();
            }

            foreach ($data as $key => $value) {
                $fields[] = $key;
                if ($value == "NULL") $values[] = "{$value}";
                else $values[] = "'{$value}'";
            }

            $query = $this->DB_insert($this->table, implode(',', $fields), implode(',', $values));
            if ($query->query) {
                $resposta->type = "success";
                $resposta->message = "Registro cadastrado com sucesso!";
                $this->inserirRelatorio("Cadastrou expedição: [{$query->insert_id}]");
            } else {
                $resposta->type = "error";
                $resposta->message = "Aconteceu um erro no sistema, favor tentar novamente mais tarde!";
            }
        } else {
            //edit
            if (!$this->permissions[$this->permissao_ref]['editar']) {
                exit();
            }

            foreach ($data as $key => $value) {
                if ($value == "NULL") $fields_values[] = "{$key}={$value}";
                else $fields_values[] = "{$key}='{$value}'";
            }

            $query = $this->DB_update($this->table, implode(',', $fields_values) . " WHERE id={$data->id}");
            if ($query) {
                $resposta->type = "success";
                $resposta->message = "Registro alterado com sucesso!";
                $this->inserirRelatorio("Alterou expedição: [{$data->id}]");
            } else {
                $resposta->type = "error";
                $resposta->message = "Aconteceu um erro no sistema, favor tentar novamente mais tarde!";
            }
        }
        echo json_encode($resposta);
    }

    public function confirmarAction(){

        $resposta = new \stdClass();

        if($_SESSION['admin_grupo'] == 2){

            $id_usuario = $_SESSION['admin_id'];
            $id = $_POST['id'];
            
            $query = $this->DB_update($this->table, "id_user = ".$id_usuario.", coleta_confirmada = NOW() WHERE id = ".$id);

            if($query){
                $resposta->type = "success";
                $resposta->message = 'Coleta Confirmada!';
            }else{
                $resposta->type = "error";
                $resposta->message = 'Aconteceu algum erro, Chama o João!';
            }


        }else{
            $resposta->type = "attention";
            $resposta->message = 'Você não tem permissão para essa ação';
        }

        echo json_encode($resposta);

    }

    private function validaFormulario($form) {
        $resposta = new \stdClass();
        $resposta->return = true;

        if ($form->id_pedido == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "id_pedido";
            $resposta->return = false;
        } else if ($form->id_transportadora == "") {
            $resposta->type = "validation";
            $resposta->message = "Escolha uma opção";
            $resposta->field = "id_transportadora";
            $resposta->return = false;
        } else if ($form->data_coleta == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "data_coleta";
            $resposta->return = false;
        } else if (intval($form->volumes) <= 0) {
            $resposta->type = "validation";
            $resposta->message = "Número de volumes deve ser maior que 0";
            $resposta->field = "volumes";
            $resposta->return = false;
        } else if ($err = array_filter([
            'n_nota', 'nome', 'telefone', 'cpf_cnpj', 'cep', 'endereco',
            'numero', 'bairro', 'complemento', 'id_estado', 'id_cidade',
        ], function ($k) use ($form) {
            return !isset($form->$k);
        })) {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = $err[0];
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
