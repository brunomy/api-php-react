<?php

date_default_timezone_set('America/Sao_Paulo');

require_once __DIR__.'/../../../../vendor/autoload.php';
set_include_path(
        get_include_path() . PATH_SEPARATOR
        . __DIR__ . '/../../System/Core/' . PATH_SEPARATOR
        . __DIR__ . '/../../System/Libs/' . PATH_SEPARATOR
        . __DIR__ . '/../../System/Utils/' . PATH_SEPARATOR
        . __DIR__ . '/../../Modules/' . PATH_SEPARATOR
        . __DIR__ . '/../../');

/**
 * Classe Loader
 * @author	<contato@hibrida.biz>
 * @copyright	Copyright (c) 2015 Híbrida
 * @link	http://www.hibrida.biz
 */
class Loader {

    public static function loader_system() {

        spl_autoload_register(function($_className) {
            //verifica classes do sistema
            if (strpos($_className, '\\')) {
                $className = str_replace('\\', DIRECTORY_SEPARATOR, $_className);
                $caminho = dirname(dirname(__DIR__)) . "/" . str_replace('\\', DIRECTORY_SEPARATOR, $_className) . '.php';
            } else {
                $className = str_replace('//', DIRECTORY_SEPARATOR, $_className);
                $caminho = dirname(dirname(__DIR__)) . "/" . str_replace('//', DIRECTORY_SEPARATOR, $_className) . '.php';
            }

            if (Loader::file_exists_ci($caminho)) {
                include Loader::file_exists_ci($caminho);
            } else {
                //verifica classes do site
                if (strpos($_className, '\\')) {
                    $className = str_replace('\\', DIRECTORY_SEPARATOR, $_className);
                    $caminho = dirname(dirname(dirname(dirname(__DIR__)))) . "/" . str_replace('\\', DIRECTORY_SEPARATOR, $_className) . '.php';
                } else {
                    $className = str_replace('//', DIRECTORY_SEPARATOR, $_className);
                    $caminho = dirname(dirname(dirname(dirname(__DIR__)))) . "/" . str_replace('//', DIRECTORY_SEPARATOR, $_className) . '.php';
                }

                if (Loader::file_exists_ci($caminho)) {
                    include Loader::file_exists_ci($caminho);
                } else {
                    echo 'Arquivo não existe ' . $caminho;
                }
            }
        });
    }

    public static function file_exists_ci($file) {
        if (file_exists($file))
            return $file;
        $lowerfile = strtolower($file);
        if (is_array(glob(dirname($file) . '/*'))) {
        foreach (glob(dirname($file) . '/*') as $file)
            if (strtolower($file) == $lowerfile)
                return $file;
        }
        return false;
    }

}


//END CLASS LOADER
Loader::loader_system();


use System\Libs\IpTables;

if(isset($_SERVER['REMOTE_ADDR']) && isset($_SERVER['REQUEST_URI'])){
    $redis = new Redis();
    $redis->connect('127.0.0.1', 6379);
    if($redis->sIsMember('iptables_blacklist', $_SERVER['REMOTE_ADDR'])){
        $redis->close();
        IpTables::saveRequests(1);
        header("HTTP/1.1 503 Service Unavailable");
        header("Status: 503 Service Unavailable");
        echo 'Desculpe, o serviço não está disponível no momento. Tente novamente mais tarde.';
        exit();
    }
    $redis->close();

    IpTables::saveRequests(0);
}