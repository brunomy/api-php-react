<?php

namespace System\Core;

use System\Core\System;

class Bootstrap extends System {

    function __construct() {
        parent::__construct();
    }

    public function getController($parametro) {

        $http = "https://";
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on')
            $http = "https://";

        $resposta = "";
        $new_request = "";
        $separador = "";
        $parametro = $http.$parametro;


        $base = explode("/", $this->system_path);
        $request = explode("/", $parametro);

        foreach ($request as $request) {
            if (!in_array($request, $base)) {
                $new_request .= $separador . $request;
                $separador = "/";
            }
        }



        $caminhos = explode("/", $new_request);

        $module = explode("?", $caminhos[0]);
        $module = $module[0];
        
        if ($module == "") {
            $module = "dashboard";
        }

        $caminho = __sys_path__."Modules/$module/Controller/$module.php";

        if ($this->file_exists_ci($caminho)) {
            $resposta = $this->file_exists_ci($caminho);
        } else {
            $resposta = __sys_path__."Modules/dashboard/Controller/Dashboard.php";
        }

        /*
        if (isset($_REQUEST))
            $this->createSession($_REQUEST, $module);
         * 
         */

        return $resposta;
    }

    //cria sessão de toda requisão #desativado
    public function createSession($inp = null, $module = null) {
        if (count($inp) > 0 && $inp != null && $module != null) {
            foreach ($inp as $key => $value) {
                if (is_array($value)) {
                    return array_map(__METHOD__, $inp, $module);
                } else {
                    if ($key != "password")
                        $_SESSION[$module][$key] = $value;
                }
            }
        }
    }

}
