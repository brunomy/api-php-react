<?php 
    //unset($_SESSION['cassino_logado']);
    if(!isset($_SESSION['cassino_logado']) && !isset($_POST['chaveAcesso'])){
        header("Location: /?r=1");
        exit();
    }

    else if(isset($_SESSION['cassino_logado']) && isset($_POST['chaveAcesso']) && $_POST['chaveAcesso'] != $_SESSION['cassino_logado']){
        header("Location: /?r=2");
        exit();
    }

    else if(!isset($_SESSION['cassino_logado']) && isset($_POST['chaveAcesso'])){
        $mesa = getMesa($_POST['chaveAcesso']);
        $_SESSION['cassino_logado'] = $_POST['chaveAcesso'];
    }

    else if(isset($_SESSION['cassino_logado'])){
        $mesa = getMesa($_SESSION['cassino_logado']);
    }
    

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
        body,html {
            background-color: #373435;
            height: 100%;
            overflow: hidden;
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
        .container {
            position: absolute;
            width: 1200px;
            height: 800px;
            left: 50%;
            top: 50%;
            margin-left: -600px;
            margin-top: -400px;
            background-color: rgba(255,255,255,0.01);
        }
        .row {
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 5px 0;
            clear: both;
            vertical-align: middle;
        }
        .numeros, .input_box, .controle {
            display: inline-block;
            margin: 0 5px;
        }
        .input_box {
            width: 650px;
            height: 80px;
            line-height: 80px;
            margin: 0 20px;
            box-sizing: border-box;
            background-color: #fff;
            border-radius: 20px;
            color: #373435;
        }
        .input_box.input_titulo { width: 650px; }
        .input_box.input_ultimos { width: 500px; }
        .input_box input {
            box-sizing: border-box;
            width: 80%;
            height: 80px;
            padding: 0 30px;
            background-color: transparent;
            border: none;
            font-size: 40px;
        }
        .input_box button {
            position: relative;
            box-sizing: border-box;
            width: 18%;
            height: 60px;
            background: rgba(255,239,18,1);
            background: -moz-linear-gradient(top, rgba(255,239,18,1) 0%, rgba(245,132,52,1) 100%);
            background: -webkit-gradient(left top, left bottom, color-stop(0%, rgba(255,239,18,1)), color-stop(100%, rgba(245,132,52,1)));
            background: -webkit-linear-gradient(top, rgba(255,239,18,1) 0%, rgba(245,132,52,1) 100%);
            background: -o-linear-gradient(top, rgba(255,239,18,1) 0%, rgba(245,132,52,1) 100%);
            background: -ms-linear-gradient(top, rgba(255,239,18,1) 0%, rgba(245,132,52,1) 100%);
            background: linear-gradient(to bottom, rgba(255,239,18,1) 0%, rgba(245,132,52,1) 100%);
            filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#ffef12', endColorstr='#f58434', GradientType=0 );
            border: 1 solid #f58434;
            border-radius: 10px;
            font-size: 40px;
            font-weight: bold;
            color: #fff;
        }
        .numeros a {
            display: inline-block;
            width: 120px;
            height: 100px;
            line-height: 100px;
            margin: 0 2px;
            border: 8px solid #fff;
            border-radius: 100px;
            font-size: 85px;
            font-weight: bold;
            text-align: center;
            color: #fff;
        }
        .numeros a.num_verde {
            background-color: #00A859;
        }
        .numeros a.num_vermelho {
            background-color: #B23136;
        }
        .numeros a.num_preto {
            background-color: #373435;
        }

        .controle a {
            display: inline-block;
            width: 70px;
            height: 70px;
            margin: 0 5px;
            background-color: #f4f4f4;
            border-radius: 10px;
            font-size: 60px;
            font-weight: bold;
            text-align: center;
        }

        .controle a.sair {
            background: radial-gradient(#ff5d71, #B23136);
            color: #fff;
        }

        .controle a.link_view {
            padding: 10px;
            background: radial-gradient(#96989A, #FEFEFE);
            color: #373435;
        }

    </style>
    <script src="js/jquery.js"></script>
    <script type="text/javascript">
        $(document).ready(function(){
            var today = "<?php echo date('Y-m-d',strtotime('-12 hours',strtotime('now'))); ?>";
            var connected = false;
            var interval = "";
            var sair = false;
            var mesa = {};
            var defaults = {
                id: "",
                mesa: "<?php echo $mesa['chaveAcesso']; ?>",
                titulo: $("input[name=titulo]").val(),
                views: [],
                ultimos_numeros: [],
                rodadas_registros: [],
                rodadas_qtd: []
            };
            setTimeout(function(){
                if(!connected){
                    log('SERVIDOR OFFLINE');
                    window.location.href = "/sair?r=101";
                }
            },2000);

            let numeros = [];
            let ultimos = [];

            $("input[name=titulo]").change(function(){
                socket.send(JSON.stringify({
                    request: "titulo",
                    mesa: "<?php echo $mesa['chaveAcesso'] ?>", 
                    titulo: $("input[name=titulo]").val()
                }));
                mesa.titulo = $("input[name=titulo]").val();
            });

            $(".numeros a").click(function(){
                if(numeros.length>11){
                    numeros.splice(0,1);
                }
                if(ultimos.length>4){
                    ultimos.splice(0,1);
                }

                let numero = $(this).text();

                numeros.push(numero);
                ultimos.push(numero);

                if(numero == "00") numero = 37;
                mesa.ultimos_numeros.push(numero);
                mesa.rodadas_registros[numero]++;

                if (mesa.rodadas_qtd.hasOwnProperty(today)) {
                    mesa.rodadas_qtd[today]++;
                }else{
                    mesa.rodadas_qtd[today] = 1;
                }

                socket.send(JSON.stringify({
                    request: "numeros",
                    mesa: mesa
                }));

                $(".input_ultimos input").val(ultimos.join("-"));
            });

            $(".sair").click(function(){
                sair = 1;
                socket.close();
            });

            $(".input_ultimos button").click(function(){
                if(numeros.length>0){
                    numeros.splice((numeros.length-1),1);
                }
                if(ultimos.length>0){
                    ultimos.splice((ultimos.length-1),1);
                }

                let len = mesa.ultimos_numeros.length;

                if(len>0){

                    let numero = mesa.ultimos_numeros[(len-1)];
                    mesa.rodadas_registros[numero]--;

                    if (mesa.rodadas_qtd.hasOwnProperty(today)) {
                        mesa.rodadas_qtd[today]--;
                    }else{
                        mesa.rodadas_qtd[today] = 1;
                    }

                    mesa.ultimos_numeros.splice((len-1),1);

                    socket.send(JSON.stringify({
                        request: "numeros",
                        mesa: mesa
                    }));

                }

                $(".input_ultimos input").val(ultimos.join("-"));
            });


        });

        function log(msg){
            console.log(msg);
        }

        function connectWS(){
            <?php if($_SERVER['SERVER_NAME'] == "local.cassino"):  ?>
                socket = new WebSocket("ws://127.0.0.1:8002/");
            <?php else: ?>
                socket = new WebSocket("ws://18.214.216.6:8002/");              
            <?php endif ?>

            socket.onmessage = function (msg) {
                data = JSON.parse(msg.data);
                log(data);

                if(data.action == "ocupado"){
                    sair = 2;
                    socket.close();
                }

                else if(data.action == "id"){
                    defaults.id = data.id;
                }

                else if(data.action == "connected"){
                    mesa = $.extend(defaults, data.mesa);
                    log(mesa);
                }
            };

            socket.onopen = function () {
                connected = true;
                clearInterval(interval);
                socket.send(JSON.stringify({
                    request: "connect",
                    mesa: defaults
                }));
                log('CONECTADO COM SERVIDOR');
            };

            socket.onerror = function () {
                log('ACONTECEU ALGUM ERRO INESPERADO');
                //window.location.href = "/?r=100";

                interval = setInterval(connectWS,5000);
            };

            socket.onclose = function(){
                connected = false;
                log('DESCONECTADO COM SERVIDOR');
                if(sair == 1)
                    window.location.href = "/sair";
                else if(sair == 2)
                    window.location.href = "/sair?r=102";
                else
                    //window.location.href = "/sair?r=101";
                    interval = setInterval(connectWS,5000);
            }
        }


    </script>
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="numeros">
                <a href="#" class="num_verde">0</a>
                <a href="#" class="num_verde">00</a>
            </div>
            <div class="input_box input_titulo">
                <?php if ($mesa['titulo']!=""): ?>
                    <input type="text" name="titulo" value="<?php echo $mesa['titulo'] ?>"><button>OK</button>
                <?php else: ?>
                    <input type="text" name="titulo" value="MIN 5 | MAX 5.000"><button>OK</button>
                <?php endif ?>
            </div>
            <div class="controle">
                <a href="view" target="_blank" class="link_view"><img src="img/monitor.svg" alt=""></a>
                <a href="#" class="sair">X</a>
            </div>
        </div>
        <div class="row">
            <div class="numeros">
                <a href="#" class="num_vermelho">1</a>
                <a href="#" class="num_preto">2</a>
                <a href="#" class="num_vermelho">3</a>
                <a href="#" class="num_preto">4</a>
                <a href="#" class="num_vermelho">5</a>
                <a href="#" class="num_preto">6</a>
                <a href="#" class="num_vermelho">7</a>
                <a href="#" class="num_preto">8</a>
            </div>
        </div>
        <div class="row">
            <div class="numeros">
                <a href="#" class="num_vermelho">9</a>
                <a href="#" class="num_preto">10</a>
                <a href="#" class="num_preto">11</a>
                <a href="#" class="num_vermelho">12</a>
                <a href="#" class="num_preto">13</a>
                <a href="#" class="num_vermelho">14</a>
                <a href="#" class="num_preto">15</a>
                <a href="#" class="num_vermelho">16</a>
            </div>
        </div>
        <div class="row">
            <div class="numeros">
                <a href="#" class="num_preto">17</a>
                <a href="#" class="num_vermelho">18</a>
                <a href="#" class="num_vermelho">19</a>
                <a href="#" class="num_preto">20</a>
                <a href="#" class="num_vermelho">21</a>
                <a href="#" class="num_preto">22</a>
                <a href="#" class="num_vermelho">23</a>
                <a href="#" class="num_preto">24</a>
            </div>
        </div>
        <div class="row">
            <div class="numeros">
                <a href="#" class="num_vermelho">25</a>
                <a href="#" class="num_preto">26</a>
                <a href="#" class="num_vermelho">27</a>
                <a href="#" class="num_preto">28</a>
                <a href="#" class="num_preto">29</a>
                <a href="#" class="num_vermelho">30</a>
                <a href="#" class="num_preto">31</a>
                <a href="#" class="num_vermelho">32</a>
            </div>
        </div>
        <div class="row">
            <div class="numeros">
                <a href="#" class="num_preto">33</a>
                <a href="#" class="num_vermelho">34</a>
                <a href="#" class="num_preto">35</a>
                <a href="#" class="num_vermelho">36</a>
            </div>
            <div class="input_box input_ultimos">
                <input type="text" name="titulo" value=""><button> << </button>
            </div>
        </div>
    </div>
</body>
</html>