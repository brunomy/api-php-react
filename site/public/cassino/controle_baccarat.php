<?php 
    include 'mesas_header.php';
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
        .input_box, .controle {
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
        .input_box.input_ultimos { width: 350px; }
        .input_box.input_ultimos  input{ font-size: 30px; }
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
        .internet {
            display: none;
            width: 200px;
            padding: 10px;
            background: #FFF212;
            border: 1px solid #D71B36;
            border-radius: 30px;
            text-align: center;
            color: #201F35;
        }
        .numeros {
            margin: 90px 0;
            width: 885px;
            display: flex;
            flex-wrap: wrap;
            gap: 20px 0;
            justify-content: center;
        }
        .numeros a {
            display: inline-block;
            width: 195px;
            height: 165px;
            margin: 0 50px;
            font-size: 85px;
            font-weight: bold;
            text-align: center;
            color: #fff;
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

        var rodadas_registros = [];
        var rodadas_qtd = {};
        var historico_numeros = [];
        var internetConnection = true;
        var timeout = 5; //segundos
        var wait = false;


        <?php ($mesa['titulo']=="") ? $titulo = "MIN 5 | MAX 5.000" : $titulo = $mesa['titulo']; ?>

        var mesa = {
            id: "",
            mesa: "<?php echo $mesa['chaveAcesso']; ?>",
            titulo: "<?php echo $titulo; ?>",
            historico: [],
            colunas:[[],[],[],[],[],[],[],[],[],[]]
        };


        $(document).ready(function(){

            let ultimos = [];
            let imagens = [];
            updateStorage();

            $("input[name=titulo]").change(function(){
                mesa.titulo = $("input[name=titulo]").val();
                updateStorage();
            });

            $(".numeros a").click(function(){
                if(!wait){
                    wait = true;
                    setTimeout(function(){ wait = false; }, (timeout*1000));

                    if(ultimos.length>4){
                        ultimos.splice(0,1);
                    }
                    if(imagens.length>4){
                        imagens.splice(0,1);
                    }

                    let jogada = $(this).attr('data-jogada');
                    let imagem = $(this).attr('data-bg');

                    ultimos.push(jogada);
                    imagens.push(imagem);

                    mesa.historico.unshift(imagem); //add no inicio do array

                    //preenche as colunas com a última jogada
                    for(i=0;i<10;i++){
                        if(mesa.colunas[i].length<6){
                            mesa.colunas[i].push(imagem);
                            break;
                        }
                    }

                    if(mesa.colunas[8].length==6){
                        mesa.colunas.shift(); //remove a primeira coluna
                        mesa.colunas.push([]); // adicionar uma coluna no final
                    }

                    $(".input_ultimos input").val(ultimos.join("-"));

                    updateStorage();
                }else{
                    alert('É necessário aguardar '+timeout+' segundos entre uma rodada');
                }
            });

            $(".sair").click(function(){
                //window.location = "sair";
                let r = confirm("Tem certeza que deseja sair?");
                if(r==true){
                    window.location = "sair";
                }
            });

            $(".input_ultimos button").click(function(){

                if(ultimos.length>0){
                    wait = false;
                    ultimos.splice((ultimos.length-1),1);
                    mesa.historico.shift(); //remove o primeiro item do array

                    //remove ultima jogada da ultioma colunas preenchida
                    for(i=0;i<10;i++){
                        if(mesa.colunas[i].length<6){
                            mesa.colunas[i].pop();
                            break;
                        }
                    }
                    
                    updateStorage();
                    $(".input_ultimos input").val(ultimos.join("-"));
                }
                
            });

        });

        function log(msg){
            console.log(msg);
        }

        function updateStorage(){
            localStorage.setItem('mesa', JSON.stringify(mesa));
        }


    </script>
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="input_box input_titulo">
                <input type="text" name="titulo" value="<?php echo $titulo ?>"><button>OK</button>
            </div>
            <div class="controle">
                <a href="view_baccarat" target="_blank" class="link_view"><img src="cassino/img/monitor.svg" alt=""></a>
                <a href="#" class="sair">X</a>
            </div>
        </div>
        <div class="row">
            <div class="numeros">
                <a href="#" class="bt_banker" data-bg="B" data-jogada="B"><img src="cassino/img/controle_banker.png" alt=""></a>
                <a href="#" class="bt_player" data-bg="P" data-jogada="P"><img src="cassino/img/controle_player.png" alt=""></a>
                <a href="#" class="bt_tie" data-bg="T" data-jogada="T"><img src="cassino/img/controle_tie.png" alt=""></a>

                <a href="#" class="bt_banker" data-bg="B2" data-jogada="B"><img src="cassino/img/controle_banker_2.png" alt=""></a>
                <a href="#" class="bt_player" data-bg="P2" data-jogada="P"><img src="cassino/img/controle_player_2.png" alt=""></a>
                <a href="#" class="bt_tie" data-bg="T2" data-jogada="T"><img src="cassino/img/controle_tie_2.png" alt=""></a>
            </div>
        </div>
        <div class="row">
            <div class="input_box input_ultimos">
                <input type="text" name="titulo" value=""><button> << </button>
            </div>
            <div class="internet">SEM CONEXÃO<br>Continue operando, os números serão enviados depois.</div>
        </div>
    </div>
</body>
</html>