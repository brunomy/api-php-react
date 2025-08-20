<?php 
    //unset($_SESSION['cassino_logado']);
    if(!isset($_SESSION['cassino_logado'])){
        header("Location: /?r=1");
        exit();
    }else{
        $mesa = getMesa($_SESSION['cassino_logado']);
    }

    function getMesa($chave){

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
            header("Location: /?r=3");
            exit();
        }
    }

?>
