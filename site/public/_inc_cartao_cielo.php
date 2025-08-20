<?php

session_start();

require_once 'sistema/System/Core/Loader.php';

use System\Core\Bootstrap;
use payments\cielo_transparente\CieloCheckoutTransparent;

require_once "_system.php";

$sistema = new _sys();

if(isset($_GET['id'])){
	$idPedido = $_GET['id'];

	$pedido = $sistema->DB_fetch_array('SELECT a.*, a.id pedido_id, b.id cliente_id, b.nome cliente_nome, b.email cliente_email, b.endereco cliente_endereco, b.numero cliente_numero, b.bairro cliente_bairro, b.complemento cliente_complemento, b.cep cliente_cep, c.cidade cliente_cidade, d.uf cliente_uf FROM tb_pedidos_pedidos a INNER JOIN tb_clientes_clientes b ON a.id_cliente = b.id INNER JOIN tb_utils_cidades c ON c.id = b.id_cidade INNER JOIN tb_utils_estados d ON c.id_estado = d.id WHERE a.id = '.$idPedido);

	$pedido = $pedido->rows[0];
}

?>
<div class="cartao_cielo">
	<form class="form_cartao_cielo" action="javascript:;" method="post">
		<input type="hidden" name="id_pedido" value="<?php echo $idPedido ?>">
		<?php if (!isset($idPedido)): ?>
			<div class="titulo">SEU PEDIDO NÃO FOI ENCONTRADO!</div>
		<?php else: ?>
			<div class="titulo">DADOS DO CARTÃO DE CRÉDITO</div>
			<div class="comp-grid-row">
				<div class="comp-forms-grid-once comp-forms-item">
					<label>
						<span>Nome impresso no cartão:</span>
						<input type="text" name="Holder">
					</label>
				</div>
			</div>
			<div class="comp-grid-row">
				<div class="comp-forms-grid-twothird-mobile comp-forms-item">
					<label>
						<span>Número do Cartão:</span>
						<input type="text" name="CardNumber" class="card_number">
					</label>
					<input type="hidden" name="Brand">
				</div>
				<div class="comp-forms-grid-third-mobile comp-forms-item">
					<label>
						<span>CVC:</span>
						<input type="text" name="SecurityCode" class='security_code'>
					</label>
				</div>
				<div class="clear"></div>
				<div class="card_brands">
					<img src="img/checkout_cielo_card_brand_visa.png" class="visa">
					<img src="img/checkout_cielo_card_brand_mastercard.png" class="mastercard">
					<img src="img/checkout_cielo_card_brand_elo.png" class="elo">
					<img src="img/checkout_cielo_card_brand_amex.png" class="amex">
					<img src="img/checkout_cielo_card_brand_diners.png" class="diners">
					<img src="img/checkout_cielo_card_brand_aura.png" class="aura">
				</div>
			</div>
			<div class="comp-grid-row">
				<div class="comp-forms-grid-half-mobile comp-forms-item">
					<label>
						<span>Data de Expiração:</span>
						<!--<input type="text" name="ExpirationDate">-->

						<select style="width:50px;" name="ExpirationMonth">
							<?php 
								for ($i=1; $i <= 12 ; $i++) { 
									$val = $i;
									if($val < 10) $val = "0".$val;
							?>
								<option value="<?php echo $val ?>"><?php echo $val ?></option>
							<?php 
								}
							?>
						</select>
						<select style="width:70px;" name="ExpirationYear">
							<?php
								$Y = date('Y');
								for ($i=$Y; $i < ($Y+10) ; $i++) { 
							?>
									<option value="<?php echo $i ?>"><?php echo $i ?></option>
							<?php
								}
							?>
						</select>
					</label>
				</div>
				<div class="comp-forms-grid-half-mobile comp-forms-item">
					<label>
						<span>Parcelas:</span>
						<select name="Installments">
							<option value="1">à vista (R$ <?php echo $sistema->formataMoedaShow($pedido['valor_final']) ?>)</option>
							<?php for($i=2;$i<=10;$i++){ ?>
								<option value="<?php echo $i ?>"><?php echo $i ?>x (R$ <?php echo $sistema->formataMoedaShow($pedido['valor_final']/$i) ?>)</option>
							<?php } ?>
						</select>
					</label>
				</div>
			</div>
			<div class="total">TOTAL A PAGAR: R$ <?php echo $sistema->formataMoedaShow($pedido['valor_final']) ?></div>
			<div class="comp-grid-row">
				<div class="comp-forms-grid-twothird-mobile comp-forms-item">
					<button type="button" class="clickformsubmit">FINALIZAR PAGAMENTO</button>
				</div>
				<div class="comp-forms-grid-third-mobile comp-forms-item">
					<button type="button" class="btn_cancel">CANCELAR</button>
				</div>
			</div>
		<?php endif ?>
	</form>
</div>

<script>
	
//https://github.com/moiplabs/moipjs


	var cards_class = ['visa','mastercard','elo','amex','aura','diners'];
	var temp_card_brand = '';
	$('input.card_number').inputmask({'mask': '9999.9999.9999.9999'});
	$('input[name=ExpirationDate]').inputmask({'mask': '99/9999'});

    $('input[name=CardNumber]').keyup(function () {
        
        cardnum = $(this).val().replace(/[^0-9.]/g, '');
        cardnum = cardnum.replace('.', '');


    	if(cardnum.length > 4){

	        $.getJSON( "scripts/card_validator.php", { cardnum: cardnum }, function(data){
	        	
	        	if(temp_card_brand != data[0]) {
	        		$('.card_brands').removeClass(temp_card_brand);
	        		$('.card_brands').addClass(data[0]);
	        	}
	        	
	        	if(data[1]){
	        		$('.card_number').addClass(data[0]);
	        		//$('.security_code').focus();
	        	}else{
	        		cards_class.forEach(function(card){
			    		$('.card_number').removeClass(card);
			    	});
	        	}

	        	temp_card_brand = data[0];


	        	$('input.card_number')[0].inputmask.remove();

	        	if(data[0] != 'amex' || data[0] != 'diners'){
	        		$('input.card_number').inputmask({'mask': '9999.9999.9999.9999'});
	        		$('input.security_code').inputmask({'mask': '999'});
	        	}

	        	switch(data[0]){
	        		case "visa":
	        			$('input[name=Brand]').val('Visa');
	        		break
	        		case "mastercard":
	        			$('input[name=Brand]').val('Master');
	        		break
	        		case "elo":
	        			$('input[name=Brand]').val('Elo');
	        		break
	        		case "amex":
	        			$('input[name=Brand]').val('Amex');
						$('input.card_number').inputmask({'mask': '9999.999999.99999'});
						$('input.security_code').inputmask({'mask': '9999'});
	        		break
	        		case "diners":
	        			$('input[name=Brand]').val('Diners');
						$('input.card_number').inputmask({'mask': '9999.999999.9999'});
	        		break
	        		case "aura":
	        			$('input[name=Brand]').val('Aura');
	        		break
	        	}

	        });

	    }else{
    		cards_class.forEach(function(card){
    			$('.card_brands').removeClass(card);
	    		$('.card_number').removeClass(card);
	    	});
	    }
    });

    $('input[name=SecurityCode]').keyup(function () {
        
        securecode = $(this).val().replace(/[^0-9.]/g, '');
        securecode = securecode.replace('.', '');

		$(this).removeClass('ok');

    	if(securecode.length > 1){

	        $.getJSON( "scripts/card_validator.php", { cardnum: cardnum, cvc: securecode }, function(data){
	        	
	        	if(data[2]){
	        		$('input.security_code').addClass('ok');
	        	}

	        });

	    }
    });

</script>
