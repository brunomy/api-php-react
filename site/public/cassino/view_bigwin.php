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
        .container {
            position: absolute;
            width: 1366px;
            height: 768px;
            background: #3B393A;
            left: 50%;
            top: 50%;
            margin:-384px 0 0 -683px;
        }

        .input_titulo {
            margin: 30px 100px;
            font-size: 50px;
            text-align: center;
            color: #fff;
        }

        .ultimos_numeros {
            position: absolute;
            width: 90%;
            height: 100px;
            bottom: 40px;
            left: 50%;
            margin-left: -45%;
            text-align: center;
        }

        .ultimos_numeros span {
            display: inline-block;
            width: 80px;
            height: 80px;
            line-height: 80px;
            margin: 0 10px;
            border-radius: 100px;
            font-size: 45px;
            text-align: center;
            color: #fff;
        }

        .ultimos_numeros span.card_1 { background-color: #C4AA39; }
        .ultimos_numeros span.card_2 { background-color: #1959AF; }
        .ultimos_numeros span.card_5 { background-color: #7CA82D; }
        .ultimos_numeros span.card_2x { background-color: #000; }
        .ultimos_numeros span.card_10 { background-color: #892D7B; }
        .ultimos_numeros span.card_20 { background-color: #AF5127; }
        .ultimos_numeros span.card_40 { background-color: #A52426; }
        .ultimos_numeros span.card_7x { background-color: #000; }

        .ultimo_lance {
            position: absolute;
            width: 700px;
            height: 350px;
            line-height: 350px;
            left: 50%;
            top: 50%;
            margin: -200px 0 0 -350px;
            text-align: center;
            vertical-align: middle;
        }

        .ultimo_lance div { display: none; }


    </style>
</head>
<body>
    <div class="background">
        <div class="item" style="background-image: url('cassino/img/background_cassino.jpg');"></div>
    </div>
    <div class="container">
        <div class="input_titulo">MIN 5 | MAX 5000</div>
        <div class="ultimo_lance">
            <div class="card_1"><img src="cassino/img/bigwin_card_1.jpg" alt=""></div>
            <div class="card_2"><img src="cassino/img/bigwin_card_2.jpg" alt=""></div>
            <div class="card_5"><img src="cassino/img/bigwin_card_5.jpg" alt=""></div>
            <div class="card_2x"><img src="cassino/img/bigwin_card_2x.jpg" alt=""></div>
            <div class="card_10"><img src="cassino/img/bigwin_card_10.jpg" alt=""></div>
            <div class="card_20"><img src="cassino/img/bigwin_card_20.jpg" alt=""></div>
            <div class="card_40"><img src="cassino/img/bigwin_card_40.jpg" alt=""></div>
            <div class="card_7x"><img src="cassino/img/bigwin_card_7x.jpg" alt=""></div>
        </div>
        <div class="ultimos_numeros">
        </div>
    </div> 
    <script src="js/jquery.js"></script>
    <script type="text/javascript">

        var numeros_mapeados = [
            "<span class='card_1'>x1</span>",
            "<span class='card_2'>x2</span>",
            "<span class='card_5'>x5</span>",
            "<span class='card_2x'>2x</span>",
            "<span class='card_10'>x10</span>",
            "<span class='card_20'>x20</span>",
            "<span class='card_40'>x40</span>",
            "<span class='card_7x'>7x</span>"
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

            $(".ultimo_lance div").hide();
            $(".ultimo_lance .card_"+mesa.ultimos_numeros[(len-1)]).show();

            let index = $(".ultimo_lance .card_"+mesa.ultimos_numeros[(len-1)]).index();


            let numbs = [];
            if(len>12){
                for(i=(len-1); i>(len-13); i--){
                    numbs.push(mesa.ultimos_numeros[i]);
                }
                numbs = numbs.reverse();
            }else{
                numbs = mesa.ultimos_numeros;
            }

            let nums = "";
            for(i in numbs){
                nums += "<span class='card_"+numbs[i]+"'>"+numbs[i]+"</span>";
            }

            $(".ultimos_numeros").html(nums);

        }

        getStorage();
        window.addEventListener('storage', getStorage);

    </script>
</body>
</html>
