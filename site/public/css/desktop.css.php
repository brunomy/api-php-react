<?php 
    include 'include.css.php';
?>



/* =========== HEADER CONTENT ======== */


header .barra_topo1 .features a,
header #carrinho .actions a,
header .barra_topo3 .home a,
header .barra_topo3 nav li,
header .barra_topo3 nav li span,
header .barra_topo3 nav li a,
.loginbox .clickformsubmit,
.produto-list .info a,
.pronta_entrega .produto-list,
.inputfile + label,
.barra_preco .comprar button,
.checkout_carrinho .forms .input button,
.checkout_carrinho .totals a.bt_finalizar,
.form_cadastro button.clickformsubmit,
form.metodo_pagamento button.clickformsubmit,
.paginacao a,
ul.aside_menu li a {
	-webkit-transition: .4s;
	-o-transition: .4s;
	transition: .4s;
}

header .barra_topo3 nav > li.menu-aberto:hover > ul > li:hover > ul > li > a:hover,
header .barra_topo3 nav > li.menu-aberto:hover > ul > li:hover > ul > li > span:hover{
	color: #000;
}

header .barra_topo1 .features a:hover { color: #ef5248; }

.paginacao a:hover,
ul.aside_menu li a:hover { background: #b7231a; color: #fff; }


header .barra_topo3 .home a:hover,
header .barra_topo3 nav > li:hover,
header .barra_topo3 nav > li > span:hover,
header .barra_topo3 nav > li > a:hover,
.checkout_carrinho .forms .input button:hover {
	background: #9c140c;
}

header .barra_topo3 nav > li > ul li:hover,
header .barra_topo3 nav > li > ul li:hover > span,
header .barra_topo3 nav > li > ul li a:hover {
	background: #9c140c;
	border-bottom-color: #9c140c;
	color: #fff;
}

header .barra_topo3 nav > li > ul li:hover > span:before {
	border-left-color: #fff;
}

header .barra_topo3 nav > li:hover > ul {
	display: block;
}

header .barra_topo3 nav > li:hover > ul > li:hover > ul {
	display: block;
}

header .barra_topo3 nav > li.menu-aberto:hover > ul > li:hover,
header .barra_topo3 nav > li.menu-aberto:hover > ul > li:hover span {
	background-color: #f9f9f9;
	color: #666;
}

header .barra_topo3 nav > li.menu-aberto:hover > ul > li:hover > ul > li > a,
header .barra_topo3 nav > li.menu-aberto:hover > ul > li:hover > ul > li > span {
	background-color: transparent;
	color: <?php echo $color_main_red ?>;
}

header #carrinho .actions a.finalizar:hover,
.loginbox .clickformsubmit:hover,
.produto-list .info a:hover,
.barra_preco .comprar button:hover,
.checkout_carrinho .totals a.bt_finalizar:hover,
.form_cadastro button.clickformsubmit:hover,
form.metodo_pagamento button.clickformsubmit:hover { background: #33601e; }

header #carrinho .actions a:hover,
.produto-list .info .opcoes .mesas a:hover,
.inputfile:focus + label,
.inputfile + label:hover { background: #333; }


/* =========== FOOTER CONTENT ======== */

footer {}


/* =========== COMUM ======== */





/* =========== PÁGINA HOME ======== */



.pronta_entrega .produto-list:hover {
    -webkit-box-shadow: 0px 0px 57px -24px rgba(0,0,0,0.49);
    -moz-box-shadow: 0px 0px 57px -24px rgba(0,0,0,0.49);
    box-shadow: 0px 0px 57px -24px rgba(0,0,0,0.49);
}


/* =========== STYLES ======== */

.show_desktop { display: block; }