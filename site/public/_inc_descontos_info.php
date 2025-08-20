<?php

session_start();

if (!isset($_SESSION["seo_session"])) {
    $_SESSION["seo_session"] = uniqid();
}

require_once 'sistema/System/Core/Loader.php';

use System\Core\Bootstrap;
use classes\Product;

include "_system.php";

$product = new Product();

$descontos = $product->getCarrinhoProdutosHistoricoBySession($_SESSION["seo_session"]);

?>
<div class="dollarbox">
	<div class="bt_fechar"></div>
        <?php if ($descontos->num_rows) :?>
        <?php foreach ($descontos->rows as $desconto) :?>
	<p><?php echo $desconto['descricao_desconto'];?></p>
        <?php endforeach; ?>
        <?php endif;?>
</div>