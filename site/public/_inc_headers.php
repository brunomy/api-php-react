<?php
    
    $sistema->DB_connect();

    $empresa = array();
    $query = $sistema->DB_fetch_array("SELECT * FROM tb_admin_empresa");
    if($query->num_rows)
        $empresa = $query->rows[0];

    unset($query);
    
    
    $telefone = preg_replace("/[^0-9\s]/", "", $empresa['fone']);
    $telefone = str_replace(" ", "", $telefone);

    $tel = str_replace(' ', '', $empresa['fone']); 
    
    $prefix = substr($tel, 0, 4);
    $fone = substr($tel, 4);
    
    
    function getSeoBanner ($id = null) {
        global $sistema;
        $query = $sistema->DB_fetch_array("SELECT seo_banner FROM tb_seo_paginas WHERE id = $id");
        if ($query->num_rows)
            return $query->rows[0]['seo_banner'];
    }
    
    if(!isset($_SESSION['dev_path']) || isset($_GET['clear'])) $_SESSION['dev_path'] = '';
    if(isset($_GET['hibrida']))  $_SESSION['dev_path'] = 'http://192.168.25.135/clientes/realpoker/ecommerce/base/root/';
    
    $scripts = $sistema->DB_fetch_array("SELECT * FROM tb_scripts_scripts WHERE id = 3");
    
    $sistema->DB_disconnect();

    //se utm_source, gravar na sessao.  uso na newsbox.
    if(!isset($_GET['session_utm'])) $_SESSION['session_utm'] = '';
    if(isset($_GET['utm_source'])) $_SESSION['session_utm'] = $_GET['utm_source'];
    
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<base href="<?php echo $sistema->root_path; ?>" />
<meta charset="utf-8">
<meta name="revisit-after" content="5" />
<?php if(isset($currentpage)){ ?>
    <meta name="url" content="<?php echo $sistema->root_path.$currentpage['seo_url'];?>" />
    <meta name="description" content="<?php if($currentpage['seo_description'] == "") echo $sistema->seo_pages['padrao']['seo_description']; else echo $currentpage['seo_description']; ?>" />
    <meta name="keywords" content="<?php if($currentpage['seo_keywords'] == "") echo $sistema->seo_pages['padrao']['seo_keywords']; else echo $currentpage['seo_keywords'];?>" />
    <?php if (isset($title_produto_personalizado)): ?>
        <title>Vários Modelos - <?php echo $title_produto_personalizado ?></title>
    <?php else: ?>
        <title><?php if($currentpage['seo_title'] == "") echo $sistema->seo_pages['padrao']['seo_title']; else echo $currentpage['seo_title'];?></title>
    <?php endif ?>
       
    <meta property="og:url" content="<?php echo $sistema->root_path.$currentpage['seo_url'];?>">
    <meta property="og:title" content="<?php if($currentpage['seo_title'] == "") echo $sistema->seo_pages['padrao']['seo_title']; else echo $currentpage['seo_title'];?>">
    <meta property="og:site_name" content="<?php echo $empresa['nome'];?>">
    <meta property="og:description" content="<?php if($currentpage['seo_description'] == "") echo $sistema->seo_pages['padrao']['seo_description']; else echo $currentpage['seo_description']; ?>">
    <meta property="og:type" content="website">

<?php } else { ?>
    <meta name="description" content="<?php echo $sistema->seo_pages['padrao']['seo_description'] ?>" />
    <meta name="keywords" content="<?php echo $sistema->seo_pages['padrao']['seo_keywords'] ?>" />
    <?php if (isset($title_produto_personalizado)): ?>
        <title>Vários Modelos - <?php echo $title_produto_personalizado ?></title>
    <?php else: ?>
        <title><?php echo $sistema->seo_pages['padrao']['seo_title'] ?></title>
    <?php endif ?>

    <meta property="og:url" content="<?php echo $sistema->root_path.$currentpage['seo_url'];?>">
    <meta property="og:title" content="<?php if($currentpage['seo_title'] == "") echo $sistema->seo_pages['padrao']['seo_title']; else echo $currentpage['seo_title'];?>">
    <meta property="og:site_name" content="<?php echo $empresa['nome'];?>">
    <meta property="og:description" content="<?php if($currentpage['seo_description'] == "") echo $sistema->seo_pages['padrao']['seo_description']; else echo $currentpage['seo_description']; ?>">
    <meta property="og:type" content="website">

<?php } ?>
<meta name="facebook-domain-verification" content="0nr8qy3oyec1gyafs4dg8c9bdomrzh" />
<meta id="viewport" name="viewport" content="width=device-width, initial-scale=1, user-scalable=0">
<link href="<?php echo $_SESSION['dev_path'] ?>css/core.css.php" rel="stylesheet" />
<link href="<?php echo $_SESSION['dev_path'] ?>css/desktop.css.php" rel="stylesheet" media="screen and (min-width:1240px)" />
<link href="<?php echo $_SESSION['dev_path'] ?>css/tablet.css.php" rel="stylesheet" media="screen and (max-width:1239px)" />
<link href="<?php echo $_SESSION['dev_path'] ?>css/mobile.css.php" rel="stylesheet" media="screen and (max-width:740px)" />
<link href="plugins/fontawesome/css/font-awesome.min.css" rel="stylesheet" />
<link rel="shortcut icon" href="<?php echo $sistema->getImageFileSized($empresa['favicon'],16,16); ?>" type="image/x-icon" />
<script type="text/javascript">
    var pagina = "<?php echo $pagina; ?>";
    var path = "<?php echo $sistema->root_path;?>";
    var ip = "<?php echo $_SERVER['REMOTE_ADDR'] ?>";
    var is_desktop = <?php if (!$sistema->detect->isMobile() && !$sistema->detect->isTablet()){echo 1;}else{echo 0;} ?>;
    var is_tablet = <?php if ($sistema->detect->isTablet()){echo 1;}else{echo 0;} ?>;
    var is_mobile = <?php if ($sistema->detect->isMobile()){echo 1;}else{echo 0;} ?>;
    var cotacao_dollar = <?php echo $sistema->cotacao_dollar; ?>;
    var newsboxpopup = <?php if(!isset($_COOKIE['cookie_newsbox']) && strpos($_SESSION['session_utm'],'mkt') === false){echo 1;}else{echo 0;} ?>;
    var openlogin = <?php if(isset($_GET['login'])){echo 1;}else{echo 0;} ?>;
</script>
