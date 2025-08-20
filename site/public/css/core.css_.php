<?php 
    include 'include.css.php';
?>

@charset "utf-8";
/* CSS Document */

* {
	margin:0;
	padding:0;
	list-style:none;
	text-decoration:none;
	border:none;
	line-height:inherit;
	outline:none;
}

/*
* { background-color: rgba(255,0,0,.2); }
* * { background-color: rgba(0,255,0,.2); }
* * * { background-color: rgba(0,0,255,.2); }
* * * * { background-color: rgba(255,0,255,.2); }
* * * * * { background-color: rgba(0,255,255,.2); }
* * * * * * { background-color: rgba(255,255,0,.2); }
*/



@font-face {
    font-family:opensans;
    src: url('../webfonts/opensans-regular-webfont.eot');
    src: url('../webfonts/opensans-regular-webfont.eot?#iefix') format('embedded-opentype'),
         url('../webfonts/opensans-regular-webfont.woff') format('woff'),
         url('../webfonts/opensans-regular-webfont.ttf') format('truetype'),
         url('../webfonts/opensans-regular-webfont.svg#opensans') format('svg');
    font-weight: normal;
    font-style: normal;
}

@font-face {
    font-family:opensans;
    src: url('../webfonts/opensans-italic-webfont.eot');
    src: url('../webfonts/opensans-italic-webfont.eot?#iefix') format('embedded-opentype'),
         url('../webfonts/opensans-italic-webfont.woff') format('woff'),
         url('../webfonts/opensans-italic-webfont.ttf') format('truetype'),
         url('../webfonts/opensans-italic-webfont.svg#opensans') format('svg');
    font-weight: normal;
    font-style: italic;
}

@font-face {
    font-family:opensans;
    src: url('../webfonts/opensans-light-webfont.eot');
    src: url('../webfonts/opensans-light-webfont.eot?#iefix') format('embedded-opentype'),
         url('../webfonts/opensans-light-webfont.woff') format('woff'),
         url('../webfonts/opensans-light-webfont.ttf') format('truetype'),
         url('../webfonts/opensans-light-webfont.svg#opensans') format('svg');
    font-weight: 300;
    font-style: normal;
}

@font-face {
    font-family:opensans;
    src: url('../webfonts/opensans-lightitalic-webfont.eot');
    src: url('../webfonts/opensans-lightitalic-webfont.eot?#iefix') format('embedded-opentype'),
         url('../webfonts/opensans-lightitalic-webfont.woff') format('woff'),
         url('../webfonts/opensans-lightitalic-webfont.ttf') format('truetype'),
         url('../webfonts/opensans-lightitalic-webfont.svg#opensans') format('svg');
    font-weight: 300;
    font-style: italic;
}

@font-face {
    font-family:opensans;
    src: url('../webfonts/opensans-semibold-webfont.eot');
    src: url('../webfonts/opensans-semibold-webfont.eot?#iefix') format('embedded-opentype'),
         url('../webfonts/opensans-semibold-webfont.woff') format('woff'),
         url('../webfonts/opensans-semibold-webfont.ttf') format('truetype'),
         url('../webfonts/opensans-semibold-webfont.svg#opensans') format('svg');
    font-weight: 600;
    font-style: normal;
}

@font-face {
    font-family:opensans;
    src: url('../webfonts/opensans-semibolditalic-webfont.eot');
    src: url('../webfonts/opensans-semibolditalic-webfont.eot?#iefix') format('embedded-opentype'),
         url('../webfonts/opensans-semibolditalic-webfont.woff') format('woff'),
         url('../webfonts/opensans-semibolditalic-webfont.ttf') format('truetype'),
         url('../webfonts/opensans-semibolditalic-webfont.svg#opensans') format('svg');
    font-weight: 600;
    font-style: italic;
}

@font-face {
    font-family:opensans;
    src: url('../webfonts/opensans-bold-webfont.eot');
    src: url('../webfonts/opensans-bold-webfont.eot?#iefix') format('embedded-opentype'),
         url('../webfonts/opensans-bold-webfont.woff') format('woff'),
         url('../webfonts/opensans-bold-webfont.ttf') format('truetype'),
         url('../webfonts/opensans-bold-webfont.svg#opensans') format('svg');
    font-weight: bold;
    font-style: normal;
}

@font-face {
    font-family:opensans;
    src: url('../webfonts/opensans-bolditalic-webfont.eot');
    src: url('../webfonts/opensans-bolditalic-webfont.eot?#iefix') format('embedded-opentype'),
         url('../webfonts/opensans-bolditalic-webfont.woff') format('woff'),
         url('../webfonts/opensans-bolditalic-webfont.ttf') format('truetype'),
         url('../webfonts/opensans-bolditalic-webfont.svg#opensans') format('svg');
    font-weight: bold;
    font-style: italic;
}

@font-face {
    font-family:opensans;
    src: url('../webfonts/opensans-extrabold-webfont.eot');
    src: url('../webfonts/opensans-extrabold-webfont.eot?#iefix') format('embedded-opentype'),
         url('../webfonts/opensans-extrabold-webfont.woff') format('woff'),
         url('../webfonts/opensans-extrabold-webfont.ttf') format('truetype'),
         url('../webfonts/opensans-extrabold-webfont.svg#opensans') format('svg');
    font-weight: 800;
    font-style: normal;
}

@font-face {
    font-family:opensans;
    src: url('../webfonts/opensans-extrabolditalic-webfont.eot');
    src: url('../webfonts/opensans-extrabolditalic-webfont.eot?#iefix') format('embedded-opentype'),
         url('../webfonts/opensans-extrabolditalic-webfont.woff') format('woff'),
         url('../webfonts/opensans-extrabolditalic-webfont.ttf') format('truetype'),
         url('../webfonts/opensans-extrabolditalic-webfont.svg#opensans') format('svg');
    font-weight: 800;
    font-style: italic;
}


/* font-family: 'PT Sans Caption', sans-serif; */



/* ============= HEADERS ============= */

body {
	font-family: opensans, Arial, Helvetica, sans-serif;
	font-size:12px;
	color:#666;
}

h1, h2, h3, h4, h5 {
	font-size:24px;
	font-weight:bold;
    color: #666666;
}

h2 { font-size: 20px; }
h3 { font-size: 18px; }

p { padding:10px 0; }

a { color:#6e271f; }

img {
    max-width: 100%;
    max-height: 100%;
    vertical-align: middle;
}
#mapa img { max-width: inherit; max-height: inherit; }


label {
    font-family: opensans, Arial, Helvetica, sans-serif;
    font-size:14px;
    color: #666;
}

label > span { display: block; }
label > span > span { font-size: 11px; float: right; }

input[type=text], input[type=password], input[type=email], select {
    width: 100%;
    width: -webkit-calc(100% - 20px);
    width: -moz-calc(100% - 20px);
    width: calc(100% - 20px);
    height:35px;
    line-height:35px;
    vertical-align:middle;
    padding:0 10px;
    background:#ffffff;
    border: 1px solid #ccc;
    font-family: opensans, Arial, Helvetica, sans-serif;
    font-size:12px;
    color:#999;
}

input[type=radio], input[type=checkbox] {
    height: auto;
    line-height: normal;
    vertical-align: middle;
}

textarea {
    width: 100%;
    width: -webkit-calc(100% - 20px);
    width: -moz-calc(100% - 20px);
    width: calc(100% - 20px);
    padding:10px;
    background:#ffffff;
    border: 1px solid #ccc;
    font-family: opensans, Arial, Helvetica, sans-serif;
    font-size:12px;
    font-weight: 400;
    color:#999;
    resize:none;
}

select {
    width: 100%;
	height:37px;
	line-height:37px;
	padding:8px 5px 5px 2px;
}

.inputfile {
    width: 0.1px;
    height: 0.1px;
    opacity: 0;
    overflow: hidden;
    position: absolute;
    z-index: -1;
}

.inputfile + label {
    display: inline-block;
    height: 25px;
    line-height: 25px;
    padding: 0 15px 0 8px;
    background-color: <?php echo $color_main_gray ?>;
    font-size: 12px;
    color: white;
    cursor: pointer;
}

.inputfile:focus + label,
.inputfile + label:hover {
    background-color: red;
}

.inputfile:focus + label {
    outline: 1px dotted #000;
    outline: -webkit-focus-ring-color auto 5px;
}

.inputfile + label * {
    pointer-events: none;
}

button {
    background-color: transparent;
    font-family: opensans, Arial, Helvetica, sans-serif;
    cursor: pointer;
}




/* =========== HEADER CONTENT ======== */

header { color: #fff; }
header a { color: #fff; }

header .barra_topo1 {
    height: 31px;
    padding-top: 9px;
    background: #444349;
    color: #fff;
}

header .barra_topo1 .wordseries {
    width: 700px;
    height: 25px;
    float: left;
    text-align: center;
}

header .barra_topo1 .configs {
    width: 200px;
    float: left;
}

header .barra_topo1 .configs .selected {
    padding-bottom: 10px;
    background-size: 10px auto;
}

header .barra_topo1 .configs .language {
    width: 115px;
    height: 21px;
    line-height: 21px;
    float: left;
    border-right: 1px solid #5a5962;
    cursor: pointer;
}

header .barra_topo1 .configs .language .flag {
    width: 21px;
    height: 14px;
    float: left;
    margin-right: 5px;
}

header .barra_topo1 .configs .language .lan {
    width: 70px;
    float: left;
    font-size: 12px;
}

header .barra_topo1 .configs .options {
    position: absolute;
    display: none;
    width: 130px;
    padding: 0 5px 5px 10px;
    background: #fff;
    border-top: 4px solid <?php echo $color_main_red ?>;
}

header .barra_topo1 .configs .language:hover .options { display: block; }

header .barra_topo1 .configs .options .option {
    height: 30px;
    line-height: 30px;
    font-size: 12px;
    color: #666;
}

header .barra_topo1 .configs .language .options .lan {
    width: auto;
}

header .barra_topo1 .configs .language .options .option<?php echo $clear_end; ?>

header .barra_topo1 .configs .moeda {
    width: 40px;
    height: 21px;
    line-height: 21px;
    float: left;
    padding-left: 10px;
    border-left: 1px solid #222222;
    cursor: pointer;
}

header .barra_topo1 .configs .moeda:hover .options { display: block; }
header .barra_topo1 .configs .moeda .options{
    width: 40px;
}

header .barra_topo1 .features,
header .barra_topo1 .account {
    width: 250px;
    height: 21px;
    line-height: 21px;
    border-left: 1px solid #222222;
    float: right;
}

header .barra_topo1 .account {
    display: block;
    display: none;
    width: 150px;
}

header .barra_topo1 .features:before,
header .barra_topo1 .account:before {
    content: '';
    position: absolute;
    display: block;
    width: 1px;
    height: 21px;
    margin-left: -2px;
    background: #5a5962;
}


header .barra_topo1 .features a {
    display: inline-block;
    margin: 0 10px;
    /*padding-left: 22px;*/
    background-repeat: no-repeat;
    background-position: left center;
    background-size: 16px auto;
}

header .barra_topo1 .features a i { width: 20px; font-size: 16px; }

header .barra_topo1 .account i { width: 20px; margin-left: 10px; font-size: 16px; }
/*
header .barra_topo1 .features a.login { margin-left: 20px; background-image: url('../img/icon_user_login.png'); }
header .barra_topo1 .features a.cadastro { background-image: url('../img/icon_cadastrar.png'); }
header .barra_topo1 .features a.wishlist { background-image: url('../img/icon_wishlist.png'); }
*/



header .barra_topo2 {
    height: 100px;
    background: #222;
}

header .barra_topo2 .logomarca {
    width: 155px;
    height: 75px;
    line-height: 75px;
    float: left;
    margin-top: 10px;
}

header .barra_topo2 .barra_atendimentos {
    width: 500px;
    float: right;
    margin-top: 35px;
}

header .barra_topo2 .barra_atendimentos a {
    position: relative;
    display: block;
    width: 110px;
    height: 30px;
    line-height: 15px;
    float: left;
    padding-left: 40px;
    margin-right: 10px;
    background-repeat: no-repeat;
    background-size: auto 30px;
    background-position: left center;
    font-size: 16px;
}

header .barra_topo2 .barra_atendimentos a.atendimento_televendas { background-image: url('../img/icon_atendimentos_televendas.png'); }
header .barra_topo2 .barra_atendimentos a.atendimento_chat { background-image: url('../img/icon_atendimentos_chat.png'); }
header .barra_topo2 .barra_atendimentos a.atendimento_contato { background-image: url('../img/icon_atendimentos_contato.png'); }


header .barra_topo2 .barra_atendimentos a span {
    font-size: 12px;
}

header .barra_topo2 .barra_atendimentos a.atendimento_televendas span {
    display: inline-block;
    padding-right: 15px;
    background: url('../img/lang_down.png') no-repeat right 6px;
    background-size: 10px auto;
}

header .barra_topo2 .carrinho {
    width: 290px;
    height: 35px;
    float: right;
    margin-top: 35px;
    cursor: pointer;
    z-index: 102;
}

header.fixa .barra_topo2 .carrinho {
    position: fixed;
    width: 230px;
    top: 5px;
    left: 50%;
    margin: 0 0 0 385px;
    background: <?php echo $color_main_red ?>;
}

header .barra_topo2 .carrinho .resumo {
    height: 35px;
    line-height: 35px;
    padding-right: 50px;
    background: url('../img/icon_carrinho.png') no-repeat;
    background-position: right 14px top 8px;
    background-size: 27px auto;
    border: 1px solid #505050;
    border-radius: 20px;
    font-size: 14px;
    text-align: right;
}

header.fixa .barra_topo2 .carrinho .resumo {
    height: 28px;
    line-height: 28px;
    background-position: right 14px top -24px;
    border-color: #fff;
}

header .barra_topo3 {
    height: 40px;
    line-height: 40px;
    background: <?php echo $color_main_red ?>;
    z-index: 101;
}

header.fixa {padding-bottom: 40px; background: <?php echo $color_main_red ?>; }

header.fixa .barra_topo3 {
    position: fixed;
    width: 100%;
    top: 0;
}

body.menu-over-all header .barra_topo3 {
    position: absolute;
    width: 100%;
    height: 100%;
    overflow-y:scroll;
    background: transparent;
}
body.menu-over-all header .barra_topo3 .comp-grid-main {background: <?php echo $color_main_red ?>}
body.menu-over-all header .barra_topo2 .carrinho { display: none; }

body.menu-over-all {
    overflow:hidden;
}

header .barra_topo3 .mobilenavbutton { display: none; }

header .barra_topo3 .home {
    position: absolute;
    width: 56px;
    height: 40px;
    float: left;
    border-left: 1px solid #a41733;
    border-right: 1px solid #a41733;
    text-align: center;
    z-index: 11;
}
header.fixa .barra_topo3 .home { display: none; }

header .barra_topo3 .home a {
    display: block;
    width: 100%;
    height: 24px;
    line-height: 24px;
    padding: 8px 0;
}

header .barra_topo3 nav {
    position: relative;
    height: 40px;
    padding-left: 56px;
    z-index: 10;
}

header .barra_topo3 nav > li {
    position: relative;
    display: block;
    width: <?php echo $menuElementWidth; ?>px;
    height: 40px;
    line-height: 40px;
    float: left;
    border-right: 1px solid #a41733;
    text-align: center;
}
header.fixa .barra_topo3 nav > li {width: <?php echo $menuFixedElementWidth; ?>px; }

header.fixa .barra_topo3 nav { padding-left: 0; border-left: 1px solid #a41733; }

header .barra_topo3 nav > li > span,
header .barra_topo3 nav > li > a {
    display: block;
    height: 40px;
    font-weight: bold;
    cursor: pointer;
}

header .barra_topo3 nav > li > span:after {
    content: '';
    position: relative;
    width: 0;
    height: 0;
    top: 10px;
    margin-left: 10px;
    border: 5px solid;
    border-color: transparent;
    border-top-color: #fff;
}

header .barra_topo3 nav > li > ul {
    position: absolute;
    display: none;
    width: <?php echo $menuElementWidth; ?>px;
    background: #f9f9f9;
    top: 40px;
    left: 0;
    z-index: 10;
}
header.fixa .barra_topo3 nav > li > ul { width: <?php echo $menuFixedElementWidth; ?>px; }

header .barra_topo3 nav > li > ul li {
    position: relative;
    height: 36px;
    line-height: 36px;
    background: #f9f9f9;
    border-bottom: 1px solid #d2d2d2;
}

header .barra_topo3 nav > li > ul li span,
header .barra_topo3 nav > li > ul li a {
    display: block;
    padding-left: 20px;
    text-align: left;
    color: #666;
    cursor: pointer;
}

header .barra_topo3 nav > li > ul > li > span:before {
    content: '';
    position: absolute;
    display: block;
    width: 0;
    height: 0;
    top: 13px;
    right: 5px;
    border: 5px solid;
    border-color: transparent;
    border-left-color: #666;
}

header .barra_topo3 nav > li > ul > li > ul {
    position: absolute;
    display: none;
    width: <?php echo $menuElementWidth; ?>px;
    top: 0;
    left: 100%;
}
header.fixa .barra_topo3 nav > li > ul > li > ul { width: <?php echo $menuFixedElementWidth; ?>px; }

header .barra_topo3 nav > li > ul > li > ul > li > a:before,
header .barra_topo3 nav > li > ul > li > ul > li > span:before {
    content: ' - ';
}


/* MENU ABERTO */
header .barra_topo3 nav > li.menu-aberto {
    position: static;
}

header .barra_topo3 nav > li.menu-aberto > ul {
    width: 100%;
    overflow:auto;
    left: 0;
    -webkit-box-shadow: 0px 9px 21px -5px rgba(0,0,0,0.47);
    -moz-box-shadow: 0px 9px 21px -5px rgba(0,0,0,0.47);
    box-shadow: 0px 9px 21px -5px rgba(0,0,0,0.47);
}
header .barra_topo3 nav > li.menu-aberto > ul<?php echo $clear_end ?>

/*
header .barra_topo3 nav > li > ul,
header .barra_topo3 nav > li > ul > li > ul { display: block; }
*/

header .barra_topo3 nav > li.menu-aberto > ul > li {
    width: 185px;
    height: auto;
    float: left;
    padding: 0 10px;
    border: 0;
}

header .barra_topo3 nav > li.menu-aberto > ul > li > span {
    padding-left: 0;
    font-weight: bold;
    color: #666;
    cursor: default;
}

header .barra_topo3 nav > li.menu-aberto > ul > li > span:before { display: none; }
header .barra_topo3 nav > li.menu-aberto > ul > li > .img {
    height: 102px;
    background: #ccc;
}

header .barra_topo3 nav > li.menu-aberto > ul > li > ul {
    position: static;
    display: block;
    width: auto;
    padding: 10px 0;
}

header .barra_topo3 nav > li.menu-aberto > ul > li > ul > li {
    height: 26px;
    line-height: 26px;
    border: none;
    background: #f9f9f9;
}

header .barra_topo3 nav > li.menu-aberto > ul > li > ul > li > span,
header .barra_topo3 nav > li.menu-aberto > ul > li > ul > li > a {
    padding-left: 10px;
    font-weight: bold;
    color: <?php echo $color_main_red ?>;
}

header .barra_topo3 nav > li.menu-aberto > ul > li > ul > li > a:before,
header .barra_topo3 nav > li.menu-aberto > ul > li > ul > li > span:before {
    content: ' ';
    position: absolute;
    display: block;
    width: 0;
    height: 0;
    top: 9px;
    left: 0px;
    border: 5px solid;
    border-color: transparent;
    border-left-color: #666;
}



header #televendas {
    position: absolute;
    display: none;
    width: 850px;
    padding: 25px 55px 50px;
    top: 120px;
    left: 50%;
    margin-left: -480px;
    background: #222;
    z-index: 10;
}

header #televendas .close {
    position: absolute;
    display: block;
    width: 20px;
    height: 20px;
    right: 20px;
    top: 25px;
    background: <?php echo $color_main_red ?>;
    font-size: 14px;
    font-weight: bold;
    text-align: center;
    color: #fff;
    z-index: 1;
}

header #televendas .box {
    float: left;
    margin-right: 60px;
}

header #televendas .box.brasil { width: 485px; }
header #televendas .box.america { width: 245px; }

header #televendas .box .titulo {
    padding: 8px 0;
    border-bottom: 1px solid #343434;
    font-size: 14px;
    letter-spacing: 1px;
}

header #televendas .box.america .titulo { margin-bottom: 40px; }

header #televendas .box span {
    display: inline-block;
    width: 240px;
    height: 30px;
    line-height: 30px;
    font-size: 16px;
    font-weight: 600;
}

header #televendas .box span a { font-size: 14px; font-weight: normal; }

header #televendas .box span.whatsapp,
header #televendas .box span.skype { 
    width: 210px;
    margin: 25px 0 15px;
    padding-left: 30px;
    background: url('../img/icon_televendas_whatsapp.png') no-repeat left center;
    background-size: 20px auto;
    font-size: 14px;
    font-weight: normal;
}

header #televendas .box span.skype {
    background-image: url('../img/icon_televendas_skype.png');
}


header #carrinho {
    position: absolute;
    display: none;
    width: 290px;
    top: 130px;
    left: 50%;
    margin-left: 320px;
    padding-bottom: 15px;
    background: #fff;
    border: 1px solid #d0d0d0;
    color: #666;
    z-index: 102;
}
header.fixa #carrinho {
    position: fixed;
    top: 60px;
}

header #carrinho:before {
    content: '';
    position: absolute;
    display: block;
    width: 292px;
    height: 4px;
    top: -5px;
    left: -1px;
    background: <?php echo $color_main_red ?>;
}

header #carrinho:after {
    content: '';
    position: absolute;
    display: block;
    width: 0;
    height: 0;
    top: -19px;
    right: 25px;
    border: 7px solid;
    border-color: transparent;
    border-bottom-color: <?php echo $color_main_red ?>;
}

header #carrinho .close {
    position: absolute;
    display: block;
    width: 20px;
    height: 20px;
    top: 6px;
    right: 9px;
    background: <?php echo $color_main_red ?>;
    font-size: 14px;
    font-weight: bold;
    text-align: center;
    color: #fff;
    z-index: 1;
}

header #carrinho .itens .item {
    position: relative;
    height: 82px;
    padding: 20px 20px 20px 10px;
    border-bottom: 1px solid #d2d2d2;
    font-size: 13px;
}

header #carrinho .itens .item .img {
    width: 80px;
    height: 80px;
    float: left;
    border: 1px solid #ddd;
}

header #carrinho .itens .item .dados {
    position: relative;
    width: 165px;
    height: 82px;
    float: right;
}

header #carrinho .itens .item .unid { display: none; font-weight: bold; }

header #carrinho .itens .item .preco { 
    position: absolute;
    width: 100%;
    line-height: 20px;
    bottom: 0;
}

header #carrinho .itens .item .preco .sem_desconto {
    display: none;
}
header #carrinho .itens .item .preco .sem_desconto span {
    text-decoration: line-through;
}

header #carrinho .itens .item .preco .excluir {
    position: absolute;
    display: block;
    width: 20px;
    height: 20px;
    line-height: 9px;
    right: 0;
    bottom: 0;
    font-size: 13px;
    font-weight: bold;
    text-align: center;
    color: #666;
}

header #carrinho .total {
    height: 50px;
    line-height: 50px;
    padding: 0 20px;
    font-size: 13px;
    font-weight: bold;
    text-align: right;
}

header #carrinho .total span { font-size: 14px; }

header #carrinho .actions { text-align: center; }

header #carrinho .actions a {
    display: inline-block;
    width: 125px;
    height: 35px;
    line-height: 35px;
    margin: 0 7px;
    background: <?php echo $color_main_gray ?>;
    font-size: 10px;
    font-weight: bold;
    text-align: center;
}

header #carrinho .actions a.finalizar { background: <?php echo $color_main_green ?>; }

header #carrinho .vazio {
    display: none;
    height: 60px;
    line-height: 60px;
    padding-top: 20px;
    font-size: 16px;
    font-weight: bold;
    text-align: center;
}


header #login {
    position: absolute;
    display: none;
    width: 260px;
    top: 50px;
    left: 50%;
    margin-left: 290px;
    padding-bottom: 15px;
    background: #fff;
    border: 1px solid #d0d0d0;
    color: #666;
    z-index: 112;
}

header #login.checkout:after { right: 70%; }

header #login:before {
    content: '';
    position: absolute;
    display: block;
    width: 263px;
    height: 4px;
    top: -5px;
    left: -1px;
    background: <?php echo $color_main_red ?>;
}

header #login:after {
    content: '';
    position: absolute;
    display: block;
    width: 0;
    height: 0;
    top: -19px;
    right: 50%;
    border: 7px solid;
    border-color: transparent;
    border-bottom-color: <?php echo $color_main_red ?>;
}

header #login .close {
    position: absolute;
    display: block;
    width: 20px;
    height: 20px;
    top: 13px;
    right: 9px;
    background: <?php echo $color_main_red ?>;
    font-size: 14px;
    font-weight: bold;
    text-align: center;
    color: #fff;
    z-index: 1;
}

header #login .titulo {
    height: 47px;
    line-height: 47px;
    border-bottom: 1px solid #d2d2d2;
    font-size: 14px;
    font-weight: bold;
    text-indent: 20px;
    color: #666;
}

header #login .loginbox {
    padding: 20px 20px 0 20px;
    font-size: 14px;
}

header #login .loginbox .label { margin-bottom: 5px; }
header #login .loginbox .label span { color: <?php echo $color_main_red ?>; }
header #login .loginbox input { margin-bottom: 20px; }

header #login .loginbox .esqueceu {
    display: block;
    margin-top: -10px;
    margin-bottom: 20px;
    font-size: 10px;
    text-decoration: underline;
    cursor: pointer;
}

header #login .loginbox .clickformsubmit {
    height: 35px;
    line-height: 35px;
    padding: 0 20px;
    background: <?php echo $color_main_green ?>;
    font-size: 14px;
    font-weight: bold;
    color: #fff;
}

.mobile_main_nav { display: none; }


/* =========== FOOTER CONTENT ======== */


footer {
    position: relative;
    border-top: 1px solid #ccc;
    margin-top: 40px;
}

footer .selos {
    padding: 20px 0 25px;
    background: #f2f2f2;
    border-top: 3px solid #fff;
}

footer .selos .titulo {
    margin-bottom: 20px;
    font-size: 14px;
    font-weight: 300;
    color: 666px;
}

footer .selos .pgmts {
    width: 68%;
    float: left;
}

footer .selos .certificados {
    width: 32%;
    float: right;
}

footer .newsletter {
    height: 80px;
    line-height: 80px;
    background: <?php echo $color_main_red; ?>;
    border-bottom: 5px solid #000;
    color: #fff;
}

footer .newsletter .label {
    width: 500px;
    float: left;
    padding-left: 60px;
    background: url('../img/icon_newsletter.png') no-repeat left center;
    font-size: 21px;
    letter-spacing: 5px;
}

footer .newsletter .form_newsletter {
    width: 580px;
    height: 50px;
    line-height: 50px;
    float: right;
    margin-top: 15px;
    text-align: right;
}

footer .newsletter .form_newsletter input {
    display: block;
    width: 400px;
    height: 50px;
    line-height: 50px;
    float: left;
    padding-left: 30px;
    background: #921c15;
    font-size: 14px;
    color: #fff;
}

footer .newsletter .form_newsletter button {
    display: block;
    width: 150px;
    height: 50px;
    line-height: 50px;
    float: left;
    background: #222;
    text-align: center;
    color: #d5595d;
}


footer .rodape {
    position: relative;
    padding: 30px 0 0 0;
    background: #222;
    border-top: 3px solid <?php echo $color_main_red; ?>;
    color: #fff;
}

footer .rodape .televendas {
    width: 860px;
    float: left;
}

footer .rodape .televendas .box {
    float: left;
    margin-right: 60px;
}

footer .rodape .televendas .box.brasil { width: 485px; }
footer .rodape .televendas .box.america { width: 245px; }

footer .rodape .televendas .box .titulo {
    padding: 8px 0;
    border-bottom: 1px solid #343434;
    font-size: 14px;
    letter-spacing: 1px;
}

footer .rodape .televendas .box.america .titulo { margin-bottom: 40px; }

footer .rodape .televendas .box span {
    display: inline-block;
    width: 240px;
    height: 30px;
    line-height: 30px;
    font-size: 16px;
    font-weight: 600;
}

footer .rodape .televendas .box span a { font-size: 14px; font-weight: normal; color: #fff; }

footer .rodape .televendas .box span.whatsapp,
footer .rodape .televendas .box span.skype { 
    width: 210px;
    margin: 25px 0 15px;
    padding-left: 30px;
    background: url('../img/icon_televendas_whatsapp.png') no-repeat left center;
    background-size: 20px auto;
    font-size: 14px;
    font-weight: normal;
}

footer .rodape .televendas .box span.skype {
    background-image: url('../img/icon_televendas_skype.png');
}

footer .rodape .televendas .horario {
    padding: 30px 0;
    font-size: 14px;
}

footer .rodape .redes_sociais {
    width: 280px;
    float: right;
    padding-bottom: 30px;
}

footer .rodape .redes_sociais .titulo {
    padding: 8px 0;
    border-bottom: 1px solid #343434;
    font-size: 14px;
    letter-spacing: 1px;
}

footer .rodape .redes_sociais .youtube {
    margin: 20px 0 20px;
}

footer .rodape .redes_sociais .youtube a {
    display: inline-block;
    height: 30px;
    line-height: 30px;
    padding-left: 40px;
    background: url('../img/icon_youtube.jpg') no-repeat left center;
    background-size: 30px auto;
    font-size: 18px;
    color: #fff;
}

footer .rodape .redes_sociais .instagram {
    margin: 50px 0 30px;
}

footer .rodape .redes_sociais .instagram a {
    display: inline-block;
    height: 30px;
    line-height: 30px;
    padding-left: 40px;
    background: url('../img/icon_instagram.png') no-repeat left center;
    background-size: 30px auto;
    font-size: 18px;
    color: #fff;
}


footer .nav {
    padding: 8px 0;
    background: #343339;
}

footer .nav nav {
    width: 700px;
    height: 20px;
    line-height: 20px;
    float: left;
}

footer .nav nav li {
    display: block;
    float: left;
    padding: 0 18px;
    border-left: 1px solid #5a5962;
    border-right: 1px solid #222;
}

footer .nav nav li:first-child {
    padding-left: 0;
    border-left: none;
}

footer .nav nav li:last-child {
    padding-right: 0;
    border-right: none;
}

footer .nav nav li a { color: #fff; }

footer .nav .hibrida {
    width: 40px;
    float: right;
}


footer .copyright {
    padding: 25px 0;
    background: #444349;
    text-align: center;
    color: #fff;
}


/* =========== COMUM ======== */

.main_titles {
    position: relative;
    padding: 20px 0;
    margin: 25px 0;
    border-top: 1px solid #d2d2d2;
    font-size: 18px;
    font-weight: 300;
}

.main_titles:before {
    content:'';
    position: absolute;
    width: 170px;
    height: 4px;
    top: -4px;
    background: <?php echo $color_main_red ?>;
}

.main_titles h1,
.main_titles h2,
.main_titles h3,
.main_titles h4 {
    margin: 0;
    padding: 0;
    font-size: 18px;
    font-weight: 300;
}

.main_titles h1.destaque {
    font-size: 26px;
    color: <?php echo $color_main_red ?>;
}

.breadcrumbs {
    height: 50px;
    line-height: 50px;
    overflow: hidden;
    background: #f4f4f4;
    font-weight: 300px;
    color: #666;
}

.breadcrumbs span:after { content: ' / '; }
.breadcrumbs span:last-child:after { content: ''; }

.breadcrumbs a { color: #666; } 

.produto-list {
    margin-bottom: 40px;
    padding: 10px 3%;
    background: #fff;
    border: 1px solid #d2d2d2;
    border-top: 4px solid <?php echo $color_main_red ?>;
    color: #666;
}

ul.aside_menu { border-top: 1px solid #cfd3d4; }

ul.aside_menu li {
    display: block;
    border-bottom: 1px solid #cfd3d4;
}

ul.aside_menu li a {
    display: block;
    padding: 15px 10px 15px 20px;
    font-size: 14px;
    color: #666;
}

ul.aside_menu li a.active {
    background: <?php echo $color_main_red; ?>;
    color: #fff;
}

.produto-list .head {
    width: 100%;
    margin: -10px 0 0 -3%;
    padding: 12px 3% 16px 3%;
}

.produto-list .head .titulo {
    font-size: 18px;
    font-weight: bold;
}

.produto-list .img {
    width: 104%;
    margin: 0 0 20px -2%;
    text-align: center;
}

.produto-list .info { font-size: 14px; }

.produto-list .info .preco {
    font-weight: 300;
    color: #dd0017;
}

.produto-list .info .preco .val { margin: 5px 0; color: #666; }
.produto-list .info .preco .val span { font-size: 18px; font-weight: bold; }

.produto-list .info .fretes { color: #bd001d; }

.produto-list .info .fretes div.ter,
.produto-list .info .fretes div.aer {
    height: 30px;
    line-height: 30px;
    padding-left: 40px;
    background: url('../img/icon_frete_terrestre.png') no-repeat left center;
    background-size: 30px auto;
    font-weight: bold;
    font-style: italic;
}

.produto-list .info .fretes div.aer {
    background: url('../img/icon_frete_aereo.png') no-repeat left center;
    background-size: 35px auto;
}

.produto-list .info .fretes span.ter,
.produto-list .info .fretes span.aer {
    display: inline-block;
    width: 35px;
    height: 35px;
    margin-top: 10px;
}

.produto-list .info .opcoes {
    margin: 10px 0;
    padding-top: 15px;
    border-top: 1px solid #d2d2d2;
}

.produto-list .info .personalizar,
.produto-list .info .opcoes .mesas {
    width: 46%;
    float: left;
    font-weight: 300;
}

.produto-list .info .opcoes .mesas { float: right; }

.produto-list .info a,
.produto-list .info .opcoes .mesas a {
    display: block;
    height: 42px;
    line-height: 42px;
    margin: 15px 0;
    background: <?php echo $color_main_green ?>;
    font-size: 16px;
    font-weight: 600;
    text-align: center;
    color: #fff;
}

.produto-list .info .opcoes .personalizar a,
.produto-list .info .opcoes .mesas a {
    margin: 0;
    margin-bottom: 15px;
}

.produto-list .info .opcoes .mesas a { background: <?php echo $color_main_gray ?>; }

.paginacao {
    padding: 20px 0;
    text-align: right;
}

.paginacao a {
    display: inline-block;
    height: 46px;
    line-height: 46px;
    padding: 0 15px;
    margin: 0 2px;
    background: #f4f4f4;
    font-size: 18px;
    font-weight: 300;
    text-align: center;
    color: #bfbfbf;
}

.paginacao a.active { background: <?php echo $color_main_red ?>; color: #fff; }



.esqueceu_box {
    position: absolute;
    width: 260px;
    left: 50%;
    margin-left: -145px;
    padding: 20px 15px;
    background: #fff;
}

.esqueceu_box .top_pattern,
.esqueceu_box .bottom_pattern {
    position: absolute;
    width: 100%;
    height: 5px;
    left: 0;
    background:url('../img/bg_newsbox_pattern.jpg') repeat-x;
}
.esqueceu_box .top_pattern { top: 0; }
.esqueceu_box .bottom_pattern { bottom: 0; }

.esqueceu_box .bt_fechar {
    position: absolute;
    display: block;
    width: 20px;
    height: 20px;
    top: 10px;
    right: 10px;
    background: <?php echo $color_main_red ?>;
    font-size: 14px;
    font-weight: bold;
    text-align: center;
    color: #fff;
    z-index: 1;
    cursor: pointer;
}

.esqueceu_box .titulo {
    font-size: 20px;
    color: <?php echo $color_main_red ?>;
}

.esqueceu_box .texto {
    margin: 5px 0 20px;
}

.esqueceu_box form input {
    height: 40px;
    line-height: 40px;
    margin-bottom: 10px;
}

.esqueceu_box form .button {
    display: inline-block;
    width: auto;
    height: 35px;
    line-height: 35px;
    float: right;
    padding: 0 20px;
    background: <?php echo $color_main_green ?>;
    border: none;
    font-size: 15px;
    color: #fff;
    cursor: pointer;
}


.newsbox {
    position: absolute;
    width: 645px;
    height: 350px;
    left: 50%;
    margin-left: -323px;
    background:url('../img/bg_newsbox_mao.jpg') no-repeat left center #fff;
}

.newsbox.confirmado { height: 270px; background: #fff; }

.newsbox .top_pattern,
.newsbox .bottom_pattern {
    position: absolute;
    width: 100%;
    height: 10px;
    left: 0;
    background:url('../img/bg_newsbox_pattern.jpg') repeat-x;
}
.newsbox .bottom_pattern { bottom: 0; }

.newsbox .bt_fechar {
    position: absolute;
    width: 50px;
    height: 50px;
    top: -25px;
    right: -25px;
    background:url('../img/bt_newsbox_fechar.png') no-repeat;
    cursor: pointer;
}

.newsbox .main_content {
    position: absolute;
    width: 405px;
    height: 220px;
    top: 50px;
    left: 215px;
    font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
    font-size: 12px;
    color: #000;
}

.newsbox.confirmado .main_content {
    width: 645px;
    left: 0;
    top: 100px;
    text-align: center;
}

.newsbox.confirmado .main_content .bt_continuar {
    display: inline-block;
    height: 42px;
    line-height: 42px;
    margin-top: 40px;
    padding: 0 20px;
    background: #008C00;
    font-size: 15px;
    color: #fff;
    cursor: pointer;
}

.newsbox .main_content .titulo { font-size: 27px; }
.newsbox.confirmado .main_content .titulo { font-size: 22px; }
.newsbox .main_content .mensagem { margin-top: 15px; }
.newsbox .main_content form { margin-top: 30px; }

.newsbox .main_content form input {
    display: inline-block;
    width: 230px;
    height: 40px;
    line-height: 40px;
    padding: 0 15px;
    border: 1px solid #ccc;
}

.newsbox .main_content form .button {
    display: inline-block;
    width: auto;
    height: 42px;
    line-height: 42px;
    padding: 0 20px;
    background: #008C00;
    border: none;
    font-size: 15px;
    color: #fff;
    cursor: pointer;
}

.newsbox .main_content .opcoes {
    margin-top: 15px;
    font-size: 14px;
    text-align: right;
    color: #822215;
}

.newsbox .main_content .opcoes .links {
    text-decoration: underline;
    cursor: pointer;
}


.dollarbox {
    position: absolute;
    width: 300px;
    padding: 10px;
    left: 50%;
    margin-left: -150px;
    background: #fff;
    border: 2px solid <?php echo $color_main_red ?>;
    font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
    font-size: 14px;
    text-align: center;
    color: #000;
}

.dollarbox .bt_fechar {
    position: absolute;
    width: 50px;
    height: 50px;
    top: -25px;
    right: -25px;
    background:url('../img/bt_newsbox_fechar.png') no-repeat;
    cursor: pointer;
}

/* =========== PÁGINA HOME ======== */


.barra_features {
    background: #f4f4f4;
    padding-bottom: 35px;
    margin-bottom: 50px;
}

.featured {
    min-height: 70px;
    padding-top: 35px;
}

.featured .icon {
    position: relative;
    width: 46px;
    height: 70px;
    line-height: 70px;
    float: left;
    padding: 0 12px;
    margin-right: 20px;
    background: <?php echo $color_main_red ?>;
}

.featured .icon:after {
    content: '';
    position: absolute;
    display: block;
    width: 0;
    height: 0;
    top: 50%;
    right: -14px;
    margin-top: -7px;
    border: 7px solid;
    border-color: transparent;
    border-left-color: <?php echo $color_main_red ?>;
}

.featured .info {
    width: 205px;
    float: left;
    font-weight: 600;
    color: #666;
}

.featured .info span {
    display: block;
    margin-bottom: 10px;
    font-size: 15px;
}

.featured .info p {
    margin: 0;
    padding: 0;
}

.home_destaques {
    margin-bottom: 50px !important;
}

.home_destaques .produto-list .head .titulo { font-size: 20px; }

.home_destaques .produto-list .info .preco {
    width: 50%;
    float: left;
}

.home_destaques .produto-list .info .fretes {
    width: 45%;
    float: right;
}

.banner_fichas .banner1 {
    display: inline-block;
    width: 65%;
    float: left;
}

.banner_fichas .banner2 {
    display: inline-block;
    width: 30%;
    float: right;
    text-align: right;
}

.background_poker {
    padding: 100px 0 70px;
    margin: 40px 0;
    background: url('../img/background_poker.jpg') no-repeat center center;
    background-attachment: fixed;
}

.background_poker .produto-list {
    margin: 0 4% 40px;
}

.pronta_entrega .roleta {
    width: 100%;
    min-height: 450px;
}

.pronta_entrega .produto-list {
    border: none;
}

.pronta_entrega .produto-list .img {
    width: 106%;
    margin: -10px 0 0 -3%;
}

.pronta_entrega .produto-list .titulo {
    height: 40px;
    line-height: 20px;
    margin: 10px 0;
    font-size: 14px;
}

.pronta_entrega .produto-list .preco { font-size: 12px; }
.pronta_entrega .produto-list .preco .val { font-size: 14px; }

.banners_avulsos { margin: 10px 0 60px; }
.banners_avulsos .banner { margin: 10px 0; text-align: center; }

.clientes .home_titles { margin-bottom: 0; }

.clientes .roleta { position: relative; margin-top: -20px; }

.clientes .roleta .items .item { text-align: center; }
.clientes .roleta .items .item img { width: auto; }

.roleta .setas {
    position: absolute;
    width: 70px;
    height: 30px;
    line-height: 30px;
    right: 0;
    top: -50px;
}

.roleta .setas span {
    display: block;
    width: 30px;
    height: 30px;
    float: left;
    cursor: pointer;
}

.roleta .setas span.right { float: right; }


/* =========== PÁGINA INSTITUCIONAL ======== */





/* =========== PÁGINA PRODUTO ======== */


.inc_video {
    width: 96%;
    height: 96%;
    padding: 2%;
    background: #fff;
}

.inc_video .close {
    position: absolute;
    display: block;
    width: 21px;
    height: 21px;
    line-height: 20px;
    right: 5px;
    top: 5px;
    background: <?php echo $color_main_red ?>;
    font-size: 14px;
    font-weight: bold;
    text-align: center;
    color: #fff;
    cursor: pointer;
}

.produto {
    margin: 20px 0;
}

.produto .main_info { position: relative; height: 330px; margin: 30px 0; }
.produto .main_info<?php echo $clear_end ?>

.produto .main_info .img {
    width: 600px;
    height: 330px;
    float: left;
    text-align: center;
}

.produto .main_info .img img { width: 100%; height: auto; }

.produto .main_info .info {
    position: relative;
    width: 560px;
    height: 330px;
    float: right;
}

.produto .main_info h1 {
    width: 560px;
    float: right;
    font-size: 32px;
}

.produto .main_info .descricao {
    width: 560px;
    height: 150px;
    float: right;
    overflow: hidden;
    font-size: 13px;
}

.produto .main_info .features<?php echo $clear_end ?>

.produto .main_info .features .featured {
    width: 47%;
    min-height: 75px;
    padding-top: 10px;
    float: left;
}

.produto .main_info .features .featured:nth-child(2),
.produto .main_info .features .featured:nth-child(4) {
    float: right;
}

.produto .main_info .features .featured .icon {
    position: relative;
    width: 34px;
    height: 54px;
    line-height: 54px;
    padding: 0 10px;
    margin-right: 20px;
}

.produto .main_info .features .featured .icon:after {
    right: -10px;
    margin-top: -5px;
    border: 5px solid;
    border-color: transparent;
    border-left-color: <?php echo $color_main_red ?>;
}

.produto .main_info .features .featured .info {
    width: 100%;
    width: -webkit-calc(100% - 75px);
    width: -moz-calc(100% - 75px);
    width: calc(100% - 75px);
    height: auto;
    float: right;
    font-size: 11px;
    font-weight: 600;
    color: #666;
}

.produto .main_info .features .featured .info span {
    display: block;
    margin-bottom: 5px;
    font-size: 13px;
}

.produto .main_info .fotos {
    position: absolute;
    width: 560px;
    height: 125px;
    bottom: 0;
    right: 0;
}

.produto .main_info .fotos .titulo {
    height: 30px;
    line-height: 30px;
    padding: 0 20px;
    background: #ededed;
    font-size: 14px;
}

.produto .main_info .fotos .roleta {
    margin-top: 15px;
}

.produto .main_info .fotos .roleta button {
    display: block;
    width: 25px;
    height: 80px;
    line-height: 80px;
    float: left;
    background: #ededed;
}

.produto .main_info .fotos .roleta button.next { float: right; }

.produto .main_info .fotos .roleta .items {
    width: 100%;
    width: -webkit-calc(100% - 60px);
    width: -moz-calc(100% - 60px);
    width: calc(100% - 60px);
    margin: 0 auto;
}

.produto .main_info .fotos .roleta .items .item {
    width: 80px;
    margin: 0 auto;
}

.produto .main_info .fotos .roleta .items .item img { width: auto; height: auto; }

.produto .curinga_info {
    margin: 20px 0;
}


.personalizacoes {
    padding: 30px 0;
}

button.slidetoggle_conjuntos {
    position: relative;
    display: inline-block;
    height: 42px;
    padding: 0 55px 0 30px;
    background: #666;
    font-size: 15px;
    font-weight: 800;
    color: #fff;
}

button.slidetoggle_conjuntos span {
    position: absolute;
    width: 10px;
    height: 10px;
    right: 20px;
    top: 12px;
    border: 3px solid #fff;
    border-top: none;
    border-left: none;
    transform: rotate(45deg);
}

.personalizacoes .conjuntos<?php echo $clear_end; ?>

.personalizacoes .conjuntos .conjunto {
    position: relative;
    width: 560px;
    height: 142px;
    margin: 40px 0;
}

.personalizacoes .conjuntos .conjunto:nth-child(odd) { float: left; }
.personalizacoes .conjuntos .conjunto:nth-child(even) { float: right; }

.personalizacoes .conjuntos .conjunto .titulo {
    width: 330px;
    line-height: 30px;
    float: right;
    font-size: 14px;
    font-weight: bold;
    font-size: 14px;
    color: <?php echo $color_main_red ?>;
}

.personalizacoes .conjuntos .conjunto .titulo span {
    display: inline-block;
    width: 30px;
    height: 30px;
    margin-right: 10px;
    background: <?php echo $color_main_red ?>;
    text-align: center;
    font-weight: normal;
    color: #fff;
}

.personalizacoes .conjuntos .conjunto .imgs {
    width: 210px;
    height: 142px;
    background-image: url('../img/bg_personalizacao_imagem.jpg'); 
    background-repeat: no-repeat;
    background-position: center;
    background-size: 100% 100%;
    /*float: left;*/
}

.personalizacoes .conjuntos .conjunto .imgs span { display: none; }

.personalizacoes .conjuntos .conjunto .imgs span[data-ampliar] {
    cursor: pointer;
}

.personalizacoes .conjuntos .conjunto .imgs span[data-ampliar]:after {
    content: 'clique para ampliar';
}

.personalizacoes .conjuntos .conjunto .imgs span[data-video] {
    position: relative;
    cursor: pointer;
}

.personalizacoes .conjuntos .conjunto .imgs span[data-video]:after {
    content: 'clique para ampliar o vídeo';
}

.personalizacoes .conjuntos .conjunto .imgs span[data-video]:before {
    content: ' ';
    position: absolute;
    display: block;
    width: 60px;
    height: 60px;
    left: 50%;
    top: 50%;
    margin: -30px 0 0 -30px;
    background: url('../img/icon_play.png') no-repeat center;
    background-size: 60px auto;
    z-index: 100;
}

.personalizacoes .conjuntos .conjunto .descricao {
    width: 330px;
    float: right;
    margin: 10px 0;
}

.personalizacoes .conjuntos .conjunto .selection {
    position: absolute;
    width: 330px;
    right: 0;
    bottom: 0;
}

.personalizacoes .conjuntos .conjunto .selection .disabled-message {
    display: none;
    padding-bottom: 5px;
}

.personalizacoes .conjuntos .conjunto .selection .disabled-message i { display: none; }

.personalizacoes .conjuntos .conjunto .selection .disabled-message:before {
    content: ' ';
    display: block;
    width: 18px;
    height: 18px;
    float: left;
    margin: -1px 10px 0 0;
    background: url('../img/icon_warning.png') no-repeat center center;
    background-size: 20px auto;
}

.personalizacoes .conjuntos .conjunto .selection .disabled-message i { font-size: 14px; color: <?php echo $color_main_red ?>; }

.personalizacoes .conjuntos .conjunto .selection.disabled .disabled-message { display: block; }
.personalizacoes .conjuntos .conjunto .selection.disabled select { display: none; }

.personalizacoes .conjuntos .conjunto .selection .box-selection {
    position: absolute;
    display: none;
    width: -webkit-calc(100% - 30px);
    width: -moz-calc(100% - 30px);
    width: calc(100% - 30px);
    min-height: 90px;
    padding: 10px 15px;
    bottom: 0;
    background: #fff;
    border: 1px solid #ccc;
    border-top: 4px solid #b7231a;
}

.personalizacoes .conjuntos .conjunto .selection .box-selection .close {
    position: absolute;
    display: block;
    width: 21px;
    height: 21px;
    line-height: 20px;
    right: 10px;
    top: 10px;
    background: <?php echo $color_main_red ?>;
    font-size: 14px;
    font-weight: bold;
    text-align: center;
    color: #fff;
    cursor: pointer;
}

.personalizacoes .conjuntos .conjunto .selection .box-selection .option,
.personalizacoes .conjuntos .conjunto .selection .box-selection .desc {
    margin: 5px 0;
    font-size: 11px;
    font-weight: bold;
}
.personalizacoes .conjuntos .conjunto .selection .box-selection .desc { font-weight: normal; }

.personalizacoes .conjuntos .conjunto .selection .box-selection input {
    display: block;
    height: 25px;
    line-height: 25px;
    margin: 5px 0;
}

.resumo_produto .main_titles span.toggle {
    display: none;
}

.resumo_produto .nome {
    font-size: 14px;
    font-weight: bold;
}

.resumo_produto .configuracoes {
    border: 1px solid #ccc;
    padding: 10px 30px;
}
.resumo_produto .configuracoes<?php echo $clear_end; ?>

.resumo_produto .configuracoes span {
    display: block;
    width: 50%;
    height: 30px;
    line-height: 30px;
    float: left;
}

.mobile_valores,
.mobile_quantidade,
.mobile_comprar {display: none;}

.barra_preco {
    position: relative;
    width: 100%;
    bottom: 0;
    padding: 10px 0;
    margin: 30px 0 -40px 0;
    background: #ebebeb;
    border-top:1px solid #ccc;
    z-index: 100;
}
.barra_preco:before {
    content: ' ';
    position: absolute;
    display: block;
    width: 100%;
    height: 3px;
    top: 0;
    background: #fff;
}

.barra_preco.fixa {
    position: fixed;
    margin: 0;
    transform: translateZ(0);
    -moz-transform: translatez(0);
    -ms-transform: translatez(0);
    -o-transform: translatez(0);
    -webkit-transform: translateZ(0);
    -webkit-font-smoothing: antialiased;
}

.barra_preco<?php echo $clear_end; ?>

.barra_preco .quantidade {
    width: 360px;
    float: left;
    padding: 10px 0;
    margin-right: 30px;
    background: #e0e0e0;
    color: <?php echo $color_main_green; ?>;
    -webkit-transition: .4s;
    -o-transition: .4s;
    transition: .4s;
}

.barra_preco .quantidade.change {
    background: #d4d4d4;
}


.barra_preco .quantidade .valor_unitario .valor_final {
    font-weight: bold;
    -webkit-transition: .4s;
    -o-transition: .4s;
    transition: .4s;
}
.barra_preco .quantidade.change .valor_unitario .valor_final {
    background-color: #4e7a3a;
    color: #fff;
}

.barra_preco .quantidade<?php echo $clear_end; ?> 


.barra_preco .quantidade .label {
    width: 90px;
    line-height: 17px;
    float: left;
    font-size: 14px;
    text-align: right;
    color: <?php echo $color_main_green; ?>;
}

.barra_preco .quantidade .input {
    width: 70px;
    float: left;
    margin: 0 10px;
}

.barra_preco .quantidade .input input {
    width: 100%;
    height: 33px;
    line-height: 33px;
    padding: 0;
    font-size: 16px;
    text-align: center;
    color: #000;
}

.barra_preco .quantidade .valor_unitario {
    position: relative;
    float: left;
}

.barra_preco .quantidade .valor_unitario .sem_desconto {
    text-decoration: line-through;
}

.barra_preco .quantidade .valor_unitario .seta {
    position: absolute;
    display: none;
    width: 100px;
    height: 102px;
    bottom: 50px;
    background: url('../img/setacarrinho.gif') no-repeat;
}

.barra_preco .total,
.mobile_valores .total {
    width: 270px;
    float: left;
}

.mobile_valores .valor_unitario {
    margin-bottom: 15px;
    font-size: 14px;
    font-weight: bold;
    color: #666;
}

.mobile_valores .valor_unitario span {
    padding-right: 5px;
    font-size: 14px;
    font-weight: normal;
    color: <?php echo $color_main_green; ?>;
}

.mobile_valores .valor_unitario span.sem_desconto {
    display: none;
    text-decoration: line-through;
}

.barra_preco .total .label,
.mobile_valores .total .label {
    line-height: 1;
    font-size: 16px;
    font-weight: bold;
}

.barra_preco .total .parcelamento,
.mobile_valores .total .parcelamento {
    font-size: 11px;
    font-weight: bold;
}

.barra_preco .total .parcelamento span,
.mobile_valores .total .parcelamento span {
    line-height: 1;
    font-size: 18px;
    color: <?php echo $color_main_green; ?>;
}

.barra_preco .total .avista,
.mobile_valores .total .avista {
    font-size: 14px;
    color: <?php echo $color_main_green; ?>;
}

.barra_preco .descontos,
.mobile_valores .descontos {
    width: 300px;
    float: left;
    padding-top: 10px;
}

.barra_preco .descontos .avista,
.barra_preco .descontos .pokerstars,
.mobile_valores .descontos .avista,
.mobile_valores .descontos .pokerstars {
    font-size: 14px;
    font-weight: bold;
}

.barra_preco .descontos .avista span,
.barra_preco .descontos .pokerstars span,
.mobile_valores .descontos .avista span,
.mobile_valores .descontos .pokerstars span {
    font-weight: normal;
    color: <?php echo $color_main_green; ?>;
}

.barra_preco .comprar {
    width: 250px;
    float: right;

}

.barra_preco .comprar button {
    width: 250px;
    height: 55px;
    background: <?php echo $color_main_green; ?>;
    margin: 0;
    padding: 0;
    font-size: 20px;
    font-weight: bold;
    text-align: center;
    color: #fff;
}


.produtos_relacionados .produto_box {
    width: 370px;
    float: left;
    margin-right: 35px;
}

.produtos_relacionados .produto_box:last-child {
    margin-right: 0;
    float: right;
}

.produtos_relacionados .produto_box .img {
    width: 200px;
    float: left;
}

.produtos_relacionados .produto_box .dados {
    width: 160px;
    float: right;
}

.produtos_relacionados .produto_box .dados .nome {
    font-size: 16px;
    font-weight: bold;
}

.produtos_relacionados .produto_box .dados .preco {
    font-size: 14px;
}

.produtos_relacionados .produto_box .dados a {
    display: inline-block;
    height: 40px;
    line-height: 40px;
    padding: 0 10px;
    margin-top: 5px;
    background: #577A31;
    font-size: 15px;
    font-weight: 800;
    color: #fff;
}



/* =========== PÁGINA CARRINHO ======== */

.main_carrinho .vazio {
    display: none;
    height: 60px;
    line-height: 60px;
    padding-top: 20px;
    font-size: 16px;
    font-weight: bold;
    text-align: center;
}

.main_carrinho .itens .item {
    padding: 25px 0;
    border-bottom: 1px solid #ccc;
}

.main_carrinho .itens .item:<?php echo $clear_end; ?>

.main_carrinho .itens .item .table {
    display: table;
}

.main_carrinho .itens .item .icone {
    display: table-cell;
    width: 80px;
    height: 80px;
    padding-right: 20px;
}

.main_carrinho .itens .item .nome {
    display: table-cell;
    width: 370px;
    height: 80px;
    padding-right: 20px;
    vertical-align: middle;
    font-size: 14px;
    font-weight: bold;
}

.main_carrinho .itens .item .quantidade {
    display: table-cell;
    width: 90px;
    padding-right: 20px;
}

.main_carrinho .itens .item .quantidade input { font-size: 16px; text-align: center; }

.main_carrinho .itens .item .valor_unitario {
    display: table-cell;
    width: 350px;
    font-size: 14px;
    color: <?php echo $color_main_green ?>;
}
.main_carrinho .itens .item .valor_unitario:before {
    content: 'x ';
    margin: 0 5px 0 -5px;
}

.main_carrinho .itens .item .valor_unitario .sem_desconto span { text-decoration: line-through; }
.main_carrinho .itens .item .valor_unitario .sem_desconto:before { content: 'De: '; }
.main_carrinho .itens .item .valor_unitario .sem_desconto:after { content: ' Por: '; }

.main_carrinho .itens .item .subtotal {
    display: table-cell;
    width: 300px;
    font-size: 14px;
    font-weight: bold;
    color: <?php echo $color_main_green ?>;
}

.main_carrinho .itens .item .actions {
    display: table-cell;
    width: 230px;
    text-align: right;
}

.main_carrinho .itens .item .actions button.detalhes {
    margin-right: 15px;
    font-size: 14px;
    font-weight: bold;
    color: <?php echo $color_main_green ?>;
}

.main_carrinho .itens .item .actions a.editar,
.main_carrinho .itens .item .actions button.excluir {
    margin-left: 5px;
    font-size: 16px;
    color: #000;
}

.main_carrinho .itens .item .configuracoes {
    display: none;
    margin: 20px 0;
}

.main_carrinho .itens .item .configuracoes .label {
    width: 10%;
    float: left;
    font-size: 14px;
    font-weight: bold;
}

.main_carrinho .itens .item .configuracoes .dados {
    width: 83%;
    float: right;
    padding: 2% 3%;
    border: 1px solid #ccc;
    font-size: 14px;
}

.main_carrinho .itens .item .configuracoes .dados span {
    width: 50%;
    padding: 5px 0;
    float: left;
}


.checkout_carrinho {
    margin: 40px 0;
}

.checkout_carrinho<?php echo $clear_end; ?>

.checkout_carrinho .totals {
    width: 860px;
    float: right;
}

.checkout_carrinho .totals .col1 {
    width: 400px;
    float: left;
}

.checkout_carrinho .totals .col2 {
    width: 400px;
    float: right;
}

.checkout_carrinho .totals .total_box {
    padding: 20px 5%;
    margin-bottom: 40px;
    background: #f2f2f2;
    border-bottom: 1px solid #ccc;
    font-size: 14px;
    text-align: right;
}

.checkout_carrinho .totals .total_box span {
    width: 150px;
    float: right;
}

.checkout_carrinho .totals .total_box .total_geral {
    margin-top: 10px;
    font-size: 20px;
    font-weight: bold;
    color: <?php echo $color_main_green; ?>;
}

.checkout_carrinho .totals .total_box .descontos i {
    font-size: 13px;
    color: #3399cc;
    cursor: pointer;
}

.checkout_carrinho .totals .options {
    margin: 20px 0 20px 40px;
    padding-left: 70px;
    background-repeat: no-repeat;
    background-size: 45px auto;
    background-position: left center;
    font-size: 15px;
    font-weight: bold;
}

.checkout_carrinho .totals .options span {
    display: block;
    font-size: 18px;
    font-weight: normal;
    color: <?php echo $color_main_green ?>;
}

.checkout_carrinho .totals .options strong { font-size: 20px; }

.checkout_carrinho .totals .options.parcelamento { background-image: url('../img/icon_pagamentos_parcelamento.png'); }
.checkout_carrinho .totals .options.avista { background-image: url('../img/icon_pagamentos_avista.png'); }
.checkout_carrinho .totals .options.pokerstars { background-image: url('../img/icon_pagamentos_pokerstars.png'); }

.checkout_carrinho .totals a.bt_finalizar {
    display: inline-block;
    width: 100%;
    height: 60px;
    line-height: 60px;
    margin: 20px 0;
    background-color: <?php echo $color_main_green ?>;
    font-size: 24px;
    font-weight: bold;
    text-align: center;
    color: #fff;
}

.checkout_forms a { text-decoration: underline; color: #8f8f8f; }

.checkout_forms .label {
    font-size: 16px;
    font-weight: bold;
    color: <?php echo $color_main_red ?>;
}

.checkout_forms .input {
    width: 200px;
    margin-top: 10px;
}

.checkout_forms .input .field {
    width: 160px;
    float: left;
}

.checkout_forms .input button {
    width: 40px;
    height: 37px;
    background-color: <?php echo $color_main_red ?>;
    font-size: 16px;
    font-weight: 600;
    text-align: center;
    color: #fff;
}

.checkout_forms .cupom-de-desconto {
    margin: 15px 0;
    font-size: 14px;
    font-weight: bold;
    color: <?php echo $color_main_green ?>;
}

.checkout_forms.form_cupom .label {
    color: #666;
}

.checkout_forms.form_cupom .open_hidden {
    cursor: pointer;
    font-size: 18px;
}

.checkout_forms.form_cupom .hidden {
    display: none;    
}

.checkout_forms.form_frete {
    width: 250px;
    float: left;
    padding-right: 50px;
}

.checkout_forms .resultados .option-frete {
    margin: 20px 0;
}

.checkout_forms .resultados .option-frete label {
    margin-bottom: 5px;
    cursor: pointer;
}

.checkout_forms .resultados .option-frete label span {
    display: inline-block;
    height: 30px;
    line-height: 30px;
    font-size: 15px;
    font-weight: bold;
    font-style: italic;
    color: <?php echo $color_main_red ?>;
}

.checkout_forms .resultados .option-frete label span img {
    display: inline-block;
    margin: 0 5px;
}

.checkout_forms .resultados .option-frete .valor,
.checkout_forms .resultados .option-frete .prazo {
    line-height: 20px;
    font-size: 14px;
    font-weight: bold;
}

.checkout_forms .resultados .option-frete .valor span { color: <?php echo $color_main_green; ?>; }
.checkout_forms .resultados .option-frete .prazo span { font-weight: normal; color: <?php echo $color_main_red; ?>; }


.remove_cupom {
    margin-right: 10px;
    font-size: 10px;
    font-weight: bold;
    letter-spacing: -1px;
    cursor: pointer;
    color:<?php echo $color_main_red ?>
}

.remove_cupom:before {
    content: '[ ';
    font-size: 12px;
}
.remove_cupom:after {
    content: ' ]';
    font-size: 12px;
}

/* =========== PÁGINA CADASTRO ======== */


.pessoa_fisica, .pessoa_juridica { display: none; }

.form_cadastro button.clickformsubmit {
    width: 100%;
    height: 45px;
    line-height: 45px;
    margin-top: 15px;
    background-color: <?php echo $color_main_green; ?>;
    font-size: 16px;
    font-weight: bold;
    text-align: center;
    color: #fff;
}


/* =========== PÁGINA CHECKOUT ======== */


button.checkout_login {
    display: block;
    width: 170px;
    height: 40px;
    float: left;
    background: #666;
    font-size: 14px;
    font-weight: bold;
    color: #fff;
}

button.checkout_resume {
    display: block;
    height: 40px;
    float: right;
    padding: 0 10px 0 40px;
    background: #666;
    font-size: 14px;
    font-weight: bold;
    color: #fff;
}

button.checkout_resume i { margin-left: 20px; font-size: 20px; vertical-align: bottom; }

#checkout_resume {
    position: absolute;
    display: none;
    width: 280px;
    right: 0;
    top: 50px;
    background: #fff;
    border: 1px solid #d2d2d2;    
    border-top: 4px solid <?php echo $color_main_red ?>;
    z-index: 102;
}

#checkout_resume:after {
    content: '';
    position: absolute;
    display: block;
    width: 0;
    height: 0;
    top: -18px;
    right: 25px;
    border: 7px solid;
    border-color: transparent;
    border-bottom-color: <?php echo $color_main_red ?>;
}

#checkout_resume .close {
    position: absolute;
    display: block;
    width: 20px;
    height: 20px;
    top: 10px;
    right: 9px;
    background: <?php echo $color_main_red ?>;
    font-size: 14px;
    font-weight: bold;
    text-align: center;
    color: #fff;
    z-index: 1;
}

#checkout_resume .header {
    height: 40px;
    line-height: 40px;
    border-bottom: 1px solid #d2d2d2;
    font-size: 14px;
    font-weight: bold;
    text-indent: 15px;
    color: #666;
}
#checkout_resume .itens .item {
    position: relative;
    height: 82px;
    padding: 20px 20px 20px 10px;
    border-bottom: 1px solid #d2d2d2;
    font-size: 13px;
}

#checkout_resume .itens .item .img {
    width: 80px;
    height: 80px;
    float: left;
    border: 1px solid #ddd;
}

#checkout_resume .itens .item .dados {
    position: relative;
    width: 165px;
    height: 82px;
    float: right;
}

#checkout_resume .itens .item .unid { display: none; font-weight: bold; }

#checkout_resume .itens .item .preco { 
    position: absolute;
    width: 100%;
    line-height: 20px;
    bottom: 0;
}

#checkout_resume .itens .item .preco .sem_desconto {
    display: none;
}
#checkout_resume .itens .item .preco .sem_desconto span {
    text-decoration: line-through;
}

#checkout_resume .itens .item .preco .excluir {
    position: absolute;
    display: block;
    width: 20px;
    height: 20px;
    line-height: 9px;
    right: 0;
    bottom: 0;
    font-size: 13px;
    font-weight: bold;
    text-align: center;
    color: #666;
}

#checkout_resume .total {
    line-height: 20px;
    padding: 20px;
    font-size: 13px;
    text-align: right;
}

#checkout_resume .total span {
    display: inline-block;
    width: 100px;
    float: right;
    font-size: 14px;
    font-weight: bold;
}

.checkout_panel {
    margin: 50px 0;
}
.checkout_panel<?php echo $clear_end ?>

.checkout_panel .etapa {
    width: 355px;
    float: left;
    font-size: 14px;
}

.checkout_panel .etapa.checkout_pagamentos {
    padding-bottom: 80px;
    background: url('../img/icon_cem_porcento_seguro.png') no-repeat left bottom;
    background-size: auto 55px;
}

.checkout_panel .etapa.checkout_cadastro .loggedin,
.checkout_panel .etapa.checkout_cadastro .loggedout {
    margin-bottom: 30px;
}

.checkout_panel .etapa .titulo {
    line-height: 30px;
    padding: 20px 0;
    margin-bottom: 40px;
    border-bottom: 1px solid #d2d2d2;
    font-size: 14px;
    font-weight: bold;
    color: <?php echo $color_main_red ?>;
}

.checkout_panel .etapa .titulo span {
    display: inline-block;
    width: 30px;
    height: 30px;
    line-height: 30px;
    margin-right: 15px;
    background: <?php echo $color_main_red ?>;
    font-weight: normal;
    text-align: center;
    color: #fff;
}

.checkout_panel .etapa:first-child { margin-right: 55px; }
.checkout_panel .etapa:last-child { float: right; }

form.metodo_pagamento .option_pgto {
    margin: 30px 0 30px 20px;
    font-size: 14px;
    background-repeat: no-repeat;
    background-position: left bottom;
}

form.metodo_pagamento .option_pgto.pgto_deposito {
    /*padding-bottom: 35px;*/
    /*background-image: url('../img/logo_metodo_pagamento_bradesco.png');*/
    background-size: auto 30px;
}

form.metodo_pagamento .option_pgto.pgto_boleto {
    /*padding-bottom: 25px;*/
    /*background-image: url('../img/logo_metodo_pagamento_boleto.png');*/
    background-size: auto 20px;
}

form.metodo_pagamento .option_pgto.pgto_cielo {
    padding-bottom: 55px;
    background-image: url('../img/logo_metodo_pagamento_cielo.png');
    background-size: auto 50px;
}

form.metodo_pagamento .option_pgto.pgto_pagseguro {
    /*padding-bottom: 30px;*/
    /*background-image: url('../img/logo_metodo_pagamento_pagseguro.png');*/
    background-size: auto 25px;
}

form.metodo_pagamento .option_pgto.pgto_pokerstars {
    padding-bottom: 35px;
    background-image: url('../img/logo_metodo_pagamento_pokerstars.png');
    background-size: auto 30px;
}

form.metodo_pagamento .option_pgto label {
    display: inline-block;
    line-height: 1;
    padding: 5px 0;
    cursor: pointer;
    font-size: 16px;
    font-weight: bold;
}

form.metodo_pagamento .option_pgto label input {
    float: left;
    margin: 0 0 0 -20px;
}


form.metodo_pagamento .option_pgto .valor { display: block; }

form.metodo_pagamento .option_pgto .opcoes_parcelas {
    margin: 10px 0;
}

form.metodo_pagamento .option_pgto .opcoes_parcelas .chamada {
    display: inline-block;
    cursor: pointer;
    font-size: 14px;
    font-weight: bold;
    text-decoration: underline;
    color: <?php echo $color_main_green ?>;
}

form.metodo_pagamento .option_pgto .opcoes_parcelas .chamada i {
    font-size: 18px;
}

form.metodo_pagamento .option_pgto .opcoes_parcelas ul {
    display: none;
    margin: 10px;
    font-size: 12px;
}

form.metodo_pagamento .opt_in {
    margin: 20px 0;
}

form.metodo_pagamento .opt_in label {
    font-size: 16px;
}

form.metodo_pagamento .opt_in input { margin-right: 5px; }

form.metodo_pagamento button.clickformsubmit {
    display: block;
    width: 100%;
    height: 50px;
    background: <?php echo $color_main_green ?>;
    font-size: 24px;
    font-weight: bold;
    color: #fff;
}


/* =========== PÁGINA LOGIN ======== */

.col-login {
    width: 270px;
    float: left;
}

.col-login .titulo, 
.col-cadastro .titulo {
    margin-bottom: 20px;
    font-size: 18px;
    color: #666;
}

.col-login .loginbox {
    font-size: 14px;
}

.col-login .loginbox .label { margin-bottom: 5px; }
.col-login .loginbox .label span { color: <?php echo $color_main_red ?>; }
.col-login .loginbox input { margin-bottom: 20px; }

.col-login .loginbox .esqueceu {
    display: block;
    margin-top: -10px;
    margin-bottom: 20px;
    font-size: 10px;
    text-decoration: underline;
    cursor: pointer;
}

.col-login .loginbox .clickformsubmit {
    width: 270px;
    height: 35px;
    line-height: 35px;
    padding: 0 20px;
    background: <?php echo $color_main_green ?>;
    font-size: 14px;
    font-weight: bold;
    color: #fff;
}

.col-cadastro {
    width: 590px;
    float: left;
    margin-left: 60px;
}

.col-cadastro a.link-cadastro {
    width: 270px;
    display: inline-block;
    height: 35px;
    line-height: 35px;
    padding: 0 20px;
    background: <?php echo $color_main_red ?>;
    font-size: 14px;
    font-weight: bold;
    text-align: center;
    color: #fff;
}

/* =========== PÁGINA FALE CONOSCO ======== */


.contact_infos {
    width: 350px;
    float: left;
}

.contact_infos .info {
    padding: 0 0 20px;
    margin-top: 30px;
    border-bottom: 1px solid #d2d2d2;
    font-size: 14px;
}

.contact_infos .info:first-child { margin-top: 0; }
.contact_infos .info:last-child { border-bottom: none; }

.contact_infos .info .title {
    height: 40px;
    line-height: 40px;
    padding-left: 30px;
    margin-bottom: 20px;
    background-repeat: no-repeat;
    background-position: left center;
    font-size: 16px;
    font-weight: bold;
    color: <?php echo $color_main_red ?>;
}

.contact_infos .info.contatos .title {
    background-image: url('../img/icon_contact_info_phone.png');
    background-size: 13px auto;
}

.contact_infos .info.horarios .title {
    background-image: url('../img/icon_contact_info_horario.png');
    background-size: 23px auto;
}

.contact_infos .info span {
    margin: 4px 0;
    display: block;
}

.contact_infos .info span a { font-size: 14px; font-weight: normal; color: #666; }

.contact_infos .info span.whatsapp,
.contact_infos .info span.skype {
    padding-left: 30px;
    background: url('../img/icon_televendas_whatsapp.png') no-repeat left 5px center;
    background-size: auto 18px;
}

.contact_infos .info span.skype {
    background-image: url('../img/icon_televendas_skype.png');
}

.form_contato {
    width: 750px;
    float: right;
}

.form_contato .clickformsubmit {
    display: inline-block;
    height: 40px;
    line-height: 40px;
    padding: 0 30px;
    background: <?php echo $color_main_green; ?>;
    font-size: 14px;
    font-weight: bold;
    color: #fff;
}



/* =========== PÁGINA HISTÓRICO ======== */


.tabela_pedidos {
    width: 100%;
    font-size: 14px;
}

.tabela_pedidos .thead,
.tabela_pedidos .tbody,
.tabela_pedidos .tbody .pedido {
    display: table;
    width: 100%;
}

.tabela_pedidos .thead,
.tabela_pedidos .tbody .pedido {
    padding: 10px 0;
    border-bottom: 1px solid #d2d2d2;
}

.tabela_pedidos .tbody .trow { display: table-row; }

.tabela_pedidos .tcell .mobile_caption { display: none; }

.tabela_pedidos .tcell.numero {
    display: table-cell;
    width: 15%;
    float: left;
}

.tabela_pedidos .tcell.data {
    display: table-cell;
    width: 20%;
    float: left;
}

.tabela_pedidos .tcell.entrega {
    display: table-cell;
    width: 18%;
    float: left;
}

.tabela_pedidos .tcell.status {
    display: table-cell;
    width: 30%;
    float: left;
}

.tabela_pedidos .tcell.valor {
    display: table-cell;
    width: 20%;
    float: left;
}

.tabela_pedidos .tcell.detalhes {
    display: table-cell;
    width: 14%;
    float: left;
    padding-right: 1%;
    text-align: right;
}


.tabela_pedidos .tbody .tcell .cell { font-weight: bold; }
.tabela_pedidos .tbody .tcell.valor .cell,
.tabela_pedidos .tbody .tcell.detalhes .cell button { font-weight: bold; color: <?php echo $color_main_green; ?>; }
.tabela_pedidos .tcell.status .bt_pagar {
    display: inline-block;
    height: 22px;
    line-height: 22px;
    padding:0 10px;
    background: <?php echo $color_main_green; ?>;
    font-size: 12px;
    font-weight: normal;
    color: #fff;
}


.tabela_pedidos .box_detalhes {
    display: none;
    margin: 10px 0;
}

.tabela_pedidos .box_detalhes .itens {
}

.tabela_pedidos .box_detalhes .itens .item {
    margin: 0 10px;
    padding: 20px;
    background: #fbfbfb;
    border-bottom: 1px solid #d2d2d2;
}

.tabela_pedidos .box_detalhes .itens .item.rastreio {
    background: #f0f0f0;
}

.tabela_pedidos .box_detalhes .itens .item .nome { font-weight: bold; }
.tabela_pedidos .box_detalhes .itens .item .quantidade { font-size: 12px; }

.tabela_pedidos .box_detalhes .itens .item .valor {
    margin-top: 10px;
    font-size: 12px;
    font-weight: bold;
    color: <?php echo $color_main_green; ?>;
}

.tabela_pedidos .box_detalhes .itens .item .valor span { text-decoration: line-through; } 

.tabela_pedidos .box_detalhes .itens .item .descricao { margin-top: 10px; }
.tabela_pedidos .box_detalhes .itens .item .descricao<?php echo $clear_end; ?>
.tabela_pedidos .box_detalhes .itens .item .descricao span {
    display: block;
    width: 50%;
    float: left;
    font-size: 12px;
}

.tabela_pedidos .box_detalhes .itens .item .descricao a { display: block; font-size: 14px; text-decoration: underline; color: #b72319; }

.tabela_pedidos .box_detalhes .resumo {
    background: #f4f4f4;
    margin: 0 10px;
    padding: 20px 40px;
}

.tabela_pedidos .box_detalhes .resumo<?php echo $clear_end; ?>

.tabela_pedidos .box_detalhes .resumo .box {
    width: 30%;
    float: left;
    margin-right: 3%;
}

.tabela_pedidos .box_detalhes .resumo .box .label {
    margin-bottom: 5px;
    font-weight: bold;
}

.tabela_pedidos .box_detalhes .resumo .box .subtotals {
    text-align: right;
}

.tabela_pedidos .box_detalhes .resumo .box .subtotals span {
    display: inline-block;
    width: 150px;
    float: left;
}

.tabela_pedidos .box_detalhes .resumo .box .total {
    margin-top: 5px;
    font-size: 16px;
    font-weight: bold;
    color: <?php echo $color_main_green; ?>;
}

/* =========== STYLES ======== */

.left { display:block; float:left !important; }
.right { display:block; float:right !important; }
.vam { vertical-align: middle; }
.clear { clear:both; }
.row:after {
    content: " ";
    display:block;
    clear:both;
}

.dtable { display: table; }
.trow { display: table-row; }
.tcell { display: table-cell; vertical-align: middle; }

.center { text-align:center; }
.tright, .tar { text-align: right; }
.relative { position: relative; }

.bt_style1 {
    display:inline-block;
    margin-top: 20px;
    padding:10px 20px;
    color: #fff;
    font-size: 16px;
    font-weight: bold;
    background:#79b54e;
    cursor:pointer;
}

.imghover:after {
    content: " ";
    position: absolute;
    width: 600px;
    height: 600px;
    left: 50%;
    top: 50%;
    margin:-300px 0 0 -300px;
    background: url("../img/img-hover.png") no-repeat center center;
    opacity: 0;
    -webkit-transition: .4s;
    -o-transition: .4s;
    transition: .4s;
}

.show_desktop,
.show_table,
.show_mobile { display: none; }

.editable_content {
    font-size:14px;
}

.editable_content a {
    color:#333;
    font-weight:bold;
}

.editable_content a:hover {
    text-decoration:none;
}

.editable_content ul li {
    list-style-position:inside;
    list-style:disc;
    margin-left:20px;
}
/*
.editable_content img{
    max-width: inherit;
    max-height: inherit;
}

.editable_content img{
    max-width: 100%;
    max-height: 100%;
}
*/

.editable_content ul li {
    list-style-position:inside;
    list-style:disc;
    margin-left:20px;
}

.editable_content img.imgFull {
    max-width: 100%;
    max-height: 100%;
    margin: 20px 0;
}

.editable_content img.imgRight {
    float: right;
    margin-left: 20px;
}

.editable_content img.imgLeft {
    float: left;
    margin-right: 20px;
}

.editable_content iframe.video-youtube {
    width: 100%;
    height: 620px;
}

.editable_content .bloco_menor {
    width: 850px;
    margin: 0 auto;
}






/* =========== SHARED COMPONENTS =========== */



/* GRIDS */

    .comp-grid-main {
        width: 1235px;
        margin: 0 auto;
    }
    
    .comp-grid-blog {
        width: 75%;
        float: left;
    }
    
    
    
    .comp-grid-blog-side {
        width: 23%;
        float: right;
        padding-left:2%;
    }
    
    
    .blog-list-side {
        
    }
    
    
    .blog-list-side .head {
        min-height:150px;
    }
    
    .blog-list-side .subtitulo {
        margin-top:10px;
        font-size: 14px;
    }
    
    .blog-ultimas {
        padding-top: 20px;
    }
    
    .blog-ultimas .ultimas {
        margin:10px 0 30px 0;
    }
    
    .blog-ultimas .ultimas img {
        margin-bottom:10px;
    }
    
    .blog-ultimas .ultimas h3 {
        font-size: 18px;
        font-weight: 300;
    }

    .comp-grid-main-in {
        width: 1180px;
        margin: 0 auto;
    }

    .comp-grid-aside-left {
        width: 210px;
        float: left;
    }
    

    .comp-grid-aside-main {
        width: 930px;
        float: right;
    }

    .comp-grid-row:after {
        content: " ";
        display:block;
        clear:both;
    }

    .comp-grid-half { width: 47%; }
    .comp-grid-half:nth-child(odd) { float: left; }
    .comp-grid-half:nth-child(even) { float: right; }

    .comp-grid-third {
        width: 32%;
        float: left;
        margin-right: 2%;
    }

    .comp-grid-third:nth-child(3n) {
        float: right;
        margin-right: 0;
    }

    .comp-grid-fourth {
        width: 25%;
        float: left;
    }


/* BANNERS */

    .comp-banners {
        position: relative;
        width: 100%;
        height: 400px;
    }

    .comp-banners .display,
    .comp-banners .display .banner {
        position: absolute;
        width: 100%;
        height: 100%;
        background-position: center top;
        background-size: auto 100%;
        background-repeat: no-repeat;
        overflow: hidden;
    }

    .comp-banners .display .banner { display: none; }
    .comp-banners .display .banner:first-child { display: block; }

    .comp-banners .time {
        position: absolute;
        width: 100%;
        height: 2px;
        bottom: 0;
        background: <?php echo $color_main_red ?>;
    }

    .comp-banners .numeros {
        position: absolute;
        width: 100%;
        height: 10px;
        bottom: 25px;
        text-align: center;
    }

    .comp-banners .numeros span {
        display: inline-block;
        width: 35px;
        height: 10px;
        margin: 0 3px;
        background: <?php echo $color_main_red ?>;
        border-radius: 10px;
        text-indent: -300px;
        overflow: hidden;
        cursor: pointer;
    }

    .comp-banners .numeros span.atual {
        background: #FFF;
    }

    .comp-banners .display .banner .box {
        position: absolute;
        width: 50%;
        top: 85px;
        color: #fff;
        font-size: 18px;
        font-weight: 300;
    }

    .comp-banners .display .banner .box .titulo {
        line-height: 1;
        font-size: 35px;
        font-weight: 600;
    }

    .comp-banners .display .banner .box .preco {
        margin-top: 30px;
    }

    .comp-banners .display .banner .box .preco span {
        display: block;
        font-size: 35px;
        font-weight: 600;
        letter-spacing: 2px;
    }

    .comp-banners .display .banner .box .btn {
        display: inline-block;
        height: 40px;
        line-height: 40px;
        padding: 0 40px;
        margin-top: 20px;
        background: <?php echo $color_main_red ?>;
        font-size: 18px;
        font-weight: 300;
        color: #fff;
    }

    .comp-banners .display .banner .box > div { opacity: 0; }


/* FORMS */
    
    .comp-forms {}

    .comp-forms-divisao {}

    .comp-forms-divisao-titulo {
        line-height: 30px;
        padding: 20px 0;
        margin-bottom: 30px;
        border-bottom: 1px solid #d1d1d1;
        font-size: 18px;
        font-weight: bold;
        color: <?php echo $color_main_red ?>;
    }

    .comp-forms-item { margin: 10px 0; }

    .comp-forms-bt_submit {
        height: 40px;
        line-height: 40px;
        padding: 0 20px;
        background: <?php echo $color_main_green; ?>;
        font-size: 14px;
        font-weight: bold;
        color: #fff;
    }

    .comp-forms-grid-once { width: 100%; float: left; }

    .comp-forms-grid-half,
    .comp-forms-grid-half-tablet,
    .comp-forms-grid-half-mobile { width: 49%; }

    .comp-forms-grid-half:nth-child(odd),
    .comp-forms-grid-half-tablet:nth-child(odd),
    .comp-forms-grid-half-mobile:nth-child(odd) { float: left; }

    .comp-forms-grid-half:nth-child(even),
    .comp-forms-grid-half-tablet:nth-child(even),
    .comp-forms-grid-half-mobile:nth-child(even) { float: right; }


    .comp-forms-grid-third,
    .comp-forms-grid-third-tablet,
    .comp-forms-grid-third-mobile {
        width: 32%;
        float: left;
        margin-right: 2%;
    }

    .comp-forms-grid-third:nth-child(3n),
    .comp-forms-grid-third-tablet:nth-child(3n),
    .comp-forms-grid-third-mobile:nth-child(3n) {
        float: right;
        margin-right: 0;
    }




/* WSOP PAGE */ 

.topo_wsop {
    height: 590px;
    padding-top: 50px;
    background: url('../img/bg_wsop.jpg');
}

.topo_wsop .video_box {
    width: 730px;
    float: left;
}

.topo_wsop .video_box .titulo {
    margin-bottom: 20px;
    font-size: 23px;
    font-weight: 800;
    color: #fff;
}

.topo_wsop .video_box .video {
    width: 100%;
    height: 410px;
    background: #000;
}

.topo_wsop .caixa_form {
    width: 400px;
    float: right;
    padding-top: 190px;
    margin-top: -40px;
    background: url('../img/logo_wsop_realpoker.png') no-repeat center top;
}

.topo_wsop .caixa_form .form_participe {
    width: 95%;
    margin: 10px auto;
    background: #ccc;
    border: 6px solid #444349;
}

.topo_wsop .caixa_form .form_participe .head {
    height: 36px;
    line-height: 36px;
    padding: 0 20px;
    background: #444344;
    font-size: 19px;
    color: #fff;
}

.topo_wsop .caixa_form .form_participe .miolo {
    padding: 10px 20px;
    font-size: 19px;
    color: #fff;
}

.topo_wsop .caixa_form .form_participe .miolo .row {
    margin: 10px 0;
}

.topo_wsop .caixa_form .form_participe .miolo .row label {
    display: block;
    width: 70px;
    height: 30px;
    line-height: 30px;
    float: left;
    margin-right: 5px;
    font-size: 16px;
    text-align: right;
    color: #212121;
}

.topo_wsop .caixa_form .form_participe .miolo .row input {
    width: 100%;
    width: -webkit-calc(100% - 97px);
    width: -moz-calc(100% - 97px);
    width: calc(100% - 97px);
    height: 30px;
    line-height: 30px;
    float: left;
}

.topo_wsop .caixa_form .form_participe .miolo .row select {
    width: 100%;
    width: -webkit-calc(100% - 75px);
    width: -moz-calc(100% - 75px);
    width: calc(100% - 75px);
    float: left;
}

.topo_wsop .caixa_form .form_participe .miolo .row button {
    width: 100%;
    width: -webkit-calc(100% - 75px);
    width: -moz-calc(100% - 75px);
    width: calc(100% - 75px);
    height: 44px;
    padding: 0 10px;
    float: right;
    background: #060;
    font-size: 25px;
    font-weight: 800;
    color: #fff;
}

.mesa_wsop .titulo {
    height: 100px;
    line-height: 100px;
    margin-bottom: 20px;
    background: #444349;
    font-size: 23px;
    color: #fff;
}

.mesa_wsop .titulo strong { font-weight: 800; }

.mesa_wsop .mesa_foto {
    width: 55%;
    float: left;
}

.mesa_wsop .mesa_texto {
    width: 40%;
    float: right;
    font-size: 16px;
    color: #1F1F1F;
}

.mesa_wsop .mesa_texto ul li {
    margin: 20px 0;
    padding-left: 25px;
    background:url('../img/check_verde.png') no-repeat left 4px;
}


.mesa_wsop .mesa_texto ul li:last-child {
    background: none;
}

.bonus_wsop {
    margin: 30px 0;
    padding: 50px 0 20px;
    background: #ddd;
    font-size: 16px;
}

.bonus_wsop .bonus_box {
    width: 50%;
    float: left;
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


.bonus_wsop .bonus_box .bonus_revista .img,
.bonus_wsop .bonus_box .bonus_bauer .img {
    height: 180px;
}

.bonus_wsop .bonus_box .titulo {
    margin: 10px 0 30px;
    font-size: 23px;
    color: #000;
}

.bonus_wsop .bonus_box .titulo strong { font-weight: 800; }

.bonus_wsop .certificado_wsop {
    width: 50%;
    float: left;
}

.wsop_operacao > .texto {
    padding: 20px 0;
    font-size: 16px;
    color: #000;
}

.wsop_operacao .preco {
    width: 500px;
    float: left;
    margin: 100px 0 50px;
    color: #2A6500;
}

.wsop_operacao .preco .texto {
    margin-bottom: 15px;
    font-size: 16px;
    color: #000;
}

.wsop_operacao .preco .line1 {
    font-size: 35px;
}

.wsop_operacao .preco .line2 {
    line-height: 40px;
    margin-top: -15px;
    font-size: 40px;
    font-weight: 800;
}

.wsop_operacao .preco .line2 span {
    font-size: 80px;
}

.wsop_operacao .preco .line3 {
    font-size: 27px;
}

.wsop_operacao .preco .line4 {
    margin-top: -10px;
    line-height: 35px;
    font-size: 20px;
}

.wsop_operacao .preco .line4 strong { font-size: 35px; }



.wsop_operacao .caixa_form {
    width: 660px;
    float: right;
    margin: 100px 0 50px;
}

.wsop_operacao .caixa_form .form_participe2 {
    width: 95%;
    margin: 10px auto;
    background: #ccc;
    border: 6px solid #444349;
}

.wsop_operacao .caixa_form .form_participe2 .head {
    height: 36px;
    line-height: 36px;
    padding: 0 20px;
    background: #444344;
    font-size: 19px;
    color: #fff;
}

.wsop_operacao .caixa_form .form_participe2 .miolo {
    padding: 10px 20px;
    font-size: 19px;
    color: #fff;
}

.wsop_operacao .caixa_form .form_participe2 .miolo .col1 {
    width: 55%;
    float: left;
}

.wsop_operacao .caixa_form .form_participe2 .miolo .col2 {
    width: 45%;
    float: left;
}

.wsop_operacao .caixa_form .form_participe2 .miolo .row {
    margin: 10px 0;
}

.wsop_operacao .caixa_form .form_participe2 .miolo .row label {
    display: block;
    width: 70px;
    height: 30px;
    line-height: 30px;
    float: left;
    margin-right: 5px;
    font-size: 16px;
    text-align: right;
    color: #212121;
}

.wsop_operacao .caixa_form .form_participe2 .miolo .row input {
    width: 100%;
    width: -webkit-calc(100% - 97px);
    width: -moz-calc(100% - 97px);
    width: calc(100% - 97px);
    height: 30px;
    line-height: 30px;
    float: left;
}

.wsop_operacao .caixa_form .form_participe2 .miolo .row select {
    width: 100%;
    width: -webkit-calc(100% - 75px);
    width: -moz-calc(100% - 75px);
    width: calc(100% - 75px);
    float: left;
}

.wsop_operacao .caixa_form .form_participe2 .miolo .row button {
    width: 100%;
    width: -webkit-calc(100% - 20px);
    width: -moz-calc(100% - 20px);
    width: calc(100% - 20px);
    height: 55px;
    padding: 0 10px;
    float: right;
    background: #060;
    font-size: 30px;
    font-weight: 800;
    color: #fff;
}