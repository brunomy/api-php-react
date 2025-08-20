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

        .controle {
            display: flex;
            align-items: center;
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

        .controle a.bt-bonus {
            width: auto;
            height: 70px;
            line-height: 70px;
            border-radius: 20px;
            text-align: center;
            padding: 10px 15px;
            font-size: 30px;
            vertical-align: middle;
            color: #373435;
        }

        .lance-bonus {
            display: none;
            background: #1b41d7;
            border: 8px solid #fff;
            border-radius: 50px;
            padding: 15px 30px;
            font-size: 50px;
            font-weight: bold;
            color: #fff;
        }

    </style>
    <script src="js/jquery.js"></script>
    <script type="text/javascript">

        var today = "<?php echo date('Y-m-d',strtotime('-12 hours',strtotime('now'))); ?>";
        var rodadas_registros = [];
        var rodadas_qtd = {};
        var historico_numeros = [];
        var historico_bonus = [];
        var internetConnection = true;
        var sair = false;
        var timeout = 20; //segundos
        var wait = false;
        var concluirRodadaB = false;

    <?php if($mesa['rodadas_registros_json'] != ""): ?>
        rodadas_registros = <?php echo $mesa['rodadas_registros_json'] ?>;
    <?php else: ?>
        rodadas_registros = [];
        
        for(i=0;i<=37;i++){
            //obs: indice 37 representa o 00
            rodadas_registros[i]=0;
        }
    <?php endif ?>

    <?php if($mesa['rodadas_qtd_json'] != ""): ?>
        rodadas_qtd = <?php echo $mesa['rodadas_qtd_json'] ?>
    <?php endif ?>

    <?php if($mesa['rodadas_historico_json'] != ""): ?>
        historico_numeros = <?php echo $mesa['rodadas_historico_json'] ?>
    <?php endif ?>

    <?php if($mesa['bonus_historico_json'] != ""): ?>
        historico_bonus = <?php echo $mesa['bonus_historico_json'] ?>
    <?php endif ?>

        <?php ($mesa['titulo']=="") ? $titulo = "MIN 5 | MAX 5.000" : $titulo = $mesa['titulo']; ?>

        var mesa = {
            id: "",
            mesa: "<?php echo $mesa['chaveAcesso']; ?>",
            titulo: "<?php echo $titulo; ?>",
            ultimos_numeros: [],
            rodadas_registros: rodadas_registros,
            historico_numeros: historico_numeros,
            historico_bonus: historico_bonus,
            ultimo_bonus:[],
            rodadas_qtd: rodadas_qtd,
            dois_zeros: <?php echo $mesa['tipo']=='Dois Zeros' ? "true": "false"; ?>,
            bonus: {
                modo_ativo: false,
                rodada_ativa: false
            }
        };


        $(document).ready(function(){

            let ultimos = [];
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

                    let numero = $(this).text();

                    ultimos.push(numero);

                    if(numero == "00") numero = 37;
                    mesa.ultimos_numeros.push(numero);
                    mesa.rodadas_registros[numero]++;

                    mesa.historico_numeros.unshift(numero.toString()); //add no inicio do array
                    while(mesa.historico_numeros.length>1000){
                        mesa.historico_numeros.pop();//remove o ultimo do array
                    }

                    if (mesa.rodadas_qtd.hasOwnProperty(today)) {
                        mesa.rodadas_qtd[today]++;
                    }else{
                        mesa.rodadas_qtd[today] = 1;
                    }

                    $(".input_ultimos input").val(ultimos.join("-"));

                    mesa.bonus.rodada_ativa = false;

                    if(concluirRodadaB) concluirRodadaBonus(numero);

                    updateStorage();
                }else{
                    alert('É necessário aguardar '+timeout+' segundos entre uma rodada');
                }
            });

            $(".sair").click(function(){
                //window.location = "sair";
                let r = confirm("Tem certeza que deseja sair?");
                if(r==true){
                    if($('.internet:hidden').length == 0){
                        alert("Estamos sem internet, se fechar agora vai perder os dados da ultima conexão.");
                    }else{
                        sair = true;
                        updateBD();
                    }
                }
            });

            $(".input_ultimos button").click(function(){
                wait = false;

                if(ultimos.length>0){
                    ultimos.splice((ultimos.length-1),1);
                }

                let len = mesa.ultimos_numeros.length;

                if(len>0){

                    let numero = mesa.ultimos_numeros[(len-1)];
                    mesa.rodadas_registros[numero]--;

                    mesa.historico_numeros.shift(); //remove o primeiro item do array

                    if (mesa.rodadas_qtd.hasOwnProperty(today)) {
                        mesa.rodadas_qtd[today]--;
                    }

                    mesa.ultimos_numeros.splice((len-1),1);

                    updateStorage();

                }

                $(".input_ultimos input").val(ultimos.join("-"));
            });

            $(".bt-bonus").click(function(e){
                e.preventDefault();
                if(mesa.bonus.modo_ativo){
                    $(".lance-bonus").hide();
                    mesa.bonus.modo_ativo = false;
                    mesa.bonus.rodada_ativa = false;
                }else{                    
                    $(".lance-bonus").show();
                    mesa.bonus.modo_ativo = true;
                    mesa.bonus.rodada_ativa = false;
                }
                updateStorage();
            });

            $(".lance-bonus").click(function(e){
                e.preventDefault();
                if(mesa.bonus.rodada_ativa){
                    mesa.bonus.rodada_ativa = false;
                }else{                    
                    mesa.bonus.rodada_ativa = true;
                    rodadaBonus();
                }
            });

        });

        function log(msg){
            console.log(msg);
        }

        function updateStorage(){
            setHotColdNumbers();
            localStorage.setItem('mesa', JSON.stringify(mesa));
        }

        function updateBD(){
            $.post("update", {"titulo":mesa.titulo, "rodadas_registros": JSON.stringify(mesa.rodadas_registros), "rodadas_qtd": JSON.stringify(mesa.rodadas_qtd), "rodadas_historico": JSON.stringify(mesa.historico_numeros), "bonus_historico": JSON.stringify(mesa.historico_bonus), "mesa": mesa.mesa}, function(data,status){

                if(data=='sair'){
                    alert("Outro computador acaba de se logar nessa mesma mesa, você será deslogado automaticamente agora.");
                    window.location = "sair";
                }
                
                if(status=="success") $('.internet').hide();
                else $('.internet').show();
                if(sair) window.location = "sair";
            }).done(function(){
                $('.internet').hide();
            }).fail(function(){
                $('.internet').show();
                if(sair) alert("Estamos sem internet, se fechar agora vai perder os dados da ultima conexão.");
                sair = false;
            });
        }

        function setHotColdNumbers(){
            var somaHistorico = [];
            var qtdNumerosNaRoleta = "<?php if($mesa['tipo']=='Dois Zeros') echo 37; else echo 36 ?>";
            for(j=0;j<=qtdNumerosNaRoleta;j++){
                somaHistorico[j] = 0;
            }
            for(i=0;i<mesa.historico_numeros.length;i++){
                var num = parseInt(mesa.historico_numeros[i]);
                somaHistorico[num]++;
            }

            //HOT NUMBERS
            var num1 = getHotNumber(somaHistorico,[]);
            var num2 = getHotNumber(somaHistorico,[num1]);
            var num3 = getHotNumber(somaHistorico,[num1,num2]);
            mesa.hotNumbers = [
                {num:num1,qtd:somaHistorico[num1]},
                {num:num2,qtd:somaHistorico[num2]},
                {num:num3,qtd:somaHistorico[num3]}
            ]

            //COLD NUMBERS
            num1 = getColdNumber(somaHistorico,[]);
            num2 = getColdNumber(somaHistorico,[num1]);
            num3 = getColdNumber(somaHistorico,[num1,num2]);
            mesa.coldNumbers = [
                {num:num1,last:mesa.historico_numeros.indexOf(num1.toString())},
                {num:num2,last:mesa.historico_numeros.indexOf(num2.toString())},
                {num:num3,last:mesa.historico_numeros.indexOf(num3.toString())}
            ]
        }

        function getHotNumber(arr,notIn){
            var greaterNum = 0;
            var position = 0;
            for(i=0;i<arr.length;i++){
                var num = parseInt(arr[i]);
                if(num >= greaterNum && !notIn.includes(i)){
                    greaterNum = num;
                    position = i;
                }
            }
            return position;
        }

        function getColdNumber(arr,notIn){
            var minorNum = 1000;
            var position = 0;
            for(i=0;i<arr.length;i++){
                var num = parseInt(arr[i]);
                if(num <= minorNum && !notIn.includes(i)){
                    minorNum = num;
                    position = i;
                }
            }
            return position;
        }

        function rodadaBonus(){

            rand1 = uniqueRandom();
            rand2 = uniqueRandom([rand1]);
            rand3 = uniqueRandom([rand1,rand2]);
            rand4 = uniqueRandom([rand1,rand2,rand3]);

            mesa.ultimo_bonus = [rand1.toString(),rand2.toString(),rand3.toString(),rand4.toString()];
            concluirRodadaB = true;

            updateStorage();

        }

        function uniqueRandom(unique=[]){
            max = 37;
            if(mesa.dois_zeros) max = 38
            rand = parseInt(Math.random() * max);
            for (var i = unique.length - 1; i >= 0; i--) {
                if(unique[i] == rand) return uniqueRandom(unique);
            }
            return rand;
        }

        function concluirRodadaBonus(numero){
            concluirRodadaB = false;
            var copy = [mesa.ultimo_bonus[0],mesa.ultimo_bonus[1],mesa.ultimo_bonus[2],mesa.ultimo_bonus[3]];
            copy.sort((a,b)=>a-b);
            var rodada = copy[0]+","+copy[1]+","+copy[2]+","+copy[3]+":"+numero;
            mesa.historico_bonus.unshift(rodada);//add no inicio do array
            while(mesa.historico_bonus.length>100){
                mesa.historico_bonus.pop();//remove o ultimo do array
            }
        }



        setInterval(updateBD, 60000);

    </script>
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="numeros">
                <a href="#" class="num_verde">0</a>
                <?php if ($mesa['tipo']=='Dois Zeros'): ?>
                    <a href="#" class="num_verde">00</a>
                <?php endif ?>
            </div>
            <div class="input_box input_titulo">
                <input type="text" name="titulo" value="<?php echo $titulo ?>"><button>OK</button>
            </div>
            <div class="controle">
                <a href="view2" target="_blank" class="link_view"><img src="cassino/img/monitor.svg" alt=""></a>
                <a href="#" class="sair">X</a>
                <a href="#" class="bt-bonus">Bônus</a>
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
            <div class="internet">SEM CONEXÃO<br>Continue operando, os números serão enviados depois.</div>
            <a href="#" class="lance-bonus">100X</a>
        </div>
    </div>
</body>
</html>