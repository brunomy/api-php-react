

            <?php if ($total > $itens) : ?>
                <div class="paginacao">

                    <a href="<?php echo $currentpage['seo_url'] . $complemento . "/" . $sistema->pagination_tag . "/1"; ?>"><i class="fa fa-angle-double-left"></i></a>

                    <?php
                    $pag = $sistema->getParameter('pg');
                    $a = $pag - 1;
                    $p = $pag + 1;

                    if ($a < 1)
                        $a = 1;

                    if ($p > $pagination->pages_total)
                        $p = $pagination->pages_total;
                    ?>

                    <a href="<?php echo $currentpage['seo_url'] . $complemento . "/" . $sistema->pagination_tag . "/" . $a; ?>" title="Anterior"><i class="fa fa-angle-left"></i></a>

                    <?php
                    $c =  0;
                    for ($i = $pagination->range_initial_number; $i <= $pagination->range_end_number; $i++) {
                        if ($i > 0) {
                            $c++;

                            if ($c < 9)
                                $c = "0" . $c;
                            ?>
                            <a href="<?php echo $currentpage['seo_url'] . $complemento . "/" . $sistema->pagination_tag . "/" . $i; ?>" class="<?php if ($pagination->page_current == $i) echo "active"; ?>"><?php echo $c; ?></a>

                        <?php
                    }
                }// end for()
                ?>

                    <a href="<?php echo $currentpage['seo_url'] . $complemento . "/" . $sistema->pagination_tag . "/" . $p; ?>" title="Próximo"><i class="fa fa-angle-right"></i></a>
                    <a href="<?php echo $currentpage['seo_url'] . $complemento . "/" . $sistema->pagination_tag . "/" . $pagination->pages_total; ?>"><i class="fa fa-angle-double-right"></i></a>
                </div>
            <?php endif; ?>