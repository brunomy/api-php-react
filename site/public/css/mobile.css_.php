<?php 
    include 'include.css.php';
?>

/* =========== HEADER CONTENT ======== */

header {}

header .barra_topo1 .wordseries {
    position: absolute;
    width: 140px;
    top: 60px;
    left: 50%;
    margin-left: 20px;
    font-size: 10px;
    z-index: 1;
}
header .barra_topo1 .wordseries img { display: block; margin: 0 auto; height: 30px; }

header .barra_topo1 .configs {
	position: absolute;
    width: 80px;
    float: none;
    left: 15px;
    top: 12px;
    z-index: 1;
}

header .barra_topo1 .configs .selected { background-size: 8px auto; }

header .barra_topo1 .configs .language {
    width: 40px;
    height: 20px;
    line-height: 20px;
    float: left;
    border-right: none;
}

header .barra_topo1 .configs .language .selected .flag {
    width: 17px;
    height: 11px;
}

header .barra_topo1 .configs .language .selected .lan { display: none; }

header .barra_topo1 .configs .moeda {
    width: 30px;
    height: 20px;
    line-height: 20px;
    float: right;
    padding-left: 0;
    border-left: none;
}

header .barra_topo1 .features {
    width: 180px;
    padding-left: 10px;
}

header .barra_topo1 .features:before { display: none; }

header .barra_topo1 .features a {
    display: inline-block;
    margin: 0 5px 0 0; 
}

header .barra_topo1 .features a i { width: 20px; font-size: 11px; }



header .barra_topo2 {
	position: relative;
	height: 110px;
}

header .barra_topo2 .logomarca {
    width: 100%;
    height: 60px;
	margin: 0;
}

header .barra_topo2 .barra_atendimentos {
    position: absolute;
    width: 240px;
    top: 85px;
    left: 50%;
    margin: 0;
    margin-left: -120px;
}


header .barra_topo2 .barra_atendimentos a {
    width: 85px;
    height: 20px;
    line-height: 20px !important;
    padding-left: 25px;
    background-size: auto 20px;
    font-size: 12px;
}

header .barra_topo2 .barra_atendimentos a:nth-child(3) { float: right; margin-right: 0; }

header .barra_topo2 .barra_atendimentos a.atendimento_televendas:after {
    width: 10px;
    height: 15px;
    right: 5px;
    top: 3px;
    background: url('../img/lang_down.png') no-repeat right;
    background-size: 8px auto;
}

header .barra_topo2 .barra_atendimentos a span { display: none; }
header .barra_topo2 .barra_atendimentos a.atendimento_chat { display: none; }

header .barra_topo2 .carrinho,
header.fixa .barra_topo2 .carrinho {
	width: 210px;
    top: 113px;
    right: 15px;
}

header.fixa .barra_topo2 .carrinho {
    top: 0;
}

header .barra_topo2 .carrinho .resumo,
header.fixa .barra_topo2 .carrinho .resumo {
	padding-right: 40px;
    background-position: right 0px top -20px;
    border: none;
}



header .barra_topo3 {
    height: 40px;
    line-height: 40px;
}

header .barra_topo3 .mobilenavbutton { 
	padding-left: 15px;
    background-size: 23px auto;
    font-size: 12px;
}

header .barra_topo3 nav {
    position: absolute;
    display: none;
    width: 100%;
    margin-left: -71px;
    background: #83130b;
}


/* MENU ABERTO */
header .barra_topo3 nav > li.menu-aberto > ul > li {
    width: 46%;
    padding: 0 2%;
}

header .barra_topo3 nav > li.menu-aberto > ul > li .img {
    height: 24vw;
}


header #televendas {
    width: 100%;
    width: -webkit-calc(100% - 30px);
    width: -moz-calc(100% - 30px);
    width: calc(100% - 30px);
    top: 205px;
    left: 0;
    margin-left: 0;
    padding: 25px 15px 50px 15px;
}

header #televendas .box.brasil { width: 235px; }
header #televendas .box.america { 
	width: 235px;
	margin-top: 20px;
}

header #televendas .box .titulo {
    padding: 8px 0;
    border-bottom: 1px solid #343434;
    font-size: 11px;
    letter-spacing: 1px;
}

header #televendas .box.america .titulo { margin-bottom: 20px; }

header #televendas .box span {
    width: 235px;
    height: 20px;
    line-height: 20px;
    font-size: 12px;
}

header #televendas .box span a { font-size: 11px; }

header #televendas .box span.whatsapp { 
    width: 210px;
    margin: 25px 0 5px;
    padding-left: 30px;
    background: url('../img/icon_televendas_whatsapp.png') no-repeat left 5px center;
    background-size: 15px auto;
    font-size: 11px;
}

header #televendas .box span.skype {
    width: 200px;
    margin: 0 0 15px;
    padding-left: 30px;
    background-size: 25px auto;
    font-size: 11px;
}


header #carrinho {
    top: 220px;
    right: 15px;
}

header #login { left: inherit; right: 20px; }
header #login:after { right: 50%; }

/* =========== FOOTER CONTENT ======== */


footer .selos .titulo {
    margin: 5px 0;
    font-size: 12px;
}

footer .selos .pgmts,
footer .selos .certificados {
    width: auto;
    float: none;
    text-align: center;
}

footer .selos .certificados .titulo { margin-top: 20px; }

footer .selos img { width: auto; height: 35px; }


footer .newsletter {
    height: auto;
    line-height: auto;
}

footer .newsletter .label {
    width: auto;
    height: 50px;
    line-height: 50px;
    float: none;
    padding-left: 40px;
    background-size: 30px auto; 
    font-size: 11px;
    letter-spacing: 1px;
}

footer .newsletter .form_newsletter {
    width: 100%;
    float: none;
    margin: 0px 0 20px;
}

footer .newsletter .form_newsletter input {
    width: 68%;
}

footer .newsletter .form_newsletter button {
    width: 30%;
    font-size: 10px;
}




footer .rodape .televendas .box.brasil span,
footer .rodape .televendas .box.brasil span { width: 100%; }


footer .rodape .televendas .box.brasil span.whatsapp {
    width: 100%;
    width: -webkit-calc(100% - 40px);
    width: -moz-calc(100% - 40px);
    width: calc(100% - 40px);
    margin: 20px 0 0 0;
    padding-left: 40px;
    background-position: 5px center;
}

footer .rodape .televendas .box.brasil span.skype {
    width: 100%;
    width: -webkit-calc(100% - 40px);
    width: -moz-calc(100% - 40px);
    width: calc(100% - 40px);
    margin: 0 0 20px;
}

footer .rodape .televendas .box.america {
    width: 100%;
    margin-top: 30px;
}

footer .rodape .televendas .box.america .titulo { margin-bottom: 20px; }

footer .rodape .televendas .horario {
    width: 100%;
    padding: 30px 0;
    font-size: 14px;
}

footer .rodape .redes_sociais {
    position: relative;
    width: 100%;
    top: inherit;
    left: inherit;
}


footer .nav nav {
    width: 100%;
    height: auto;
}

footer .nav nav li {
    display: block;
    float: none;
    padding: 5px 0;
    border: none;
    text-align: center;
}

footer .copyright { font-size: 11px; }

/* =========== COMUM ======== */


.main_titles h1.destaque {
    font-size: 18px;
}

.roleta .setas { display: none; }

.produto-list .head { background: none; }


.produto-list .head .titulo,
.produto-list .head .subtitulo {
    display: block;
}

.produto-list .head .titulo { padding-right: 0; font-size: 15px; }
.produto-list .subtitulo { font-size: 11px; font-weight: 300; }

.produto-list .img,
.produto-list .info {
    width: auto;
    float: none;
    font-size: 11px;
}

.produto-list .info .preco { margin-bottom: 10px; }

.produto-list .info .preco .val span { font-size: 15px; }

.produto-list .info .fretes div.ter,
.produto-list .info .fretes div.aer {
    height: 25px;
    line-height: 25px;
    padding-left: 35px;
    background-size: 28px auto;
}

.produto-list .info .fretes span.ter,
.produto-list .info .fretes span.aer {
    width: 25px;
    height: 25px;
    margin-top: 1px;
}

.produto-list .info a,
.produto-list .info .opcoes .personalizar a,
.produto-list .info .opcoes .mesas a {
    height: 33px;
    line-height: 33px;
    font-size: 12px;
}

.produto-list .info a {
    margin: 5px 0 0;
}



.newsbox {
    position: absolute;
    width: 90%;
    height: 300px;
    left: 5%;
    margin-left: 0;
    background:#fff;
    text-align: center;
}

.newsbox .bt_fechar {
    top: -15px;
    right: -12px;
}

.newsbox .main_content {
    position: absolute;
    width: 90%;
    top: 50px;
    left: 5%;
}

.newsbox .main_content .titulo { font-size: 4.5vw; }

.newsbox .main_content form {
    width: 260px;
    margin: 30px auto 0;
}

.newsbox .main_content form input {
    width: 50%;
}

.newsbox .main_content form .button {
    padding: 0 10px;
    font-size: 12px;
}

.newsbox .main_content .opcoes {
    font-size: 12px;
    text-align: center;
}


/* =========== PÁGINA HOME ======== */

.barra_features { padding-bottom: 15px; }

.featured {
    min-height: 50px;
    padding-top: 15px;
}

.featured .icon {
    position: relative;
    width: 20px;
    height: 30px;
    line-height: 30px;
    padding: 0 4px;
}

.featured .info { 
    width: 100%;
    width: -webkit-calc(100% - 50px);
    width: -moz-calc(100% - 50px);
    width: calc(100% - 50px);
    margin-left: -10px;
    font-size: 10px;
}
.featured .info span { margin-bottom: 5px; font-size: 11px; }
.featured .info p { display: none; }


#home .produto-list .head {
    margin-bottom: 0;
    background-color: transparent;
}
#home .produto-list .head .titulo,
#home .produto-list .head .subtitulo { display: block; margin: 0; }

#home .home_destaques .produto-list .head .titulo { font-size: 15px; }

#home .home_destaques .produto-list .info .preco,
#home .home_destaques .produto-list .info .fretes {
    width: auto;
    float: none;
}

#home .produto-list .img { width: 104%; }
#home .produto-list .info { width: auto; }




.home_titles { 
    margin: 25px 0 0;
    font-size: 14px;
}

.banner_fichas .banner1 {
    width: 100%;
    float: none;
}

.banner_fichas .banner2 { display: none; }

.background_poker {
    padding: 20px 0 0px;
    margin: 40px 0;
    background: url('../img/background_poker.jpg') no-repeat center center;
    background-attachment: fixed;
}


.background_poker .produto-list { margin: 0 3% 20px; }
.background_poker .produto-list .preco .val span {
    display: block;
}


.pronta_entrega .roleta {
    min-height: 300px;
}

.pronta_entrega .roleta .items .item { width: auto; }

.pronta_entrega .produto-list .head {
    margin: 0;
    padding: 0;
}

.pronta_entrega .produto-list .preco .val span { display: block; }
.pronta_entrega .produto-list .titulo { font-size: 13px; }
.pronta_entrega .produto-list .preco { font-size: 11.1px; }
.pronta_entrega .produto-list .preco .val { font-size: 10px; }



/* =========== PÁGINA PRODUTO ======== */

.mobile_valores .valor_unitario,
.mobile_comprar { display: block; }

.mobile_valores .descontos {
    width: 270px;
}

.mobile_quantidade {
    display: block;
    padding: 10px;
    margin: 10px 0;
    background: #ebebeb;
    border: 1px solid #ccc;
}

.mobile_quantidade .label {
    width: 78%;
    line-height: 35px;
    font-size: 5vw;
    float: left;
}

.mobile_quantidade .input {
    width: 20%;
    float: left;
    margin-right: 2%;
}

.mobile_quantidade .input input {
    width: 100%;
    height: 33px;
    line-height: 33px;
    padding: 0;
    border: 1px solid #ccc;
    font-size: 12px;
    text-align: center;
    color: #000;
}

.mobile_comprar button {
    width: 100%;
    height: 55px;
    background: <?php echo $color_main_green; ?>;
    margin: 0;
    padding: 0;
    font-size: 20px;
    font-weight: bold;
    text-align: center;
    color: #fff;
}

.produto .main_info h1 {
    font-size: 24px;
}

.produto .main_info .fotos .roleta button {
    width: 30px;
    height: 120px;
    line-height: 120px;
}

.produto .main_info .fotos .roleta .items .item { width: 120px; }


.personalizacoes .conjuntos .conjunto { height: auto; }

.personalizacoes .conjuntos .conjunto .titulo,
.personalizacoes .conjuntos .conjunto .imgs,
.personalizacoes .conjuntos .conjunto .descricao,
.personalizacoes .conjuntos .conjunto .selection {
    position: relative;
    width: 100%;
    float: none;
}

.personalizacoes .conjuntos .conjunto .imgs {
    height: 57vw;
    margin: 10px 0;
    text-align: center;
}

.personalizacoes .conjuntos .conjunto .imgs img { width: 100%; }

.resumo_produto .main_titles span.toggle {
    display: inline-block;
    padding: 0 10px;
    cursor: pointer;
}

.resumo_produto.personalizado .main_titles span.toggle {
    display: none;
}

.resumo_produto .configuracoes {
    display: none;
    padding: 10px;
}

.resumo_produto.personalizado .configuracoes {
    display: block;
}

.resumo_produto .configuracoes span {
    display: block;
    width: 100%;
    height: auto;
    line-height: inherit;
    float: none;
    margin: 10px 0;
}
.resumo_produto .configuracoes span strong { display: block; }

.barra_preco .quantidade {
    width: 90px;
    margin-right: 10px;
    padding: 5px 0;
}

.barra_preco .quantidade .input {
    display: none;
    width: 40px;
    margin: 0 10px;
}

.barra_preco .quantidade .valor_unitario {
    width: 150px;
    font-size: 10px;
}

.barra_preco .quantidade .valor_unitario span {
    padding-right: 5px;
    font-size: 12px;
}

.barra_preco .quantidade .input input {
    font-size: 11px;
}

/*.barra_preco .total { display: none; }*/

.barra_preco .total {
    width: 120px;
}

.barra_preco .total .label {
    font-size: 10px;
}

.barra_preco .total .parcelamento span { display: block; font-size: 13px; }

.barra_preco .total .avista {
    display: none;
}

.barra_preco .comprar { width: 22%; }

.barra_preco .comprar button {
    height: 45px;
    font-size: 10px;
}



/* =========== PÁGINA CARRINHO ======== */


.main_carrinho .itens .item {
    min-height: 150px;
}

.main_carrinho .itens .item .icone {
    width: 80px;
    padding-right: 10px;
}

.main_carrinho .itens .item .nome {
    width: 100%;
    width: -webkit-calc(100% - 110px);
    width: -moz-calc(100% - 110px);
    width: calc(100% - 110px);
    line-height: 20px;
}
.main_carrinho .itens .item .quantidade {
    position: absolute;
    width: 80px;
    top: 90px;
}

.main_carrinho .itens .item .valor_unitario {
    position: absolute;
    padding: 0;
    top: 100px;
    left: 90px;
    font-size: 12px;
}

.main_carrinho .itens .item .subtotal {
    position: absolute;
    top: 135px;
    right: inherit;
    padding: 0;
}

.main_carrinho .itens .item .actions {
    position: absolute;
    width: 180px;
    right: 0;
    top: 130px;
}

.main_carrinho .itens .item .configuracoes {
    margin-top: 90px;
}

.checkout_carrinho .totals,
.checkout_forms.form_frete {
    width: 100%;
    float: none;
}

.checkout_carrinho .totals .total_box .total_geral { font-size: 18px; }

.checkout_carrinho .totals .options {
    margin: 20px 0 20px 0;
}


/* =========== PÁGINA CHECKOUT ======== */


.checkout_panel .form_frete .option-frete {
    width: 100%;
    float: none;
}



/* =========== PÁGINA LOGIN ======== */


.col-login,
.col-cadastro {
    position: relative;
    width: 90%;
    float: none;
    margin: 0 5% 50px;
}


/* =========== PÁGINA FALE CONOSCO ======== */


.contact_infos .info.contatos span {
    width: auto;
    float: none;
}



/* =========== PÁGINA HISTÓRICO ======== */

.tabela_pedidos .thead,
.tabela_pedidos .tbody,
.tabela_pedidos .tbody .pedido,
.tabela_pedidos .tbody .trow {
    display: block;
    width: 100%;
}

.tabela_pedidos .tbody .pedido<?php echo $clear_end; ?>
.tabela_pedidos .tbody .trow<?php echo $clear_end; ?>

.tabela_pedidos .thead { display: none; }

.tabela_pedidos .tcell .mobile_caption { display: block; }

.tabela_pedidos .tcell,
.tabela_pedidos .tcell.numero,
.tabela_pedidos .tcell.data,
.tabela_pedidos .tcell.entrega,
.tabela_pedidos .tcell.status,
.tabela_pedidos .tcell.valor,
.tabela_pedidos .tcell.detalhes {
    display: block;
    width: 46%;
    height: 60px;
    float: left;
    margin: 10px 2%;
}

.tabela_pedidos .tcell.detalhes {
    height: 45px;
    text-align: left;
    padding: 15px 0 0 0;
}

.tabela_pedidos .box_detalhes .itens .item {
    padding: 10px;
    margin: 0 5px;
}

.tabela_pedidos .box_detalhes .itens .item .descricao span {
    width: 100%;
    padding: 4px 0;
}

.tabela_pedidos .box_detalhes .itens .item .descricao span strong {
    display: block;
}

.tabela_pedidos .box_detalhes .resumo {
    padding: 10px 20px;
    margin: 0 5px;
}

.tabela_pedidos .box_detalhes .resumo .box {
    width: 100%;
    margin: 20px 0 0;
    float: none;
}

.tabela_pedidos .box_detalhes .resumo .box .subtotals {
    width: 190px;
}

.tabela_pedidos .box_detalhes .resumo .box .subtotals span {
    width: 50%;
}



/* =========== STYLES ======== */


.show_mobile { display: block; }



/* =========== SHARED COMPONENTS =========== */


/* GRIDS */

    .comp-grid-main {
        width: auto;
        padding: 0 15px;
    }
    
     

    .comp-grid-half.two-one { width: auto; }
    .comp-grid-half.two-one:nth-child(odd) { float: none; }
    .comp-grid-half.two-one:nth-child(even) { float: none; }

    .comp-grid-third.two-one { width: auto; }
    .comp-grid-third.two-one:nth-child(odd) { float: none; }
    .comp-grid-third.two-one:nth-child(even) { float: none; }

    .comp-grid-fourth {
        width: 100%;
        float: none;
    }

    .comp-grid-fourth.two-two {
        width: 50%;
        float: left;
    }


/* BANNERS */

    .comp-banners { height: 135px; }

    .comp-banners .numeros { display: none; }

    .comp-banners .display .banner .box {
        top: 10px;
        font-size: 8px;
    }

    .comp-banners .display .banner .box .titulo { font-size: 16px; }

    .comp-banners .display .banner .box .subtitulo { display: none; }

    .comp-banners .display .banner .box .preco { margin-top: 10px; }

    .comp-banners .display .banner .box .preco span {
        font-size: 16px;
        letter-spacing: 0;
    }

    .comp-banners .display .banner .box .btn {
        height: 18px;
        line-height: 18px;
        padding: 0 20px;
        margin-top: 10px;
        font-size: 8px;
    }



/* FORMS */
    
    .comp-forms-grid-half-tablet { width: 100%; }
    .comp-forms-grid-third-tablet {
        width: 100%;
        margin-right: 0;
    }


@media (max-width: 500px) {


    /* =========== PÁGINA PRODUTO ======== */

        .produto .main_info .info .features .featured {
            width: 100%;
            padding: 0;
        }

        .produto .main_info .fotos { margin-top: 30px; }

        .produto .main_info .fotos .roleta button {
            width: 25px;
            height: 60px;
            line-height: 60px;
        }

        .produto .main_info .fotos .roleta .items .item { width: 60px; }


    /* =========== PÁGINA CHECKOUT ======== */


    button.checkout_login {
        width: 33%;
    }

    button.checkout_resume {
        width: 65%;
    }
    
    .editable_content img.imgRight,
    .editable_content img.imgLeft {
        float: none;
        margin: 20px 0;
    }
    

}

@media (max-width: 375px) {

    /* =========== PÁGINA PRODUTO ======== */
/*
    .barra_preco .comprar { width: 24%; }
    .barra_preco .comprar button { font-size: 12px; }
*/
}



/* WSOP PAGE */ 

#wsop .comp-grid-main {
    width: auto;
    margin: 0 auto;
}

.topo_wsop .video_box .video {
    height: 48vw;
}

.mesa_wsop .titulo { font-size: 14px; }


.bonus_wsop {
    padding-top: 20px;
}


.bonus_wsop {
    text-align: center;
}

.bonus_wsop .bonus_box .bonus_revista,
.bonus_wsop .bonus_box .bonus_bauer {
    float: none;
    margin: 20px auto;
}



.wsop_operacao .preco .texto {
    font-size: 14px;
}

.wsop_operacao .preco .line1 {
    font-size: 30px;
}

.wsop_operacao .preco .line2 {
    font-size: 30px;
}

.wsop_operacao .preco .line2 span {
    font-size: 60px;
}

.wsop_operacao .preco .line3 {
    font-size: 25px;
}

.wsop_operacao .preco .line4 {
    font-size: 15px;
}

.wsop_operacao .preco .line4 strong { font-size: 30px; }