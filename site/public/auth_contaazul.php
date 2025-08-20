<?php 

require_once __DIR__.'/../vendor/autoload.php';
$dotenv = \Dotenv\Dotenv::createMutable(dirname((__DIR__)));
$dotenv->load();
$redirect_uri = urlencode($_ENV['APP_URL']."auth_contaazul");

if(isset($_GET['code']) && isset($_GET['state']) && isset($_SESSION['acastate'])){

    if($_GET['state'] == $_SESSION['acastate']){

        $args = '?grant_type=authorization_code&redirect_uri='.$redirect_uri.'&code='.$_GET['code'];
        $url = 'https://api.contaazul.com/oauth2/token'.$args;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Basic '.base64_encode($_ENV['CONTAAZUL_CLIENT_ID'].":".$_ENV['CONTAAZUL_CLIENT_SECRET'])));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10000);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        $result = curl_exec($ch);
        
        if ($result === false) {
            echo curl_error($ch);
        }else{

            $result = json_decode($result);

            if(isset($result->error)) {
                print_r($result);
                exit();
            }

            $conn = new mysqli($_ENV['DB_HOST'], $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD'], $_ENV['DB_DATABASE']);
            if($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
                exit();
            }
            

            $expires_in = date('Y-m-d H:i:s', (time() + $result->expires_in));

            $update = $conn->query("UPDATE tb_admin_empresa SET contaazul_refresh_token = '".$result->refresh_token."', contaazul_access_token = '".$result->access_token."', contaazul_access_token_expires_at='".$expires_in."' WHERE id=1");

            if($update===true){
                echo "Conta Azul conectada com sucesso!<br><br>";
            }else{
                echo "Não foi possível gravar os tokens de acesso. Feche essa janela e tente novamente.";
            }
        }

        curl_close($ch);

    }else{
        http_response_code(401);
        echo "erro";
        exit();
    }

}else{

    $_SESSION['acastate'] = date('YmdHis');

    $redirect = 'https://api.contaazul.com/auth/authorize?redirect_uri='.$redirect_uri.'&client_id='.$_ENV['CONTAAZUL_CLIENT_ID'].'&scope=sales,costumers&state='.$_SESSION['acastate'];

    //echo '<a href="'.$redirect.'">click aqui</a> para conectar Real Poker a Conta Azul.';

    header('Location: '.$redirect);

}

?>