<?php 

    header("Content-type: text/css");
    
    session_start();
/*
    $_SESSION["cotacaoDolarOff"] = true;

    require_once '../sistema/System/Core/Loader.php';

    use System\Core\Bootstrap;

    require_once "../_system.php";

    $sistema = new _sys();
    
    $menu = $sistema->DB_fetch_array("SELECT * FROM tb_links_links A WHERE A.id_pai IS NULL AND A.stats = 1 ORDER BY A.ordem");
*/
    
    $qtdMainMenuItens = 6; // <--- BUSCAR NO BANCO A QUANTIDADE DE ITENS DO MENU PRINCIPAL
    $spaceToFit = 1235 - 58;
    $menuElementWidth = ($spaceToFit / $qtdMainMenuItens) - 1;
    $menuFixedElementWidth = 990 / $qtdMainMenuItens;


	$color_main_red = '#b7231a';
	$color_main_green = '#4e7a3a';
	$color_main_gray = '#535353';

	$clear_end = ':after{content:"";display:block;clear:both;}';

?>