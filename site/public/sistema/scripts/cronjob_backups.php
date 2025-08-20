<?php

set_time_limit(0);
require_once '../System/Core/Loader.php';

use System\Core\System;

$sistema = new System();

echo json_encode(Array("result" => $sistema->DB_backup()));
