<?php 
    session_start();
    unset($_SESSION['cassino_logado']);
    if(isset($_GET['r'])){
        header("Location: /?r=".$_GET['r']);
    }else{
        header("Location: /");
    }
?>
