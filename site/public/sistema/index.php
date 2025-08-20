<?php

if (!isset($_SESSION))
    session_start();
/**
 *
 * @author	<contato@hibrida.biz>
 * @copyright	Copyright (c) 2015 Híbrida
 * @link	http://www.hibrida.biz
 */
ini_set('display_errors', 1);
ini_set('short_open_tag', 1);
ini_set('safe_mode', 0);
ini_set('display_startup_erros', 1);
error_reporting(E_ALL);
header('Content-Type: text/html; charset=utf-8', true);

require_once 'System/Core/Loader.php';

use System\Core\Bootstrap;
use System\Libs\IpTables;

if (!isset($sistema)) {
    $sistema = new Bootstrap();
}

if(isset($_SESSION['ip_whitelisted'])){
    if($_SESSION['ip_whitelisted'] != $_SERVER['REMOTE_ADDR']){
        $ip = new IpTables($sistema);
        $ip->whitelist($_SERVER['REMOTE_ADDR']);
    }
}

$http = "https://";
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on')
    $http = "https://";

$gets = explode("/", str_replace(strrchr($_SERVER["REQUEST_URI"], "?"), "", $_SERVER["REQUEST_URI"]));
array_shift($gets);

if ($_SERVER['HTTP_HOST'] == "realpoker.local" || $_SERVER['HTTP_HOST'] == "www.realpoker.local" || $_SERVER['HTTP_HOST'] == "realgaming.com.br" || $_SERVER['HTTP_HOST'] == "www.realgaming.com.br" || $_SERVER['HTTP_HOST'] == "18.214.216.6" || $_SERVER['HTTP_HOST'] == "local.realpoker.com.br" || $_SERVER['HTTP_HOST'] == "local.cassino" || $_SERVER['HTTP_HOST'] == "adm.realpoker.com.br" || $_SERVER['HTTP_HOST'] == "admin.realpoker.com.br" || $_SERVER['HTTP_HOST'] == "ad.realpoker.com.br") {
    $path = $http.$_SERVER['HTTP_HOST'] . "/";
    if (!isset($_SESSION['firsturl'])) {
        $_SESSION['firsturl'] = $http.$_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
    }

    require_once $sistema->getController($_SERVER['HTTP_HOST'] . $_SERVER["REQUEST_URI"]);
} else if ($_SERVER['HTTP_HOST'] == "realpoker.com.br" || $_SERVER['HTTP_HOST'] == "www.realpoker.com.br") {
    echo "Este acesso mudou.  Procure o administrador.";
} else {
    echo "Esse site não pertença a esse domínio.  Procure o administrador.";
}
?>
