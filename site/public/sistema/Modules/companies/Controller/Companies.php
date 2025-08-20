<?php

use System\Core\Bootstrap;
use System\Libs\BlingV3;

Companies::setAction();

class Companies extends Bootstrap {

    public $module = "companies";
    public $permissao_ref = "admin-empresa";
    public $table = "tb_admin_empresas";

    function __construct() {
        parent::__construct();
        if (!isset($_SESSION['admin_logado'])) {
            header("Location: " . $this->system_path . "login");
        }
        $this->getAllPermissions();

        $this->module_icon = "icomoon-icon-office";
        $this->module_link = "company";
        $this->module_title = "Empresas";
        $this->retorno = "companies";

    }

    private function indexAction() {
        if (!$this->permissions[$this->permissao_ref]['ler']) {
            $this->noPermission();
        }

        $this->list = $this->DB_fetch_array("SELECT * FROM $this->table");

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

            $query = $this->DB_fetch_array("SELECT * FROM $this->table  WHERE id = $this->id", "form");
            $this->registro = $query->rows[0];

        }


        $this->renderView($this->getModule(), "edit");
    }


    private function delAction() {
        if (!$this->permissions[$this->permissao_ref]['excluir'])
            exit();

        $id = $this->getParameter("id");
        
        $dados = $this->DB_fetch_array("SELECT * FROM $this->table WHERE id = $id");

        $pedidos = $this->DB_fetch_array("SELECT id FROM tb_pedidos_pedidos WHERE id_empresa = $id");

        if($pedidos->num_rows){
            $this->inserirRelatorio("Empresa [$id] " . $dados->rows[0]['nome_fantasia'] . " não pode ser apagada pois possui pedidos faturados nela.");
            echo "error";
        }else{

            if($dados->rows[0]['stats']){
                $this->inserirRelatorio("Empresa [$id] " . $dados->rows[0]['nome_fantasia'] . " não pode ser apagada pois é esta ativa no momento.");
                echo "error";
            }else{

                $this->inserirRelatorio("Apagou empresa: [" . $dados->rows[0]['nome_fantasia'] . "] id: [$id]");
                $this->DB_delete($this->table, "id=$id");

            }

        }

        echo $this->getModule();
    }

    private function statusAction() {
        $id = $_POST["i"];

        if ($this->permissions[$this->permissao_ref]['editar']) {
            $this->DB_update($this->table, "stats = 0");
            $this->DB_update($this->table, "stats = 1 WHERE id = $id");
            $this->inserirRelatorio("Ativou registro empresa [$id]");
            echo "desativar";
        } else {
            echo "Você não tem permissão para editar esta função";
        }
    }

    private function saveAction() {
        $formulario = $this->formularioObjeto($_POST);
        $validacao = $this->validaFormulario($formulario);
        if (!$validacao->return) {
            echo json_encode($validacao);
        } else {


            if (!$validacao->return) {
                echo json_encode($validacao);
            } else {
                $resposta = new \stdClass();

                $data = $this->formularioObjeto($_POST, $this->table);

                $data->payment_rede_token = str_replace(' ', '', $data->payment_rede_token);
                $data->payment_rede_store_id = str_replace(' ', '', $data->payment_rede_store_id);


                if ($formulario->id == "") {
                    //criar
                    if (!$this->permissions[$this->permissao_ref]['gravar'])
                        exit();

                    foreach ($data as $key => $value) {
                        $fields[] = $key;
                        if ($value == "NULL")
                            $values[] = "$value";
                        else
                            $values[] = "'$value'";
                    }

                    $query = $this->DB_insert($this->table, implode(',', $fields), implode(',', $values));
                    if ($query->query) {
                        $resposta->type = "success";
                        $resposta->message = "Registro cadastrado com sucesso!";
                        $this->inserirRelatorio("Cadastrou empresa: [" . $data->nome_fantasia . "]");
                    } else {
                        $resposta->type = "error";
                        $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
                    }
                } else {
                    //alterar
                    if (!$this->permissions[$this->permissao_ref]['editar'])
                        exit();

                    foreach ($data as $key => $value) {
                        if ($value == "NULL")
                            $fields_values[] = "$key=$value";
                        else
                            $fields_values[] = "$key='$value'";
                    }

                    $query = $this->DB_update($this->table, implode(',', $fields_values) . " WHERE id=" . $data->id);
                    if ($query) {
                        $resposta->type = "success";
                        $resposta->message = "Registro alterado com sucesso!";
                        $this->inserirRelatorio("Alterou empresa: [" . $data->nome_fantasia . "]");
                    } else {
                        $resposta->type = "error";
                        $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
                    }
                }

                echo json_encode($resposta);
            }
        }
    }

    private function blingAuthAction() {
        $data = $this->formularioObjeto($_POST);

        $data->status = 1;

        //variável enviada e retornada para validar a transação das chaves
        $state = md5($data->id);
        $_SESSION[$state] = $data->id;

        $this->DB_update($this->table, "invoice_bling_client_id='{$data->c_id}', invoice_bling_client_secret='{$data->c_secret}', invoice_bling_autorization_code=NULL,  invoice_bling_refresh_token=NULL, invoice_bling_token_expires_at=NULL WHERE id={$data->id}");

        $this->bling_company_id = $data->id;
        $data->redirect_auth_url = (new BlingV3($this))->getAuthorizetionUrl($state);

        echo json_encode($data);
    }

    private function blingReturnAction() {

        $data = $this->formularioObjeto($_GET);

        if(!$data->code || !$data->state) exit("Deu erro 1.");

        if(!isset($_SESSION[$data->state])) exit("Deu erro 2.");

        $this->bling_company_id = $_SESSION[$data->state];
        (new BlingV3($this))->authenticate($data->code);
        
        header('Location: '.$this->getModule().'/edit/id/'.$this->bling_company_id);

    }

    private function validaFormulario($form) {

        $resposta = new \stdClass();
        $resposta->return = true;

        if ($form->nome_fantasia == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "nome_fantasia";
            $resposta->return = false;
        } else if ($form->razao_social == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "razao_social";
            $resposta->return = false;
        } else if ($form->cnpj == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo corretamente";
            $resposta->field = "cnpj";
            $resposta->return = false;
        } else if (!$this->validaCNPJ($form->cnpj)) {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo com CNPJ válido";
            $resposta->field = "cnpj";
            $resposta->return = false;
        } else if (!isset($form->payment_rede_environment) || $form->payment_rede_environment == "" || ($form->payment_rede_environment != "production" && $form->payment_rede_environment != "sandbox")) {
            $resposta->type = "attention";
            $resposta->message = "Selecione o ambiente Teste ou Produção";
            $resposta->return = false;
        } else if ($form->payment_rede_store_id == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo corretamente";
            $resposta->field = "payment_rede_store_id";
            $resposta->return = false;
        } else if ($form->payment_rede_token == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo corretamente";
            $resposta->field = "payment_rede_token";
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
