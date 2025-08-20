<?php

use System\Core\Bootstrap;

ProductionEmployee::setAction();

class ProductionEmployee extends Bootstrap {

    public $module = "";
    public $permissao_ref = "producao-servicos";
    public $table = "tb_producao_equipes_has_users_has_servicos";

    function __construct() {
        parent::__construct();
        if (!isset($_SESSION['admin_logado'])) {
            header("Location: " . $this->system_path . "login");
            exit;
        }
        $this->getAllPermissions();

        $this->module_icon = "entypo-icon-users ";
        $this->module_link = "productionteams";
        $this->module_title = "Cadastro de Capacitação de Funcionários";
        $this->retorno = "productionemployee/index/id/";
    }

    private function indexAction() {

        if (!$this->permissions[$this->permissao_ref]['ler']) {
            $this->noPermission();
        }

        $this->id = $this->getParameter("id");
        $this->list = $this->DB_fetch_array("SELECT A.id_equipe, A.id_user, B.nome FROM $this->table A JOIN tb_admin_users B ON A.id_user = B.id WHERE A.id_servico IS NULL AND A.id_equipe = ".$this->id);

        $equipe = $this->DB_fetch_array("SELECT * FROM tb_producao_equipes A WHERE A.id = ".$this->id);
        $this->equipe = $equipe->rows[0]['nome'];

        $this->renderView($this->getModule(), "index");
    }

    private function editAction() {
        if (!$this->permissions[$this->permissao_ref]['editar'])
            $this->noPermission();

        $this->id_usuario = $this->getParameter("usuario");
        $this->id_equipe = $this->getParameter("equipe");
        $this->usuario = $this->DB_fetch_array("SELECT * FROM tb_admin_users A WHERE A.id = ".$this->id_usuario);
        $this->usuario = $this->usuario->rows[0]['nome'];
        $this->equipe = $this->DB_fetch_array("SELECT * FROM tb_producao_equipes A WHERE A.id = ".$this->id_equipe);
        $this->equipe = $this->equipe->rows[0]['nome'];
        $this->servicos = $this->DB_fetch_array("SELECT * FROM tb_producao_servicos A LEFT JOIN (SELECT * FROM tb_producao_equipes_has_users_has_servicos WHERE id_equipe = ".$this->id_equipe." AND id_user = ".$this->id_usuario.") B ON A.id=B.id_servico");
        $this->renderView($this->getModule(), "edit");

    }


    private function saveAction() {

        $resposta = new \stdClass();
        $data = $this->formularioObjeto($_POST);

        //alterar
        if (!$this->permissions[$this->permissao_ref]['editar'])
            exit();

        $this->DB_delete("tb_producao_equipes_has_users_has_servicos", "id_servico IS NOT NULL AND id_equipe=" . $data->id_equipe . " AND id_user=" . $data->id_usuario);

        if (isset($_POST['servicos'])) {
            foreach ($_POST['servicos'] as $servico) {
                $insertData[] = "(" . $data->id_usuario . "," . $data->id_equipe . "," . $servico . ")";
            }
        }

        $this->DB_connect();
        if (isset($insertData))
            $this->mysqli->query("INSERT INTO tb_producao_equipes_has_users_has_servicos (id_user,id_equipe,id_servico) VALUES " . implode(',', $insertData));
        $this->DB_disconnect();

        $resposta = new stdClass();
        $resposta->type = "success";
        $resposta->message = "Registro alterado com sucesso!";
        $this->inserirRelatorio("Alterou capacitação de usuario: id usuario: [" . $data->id_usuario . "] id equipe: [".$data->id_equipe."]");

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
