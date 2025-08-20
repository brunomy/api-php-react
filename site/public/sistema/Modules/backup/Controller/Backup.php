<?php

use System\Core\Bootstrap;

Backup::setAction();

class Backup extends Bootstrap {

    public $module = "";
    public $permissao_ref = "admin-backups";
    public $table = "tb_admin_backups";

    function __construct() {
        parent::__construct();
        if (!isset($_SESSION['admin_logado'])) {
            header("Location: " . $this->system_path . "login");
            exit;
        }

        $this->getAllPermissions();


        $this->module_icon = "icomoon-icon-database";
        $this->module_link = "backup";
        $this->module_title = "Central de Backups";
        $this->retorno = "backup";
    }

    private function indexAction() {

        if (!$this->permissions[$this->permissao_ref]['ler']) {
            $this->noPermission();
        }


        $this->list = $this->DB_fetch_array("SELECT * FROM $this->table A");

        $this->renderView($this->getModule(), "index");
    }

    private function getAction() {

        if (!$this->permissions[$this->permissao_ref]['ler'])
            exit();

        $id = $this->getParameter("id");

        $query = $this->DB_fetch_array("SELECT * FROM $this->table WHERE id = $id");
        if ($query->num_rows) {
            $arquivo = $query->rows[0];
            $new_file = str_replace(".php", ".sql", $arquivo['arquivo']);
            if (!file_exists($new_file)) {
                $arr = file($arquivo['arquivo']); // Lê todo o arquivo para um vetor
                $conteudo = file_get_contents($arquivo['arquivo']);

                $new_contents = str_replace(array("<?php", "/*", "*/"), "", $conteudo);

                $fh = fopen($new_file, "w");

                fputs($fh, $new_contents);

                fclose($fh);
            }

            $diretorio = 'backups/';



            $zip = new \ZipArchive();
            $newZip = str_replace(".sql", ".zip", $new_file);
            if ($zip->open($newZip, ZIPARCHIVE::CREATE) == TRUE) {
                $zip->addFile($new_file, 'data_base.sql');
            } else {
                
            }

            // Fecha arquivo Zip aberto
            $zip->close();
            header("Location: " . $this->system_path . $newZip);
        }
    }

    private function delAction() {
        if (!$this->permissions[$this->permissao_ref]['ler'])
            exit();

        $id = $this->getParameter("id");

        $query = $this->DB_fetch_array("SELECT * FROM $this->table WHERE id = $id");
        if ($query->num_rows) {
            $arquivo = $query->rows[0];

            unlink(dirname(dirname(dirname(dirname(__FILE__)))) . DIRECTORY_SEPARATOR . $arquivo['arquivo']);
            $new_file = str_replace(".php", ".sql", $arquivo['arquivo']);
            @unlink(dirname(dirname(dirname(dirname(__FILE__)))) . DIRECTORY_SEPARATOR . $new_file);
            $new_file = str_replace(".php", ".zip", $arquivo['arquivo']);
            @unlink(dirname(dirname(dirname(dirname(__FILE__)))) . DIRECTORY_SEPARATOR . $new_file);

            $this->DB_delete("tb_admin_backups", "id = $id");
            $this->inserirRelatorio("Apagou backup database: [{$arquivo['arquivo']}], data: [{$arquivo['data']}], id: [{$arquivo['id']}]");

            echo json_encode(array("result" => true));
        } else {
            echo json_encode(array("result" => false));
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
