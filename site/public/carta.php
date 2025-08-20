<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>

* {
    margin:0;
    padding:0;
    list-style:none;
    text-decoration:none;
    border:none;
    line-height:inherit;
    outline:none;
}

body {
    font-family: Verdana, Geneva, sans-serif;
    font-size:16px;
    text-align: justify;
    color:#222;
}

.clear {
    clear: both;
}

@page {
    size: A4;
    margin: 0;
}

.page {
    position: relative;
    width: 21cm;
    height: 29.7cm;
    padding: 0 1.7cm;
    margin: 1cm auto;
    border: 1px #D3D3D3 solid;
    border-radius: 5px;
    background: white;
    box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
}

@media print {
    .page {
        margin: 0;
        border: initial;
        border-radius: initial;
        width: initial;
        min-height: initial;
        box-shadow: initial;
        background: initial;
        page-break-after: always;
    }
}

p {
    margin: 30px 0;
}

img {
    max-width: 100%;
    max-height: 100%;
    vertical-align: middle;
}

.vendedor { font-size: 22px; }

.logomarca {
    float: right;
    width: 200px;
    margin-top: 1.5cm;
}

.order_id {
    float: left;
    width: 100px;
    height: 30px;
    margin-top: 50px;
    font-style: italic;
}

.conteudo {
    margin-top: 50px;
    font-size: 20px;
}

.luck {
    margin: 50px 0;
    font-size: 28px;
    font-style: italic;
}

.ps {
    margin: 50px 0;
    font-style: italic;
}

.rodape {
    position: absolute;
    width: 100%;
    height: auto;
    left: 0;
    bottom: 0;
}

.rodape img {
    width: 100%;
    height: auto;
}


</style>
</head>
<body>
<?php

    if(isset($_GET['p']) && $_GET['p'] != ""){

        if($sistema->isValidMd5($_GET['p'])){
            $where =  " WHERE MD5(a.id) = '".$_GET['p']."'";
        }else{
            $where =  " WHERE a.id = '".$sistema->desembaralhar($_GET['p'])."'";
        }
        
        $pedido = $sistema->DB_fetch_array('SELECT b.nome vendedor, b.telefone, c.nome cliente, a.id pedido_id FROM tb_pedidos_pedidos a LEFT JOIN tb_admin_users b ON a.id_vendedor=b.id JOIN tb_clientes_clientes c ON a.id_cliente=c.id ' . $where);

        if($pedido->num_rows){
            $primeiro_nome = explode(" ",$pedido->rows[0]['cliente']);
?>
<div class="page">
    <div class="logomarca"><img src="../img/logo-carta.png" alt=""></div>
    <div class="order_id">#<?php echo $pedido->rows[0]['pedido_id']; ?></div>
    <div class="clear"></div>
    <div class="conteudo">
        <p><strong>Parabéns, seu jogo agora foi para outro nível.</strong></p>
        <p>Olá <strong><?php echo ucfirst($primeiro_nome[0]); ?></strong>, <br>Yesssssss! Seu pedido chegou e está muito lindo.</p>
        <p>Estamos ansiosos para ver os itens no seu ambiente, tire uma foto por favor e <strong>nos mande no WhatsApp</strong>, pode ser até mesmo com você ou em uma partida com seus amigos.</p>
        <p>Sempre que postar no Instagram marque <strong>@real_poker</strong>. Teremos o prazer de compartilhar nas nossas redes sociais.</p>
        
        <div class="luck"><strong>GOOD LUCK</strong> de toda equipe Real Poker</div>
        <div class="ps">PS: É muito importante para nós recebermos sua foto pois valorizamos muito ver o resultado final da nossa parceria. Então, faz essa forcinha e manda essa foto pra gente =)</div>

        <?php if($pedido->rows[0]['vendedor'] != ""){ ?>
            <p>Atenciosamente:<br>
                <strong class="vendedor"><?php echo $pedido->rows[0]['vendedor'] ?> <br>
                <?php if($pedido->rows[0]['telefone'] != ''){ echo $pedido->rows[0]['telefone']; } ?></strong>
            </p>
        <?php } ?>

    </div>
    <div class="rodape"><img src="../img/rodape-carta.png" alt=""></div>

    <?php 
            }else{
    ?>
    <p>Cliente não foi encontrado!</p>
    <?php 
            }
        }else{
    ?>
    <p>Cliente não foi encontrado!</p>
</div>
<?php
    }
?>



</body>
</html>