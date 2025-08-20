<footer>
	<div class="selos">
		<div class="comp-grid-main-in comp-grid-row">
			<div class="pgmts">
				<div class="titulo">FORMAS DE PAGAMENTO</div>
				<span><img src="img/selo_cartoes_credito.jpg" alt="Cartão de Créditos"></span>
				<span><img src="img/selo_boleto_debito.jpg" alt="Boleto e Débito"></span>
				<span><img src="img/selo_pagseguro_pokerstars.jpg" alt="PagSeguro e Pokerstars"></span>
			</div>
			<div class="certificados">
				<div class="titulo">CERTIFICADOS DE SEGURANÇA</div>
                <span>
				    <a href="https://transparencyreport.google.com/safe-browsing/search?url=www.realpoker.com.br" target="_blank"><img  width="120px" style="margin-left: 10px
				    ;" src="img/selo_certificado_seguranca_google.png" alt="Cerificados de Segurança"></a>
                    <div class="reclameAqui" style="float: left;" id="ra-verified-seal"><script type="text/javascript" id="ra-embed-verified-seal" src="https://s3.amazonaws.com/raichu-beta/ra-verified/bundle.js" data-id="Nzc4NDA6cmVhbC1wb2tlcg==" data-target="ra-verified-seal" data-model="2"></script></div>
			    </span>
            </div>



		</div>
	</div>
	<div class="newsletter">
		<div class="comp-grid-main-in comp-grid-row">
			<div class="label">RECEBA NOVIDADES E PROMOÇÕES</div>
			<form class="form_newsletter" action="javascript:;" method="post">
				<input name="email" id="email" placeholder="Digite aqui o seu e-mail:">
				<button type="button" class="clickformsubmit">CADASTRAR</button>
			</form>
		</div>
	</div>
	<div class="rodape">
		<div class="comp-grid-main-in comp-grid-row">
	    	<div class="televendas">
	    		<div class="comp-grid-row">
			        <div class="box brasil">
			            <div class="titulo">CONTATO NO BRASIL</div>
			            <?php foreach ($whatsapp_numbers as $whatsapp): ?>
			            	<span class="whatsapp">WhatsApp: <a href="<?php echo $whatsapp['link']; ?>" target="_blank"><?php echo $whatsapp['number']; ?></a></span>
			            <?php endforeach ?>
				        <span>Belém</span>
				        <span>Belo Horizonte</span>
				        <span>Brasília</span>
				        <span>Campinas</span>
				        <span>Curitiba</span>
				        <span>Goiânia</span>
				        <span>Porto Alegre</span>
				        <span>Recife</span>
				        <span>Rio de Janeiro</span>
				        <span>Salvador</span>
				        <span>Santos</span>
				        <span>São Paulo</span>
				        <span>São José</span>
			        </div>
			        <div class="box america">
			            <div class="titulo">CONTATO EM OUTROS PAÍSES</div>
			            <div class="col">
			                <span>Amsterdam: <a href="#">(soon)</a></span>
			                <span>Buenos Aires: <a href="#">(luego)</a></span>
			                <span>Las Vegas: <a href="#">(soon)</a></span>
			                <span>Mexico City: <a href="#">(luego)</a></span>
			                <span>Portugal: <a href="#">(em breve)</a></span>
							<br><br><br>



			                <span style="height: auto;"> <img src="/img/selo-sustentavel.png"></span>
			                <br><br>
			                <span> <img src="/img/lang_br.png" alt="Fabricação" width="21px" /> Fabricação Própria </span>

			            </div>
			        </div>
		        </div>
	    		<div class="comp-grid-row">
	    			<div class="horario">Horário de atendimento das 8:00 às 11:00 e das 13:00 às 18:00</div>
		        </div>
	    	</div>
	    	<div class="redes_sociais">
	    		<div class="titulo">SIGA NOSSAS REDES SOCIAIS</div>

	    		<div class="instagram"><a href="<?php echo $empresa['link_instagram'] ?>" target="_blank">@real_poker</a></div>
	    		<div class="tiktok"><a href="https://www.tiktok.com/@real_poker" target="_blank">@real_poker</a></div>
	    		<div class="youtube"><a href="https://www.youtube.com/RealPoker?sub_confirmation=1" target="_blank">/RealPoker</a></div>
	    		<div class="facebook"><a href="https://www.facebook.com/realpoker/" target="_blank">/RealPoker</a></div>
	    		<div class="linktree"><a href="https://linktr.ee/realpoker" target="_blank">/RealPoker</a></div>
	    		<div class="blogger"><a href="/blog" target="_blank">Blog</a></div>

	    	</div>
        </div>
	</div>
	<div class="nav">
		<div class="comp-grid-main-in comp-grid-row">
			<nav>

				<li><a href="blog">Blog Real Poker</a></li>
				<li><a href="seguranca-privacidade">Segurança e Privacidade</a></li>
				<li><a href="<?php echo $sistema->seo_dynamic_pages['institucional'][117]['seo_url']; ?>">Trocas e Reparos</a></li>
				<li><a href="prazo-entrega">Prazo de Entrega</a></li>
				<li><a href="institucional">Quem Somos</a></li>
				<li><a href="como_comprar">Como Comprar</a></li>
			</nav>
		</div>
	</div>
	<div class="copyright">
		Todos os direitos reservados. Consulte nossas políticas de segurança, privacidade, trocas e entrega.
		<br>
		Av. Madrid, Qd. 90 Lt. 5, St. Faiçalville, Goiânia/GO - CEP: 74.350-730
		<br>
		Real Poker Mesas e Fichas Personalizadas Eireli - CNPJ: 20.087.324/0001-16

	</div>
</footer>
<div id="fb-root"></div>
<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/pt_BR/sdk.js#xfbml=1&version=v2.5&appId=125450334231550";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>

<script src="js/jquery.js"></script>
<script src="js/jquery.clickform.js"></script>
<script src="js/jquery.inputmask.bundle.min.js"></script>
<script src="js/jquery.redirect.js"></script>
<script src="js/jquery.popup.js"></script>
<script src="js/jquery.popupoverlay.js"></script>
<script src="js/scripts.js?t=<?php echo uniqid(); ?>"></script>

<!-- SEO SCRIPTS -->
<?php
if (isset($currentpage['seo_scripts']) && $currentpage['seo_scripts'] != "") {
    echo $sistema->stripslashes($currentpage['seo_scripts']);
}
if ($scripts->num_rows) {
    echo $sistema->stripslashes($scripts->rows[0]['script']);
}
?>

<?php if ($pagina != "blog" && $pagina != "post"): // inicializa chat para todas as páginas menos para blog e post ?>

    <a href="<?php echo $whatsapp_numbers[0]['link'] ?>"  target="_blank"><div class="whatsapp_bar2"><div class="icon"></div><div class="desktop">Whatsapp <span>(on-line)</span><strong> <br><?php echo $whatsapp_number; ?></strong></div><div class="mobile"><strong>Abrir <span>(on-line)</span></strong>Whatsapp</div></div></a>

<?php endif ?>

<?php if (isset($_GET['popupcielocard'])): ?>
	<script>

		$(document).ready(function(){
	        $('body').popup({'url': '_inc_cartao_rede.php', 'width': 300, 'height': 300, 'closebutton': '.bt_fechar'},function(){

	            $(".form_cartao_cielo").clickform({'validateUrl': 'scripts/form-rede-transparent.php', 'submitButton': '.clickformsubmit', 'blockScroll': true}, function (data) {
	                if (data.type == "success") {
	                    //$.redirect('pagamento', {'pedido': data.pedido, 'metodo': data.metodo, 'id_cliente': data.id_cliente});
	                }
	            });

	        });
		});

	</script>
<?php endif ?>

<?php include '_inc_seo_acessos.php'; ?>
