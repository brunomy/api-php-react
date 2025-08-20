<?php
session_start();
$_SESSION['tipo_cliente'] = $_GET['tipo_cliente'];
echo $_SESSION['tipo_cliente'];
