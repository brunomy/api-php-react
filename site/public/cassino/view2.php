<?php 
    if(!isset($_SESSION['cassino_logado'])){
        header("Location: /?r=4");
        exit();
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
        .background, .container, .bonus_rodada_ativada {
            position: absolute;
            width: 768px;
            height: 1366px;
            background: #000;
            left: 50%;
            margin-left: -384px;
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

        .bonus_rodada_ativada {
            background: url("cassino/img/roleta_bonus.jpg") no-repeat center #000105;
            background-size: auto 100%;
        }

        .bonus_rodada_ativada > div {
            display: none;
            position: absolute;
            width: 330px;
            height: 330px;
            line-height: 330px;
            background-image: url("cassino/img/raios_bg.gif");
            background-repeat: no-repeat;
            background-size: 100% auto;
            background-position: center;
            font-size: 130px;
            font-weight: bold;
            text-align: center;
        }

        .bonus_rodada_ativada > div:after {
            content: "";
            position: absolute;
            width: 200px;
            height: 100px;
            background: url("cassino/img/100x.png") no-repeat center top;
            background-size: 100% auto;
            bottom: -70px;
            left: 70px;
        }

        .bonus_rodada_ativada > div:nth-child(1) {
            top: 155px;
            left: 55px;
        }

        .bonus_rodada_ativada > div:nth-child(2) {
            top: 375px;
            left: 385px;
        }

        .bonus_rodada_ativada > div:nth-child(3) {
            top: 600px;
            left: 50px;
        }

        .bonus_rodada_ativada > div:nth-child(4) {
            top: 830px;
            left: 400px;
        }

        .input_titulo {
            margin: 30px 100px;
            font-size: 50px;
            text-align: center;
            color: #fff;
        }

        .hot_cold_numbers {
            position: absolute;
            display: none;
            width: 305px;
            height: 305px;
            padding: 20px;
            background: #000;
            border: 7px solid #999;
            left: 30px;
            font-size: 18px;
            color: #fff;
        }

        .hot {  
            bottom: 890px;
            background: url('cassino/img/bg_hot.jpg') no-repeat left top #000;
            color: #EEC31F;
        }

        .cold {
            bottom: 570px;
            background: url('cassino/img/bg_cool.jpg') no-repeat left top #000;
            color: #3d90dc;
        }

        .hot_cold_numbers .titulo {
            font-size: 25px;
            font-weight: bold;
        }

        .hot_cold_numbers .titulo span { font-size: 40px; }

        .hot_cold_numbers .numbers {
            height: 130px;
            margin-top: 30px;
        }

        .hot_cold_numbers .numbers li {
            float: left;
            margin-right: 10px;
            font-size: 30px;
            font-weight: bold;
            text-align: center;
            background-repeat: no-repeat;
            background-position: center top;
        }
        .hot_cold_numbers.hot .numbers li { background-image: url('cassino/img/ball_hot.png'); }
        .hot_cold_numbers.cold .numbers li { background-image: url('cassino/img/ball_cold.png'); }

        .hot_cold_numbers .numbers li span {
            display: block;
            width: 70px;
            height: 70px;
            line-height: 65px;
            margin-bottom: 10px;
            border-radius: 50px;
            font-size: 40px;
            color: #fff;
        }


        .ultimo_numero {
            position: absolute;
            width: 305px;
            height: 500px;
            left: 30px;
            bottom: 50px;
            border: 7px solid #999;
            background: url('cassino/img/bg_ultimo_numero.jpg');
            background-size: cover;
        }

        .ultimos_numero .num {
            position: absolute;
            width: 100%;
            height: 50%;
        }

        .ultimo_numero .num span {
            position: absolute;
            width: 100%;
            bottom: 0;
            display: inline-block;
            font-size: 160px;
            font-weight: bold;
            text-align: center;
        }

        .bonus-container {
            position: absolute;
            width: 100%;
            height: 230px;
            background: #1b41d7;
            background: url("cassino/img/roleta_bonus.jpg") no-repeat center #000105;
            background-size: 100% auto;
        }

        .bonus-container .header {
            width: 100%;
            min-height: 100%;
            padding: 10px;
            font-size: 20px;
            color: #fff;
            background: url("cassino/img/100x.png") no-repeat center center;
            background-size: 130px auto;
        }

        .bonus-container .bonus_numeros {
            position: absolute;
            width: 100%;
            bottom: 0;
            padding-bottom: 15px;
            text-align: center;
        }

        .bonus-container .bonus_numeros div {
            display: inline-block;
            width: 60px;
            height: 60px;
            line-height: 60px;
            background: url("cassino/img/raios_bg.gif") no-repeat center;
            background-size: 100% auto;
            margin: 0 2px;
            font-size: 25px;
            font-weight: bold;
            color: #fff;
        }

        .ultimos_numeros {
            position: absolute;
            width: 308px;
            height: 1145px;
            bottom: 50px;
            right: 30px;
            border: 7px solid #999;
            background: url('cassino/img/bg_ultimos_numeros.jpg');
            background-size: cover;
        }

        .ultimos_numeros span {
            display: block;
            width: 110px;
            height: 75px;
            line-height: 75px;
            font-size: 80px;
            font-weight: bold;
            text-align: center;
            color: #ccc;
        }

        span.num_vermelho {
            color: #F00;
        }

        span.num_verde {
            color: #2DB200;
        }
        .ultimos_numeros span.num_verde {
            margin: 0 auto;
        }

        span.num_preto {
            color: #FFBF00;
        }
        .ultimos_numeros span.num_preto {
            margin-left: 185px;
        }

    </style>
</head>
<body>
    <div class="background">
        <div class="item" style="background-image: url('cassino/img/background_cassino.jpg');"></div>
    </div>
    <div class="container">
        <div class="input_titulo">MIN 5 | MAX 5000</div>
        <div class="ultimo_numero">
            <div class="bonus-container">
                <div class="header">
                    Pleno: 25x <br>
                    Pleno: Bônus 100x
                </div>
                <div class="bonus_numeros">
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                </div>
            </div>
            <div class="num"></div>
        </div>
        <div class="hot_cold_numbers hot">
            <div class="titulo"><span>HOT</span> numbers</div>
            <ul class=numbers>
                <li><span>2</span> 78</li>
                <li><span>15</span> 32</li>
                <li><span>32</span> 67</li>
            </ul>
            <div class="label">Vitórias nas últimas <span>1000</span> jogadas</div>
        </div>
        <div class="hot_cold_numbers cold">
            <div class="titulo"><span>COLD</span> numbers</div>
            <ul class=numbers>
                <li><span>2</span> 78</li>
                <li><span>15</span> 32</li>
                <li><span>32</span> 67</li>
            </ul>
            <div class="label">Rodadas desde a última vitória</div>
        </div>
        <div class="ultimos_numeros">
        </div>
    </div> 
    <div class="bonus_rodada_ativada">
        <div></div>
        <div></div>
        <div></div>
        <div></div>
    </div>
    <script src="js/jquery.js"></script>
    <script type="text/javascript">

        var numeros_mapeados = [
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

        var numeros = [];
        var mesa = {};
        var bonus_recem_ativado = false;

        function log(msg){
            console.log(msg);
        }

        function getStorage(){
            mesa = JSON.parse(localStorage.getItem('mesa'));
            $('.input_titulo').html(mesa.titulo);

            let len = mesa.ultimos_numeros.length;

            /*
            if(mesa.ultimos_numeros[(len-1)] == 37) $(".ultimo_numero span").html("00");
            else $(".ultimo_numero span").html(mesa.ultimos_numeros[(len-1)]);
            */

            $(".ultimo_numero .num").html(numeros_mapeados[mesa.ultimos_numeros[(len-1)]]);


            let numbs = [];
            if(len>14){
                for(i=(len-1); i>(len-15); i--){
                    numbs.push(mesa.ultimos_numeros[i]);
                }
                numbs = numbs.reverse();
            }else{
                numbs = mesa.ultimos_numeros;
            }

            let nums = "";
            for(i in numbs){
                nums += numeros_mapeados[numbs[i]];
            }

            $(".ultimos_numeros").html(nums);
            $(".hot_cold_numbers.hot .label span").html(mesa.historico_numeros.length);
            
            //PREENCHER HOT NUMBERS
                flag = true;
                for (var i=0; i<mesa.coldNumbers.length; i++) {
                    if(mesa.hotNumbers[i].qtd==0) flag = false;
                    if(mesa.hotNumbers[i].num=="37") num = "00"; else num = mesa.hotNumbers[i].num;
                    $(".hot_cold_numbers.hot .numbers li:eq("+i+")").html('<span>'+num+'</span>'+mesa.hotNumbers[i].qtd);
                }
                if(flag) $(".hot_cold_numbers.hot").show();//mostrar somente se os 3 números tiver saido pelo menos uma vez
                else $(".hot_cold_numbers.hot").hide();

            //PREENCHER COLD NUMBERS
                flag = true;
                for (var i=0; i<mesa.coldNumbers.length; i++) {
                    if(mesa.coldNumbers[i].last==-1){
                        flag = false;
                        last = '-';
                    }else{
                        last = mesa.coldNumbers[i].last;
                    }
                    if(mesa.coldNumbers[i].num=="37") num = "00"; else num = mesa.coldNumbers[i].num;
                    $(".hot_cold_numbers.cold .numbers li:eq("+i+")").html('<span>'+num+'</span>'+last);
                }
                if(flag || mesa.historico_numeros.length > 100) $(".hot_cold_numbers.cold").show(); //mostrar somente se todos os números tiver saido pelo menos uma vez ou se o histórico de número for maior que 100
                else $(".hot_cold_numbers.cold").hide();


            if(mesa.bonus.modo_ativo){
                $(".ultimo_numero .bonus-container").show();
            }else{
                $(".ultimo_numero .bonus-container").hide();
            }

            if(mesa.bonus.rodada_ativa){
                rodadaBonus();
            }else{
                $(".bonus_rodada_ativada > div").hide();
                $(".bonus_rodada_ativada").hide();
            }
        }

        function rodadaBonus(){
            $(".bonus_rodada_ativada").fadeIn();

            $(".bonus_rodada_ativada div").fadeOut();

            setTimeout(function(){ $(".bonus_rodada_ativada > div:eq(0)").html(numeros_mapeados[mesa.ultimo_bonus[0]]).fadeIn(); },500);
            setTimeout(function(){ $(".bonus_rodada_ativada > div:eq(1)").html(numeros_mapeados[mesa.ultimo_bonus[1]]).fadeIn(); },1000);
            setTimeout(function(){ $(".bonus_rodada_ativada > div:eq(2)").html(numeros_mapeados[mesa.ultimo_bonus[2]]).fadeIn(); },1500);
            setTimeout(function(){ $(".bonus_rodada_ativada > div:eq(3)").html(numeros_mapeados[mesa.ultimo_bonus[3]]).fadeIn(); },2000);

            setTimeout(function(){ $(".bonus-container .bonus_numeros div:eq(0)").html(numeros_mapeados[mesa.ultimo_bonus[0]]); },500);
            setTimeout(function(){ $(".bonus-container .bonus_numeros div:eq(1)").html(numeros_mapeados[mesa.ultimo_bonus[1]]); },500);
            setTimeout(function(){ $(".bonus-container .bonus_numeros div:eq(2)").html(numeros_mapeados[mesa.ultimo_bonus[2]]); },500);
            setTimeout(function(){ $(".bonus-container .bonus_numeros div:eq(3)").html(numeros_mapeados[mesa.ultimo_bonus[3]]); },500);

        }

        getStorage();
        window.addEventListener('storage', getStorage);

    </script>
</body>
</html>
