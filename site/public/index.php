<?php 

// Verifica se a solicitação é para um ativo (asset)
if (preg_match('/\.(jpg|jpeg|png|gif|ico|css|js)$/', $_SERVER['REQUEST_URI'])) {
    // É uma solicitação para um ativo
    header("HTTP/1.0 404 Not Found");
    exit('Arquivo não encontrado.');
}


//-------------------------------------------------------------------


ini_set('session.gc_maxlifetime', 14400);
session_start();

if (!isset($_SESSION["seo_session"])) {
    $_SESSION["seo_session"] = uniqid();
}

//-------------------------------------------------------------------


if ($_SERVER['HTTP_HOST'] == "adm.realpoker.com.br" || $_SERVER['HTTP_HOST'] == "admin.realpoker.com.br" || $_SERVER['HTTP_HOST'] == "ad.realpoker.com.br") {
    include 'sistema/index.php';
    exit();
}

//-------------------------------------------------------------------

if (strpos($_SERVER['HTTP_HOST'], 'realgaming') !== false) {
    include 'cassino/index.php';
    exit();
}

//-------------------------------------------------------------------


require_once 'sistema/System/Core/Loader.php';

use System\Core\Bootstrap;
use classes\Product;
use classes\Frete;
use classes\Pedido;
use classes\Expedicao;

include "_system.php";
$sistema = new _sys();



//-------------------------------------------------------------------


$rotas = array(
    "frete" => "classes/Frete",
    "product" => "classes/Product",
    "pedido" => "classes/Pedido",
    "expedicao" => "classes/Expedicao",
    "payments/boleto",
);

if ($sistema->getParameter("script") && isset($rotas[$sistema->getParameter("script")])) {
    
    #sequencia deve herdar do Bootstrap.php
    switch ($sistema->getParameter("script")) {

        case 'frete':
            new Frete();
            break;
            
        case 'product':
            new Product();
            break;
            
        case 'expedicao':
            new Expedicao();
            break;
            
        case 'pedido':
            new Pedido();
            break;
    }

    exit();
}


//-------------------------------------------------------------------


//instancia a classe product que está sendo usanda na header e em outras páginas do site.
$product = new Product();

$http = "http://";
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on')
    $http = "https://";

$uri = $http . $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"];
//$sistema->inserirRelatorio('[debug][index][url:'.$uri.']');
//$uri = str_replace($sistema->root_path, '', $uri);
$uri = substr_replace($_SERVER["REQUEST_URI"], '', 0, 1);
$url_base = $uri;
$uris = explode('/', $uri);


//-------------------------------------------------------------------


$internas = array();
$breadcrumbs = $sistema->DB_fetch_array("SELECT A.seo_url_breadcrumbs FROM tb_seo_paginas A WHERE A.seo_url_breadcrumbs != ''");
if ($breadcrumbs->num_rows) {
    foreach ($breadcrumbs->rows as $breadcrumb) {
        $internas[] = $breadcrumb['seo_url_breadcrumbs'];
    }
}


$utm_source = "";
$utm_medium = "";
$utm_campaign = "";
$utm_content = "";
$utm_term = "";

if (isset($_GET['utm_medium'])) {
    $_SESSION['utm_medium'] = $_GET['utm_medium'];
}
if (isset($_GET['utm_term'])) {
    $_SESSION['utm_term'] = $_GET['utm_term'];
}
if (isset($_GET['utm_content'])) {
    $_SESSION['utm_content'] = $_GET['utm_content'];
}
if (isset($_GET['utm_campaign'])) {
    $_SESSION['utm_campaign'] = $_GET['utm_campaign'];
}
if (isset($_GET['utm_source'])) {
    $_SESSION['utm_source'] = $_GET['utm_source'];
}

if (isset($_SESSION['utm_medium'])) {
    $utm_medium = $_SESSION['utm_medium'];
}
if (isset($_SESSION['utm_term'])) {
    $utm_term = $_SESSION['utm_term'];
}
if (isset($_SESSION['utm_content'])) {
    $utm_content = $_SESSION['utm_content'];
}
if (isset($_SESSION['utm_campaign'])) {
    $utm_campaign = $_SESSION['utm_campaign'];
}
if (isset($_SESSION['utm_source'])) {
    $utm_source = $_SESSION['utm_source'];
}

//echo '<pre>'; print_r($sistema->getParameter("url_amiga",0));
//echo "<pre>";print_r($sistema);echo "</pre>";


//DEFININDO NUMERO QUE APARECE NO WHATAPP DO RODAPÉ---------------
$whatsapp_link = 'https://api.whatsapp.com/send/?phone=__number__&text=Ol%C3%A1,%20tenho%20interesse%20em%20produtos%20da%20Real%20Poker%0D%0A%0D%0A'.$utm_campaign;
$whatsapp_numbers = array(
    array(
        'name'=>'Isnara',
        'number'=>'(11) 95653-5294',
        'link'=>str_replace('__number__','5511956535294',$whatsapp_link),
    ),
    array(
        'name'=>'Daniele',
        'number'=>'(11) 97138-0712',
        'link'=>str_replace('__number__','5511971380712',$whatsapp_link),
    ),
);
$whatsapp_index = rand(0,1);
$whatsapp_number = $whatsapp_numbers[$whatsapp_index]['number'];
if(!isset($_COOKIE['whatsapp'])){
    setcookie('whatsapp', $whatsapp_number, (time() + (60 * 24 * 3600)));
}else{
    if($_COOKIE['whatsapp'] != $whatsapp_numbers[0]['number'] && $_COOKIE['whatsapp'] != $whatsapp_numbers[1]['number']){
        setcookie('whatsapp', $whatsapp_number, (time() + (60 * 24 * 3600)));
        $_COOKIE['whatsapp'] = $whatsapp_number;
    }
    $whatsapp_number = $_COOKIE['whatsapp'];
}

//definindo qual número aparece primeiro (topo e rodapé)
if($whatsapp_number!=$whatsapp_numbers[0]['number']){
    $whatsapp_numbers = array($whatsapp_numbers[1],$whatsapp_numbers[0]);
}

//-------------------------------------------------------------------


function verificaUrl($uri, $c = 0) {
    global $internas, $uris, $url_base;
    $url = '';

    for ($i = 0; $i < count($uris); $i++) {
        if (($i + $c) < count($uris)) {
            $url .= $uris[$i] . "/";
        }
    }
    if (!in_array($url, $internas)) {
        if ($url != "") {
            $c++;
            return verificaUrl($url, $c);
        } else {
            $newurl = explode("/", $url_base);
            return $newurl[0];
        }
    } else {
        $u = explode("/", $url_base);
        $urefer = '';
        for ($d = 0; $d < count($u); $d++) {
            $urefer .= $u[$d] . "/";
            if ($urefer == $url){
                if(array_key_exists($d + 1, $u))
                    return $u[$d + 1];
            }
        }
    }
}

$urlamiga = verificaUrl($uri);
$urlamiga = str_replace("%E2%80%93", "–", $urlamiga);
if(strpos($urlamiga,'?')){
    $urlamiga = explode('?',$urlamiga);
    $urlamiga = array_shift($urlamiga);
}

if ($urlamiga == "") {
    include "home.php";
} elseif ($urlamiga == "sistema") {
    include "../sistema/index.php";
} else {

    $urlamiga = $sistema->DB_anti_injection($urlamiga);

    $query = $sistema->DB_fetch_array("SELECT * FROM tb_seo_paginas WHERE seo_url='$urlamiga' LIMIT 1");
    if ($query->num_rows) {

        $dynamic_id = $query->rows[0]['id'];

        if (file_exists("" . $query->rows[0]['seo_pagina'] . ".php")) {
            include "" . $query->rows[0]['seo_pagina'] . ".php";
        } else {
            include "home.php";
        }
    } else {
        if (file_exists("" . $urlamiga . ".php")) {
            include "" . $urlamiga . ".php";
        } else {
            include "home.php";
        }
    }
}



//-----------------------------------------
?>
