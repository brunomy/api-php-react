<?php 
session_start();
$uri = explode("/", str_replace(strrchr($_SERVER["REQUEST_URI"], "?"), "", $_SERVER["REQUEST_URI"]));
array_shift($uri);

if (isset($uri[0])) {

    $urlamiga = $uri[0];

    if ($urlamiga == "") {
        require_once dirname(__FILE__) ."/login.php";
    } else {
        if (file_exists(dirname(__FILE__) .'/'.$urlamiga . ".php")) {
            require_once dirname(__FILE__) .'/'.$urlamiga . ".php";
        } else {
            require_once dirname(__FILE__) ."/login.php";
        }
    }
}
?>