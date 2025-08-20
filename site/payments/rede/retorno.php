<?php
session_start();
$_SESSION["cotacaoDolarOff"] = true;


if (!isset($_SESSION["seo_session"])) {
    $_SESSION["seo_session"] = uniqid();
}

require_once '../../sistema/System/Core/Loader.php';

use System\Core\Bootstrap;
use classes\Product;

include "../../_system.php";

$sistema = new _sys();

if ($_REQUEST) {

    $sistema->inserirRelatorio("retorno erede: " . json_encode($_REQUEST));
    die();

}