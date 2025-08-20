<?php 

session_start();

require_once 'sistema/System/Core/Loader.php';

use System\Core\Bootstrap;

require_once "_system.php";

$sistema = new _sys();

$query = $sistema->DB_fetch_array("SELECT * FROM tb_clientes_clientes WHERE id = {$_SESSION['cliente_id']}");

$dados = $query->rows[0];

$nome = explode(" ", $dados['nome']);
$nome = $nome[0];
$hash = sha1($dados['email'].$dados['id']);

?>
<div class="exclusao">
	<div class="top_pattern"></div>
	<div class="bt_fechar bt_close"></div>
	<div class="main_content">
		<div class="titulo"><?php echo $nome; ?>, você tem certeza disso ?</div>
		<div class="mensagem">
			Ao excluir os seus dados, estaremos exluindo também a sua conta em conformidade com a LGPD.<br>
			Após confirmação não será possivel recuperar os seus dados.<br><br>
			Para confirmar a exclusão da sua conta digite o seu e-mail (<?php echo $dados['email'] ?>) no campo abaixo.
		</div>
		<form class="form_excluir" action="javascript:;" method="post">
			<input type="hidden" name="hash" value="<?php echo $hash; ?>">
			<input type="text" name="email" placeholder="Digite aqui seu e-mail"> <span class="button clickformsubmit">EXCLUIR</span>
		</form>
	</div>
	<div class="bottom_pattern"></div>
</div>