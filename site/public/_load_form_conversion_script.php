<?php

session_start();
require_once 'sistema/System/Core/Loader.php';

use System\Core\Bootstrap;

require_once "_system.php";

$sistema = new _sys();

$sistema->DB_connect();

$analytics = array();
$query = $sistema->DB_fetch_array("SELECT script FROM tb_scripts_scripts WHERE id = {$_GET['id']}");
if ($query->num_rows) {
    $analytics = $query->rows[0];
    echo $analytics['script'];
}


unset($query);

$sistema->DB_disconnect();
?>



