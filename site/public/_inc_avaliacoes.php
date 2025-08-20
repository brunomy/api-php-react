
						<?php 

					        $itens = 3;
					        $range = 3;

	                    	isset($_GET['pg']) ? $pg = $_GET['pg'] : $pg = 1;

	                    	$order = 1;
	                    	if(isset($_GET['order'])){
	                    		$order = $_GET['order'];
	                    	}	                    	
                    		switch ($order){
                    			case "1":
                    				$orderby = "ORDER BY a.data DESC";
                    				break;
                    			case "2":
                    				$orderby = "ORDER BY a.data ASC";
                    				break;
                    			case "3":
                    				$orderby = "ORDER BY a.nota DESC";
                    				break;
                    			case "4":
                    				$orderby = "ORDER BY a.nota ASC";
                    				break;
                    		}

  
					        if (isset($_GET['id'])) {

								require_once __DIR__.'/sistema/System/Core/Loader.php';

								include "_system.php";

								$sistema = new _sys();

	                    		$total = $sistema->DB_num_rows("SELECT * FROM tb_produtos_avaliacoes WHERE id_produto = {$_GET['id']} AND stats = 1");

	    						$pagination = new pagination($itens,$total,$range,$pg);
        						
        						$avaliacoes = $sistema->DB_fetch_array("SELECT a.*, b.cidade, c.uf, DATE_FORMAT(a.data, '%d/%m/%Y') data, (SELECT SUM(nota) FROM tb_produtos_avaliacoes WHERE id_produto = {$_GET['id']}) soma FROM tb_produtos_avaliacoes a INNER JOIN tb_utils_cidades b ON a.id_cidade = b.id INNER JOIN tb_utils_estados c ON b.id_estado = c.id WHERE id_produto = {$_GET['id']} AND stats = 1 {$orderby} LIMIT ".$pagination->bd_search_starts_at.", ".$pagination->itens_per_page);

        						$id_produto = $_GET['id'];

					        }else{

					        	$total = $avaliacoes->num_rows;
	    						$pagination = new pagination($itens,$total,$range,$pg);

					        }

		                    $a = $pg - 1;
		                    $p = $pg + 1;

		                    if($a < 1) $a = 1;

		                    if($p > $pagination->pages_total) $p = $pagination->pages_total;

						?>

						<?php $j = 0; ?>
                        <?php foreach ($avaliacoes->rows as $avaliacao): ?>
                        	<?php if ($j < $itens): ?>
                                <div class="box_avaliacao">
                                    <div class="chamada">
                                        <span>"<?php echo $avaliacao['chamada'] ?>"</span>
                                        <ul class="estrelas">
                                            <?php 
                                                for ($i=0; $i < 5; $i++) { 
                                                    if(floor($avaliacao['nota']) <= $i){
                                                        $estrela = 'off';
                                                    }else{
                                                        $estrela = 'on';
                                                    }
                                            ?>
                                                    <li><img src="img/estrela_avaliacao_<?php echo $estrela; ?>.png" alt="Estrela"></li>
                                            <?php
                                                }
                                            ?>
                                        </ul>
                                    </div>
                                    <div class="corpo">
                                        <?php echo $avaliacao['avaliacao'] ?>
                                    </div>
                                    <div class="autor"><?php echo $avaliacao['nome'] .' ('.$avaliacao['cidade'].' - '.$avaliacao['uf'].') '.$avaliacao['data'] ?></div>
                                </div>
                        	<?php endif ?>
                        	<?php $j++; ?>
                        <?php endforeach ?>

                        <?php if ($total > $itens): ?>
                            <div class="paginacao">
                                <a href="#" data-params="id=<?php echo $id_produto; ?>&order=<?php echo $order; ?>&pg=1"><i class="fa fa-angle-double-left"></i></a>
                                <a href="#" data-params="id=<?php echo $id_produto; ?>&order=<?php echo $order; ?>&pg=<?php echo $a; ?>" title="Anterior"><i class="fa fa-angle-left"></i></a>
                                <?php for ($i = $pagination->range_initial_number; $i <= $pagination->range_end_number; $i++) { ?>
                                	<a href="#" data-params="id=<?php echo $id_produto; ?>&order=<?php echo $order; ?>&pg=<?php echo $i; ?>"<?php if ($pagination->page_current == $i) echo ' class="active"'; ?>><?php echo $i; ?></a>
                                <?php } ?>
                                <a href="#" data-params="id=<?php echo $id_produto; ?>&order=<?php echo $order; ?>&pg=<?php echo $p; ?>" title="Próximo"><i class="fa fa-angle-right"></i></a>
                                <a href="#" data-params="id=<?php echo $id_produto; ?>&order=<?php echo $order; ?>&pg=<?php echo $pagination->pages_total; ?>"><i class="fa fa-angle-double-right"></i></a>
                            </div>
                        <?php endif ?>
