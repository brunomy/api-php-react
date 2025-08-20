<?php
    include 'include.css.php';
?>


body {
    font-size:14px;
}

/* =========== HEADER CONTENT ======== */

header { }


header .barra_topo_casa .titulo {
    margin-left: 8%;
    font-size: 25px;
}

header .barra_topo_casa .descricao {
    font-size: 14px;
}



header .barra_topo1 .wordseries {
    width: 34%;
    font-size: 10px;
}

header .barra_topo1 .wordseries .wsop,
header .barra_topo1 .wordseries .bsop {
    height: 15px;
    margin: -1px 0 0 60px;
}

header .barra_topo1 .wordseries span { display: none; }

header .barra_topo2 .barra_atendimentos a.atendimento_televendas { line-height: 15px; }

header .barra_topo2 .barra_atendimentos a.atendimento_televendas:after {
    content: '';
    position: absolute;
    display: block;
    width: 10px;
    height: 15px;
    right: 5px;
    top: 8px;
    background: url('../img/lang_down.png') no-repeat right 6px;
    background-size: 10px auto;
}

header .barra_topo2 .barra_atendimentos a.atendimento_televendas span { display: none; }

header .barra_topo2 .carrinho {
    position: absolute;
    float: none;
    margin-top: 0;
    top: 189px;
    right: 20px;
}

header.fixa .barra_topo2 .carrinho {
    position: fixed;
    width: 290px;
    left: inherit;
    margin: 3px 0 0 0;
    right: 20px;
}

header .barra_topo2 .carrinho .resumo {
    background-position: right 14px top -20px;
    border: 1px solid #fff;
}

header.fixa .barra_topo2 .carrinho .resumo {
    height: 35px;
    line-height: 35px;
    background-position: right 14px top -20px;
}


header .barra_topo3 .home { display: none; }

header .barra_topo3 {
    height: 55px;
    line-height: 55px;
}

header .barra_topo3 .mobilenavbutton {
    display: inline-block;
    width: 90px;
    height: 30px;
    line-height: 30px;
    padding-left: 30px;
    background: url('../img/bt_mobile_nav.png') no-repeat left center;
    background-size: 26px auto;
    font-size: 14px;
    font-weight: bold;
    color: #fff;
}

header .barra_topo3 nav {
    position: absolute;
    display: none;
    width: 100%;
    height: auto;
    margin-left: -76px;
    background: #83130b;
}

header .barra_topo3 nav > li {
    width: auto;
    height: auto;
    float: none;
    line-height: 1;
    padding: 15px 0;
    border: none;
    border-bottom: 1px solid #9d2820;
    text-align: left;
    text-indent: 20px;
}

header.fixa .barra_topo3 nav {
    padding-left: 55px;
}

header.fixa .barra_topo3 nav > li,
header.fixa .barra_topo3 nav > li ul,
header.fixa .barra_topo3 nav > li ul > li,
header.fixa .barra_topo3 nav > li ul > li > ul,
header.fixa .barra_topo3 nav > li ul > li > ul > li { width: auto; }

header .barra_topo3 nav > li span,
header .barra_topo3 nav > li a {
    height: auto;
    font-size: 11px;
}

header .barra_topo3 nav > li > span:after { display: none; }

header .barra_topo3 nav > li span:before {
    content: '+';
    position: absolute;
    display: block;
    width: 10px;
    height: 10px;
    top: 12px;
    right: 35px;
    font-size: 18px;
    font-weight: bold;
    color: #fff;
    -webkit-transition: .4s;
    -o-transition: .4s;
    transition: .4s;
}

header .barra_topo3 nav > li span.opened:before {
    content: 'x';
    font-size: 12px;
}

header .barra_topo3 nav > li ul {
    position: relative;
    display: none;
    width: 100%;
    top: 0;
    margin: 15px 0 -15px;
    background: #83130b;
}

header .barra_topo3 nav > li ul li {
    height: auto;
    background: #9d2820;
    border: none;
    border-top: 1px solid #83130b;
}

header .barra_topo3 nav > li ul li span,
header .barra_topo3 nav > li ul li a {
    color: #fff;
}


header .barra_topo3 nav > li ul li span:before {
    content: '+';
    position: absolute;
    display: block;
    width: 10px;
    height: 10px;
    top: 0;
    right: 35px;
    border: none;
    font-size: 18px;
    font-weight: bold;
    color: #fff;
    -webkit-transition: .4s;
    -o-transition: .4s;
    transition: .4s;
}

header .barra_topo3 nav > li ul li span.opened:before {
    content: 'x';
    font-size: 12px;
}

header .barra_topo3 nav > li ul li ul {
    position: relative;
    width: auto;
    top: 0;
    left: 0;
    margin: 0;
}

header .barra_topo3 nav > li ul li ul li a,
header .barra_topo3 nav > li ul li ul li span {
    background: #aa251c;
    text-indent: 30px;
}

header .barra_topo3 nav > li ul li ul li a:before,
header .barra_topo3 nav > li ul li ul li span:before {
    display: none;
}


/* MENU ABERTO */
header .barra_topo3 nav > li.menu-aberto > ul,
header .barra_topo3 nav > li.menu-aberto > ul > li {
    background: #f9f9f9;
    border: 0;
}

header .barra_topo3 nav > li.menu-aberto > ul {
    padding-bottom: 20px;
}

header .barra_topo3 nav > li.menu-aberto > ul > li {
    width: 30%;
    padding: 0 1.5%;
    background: #f9f9f9;
    text-indent: 0;
}

header .barra_topo3 nav > li.menu-aberto > ul > li .img {
    height: 16.3vw;
}

header .barra_topo3 nav > li.menu-aberto > ul > li .img img { width: 100%; }

header .barra_topo3 nav > li ul li ul li a,
header .barra_topo3 nav > li ul li ul li span {
    text-indent: 5px;
}

header .barra_topo3 nav > li.menu-aberto > ul > li > ul,
header .barra_topo3 nav > li.menu-aberto > ul > li > ul > li > a,
header .barra_topo3 nav > li.menu-aberto > ul > li > ul > li > span {
    background: #fff;
}


header #televendas {
    width: 480px;
    top: 120px;
    left: 50%;
    margin-left: -305px;
    padding: 25px 75px 50px 55px;
}

header #televendas .close { right: 10px; }

header #televendas .box.america { margin-top: 40px; }

header #carrinho {
    top: 210px;
    left: inherit;
    right: 20px;
    margin-left: 0;
}

header.fixa #carrinho { top: 70px; }

header #login { margin-left: 40px; }

/* =========== FOOTER CONTENT ======== */


footer .selos img { width: auto; height: 40px; }

footer .newsletter {
    height: 70px;
    line-height: 70px;
}

footer .newsletter .label {
    width: 40%;
    padding-left: 45px;
    background-size: 35px auto;
    font-size: 13px;
    letter-spacing: 2px;
}

footer .newsletter .form_newsletter {
    width: 50%;
    height: 40px;
    line-height: 40px;
}

footer .newsletter .form_newsletter input {
    width: 73%;
    height: 40px;
    line-height: 40px;
    padding-left: 2%;
    font-size: 12px;
}

footer .newsletter .form_newsletter button {
    width: 25%;
    height: 40px;
    line-height: 40px;
    font-size: 11px;
}


footer .rodape .televendas {
    width: 100%;
    float: none;
}

footer .rodape .televendas .box.brasil {
    width: 100%;
    float: none;
}

footer .rodape .televendas .box.brasil span { width: 49%; }

footer .rodape .televendas .box.brasil span.whatsapp {
    width: 49%;
    width: -webkit-calc(49% - 30px);
    width: -moz-calc(49% - 30px);
    width: calc(49% - 30px);
}

footer .rodape .televendas .box.brasil span.skype {
    width: 49%;
    width: -webkit-calc(49% - 40px);
    width: -moz-calc(49% - 40px);
    width: calc(49% - 40px);
}

footer .rodape .televendas .box.america {
    width: 280px;
    margin-top: 40px;
}

footer .rodape .televendas .horario {
    width: 280px;
    padding: 30px 0;
    font-size: 14px;
}

footer .rodape .redes_sociais {
    position: absolute;
    width: 280px;
    float: none;
    top: 327px;
    left: 49%;
}


footer .nav nav {
   font-size: 12px;
    float: none;
    margin: 0 auto;
}

footer .nav .hibrida {
    position: absolute;
    bottom: 5px;
    left: 50%;
    margin-left: -21px;
}

footer .copyright {
    padding: 25px 0 50px;
}

/* =========== COMUM ======== */

.produto-list<?php echo $clear_end; ?>


.produto-list .head .titulo,
.produto-list .head .subtitulo {
    display: block;
}

.produto-list .head .titulo { padding-right: 0; }

.whatsapp_bar2 {
    width: 155px;
    height: 45px;
    border-radius: 7px;
    right: 10px;
}

.whatsapp_bar2 .icon {
    width: 45px;
    height: 45px;
    margin-right: 7px;
    background-size: 25px auto;
}

.whatsapp_bar2 .desktop {
    display: none;
}

.whatsapp_bar2 .mobile {
    display: block;
    font-size: 14px;
}

.whatsapp_bar2 .mobile strong {
    display: block;
    font-size: 18px;
}

.whatsapp_bar2 .mobile strong span {
    font-weight: normal;
    font-size: 11px;
}



/* =========== PÁGINA HOME ======== */


#home .produto-list .head {
    margin-bottom: 15px;
    background: #f2f2f2;
}

#home .produto-list .head .titulo,
#home .produto-list .head .subtitulo {
    display: inline-block;
}

#home .produto-list .head .titulo { padding-right: 20px; }

#home .produto-list .img {
    width: 48%;
    float: left;
}

#home .produto-list .info {
    width: 48%;
    float: right;
}

#home .home_destaques { margin-bottom: 0 !important; }
#home .home_destaques .produto-list .head,
#home .background_poker .produto-list .head,
#home .pronta_entrega .produto-list .head { background: none; }


#home .home_destaques .produto-list .head .titulo,
#home .home_destaques .produto-list .head .subtitulo,
#home .background_poker .produto-list .head .titulo,
#home .background_poker .produto-list .head .subtitulo,
#home .pronta_entrega .produto-list .head .titulo,
#home .pronta_entrega .produto-list .head .subtitulo {
    display: block;
}

#home .home_destaques .produto-list .head .titulo,
#home .background_poker .produto-list .head .titulo,
#home .pronta_entrega .produto-list .head .titulo { padding-right: 0; }

#home .home_destaques .produto-list .img,
#home .home_destaques .produto-list .info,
#home .background_poker .produto-list .img,
#home .background_poker .produto-list .info,
#home .pronta_entrega .produto-list .img,
#home .pronta_entrega .produto-list .info {
    width: auto;
    float: none;
}

#home .home_destaques .produto-list .img img {
    width: 100%;
    height: auto;
}



/* =========== PÁGINA PRODUTO ======== */


.produto .main_info h1 {
    width: 100%;
    float: none;
    font-size: 30px;
}

.produto .main_info {
    height: auto;
}

.produto .main_info .img {
    width: 100%;
    height: auto;
    float: none;
}

.produto .main_info .info {
    width: 100%;
    height: auto;
    float: none;
    margin-top: 30px;
}

.produto .main_info .descricao {
    width: auto;
    height: auto;
    overflow: inherit;
    float: none;
    margin-bottom: 30px;
    font-size: 14px;
}

.produto .main_info .features .featured .info {
    margin-top: 0;
}

.produto .main_info .fotos {
    position: relative;
    width: 100%;
    height: 125px;
    bottom: 0;
}

.produto .main_info .fotos .roleta button {
    width: 30px;
    height: 120px;
    line-height: 120px;
}

.produto .main_info .fotos .roleta .items .item { width: 120px; }


.personalizacoes .conjuntos .conjunto { width: 100%; }

.personalizacoes .conjuntos .conjunto:nth-child(odd),
.personalizacoes .conjuntos .conjunto:nth-child(even) { float: none; }

.personalizacoes .conjuntos .conjunto .titulo,
.personalizacoes .conjuntos .conjunto .descricao,
.personalizacoes .conjuntos .conjunto .selection {
    width: 100%;
    width: -webkit-calc(100% - 230px);
    width: -moz-calc(100% - 230px);
    width: calc(100% - 230px);
}


.mobile_valores {
    display: block;
    padding: 10px;
    margin: 10px 0;
    border: 1px solid #ccc;
}
.mobile_valores<?php echo $clear_end ?>

.mobile_valores .valor_unitario { display: none; }

.barra_preco .quantidade {
    width: 270px;
    margin-right: 15px;
}


.barra_preco .quantidade .label { display: none; }
.barra_preco .descontos { display: none; }

.barra_preco .comprar { width: 18%; }
.barra_preco .comprar button {
    width: 100%;
    font-size: 15px;
}


.produtos_relacionados .produto_box,
.produtos_relacionados .produto_box:last-child,
.produtos_relacionados .produto_box .img,
.produtos_relacionados .produto_box .dados {
    width: 100%;
    float: none;
    margin-right: 0;
}

.produtos_relacionados .produto_box {
    margin-bottom: 30px;
}


/* =========== PÁGINA CARRINHO ======== */

.main_carrinho .itens .item .table {
    position: relative;
    display: block;
}

.main_carrinho .itens .item .table<?php echo $clear_end ?>

.main_carrinho .itens .item .icone,
.main_carrinho .itens .item .nome,
.main_carrinho .itens .item .quantidade,
.main_carrinho .itens .item .valor_unitario,
.main_carrinho .itens .item .subtotal,
.main_carrinho .itens .item .actions {
    display: block;
    float: left;
}

.main_carrinho .itens .item .icone {
    width: 15%;
    padding-right:0px;
}

.main_carrinho .itens .item .nome {
    width: 75%;
    height: 40px;
    line-height: 20px;
}

.main_carrinho .itens .item .valor_unitario { width: auto; padding: 10px 20px 0 0; }
.main_carrinho .itens .item .subtotal { width: 100px; padding-top: 10px; }
.main_carrinho .itens .item .actions {
    position: absolute;
    width: 180px;
    right: 20px;
}

.main_carrinho .itens .item .configuracoes .label,
.main_carrinho .itens .item .configuracoes .dados {
    width: auto;
    float: none;
}

.checkout_carrinho .totals {
    width: 100%;
    width: -webkit-calc(100% - 350px);
    width: -moz-calc(100% - 350px);
    width: calc(100% - 350px);
    float: right;
}

.checkout_carrinho .totals .col1,
.checkout_carrinho .totals .col2 {
    width: 100%;
    float: none;
}

.checkout_carrinho .totals .col2 { margin-top: 50px; }

.checkout_forms.form_frete {
    width: 290px;
    float: left;
    padding-right: 0;
}




/* =========== PÁGINA CHECKOUT ======== */

.checkout_panel .etapa {
    width: 100%;
    float: none;
    margin-bottom: 50px;
}

.checkout_panel .form_frete<?php echo $clear_end; ?>

.checkout_panel .form_frete .option-frete {
    width: 48%;
    float: left;
}


/* =========== PÁGINA LOGIN ======== */


.col-login,
.col-cadastro {
    width: 40%;
    margin: 0 5%;
}

.col-login .loginbox .clickformsubmit {
    width: 100%;
}

.col-cadastro a.link-cadastro {
    display: block;
    width: auto;
}


/* =========== PÁGINA FALE CONOSCO ======== */


.contact_infos,
.form_contato {
    width: 100%;
    float: none;
}

.contact_infos .info<?php echo $clear_end ?>

.contact_infos .info.contatos span {
    display: block;
    width: 45%;
    float: left;
}

.contact_infos .info.contatos span.whatsapp,
.contact_infos .info.contatos span.skype {
    float: none;
}



/* =========== STYLES ======== */

.show_tablet { display: block; }

.editable_content iframe.video-youtube {
    width: 100%;
    height: 53.2vw;
}

.editable_content .bloco_menor {
    width: 100%;
}

/* =========== SHARED COMPONENTS =========== */



/* GRIDS */

    .comp-grid-main,
    .comp-grid-main-in {
        width: auto;
        padding: 0 20px;
    }


    .comp-grid-blog {
        width: 100%;
        float: none;
    }


    .comp-grid-blog-side {
        width: 100%;
        float: none;
        clear: both;
        padding-left:0;
    }

    .blog-list-side {
        width: 100%;
        margin-right: 0;
        float: none;
    }

    .comp-grid-aside-left,
    .comp-grid-aside-main {
        width: 100%;
        float: none;
        margin-bottom: 40px;
    }

    .comp-grid-half { width: 100%; }
    .comp-grid-half:nth-child(odd) { float: none; }
    .comp-grid-half:nth-child(even) { float: none; }

    .comp-grid-half.two-one { width: 48%; }
    .comp-grid-half.two-one:nth-child(odd) { float: left; }
    .comp-grid-half.two-one:nth-child(even) { float: right; }

    .comp-grid-third {
        width: auto;
        float: none;
        margin-right: 0;
    }

    .comp-grid-third:nth-child(3n) {
        float: none;
    }

    .comp-grid-third.two-one { width: 48%; }
    .comp-grid-third.two-one:nth-child(odd) { float: left; }
    .comp-grid-third.two-one:nth-child(even) { float: right; }

    .comp-grid-fourth {
        width: 50%;
        float: left;
    }



/* BANNERS */

    .comp-banners {
        height: 250px;
    }

    .comp-banners .display .banner .box {
        top: 20px;
        font-size: 12px;
    }

    .comp-banners .display .banner .box .titulo { font-size: 24px; }

    .comp-banners .display .banner .box .preco { margin-top: 20px; }

    .comp-banners .display .banner .box .preco span {
        font-size: 24px;
        letter-spacing: 1px;
    }

    .comp-banners .display .banner .box .btn {
        height: 26px;
        line-height: 26px;
        padding: 0 20px;
        margin-top: 10px;
        font-size: 12px;
    }


/* FORMS */

    .comp-forms-grid-half { width: 100%; }
    .comp-forms-grid-third {
        width: 100%;
        margin-right: 0;
    }





/* WSOP PAGE */

#wsop .comp-grid-main {
    width: 590px;
    margin: 0 auto;
}

.topo_wsop {
    height: auto;
    padding-bottom: 30px;
    /*background: #444349;*/
    background-size: auto 100%;
}

.topo_wsop .video_box {
    width: 100%;
    float: none;
    margin-bottom: 20px;
}

.topo_wsop .video_box .video {
    height: 330px;
}

.topo_wsop .caixa_form {
    width: 100%;
    float: none;
    padding-top: 43vw;
    margin-top: 0;
    background-size: 100% auto;
}

.mesa_wsop .titulo {
    height: auto;
    padding: 10px 0;
    line-height: 2;
    font-size: 18px;
}

.mesa_wsop .mesa_foto,
.mesa_wsop .mesa_texto {
    width: 100%;
    float: none;
    font-size: 14px;
}



.bonus_wsop .bonus_box,
.bonus_wsop .certificado_wsop {
    width: 100%;
    float: none;
    margin: 20px auto;
}

.bonus_wsop .bonus_box .bonus_revista {
    width: 275px;
    float: left;
    margin-right: 50px;
}

.bonus_wsop .bonus_box .bonus_bauer {
    width: 220px;
    float: left;
}


.wsop_operacao .preco {
    width: auto;
    float: none;
    margin: 50px 0;
    text-align: center;
}

.wsop_operacao .preco .line1 { text-indent: -100px; }

.wsop_operacao .caixa_form {
    width: auto;
    float: none;
    margin: 10px 0 30px;
}



.wsop_operacao .caixa_form .form_participe2 .miolo .col1,
.wsop_operacao .caixa_form .form_participe2 .miolo .col2 {
    width: auto;
    float: none;
}
