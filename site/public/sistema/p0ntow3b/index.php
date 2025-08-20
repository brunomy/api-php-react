<?php 

require_once '../System/Core/Loader.php';
use System\Core\System;

$sistema = new System();

if ($_SERVER['HTTP_HOST'] == "192.168.25.135") {
    $path = "http://192.168.25.135/CLIENTES/realpoker/ecommerce/base/root/sistema/p0ntow3b/";
} else {
    $path = "http://www.realpoker.com.br/sistema/p0ntow3b/";
}

?>
<!DOCTYPE html>
<html>
<head>
<base href="<?php echo $path; ?>" />
<meta charset="utf-8">
<title>SISTEMA DE PONTO REAL POKER</title>
<meta id="viewport" name="viewport" content="width=device-width, initial-scale=1, user-scalable=yes">
<link href="css/bootstrap.css" rel="stylesheet" />
<link href="css/styles.css" rel="stylesheet" />
</head>
<body>
    <div id="app_background"></div>
    <div id="app_container">
        <form action="javascript:;" method="post" class="form_login">
            <div class="form_fields">
                <div class="col-md-12">
                    <div class="form_fields__field">
                        <input type="password" name="user_key" id="user_key" placeholder="Digite aqui seu código">
                    </div>
                </div>
            </div>
            <div class="form_actions">
                <div class="col-md-12"><button class="form_actions__bt_submit">BATER PONTO</button></div>
                <div class="clear"></div>
            </div>
        </form>
        <form action="javascript:;" method="post" class="form_ponto">
            <input type="hidden" name="senha">
            <div class="form_actions">
                <div class="col-md-12"><button class="form_actions__bt_submit">BATER PONTO</button></div>
                <div class="clear"></div>
            </div>
        </form>
    </div>
<script type="text/javascript" src="js/jquery.js"></script>
<script type="text/javascript" src="js/jquery.inputmask.bundle.min.js"></script>
<script type="text/javascript" src="js/jquery.clickform.js"></script>
<script type="text/javascript" src="js/scripts.js"></script>
</body>
</html>