<?php 

session_start();

require_once 'sistema/System/Core/Loader.php';

use System\Core\Bootstrap;

require_once "_system.php";

$sistema = new _sys();

$nome = $sistema->DB_fetch_array("SELECT c.nome FROM tb_pedidos_pedidos p JOIN tb_clientes_clientes c ON c.id=p.id_cliente WHERE p.id = ".$_GET['id']);

$nome = $nome->rows[0]['nome'];
$nome = explode(" ", $nome);
$nome = $nome[0];


?>
<div class="inc_anexar_documentos">
    <button type="button" class="close">X</button>
	<div class="titulo">Olá <?php echo $nome ?>, Parabéns pela sua compra #<?php echo $_GET['id'] ?></div>
	<div class="transacao_autorizada"><i class="fa fa-check"></i> Transação autoriazada pelo banco.</div>
	<div class="info_documentacao"><i class="fa fa-exclamation-triangle" style="color:#f4c300;"></i> Envio de documentação necessário para confirmação de titularidade.</div>
	<div class="descricao_procedimento">
    	<p>Este é um procedimento anti-fraude para ter certeza que é o titular do cartão que está realizando a compra.</p>
    	<p>O não envio da documentação solicitada em até 2 dias úteis acarretará no cancelamento do pedido. <br> A nota fiscal será emitida em nme do(a) titular do cartão de crédito que efetuou o pagamento do pedido.</p>
    	<p>Caso tenha alguma dúvida sobre o procedimento, <strong>você pode entrar em contato por qualquer um dos nossos canais de atendimento.</strong></p>
	</div>
	<form class="form_anexar" enctype="multipart/form-data" action="javascript:;" method="post">
		<input type="hidden" name="id" value="<?php echo $_GET['id'] ?>">
		<div class="step">
			<div class="desc">1 - Foto da frente do cartão de crédito. <span>NÃO ENVIAR o código de verificação</span></div>
			<div class="documento">
	    		<div class="info">
	    			<strong>Por segurança você pode tempar os números centrais do cartão.</strong> Mostre os 4 últimos e os 4 primeiros digitos.  Se necessário enviar os dois lados (Nome do títular e Numeração).  Em caso de cartão virtual, pode enviar um print e enviar foto do cartão físico.
	    		</div>
	    		<div class="anexos">
	                <div class="input">
	                    <input type="file" name="upload-cartao-front" id="upload-cartao-front" class="inputfile" />
	                    <label for="upload-cartao-front" data-label="ANEXAR FRENTE" data-label2="FRENTE ANEXADA !"><i class="fa fa-upload fa-fw"></i> <span>ANEXAR FRENTE</span></label>
	                </div>
	                <div class="input">
	                    <input type="file" name="upload-cartao-back" id="upload-cartao-back" class="inputfile" />
	                    <label for="upload-cartao-back" data-label="ANEXAR VERSO" data-label2="VERSO ANEXADA !"><i class="fa fa-upload fa-fw"></i> <span>ANEXAR VERSO</span></label> <br> (Somente se necessário)
	                </div> 
	    		</div>
	    		<div class="clear"></div>
			</div>
		</div>
		<div class="step">
			<div class="desc">2 - Documento (CNH ou RG e CPF) do títular do cartão</div>
			<div class="documento">
	    		<div class="info">
	    			Lembre-se de retirar os documentos dos plásticos de proteção para fazer a foto.
	    		</div>
	    		<div class="anexos">
	                <div class="input">
	                    <input type="file" name="upload-cnh-front" id="upload-cnh-front" class="inputfile" />
	                    <label for="upload-cnh-front" data-label="ANEXAR FRENTE" data-label2="FRENTE ANEXADA !"><i class="fa fa-upload fa-fw"></i> <span>ANEXAR FRENTE</span></label>
	                </div>
	                <div class="input">
	                    <input type="file" name="upload-cnh-back" id="upload-cnh-back" class="inputfile" />
	                    <label for="upload-cnh-back" data-label="ANEXAR VERSO" data-label2="VERSO ANEXADA !"><i class="fa fa-upload fa-fw"></i> <span>ANEXAR VERSO</span></label> <br> (Somente se necessário)
	                </div> 
	    		</div>
	    		<div class="clear"></div>
			</div>
		</div>
		<div class="step">
			<div class="desc">3 - Enviar uma foto do títular com o documento pessoal ao lado do rosto para confirmação</div>
			<div class="documento">
	    		<div class="info">
	    			No mesmo formato solicitado pelos bancos digitais.  Em caso de CNH digital no aplicativo, deve ser a foto do celular ao lado do rosto no lugar do documento.
	    		</div>
	    		<div class="anexos">
	                <div class="input">
	                    <input type="file" name="upload-selfie" id="upload-selfie" class="inputfile" />
	                    <label for="upload-selfie" data-label="ANEXAR FOTO" data-label2="FOTO ANEXADA !"><i class="fa fa-upload fa-fw"></i> <span>ANEXAR FOTO</span></label>
	                </div>
	    		</div>
	    		<div class="clear"></div>
			</div>
		</div>
		<div class="action_buttons">
			<a href="#" class="bt_submit">ENVIAR PARA ANÁLISE</a> <a href="#" class="enviar_depois">ENVIAR DEPOIS</a>
		</div>
	</form>
</div>
<script>
	var inputs = document.querySelectorAll('.inputfile');
    Array.prototype.forEach.call(inputs, function (input)
    {
        var label = input.nextElementSibling;
        var labelVal1 = label.getAttribute("data-label");
        var labelVal2 = label.getAttribute("data-label2");

        input.addEventListener('change', function (e) {
            var fileName = '';
            if (this.files && this.files.length > 1)
                fileName = (this.getAttribute('data-multiple-caption') || '').replace('{count}', this.files.length);
            else
                fileName = e.target.value.split('\\').pop();

            if (fileName)
                label.querySelector('span').innerHTML = labelVal2;
            else
                label.querySelector('span').innerHTML = labelVal1;
        });
    });
</script>