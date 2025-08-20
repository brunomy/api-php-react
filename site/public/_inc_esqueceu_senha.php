
<div class="esqueceu_box">
	<div class="top_pattern"></div>
	<div class="bt_fechar bt_close">x</div>
	<div class="titulo">Recuperar Senha</div>
	<div class="texto">Informe seu email para recuperar sua senha.</div>
	<form class="form_esqueceu_senha" action="javascript:;" method="post">
		<input type="text" name="email" placeholder="Digite aqui seu e-mail"> <span class="button clickformsubmit">ENVIAR</span>
	</form>
	<div class="bottom_pattern"></div>
</div>

<script>
    $(".form_esqueceu_senha").clickform({'validateUrl': 'scripts/form-esqueci-minha-senha.php', 'submitButton': '.clickformsubmit', 'blockScroll': true, 'conversionUrl': '_load_form_conversion_script.php?id=8'}, function (data) {
        if (data.type == "success") {
            //window.location.reload(true);
            $("#email").val('');
        }
    });
</script>