<?php

use System\Core\Bootstrap;

Newsletter::setAction();

class Newsletter extends Bootstrap {

    public $module = "";
    public $permissao_ref = "newsletter";
    public $table = "tb_newsletters_newsletters";

    function __construct() {
        parent::__construct();
        if (!isset($_SESSION['admin_logado'])) {
            header("Location: " . $this->system_path . "login");
            exit;
        }
        $this->getAllPermissions();

        $this->module_icon = "entypo-icon-email";
        $this->module_link = "newsletter";
        $this->module_title = "Newsletter";
        $this->retorno = "newsletter";
    }

    private function indexAction() {

        if (!$this->permissions[$this->permissao_ref]['ler']) {
            $this->noPermission();
        }

        $this->list = $this->DB_fetch_array("SELECT *, DATE_FORMAT(A.data, '%d/%m/%Y %H:%i') registro FROM $this->table A");

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

            $query = $this->DB_fetch_array("SELECT *, DATE_FORMAT(data, '%d/%m/%Y %H:%i') data FROM $this->table WHERE id = $this->id");
            $this->registro = $query->rows[0];
        }

        $this->historicos = $this->DB_fetch_array("SELECT DATE_FORMAT(B.date, '%d/%m/%Y às %H:%i:%s') registro, B.date, C.seo_title titulo, B.origem, CONCAT(B.cidade, ', ', B.estado, ' - ', B.pais) localizacao, B.pais, B.estado, B.cidade, B.dispositivo, B.ip, B.session FROM tb_newsletters_newsletters A LEFT JOIN tb_seo_acessos_historicos B ON B.session = A.session INNER JOIN tb_seo_paginas C ON C.id = B.id_seo WHERE A.session = '{$this->registro['session']}' ORDER BY B.date");

        $this->renderView($this->getModule(), "edit");
    }

    private function exportNewsletterAction() {
        if (!$this->permissions[$this->permissao_ref]['ler'])
            $this->noPermission();

        header('Content-type: application/x-msdownload');
        header("Content-type: application/vnd.ms-excel");
        header("Content-type: application/force-download");
        header("Content-Disposition: attachment; filename=newsletter-" . date("Y-m-d") . ".xls");
        header("Pragma: no-cache");

        $this->dados = $this->DB_fetch_array("SELECT *, DATE_FORMAT(data, '%d/%m/%Y %H:%i') data FROM tb_newsletters_newsletters");

        $this->renderExport($this->getModule(), "newsletter");
    }

    private function delAction() {
        if (!$this->permissions[$this->permissao_ref]['excluir'])
            exit();

        $id = $this->getParameter("id");
        $dados = $this->DB_fetch_array("SELECT * FROM $this->table WHERE id = $id");

        $this->inserirRelatorio("Apagou newsletter: [" . $dados->rows[0]['nome'] . "] id: [$id]");
        $this->DB_delete($this->table, "id=$id");

        echo $this->getModule();
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
