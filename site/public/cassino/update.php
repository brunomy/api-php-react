<?php 
    
    require_once dirname(dirname(__DIR__)).'/vendor/autoload.php';
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

    $query = $conn->query("SELECT * FROM tb_cassino_mesas WHERE login_session = '".$_COOKIE['session_login']."' AND chaveAcesso='".$_POST['mesa']."'");
    if(!$query->num_rows){
        echo 'sair';
        exit();
    }


    $result = $conn->query("UPDATE tb_cassino_mesas SET titulo = '".$_POST['titulo']."', rodadas_registros_json = '".$_POST['rodadas_registros']."', rodadas_qtd_json = '".$_POST['rodadas_qtd']."', rodadas_historico_json = '".$_POST['rodadas_historico']."', bonus_historico_json = '".$_POST['bonus_historico']."' WHERE chaveAcesso='".$_POST['mesa']."'");

    if($result===true){
        echo 1;
    }else{
        echo 0;
    }

?>
