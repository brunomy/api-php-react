<?php

namespace System\Core;

DEFINE("__root_path__", dirname(dirname(dirname(dirname(__DIR__)))) . DIRECTORY_SEPARATOR);
DEFINE("__sys_path__", dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR);



/**
 * Classe Config
 * @author	<contato@hibrida.biz>
 * @copyright	Copyright (c) 2015 Híbrida
 * @link	http://www.hibrida.biz
 */
class Config {

    protected $db_host = "";
    protected $db_user = "";
    protected $db_pwd = "";
    protected $db_database = "";
    protected $criptBase = "";
    public $root_path = "";
    public $system_path = "";
    public $upload_folder = __root_path__."public/uploads/";
    public $upload_path = "../uploads/";
    public $site_url = "";
    public $sys_url = "";

    function __construct() {

        // -----------------------------------------------------------------------
        // VERIFICA QUAL BANCO DE DADOS USAR DE ACORDO COM O LOCAL HOSPEDADO 
        // --------------------------------------------------------  -------------

        $dotenv = \Dotenv\Dotenv::createMutable(dirname(dirname(dirname(dirname(__DIR__)))));
        $dotenv->load();

        $this->db_host = $_ENV['DB_HOST'];
        $this->db_user = $_ENV['DB_USERNAME'];
        $this->db_database = $_ENV['DB_DATABASE'];
        $this->db_pwd = $_ENV['DB_PASSWORD'];
        $this->site_url = $_ENV['APP_URL'];
        $this->sys_url = $_ENV['SYS_URL'];
        $this->app_env = $_ENV['APP_ENV'];

        if ($this->app_env == 'development') {
            ini_set('display_errors', 1);
            ini_set('display_startup_errors', 1);
            error_reporting(E_ALL);
        }

        /*echo '<pre>';print_r($this);echo '</pre>';
        exit();*/

        if(isset($_SERVER['HTTP_HOST'])){

            //DEFINE PATHs
            if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
                // SSL connection
                
                $this->root_path = "https://" . $_SERVER['HTTP_HOST'] . "/";
                $this->system_path = $this->root_path . "sistema/";
            }else{
                
                $this->root_path = "https://" . $_SERVER['HTTP_HOST'] . "/";
                $this->system_path = $this->root_path . "sistema/";

            }
            
        }

        // -----------------------------------------------------------------------
        // DETECTA SE O DISPOSITIVO É MOBILE
        // --------------------------------------------------------  -------------


        $this->criptBase = "pokeRe@al#2016";

        // -----------------------------------------------------------------------
    }

}
