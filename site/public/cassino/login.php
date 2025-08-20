<?php

    $mesa = '';
    $message = '';
    $conn = '';
    
    if(!isset($_COOKIE['session_login'])) {
        setcookie('session_login', session_id(), time() + (86400 * 30), "/"); // 86400 = 1 day
    }

    if(isset($_SESSION['cassino_logado']) && isset($_POST['chaveAcesso']) && $_POST['chaveAcesso'] != $_SESSION['cassino_logado']){
        $message = "Existe uma sessão ativa com este computador";
    }

    else if(!isset($_SESSION['cassino_logado']) && isset($_POST['chaveAcesso'])){
        $mesa = getMesa($_POST['chaveAcesso']);
        if($mesa===false){
            $message = "Mesa não existe";
        }else{
            $_SESSION['cassino_logado'] = $_POST['chaveAcesso'];
        }
    }

    else if(isset($_SESSION['cassino_logado'])){
        $mesa = getMesa($_SESSION['cassino_logado']);
    }

    //--------------------------------------
    
    function getMesa($chave){
        global $conn;
        require_once __DIR__.'/../../vendor/autoload.php';
        $dotenv = \Dotenv\Dotenv::createMutable(dirname(dirname(__DIR__)));
        $dotenv->load();

        $db_host = $_ENV['DB_HOST'];
        $db_user = $_ENV['DB_USERNAME'];
        $db_database = $_ENV['DB_DATABASE'];
        $db_pwd = $_ENV['DB_PASSWORD'];

        $conn = new mysqli($db_host, $db_user, $db_pwd, $db_database);
        if($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
            exit();
        }

        $result = $conn->query("SELECT * FROM tb_cassino_mesas WHERE chaveAcesso = '$chave'");
        if($result->num_rows > 0){
            return $result->fetch_assoc();
        }else{
            return false;
        }
    }

    //--------------------------------------

    if(isset($_SESSION['cassino_logado'])){
        $conn->query("UPDATE tb_cassino_mesas SET login_session = '".$_COOKIE['session_login']."' WHERE chaveAcesso = '".$_SESSION['cassino_logado']."'");
        if($mesa['tipo'] == 'Baccarat') header("Location: /controle_baccarat");
        else if($mesa['tipo'] == 'Big Win') header("Location: /controle_bigwin");
        else header("Location: /controle2");
        exit();
    }else

    if(isset($_GET['r'])){
        switch ($_GET['r']) {
            case 1:
                $message = "Você não tem permissão para acessar";
                break;
            
            case 2:
                $message = "Existe uma sessão ativa com este computador";
                break;
            
            case 3:
                $message = "Mesa não existe";
                break;
            
            case 4:
                $message = "Sessão não foi encontrada";
                break;
                        
            case 100:
                $message = "Erro inexperado!";
                break;
            
            case 101:
                $message = "Servidor desconectado!";
                break;
            
            case 102:
                $message = "Esta mesa está em uso em outro canal";
                break;
            
            default:
                # code...
                break;
        }
    }



?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="robots" content="noindex, nofollow">

    <title>REAL POKER - CASSINO</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="//maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
    <style type="text/css">
        body,html {
            background-image: url('img/background_poker.jpg');
            background-size: cover;
            height: 100%;
        }

        #profile-img {
            height:180px;
        }
        .h-80 {
            height: 80% !important;
        }
        .center {
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
        }
    </style>
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
    <script type="text/javascript">
        //window.alert = function(){};
        var defaultCSS = document.getElementById('bootstrap-css');
        function changeCSS(css){
            if(css) $('head > link').filter(':first').replaceWith('<link rel="stylesheet" href="'+ css +'" type="text/css" />'); 
            else $('head > link').filter(':first').replaceWith(defaultCSS); 
        }
        $( document ).ready(function() {
            var iframe_height = parseInt($('html').height()); 
            window.parent.postMessage( iframe_height, 'https://bootsnipp.com');

            <?php if (isset($_GET['r'])): ?>
                $(".modal").modal();
            <?php endif ?>
            
        });
    </script>
</head>
<body>
<div class="container h-80">
    <div class="row align-items-center h-200">
    </div>
    <div class="row align-items-center h-100">
        <div class="col-3 mx-auto">
            <div class="text-center">
                <p id="profile-name" class="profile-name-card"></p>
                <form action="login" class="form-signin" method="POST">
                    <input type="text" name="chaveAcesso" id="chaveAcesso" class="form-control form-group" placeholder="Chave de Acesso" required autofocus>
                    <button class="btn btn-lg btn-primary btn-block btn-signin" type="submit">entrar</button>
                </form><!-- /form -->
            </div>
        </div>
    </div>
</div> 
<div class="modal fade center">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Alerta</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p><?php echo $message ?></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" data-dismiss="modal">OK</button>
      </div>
    </div>
  </div>
</div>
<script type="text/javascript">
</script>
</body>
</html>