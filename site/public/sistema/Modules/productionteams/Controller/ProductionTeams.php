<?php

use System\Core\Bootstrap;

ProductionTeams::setAction();

class ProductionTeams extends Bootstrap {

    public $module = "";
    public $permissao_ref = "producao-servicos";
    public $table = "tb_producao_equipes";

    function __construct() {
        parent::__construct();
        if (!isset($_SESSION['admin_logado'])) {
            header("Location: " . $this->system_path . "login");
            exit;
        }
        $this->getAllPermissions();

        $this->module_icon = "entypo-icon-users ";
        $this->module_link = "productionteams";
        $this->module_title = "Cadastro de Equipes";
        $this->retorno = "productionteams";
    }

    private function indexAction() {

        if (!$this->permissions[$this->permissao_ref]['ler']) {
            $this->noPermission();
        }

        $this->list = $this->DB_fetch_array("SELECT * FROM $this->table A");

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

            $id = 0;

        } else {
            //edit
            if (!$this->permissions[$this->permissao_ref]['editar'])
                $this->noPermission();

            $query = $this->DB_fetch_array("SELECT * FROM $this->table WHERE id = $this->id");
            $this->registro = $query->rows[0];

            $id = $this->id;

        }

        $this->users = $this->DB_fetch_array("SELECT A.id, A.stats, A.nome, B.id_equipe FROM tb_admin_users A LEFT JOIN (SELECT * FROM tb_producao_equipes_has_users_has_servicos WHERE id_equipe = $id) B ON A.id=B.id_user WHERE A.id_grupo=5 AND A.stats=1 GROUP BY A.id");

        $this->renderView($this->getModule(), "edit");
    }


    private function saveAction() {

        $formulario = $this->formularioObjeto($_POST);
        $validacao = $this->validaFormulario($formulario);
        if (!$validacao->return) {
            echo json_encode($validacao);
        } else {
            $resposta = new \stdClass();
            $data = $this->formularioObjeto($_POST, $this->table);

            if (!isset($data->id) || $data->id == "") {
                //criar
                if (!$this->permissions[$this->permissao_ref]['gravar'])
                    exit();

                try {
                    $query = $this->DB_insert($this->table, 'nome', '"'.$data->nome.'"');
                    $insert_id = $query->insert_id;
                    $this->inserirEquipe($_POST['usuarios'],$insert_id);
                    $this->inserirRelatorio("Cadastrou serviço: [" . $data->nome . "]");
                    $resposta->type = "success";
                    $resposta->message = "Registro cadastrado com sucesso!";
                } catch (Exception $e) {
                    $resposta->type = "error";
                    $resposta->message = "Aconteceu um erro no sistema, favor tente novamente mais tarde!";
                    $resposta->exception = $e->getMessage();
                }
                
            } else {
                //alterar
                if (!$this->permissions[$this->permissao_ref]['editar'])
                    exit();

                $this->DB_delete("tb_producao_equipes_has_users_has_servicos", "id_servico IS NULL AND id_equipe=" . $data->id);
                $this->inserirEquipe($_POST['usuarios'],$data->id);

                $resposta = new stdClass();
                $resposta->type = "success";
                $resposta->message = "Registro alterado com sucesso!";
                $this->inserirRelatorio("Alterou equipes: id formulário: [" . $data->id . "]");
            }

            echo json_encode($resposta);
        }
    }


    private function delAction() {
        if (!$this->permissions[$this->permissao_ref]['excluir'])
            exit();


        $id = $this->getParameter("id");

        $dados = $this->DB_fetch_array("SELECT * FROM $this->table WHERE id = $id");

        $this->inserirRelatorio("Apagou Equipe: [" . $dados->rows[0]['nome'] . "] id: [$id]");
        $this->DB_delete("tb_producao_equipes_has_users_has_servicos", "id_equipe=" . $id);
        $this->DB_delete($this->table, "id=$id");

        echo $this->getModule();
    }

    private function inserirEquipe($users,$id) {
        if (isset($users)) {
            foreach ($users as $user) {
                $insertData[] = "(" . $id . "," . $user . ")";
            }
        }
        $this->DB_connect();
        if (isset($insertData))
            $this->mysqli->query("INSERT INTO tb_producao_equipes_has_users_has_servicos (id_equipe,id_user) VALUES " . implode(',', $insertData));
        $this->DB_disconnect();
    }

    public function validaFormulario($form) {

        $resposta = new \stdClass();
        $resposta->return = true;

        if ($form->nome == "") {
            $resposta->type = "validation";
            $resposta->message = "Preencha este campo";
            $resposta->field = "nome";
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
