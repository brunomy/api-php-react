<?php

    session_start();
    unset($_SESSION['cliente_logado']);
    unset($_SESSION['cliente_id']);
    unset($_SESSION['cliente_nome']);
    unset($_SESSION['cliente_email']);
    unset($_SESSION['cliente_cep']);
    unset($_SESSION['cliente_endereco']);
    unset($_SESSION['cliente_numero']);
    unset($_SESSION['cliente_bairro']);
    unset($_SESSION['cliente_complemento']);
    unset($_SESSION['cliente_id_cidade']);
    unset($_SESSION['cliente_id_estado']);
    header("location: home");
    
?>