<?php

use System\Core\Bootstrap;

EletronicPointReport::setAction();

class EletronicPointReport extends Bootstrap {

    public $module = "";
    public $permissao_ref = "meu-ponto-eletronico";
    public $table = "tb_ponto_eletronico";

    function __construct() {
        parent::__construct();
        if (!isset($_SESSION['admin_logado'])) {
            header("Location: " . $this->system_path . "login");
            exit;
        }
        $this->getAllPermissions();

        $this->module_icon = "icomoon-icon-clock-2";
        $this->module_link = "eletronicpointreport";
        $this->module_title = "Meu Ponto Eletrônico";
        $this->retorno = "eletronicpointreport";
    }

    private function indexAction() {

        if (!$this->permissions[$this->permissao_ref]['ler']) {
            $this->noPermission();
        }

        $this->funcionarios = $this->DB_fetch_array("SELECT * FROM tb_admin_users A ORDER BY A.nome");

        $this->data = array();

        if($search = $this->getParameter("search")){
            $search = explode("_", $search);
            $this->data["de"] = (isset($search[0])) ? $this->formatDateUrl($search[0],1) : "";
            $this->data["ate"] = (isset($search[1])) ?$this->formatDateUrl($search[1],1) : "";
        }else{
            $this->data["de"] = "";
            $this->data["ate"] = "";
        }

        $this->renderView($this->getModule(), "index");
    }

    public function getViewAction(){
        $this->renderAjax($this->getModule(), "eletronicpointreport");
    }

    public function formatDateUrl($date, $get = 0){
        return ($get) ? str_replace("-", "/", $date) : str_replace("/", "-", $date);
    }

    private function exportAction() {
        if (!$this->permissions[$this->permissao_ref]['ler'])
            $this->noPermission();

        $this->data = $this->formularioObjeto($_POST);

        header('Content-type: application/x-msdownload');
        header("Content-type: application/vnd.ms-excel");
        header("Content-type: application/force-download");
        header("Content-Disposition: attachment; filename=ponto-eletronico-" . date("Y-m-d") . ".xls");
        header("Pragma: no-cache");

        $this->renderExport($this->getModule(), "eletronicpointreport");
    }

    public function horaParaMinutos($hora) {

        list($h, $m, $s) = explode(':', $hora);
        $hours = $h * 60;
        $mins = $m + $hours;

        if ($s > 30)
            $mins = $mins + 1;

        return $mins;
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
