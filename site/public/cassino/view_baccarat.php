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
        .clear {
            clear: both;
        }
        body,html {
            background-color: #000;
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
            width: 1158px;
            height: 800px;
            left: 50%;
            top: 50%;
            margin-left: -576px;
            margin-top: -400px;
        }

        .container .input_titulo {
            width: 1158px;
            height: 70px;
            line-height: 70px;
            background: rgba(0,0,0,0.05);
            text-align: center;
            font-size: 40px;
            color: #666;
        }

        .ultimos_numeros {
            display: flex;
            width: 1157px;
            margin: 20px auto;
            border: 1px solid #ccc;
        }

        .col {
            width: 105px;
            float: left;
        }

        .col > div {
            width: 105px;
            height: 105px;
            border: 1px solid #ccc;
            background-size: contain !important;
            background-position: center 8px !important;
        }

        .col > div.bt_player {
            background: url('cassino/img/controle_player.png') center center no-repeat;
        }

        .col > div.bt_banker {
            background: url('cassino/img/controle_banker.png') center center no-repeat;
        }

        .col > div.bt_tie {
            background: url('cassino/img/controle_tie.png') center center no-repeat;
        }

        .col > div.bt_player_2 {
            background: url('cassino/img/controle_player_2.png') center center no-repeat;
        }

        .col > div.bt_banker_2 {
            background: url('cassino/img/controle_banker_2.png') center center no-repeat;
        }

        .col > div.bt_tie_2 {
            background: url('cassino/img/controle_tie_2.png') center center no-repeat;
        }

    </style>
</head>
<body>
    <div class="container">
        <div class="input_titulo">MIN 5 | MAX 5000</div>
        <div class="ultimos_numeros">
            <div class="col">
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
            </div>
            <div class="col">
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
            </div>
            <div class="col">
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
            </div>
            <div class="col">
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
            </div>
            <div class="col">
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
            </div>
            <div class="col">
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
            </div>
            <div class="col">
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
            </div>
            <div class="col">
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
            </div>
            <div class="col">
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
            </div>
            <div class="col">
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
            </div>
            <div class="col">
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
            </div>
            <div class="clear"></div>
        </div>
    </div> 
    <script src="js/jquery.js"></script>
    <script type="text/javascript">

        var mesa = {};

        function log(msg){
            console.log(msg);
        }

        function getStorage(){
            mesa = JSON.parse(localStorage.getItem('mesa'));
            $('.input_titulo').html(mesa.titulo);

            console.log(mesa.colunas);

            $('.ultimos_numeros .col div').removeAttr('class');

            for(i=0;i<10;i++){                
                for(j=0;j<6;j++){
                    $('.ultimos_numeros .col:eq('+i+') div:eq('+j+')').attr("class",preencheQuadrado(mesa.colunas[i][j]));
                }
            }
            

        }
        
        function preencheQuadrado(imagens){
            switch(imagens){
                case "B": return "bt_banker"; break;
                case "P": return "bt_player"; break;
                case "T": return "bt_tie"; break;
                case "B2": return "bt_banker_2"; break;
                case "P2": return "bt_player_2"; break;
                case "T2": return "bt_tie_2"; break;
                default: "blank";
            }

        }

            

        getStorage();
        window.addEventListener('storage', getStorage);

    </script>
</body>
</html>
