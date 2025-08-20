<?php 
    if(!isset($_SESSION['cassino_logado'])){
        header("Location: /?r=4");
        exit();
    }
    
    $mesa = getMesa($_SESSION['cassino_logado']);

    function getMesa($chave){

        $config_file = dirname(__FILE__) . '/../sistema/System/Core/configs.json';

        if (file_exists($config_file)) {
            $configs = json_decode(file_get_contents($config_file), true);
        } else {
            throw new Exception("Arquivo de configurações ausente...");
            exit();
        }

        $db_host = $configs['db']['host'];
        $db_user = $configs['db']['user'];
        $db_database = $configs['db']['database'];
        $db_pwd = $configs['db']['password'];

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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="robots" content="noindex, nofollow">

    <title>REAL GAMING</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style type="text/css">
        * {
            margin:0;
            padding:0;
            list-style:none;
            text-decoration:none;
            border:none;
            line-height:inherit;
            outline:none;
            box-sizing: border-box;
        }
        body,html {
            background-color: #373435;
            font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
        }
        a {
            text-decoration: none;
        }
        img {
            max-width: 100%;
            max-height: 100%;
            vertical-align: middle;
        }
        .background, .container {
            position: absolute;
            width: 1080px;
            height: 1920px;
            background: #000;
            left: 50%;
            margin-left: -540px;
        }
        .container { background-color: transparent; }
        .background .item {
            position: absolute;
            width: 100%;
            height: 100%;
            background-size: cover;
            background-repeat: no-repeat;
            background-position: top center;
        }

        .input_titulo {
            margin: 30px 100px;
            font-size: 67px;
            text-align: center;
            color: #fff;
        }

        .ultimo_numero {
            position: absolute;
            width: 434px;
            height: 608px;
            left: 30px;
            bottom: 50px;
            border: 7px solid #999;
            background: url('img/bg_ultimo_numero.jpg');
        }

        .ultimo_numero span {
            position: absolute;
            width: 100%;
            bottom: 0;
            display: inline-block;
            font-size: 252px;
            font-weight: bold;
            text-align: center;
            color: #F7BC44;
        }

        .ultimos_numeros {
            position: absolute;
            width: 440px;
            height: 1670px;
            bottom: 50px;
            right: 30px;
            border: 7px solid #999;
            background: url('img/bg_ultimos_numeros.jpg');
        }

        .ultimos_numeros span {
            display: block;
            width: 220px;
            height: 145px;
            line-height: 145px;
            font-size: 140px;
            font-weight: bold;
            text-align: center;
            color: #ccc;
        }

        .ultimos_numeros span.num_vermelho {
            color: #F00;
        }

        .ultimos_numeros span.num_verde {
            margin: 0 auto;
            color: #2DB200;
        }

        .ultimos_numeros span.num_preto {
            margin-left: 200px;
            color: #FFBF00;
        }

    </style>
    <script src="js/jquery.js"></script>
    <script type="text/javascript">

        let numeros_mapeados = [
            "<span class='num_verde'>0</span>",
            "<span class='num_vermelho'>1</span>",
            "<span class='num_preto'>2</span>",
            "<span class='num_vermelho'>3</span>",
            "<span class='num_preto'>4</span>",
            "<span class='num_vermelho'>5</span>",
            "<span class='num_preto'>6</span>",
            "<span class='num_vermelho'>7</span>",
            "<span class='num_preto'>8</span>",
            "<span class='num_vermelho'>9</span>",
            "<span class='num_preto'>10</span>",
            "<span class='num_preto'>11</span>",
            "<span class='num_vermelho'>12</span>",
            "<span class='num_preto'>13</span>",
            "<span class='num_vermelho'>14</span>",
            "<span class='num_preto'>15</span>",
            "<span class='num_vermelho'>16</span>",
            "<span class='num_preto'>17</span>",
            "<span class='num_vermelho'>18</span>",
            "<span class='num_vermelho'>19</span>",
            "<span class='num_preto'>20</span>",
            "<span class='num_vermelho'>21</span>",
            "<span class='num_preto'>22</span>",
            "<span class='num_vermelho'>23</span>",
            "<span class='num_preto'>24</span>",
            "<span class='num_vermelho'>25</span>",
            "<span class='num_preto'>26</span>",
            "<span class='num_vermelho'>27</span>",
            "<span class='num_preto'>28</span>",
            "<span class='num_preto'>29</span>",
            "<span class='num_vermelho'>30</span>",
            "<span class='num_preto'>31</span>",
            "<span class='num_vermelho'>32</span>",
            "<span class='num_preto'>33</span>",
            "<span class='num_vermelho'>34</span>",
            "<span class='num_preto'>35</span>",
            "<span class='num_vermelho'>36</span>",
            "<span class='num_verde'>00</span>"
        ]

        let numeros = [];

        $(document).ready(function(){
            var connected = false;
            
            setTimeout(function(){
                if(!connected){
                    log('SERVIDOR OFFLINE');
                }
            },2000);

            <?php if($_SERVER['SERVER_NAME'] == "local.cassino"):  ?>
                socket = new WebSocket("ws://127.0.0.1:8002/");
            <?php else: ?>
                socket = new WebSocket("ws://18.214.216.6:8002/");              
            <?php endif ?>

            socket.onmessage = function (msg) {
                data = JSON.parse(msg.data);
                log(data);

                if(data.action == "titulo"){
                    $(".input_titulo").html(data.titulo);
                }

                if(data.action == "numeros"){
                    data.numeros = data.numeros.split(",");
                    let len = data.numeros.length;

                    if(data.numeros[(len-1)] == 37) $(".ultimo_numero span").html("00");
                    else $(".ultimo_numero span").html(data.numeros[(len-1)]);

                    let numbs = [];
                    if(len>11){
                        for(i=(len-1); i>(len-12); i--){
                            numbs.push(data.numeros[i]);
                        }
                        numbs = numbs.reverse();
                    }else{
                        numbs = data.numeros;
                    }

                    let nums = "";
                    for(i in numbs){
                        nums += numeros_mapeados[numbs[i]];
                    }

                    $(".ultimos_numeros").html(nums);


                }
            };
            socket.onopen = function () {
                connected = true;
                socket.send(JSON.stringify({
                    request: "view",
                    mesa: "<?php echo $mesa['chaveAcesso'] ?>"
                }));
                log('CONECTADO COM SERVIDOR');
            };
            socket.onerror = function () {
                log('ACONTECEU ALGUM ERRO INESPERADO');
                window.location.href = "/?r=100";
            };
            socket.onclose = function(){
                connected = false;
                log('DESCONECTADO COM SERVIDOR');
                window.location.href = "/?r=101";
            }

            $("input[name=titulo]").change(function(){
                socket.send(JSON.stringify({
                    request: "titulo",
                    titulo: $("input[name=titulo]").val()
                }));
            });
        });
        function log(msg){
            console.log(msg);
        }

    </script>
</head>
<body>
    <div class="background">
        <div class="item" style="background-image: url('img/background_cassino.jpg');"></div>
    </div>
    <div class="container">
        <div class="input_titulo">MIN 5 | MAX 5000</div>
        <div class="ultimo_numero"><span></span></div>
        <div class="ultimos_numeros">
        </div>
    </div> 
</body>
</html>
