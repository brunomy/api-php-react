<?php

use System\Core\Bootstrap;

General::setAction();

class General extends Bootstrap {

    function __construct() {
        parent::__construct();
        if (!isset($_SESSION['admin_logado'])) {
            header("Location: " . $this->system_path . "login");
            exit;
        }
        $this->getAllPermissions();
    }

    private function statusAction() {
        $a = $_POST["a"];
        $table = $_POST["t"];
        $id = $_POST["i"];
        $permit = $_POST["p"];

        if ($this->permissions[$permit]['editar']) {
            if ($a == "ativar") {
                $this->inserirRelatorio("Ativou registro na tabela [$table] id [$id]");
                $this->DB_update($table, "stats = 1 WHERE id = $id");
                echo "desativar";
            } else if ("desativar") {
                $this->inserirRelatorio("Desativou registro na tabela [$table] id [$id]");
                $this->DB_update($table, "stats = 0 WHERE id=$id");
                echo "ativar";
            }
        } else {
            echo "Você não tem permissão para editar esta função";
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

            /*
             * 
              echo $sistema->translate("metodo_nao_existe", $action[0]);

              if (isset($_SERVER['HTTP_REFERER'])) {
              echo "<pre><a href='{$_SERVER['HTTP_REFERER']}'>{$sistema->translate('voltar')}</a>";
              } else {
              echo "<pre><a href='$sistema->system_path" . strtolower($class) . "'>{$sistema->translate('voltar')}</a>";
              }

             */
        }
    }

}
