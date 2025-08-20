<?php
	$pagina = "produto";

    $produto = "";
    if ($sistema->getParameter('edit'))
        $produto = $sistema->getParameter('edit');

    //não é edição
    $currentpage = array();
    $query = $sistema->DB_fetch_array("SELECT A.*, IFNULL(A.qtd_minima, 1) qtd_minima, A.id id_produto, '' as id_personalizado, DATE_FORMAT(A.data, '%d/%m/%Y') data, B.seo_title, CONCAT(B.seo_url_breadcrumbs,B.seo_url) as seo_url, B.seo_keywords, B.seo_description FROM tb_produtos_produtos A INNER JOIN tb_seo_paginas B ON A.id_seo=B.id WHERE A.apagado != 1 AND A.stats=1 AND A.id_seo = $dynamic_id");
    if ($query->num_rows) {
        $currentpage = $query->rows[0];
        $fotos = $sistema->DB_fetch_array("SELECT * FROM tb_produtos_fotos A WHERE A.stats = 1 AND A.id_produto = {$currentpage['id']} ORDER BY A.ordem");
    } else {
        $query = $sistema->DB_fetch_array("SELECT C.*, A.*, IFNULL(C.qtd_minima, 1) qtd_minima, A.id id_personalizado, DATE_FORMAT(A.data, '%d/%m/%Y') data, B.seo_title, CONCAT(B.seo_url_breadcrumbs,B.seo_url) as seo_url, B.seo_keywords, B.seo_description FROM tb_produtos_personalizados A INNER JOIN tb_seo_paginas B ON A.id_seo=B.id INNER JOIN tb_produtos_produtos C ON C.id = A.id_produto WHERE  A.apagado != 1 AND A.id_seo = $dynamic_id");

        if ($query->num_rows) {
            $currentpage = $query->rows[0];
            $fotos = $sistema->DB_fetch_array("SELECT * FROM tb_produtos_fotos A WHERE A.stats = 1 AND A.id_produto = {$currentpage['id_produto']} ORDER BY A.ordem");
            $main_produto = $sistema->DB_fetch_array('SELECT *, CONCAT(b.seo_url_breadcrumbs,b.seo_url) as seo_url FROM tb_produtos_produtos a INNER JOIN tb_seo_paginas b ON a.id_seo = b.id WHERE a.id = '.$currentpage["id_produto"]);
            $main_produto = $main_produto->rows[0];
        }
    }

    if (!$query->num_rows) {
        Header( "HTTP/1.1 301 Moved Permanently" );
        Header( "Location: ".$sistema->site_url );
    }

    $custo = $currentpage['custo'] + $currentpage['frete_embutido'];

    $personalizado = false;
    if (isset($currentpage['id_personalizado']) && $currentpage['id_personalizado'] != "") {
        $personalizado = true;
        //$conjuntos = $sistema->DB_fetch_array("SELECT A.* FROM tb_produtos_conjuntos_atributos A INNER JOIN tb_produtos_atributos B ON B.id_conjunto_atributo = A.id WHERE A.id_produto = {$currentpage['id_produto']} GROUP BY A.id ORDER BY A.ordem");

        $id_produto = $currentpage['id_produto'];

    } else {
        //$conjuntos = $sistema->DB_fetch_array("SELECT A.* FROM tb_produtos_conjuntos_atributos A INNER JOIN tb_produtos_atributos B ON B.id_conjunto_atributo = A.id WHERE A.id_produto = {$currentpage['id']} GROUP BY A.id ORDER BY A.ordem");

        $id_produto = $currentpage['id'];
    }

    $conjuntos = $sistema->DB_fetch_array("SELECT A.* FROM tb_produtos_conjuntos_atributos A INNER JOIN tb_produtos_atributos B ON B.id_conjunto_atributo = A.id WHERE A.id_produto = {$id_produto} GROUP BY A.id ORDER BY A.ordem");

    $avaliacoes = $sistema->DB_fetch_array("SELECT a.*, b.cidade, c.uf, DATE_FORMAT(a.data, '%d/%m/%Y') data, (SELECT SUM(nota) FROM tb_produtos_avaliacoes WHERE id_produto = {$id_produto} AND stats = 1) soma FROM tb_produtos_avaliacoes a INNER JOIN tb_utils_cidades b ON a.id_cidade = b.id INNER JOIN tb_utils_estados c ON b.id_estado = c.id WHERE id_produto = {$id_produto} AND stats = 1 ORDER BY a.data DESC");

    $pontuacao = number_format($avaliacoes->rows[0]['soma']/$avaliacoes->num_rows, 1);

    $pagination = new pagination(1,$avaliacoes->num_rows,3,1);


    if ($produto != "") {
        //é edição

        $verificaUsuario = $sistema->DB_fetch_array("SELECT * FROM tb_carrinho_produtos_historico A INNER JOIN tb_seo_paginas B ON B.id = A.id_seo WHERE A.id = $produto AND A.session = '{$_SESSION["seo_session"]}'");
        if ($verificaUsuario->num_rows) {
            $currentpage = array();
            $query = $sistema->DB_fetch_array("SELECT A.*, H.id id_produto_carrinho, H.custo, H.quantidade, A.id id_produto, '' as id_personalizado, DATE_FORMAT(A.data, '%d/%m/%Y') data, B.seo_title, CONCAT(B.seo_url_breadcrumbs,B.seo_url) as seo_url, B.seo_keywords, B.seo_description FROM tb_produtos_produtos A INNER JOIN tb_seo_paginas B ON A.id_seo=B.id INNER JOIN tb_carrinho_produtos_historico H ON H.id_seo = A.id_seo  AND H.session = '{$_SESSION["seo_session"]}' WHERE A.apagado != 1 AND A.id_seo = $dynamic_id AND H.id = $produto");
            if ($query->num_rows) {
                $currentpage = $query->rows[0];
                $fotos = $sistema->DB_fetch_array("SELECT * FROM tb_produtos_fotos A WHERE A.stats = 1 AND A.id_produto = {$currentpage['id']} ORDER BY A.ordem");
            } else {
                $query = $sistema->DB_fetch_array("SELECT C.*, A.*, H.id id_produto_carrinho, H.custo, H.quantidade, A.id id_personalizado, DATE_FORMAT(A.data, '%d/%m/%Y') data, B.seo_title, CONCAT(B.seo_url_breadcrumbs,B.seo_url) as seo_url, B.seo_keywords, B.seo_description FROM tb_produtos_personalizados A INNER JOIN tb_seo_paginas B ON A.id_seo=B.id INNER JOIN tb_produtos_produtos C ON C.id = A.id_produto INNER JOIN tb_carrinho_produtos_historico H ON H.id_seo = A.id_seo AND H.session = '{$_SESSION["seo_session"]}' WHERE  A.apagado != 1 AND A.id_seo = $dynamic_id");
                if ($query->num_rows) {
                    $currentpage = $query->rows[0];
                    $fotos = $sistema->DB_fetch_array("SELECT * FROM tb_produtos_fotos A WHERE A.stats = 1 AND A.id_produto = {$currentpage['id_produto']} ORDER BY A.ordem");
                }
            }
        } else {
            header("Location: $sistema->root_path{$currentpage['seo_url']}");
        }

        $custo = $currentpage['custo'];

        function getAtributos($idConjunto, $personalizado = false){
            global $sistema, $currentpage, $produto;
            if ($personalizado) {

                $query = $sistema->DB_fetch_array("SELECT A.id, B.nome_conjunto, IFNULL(B.nome_atributo, A.nome) nome, A.imagem, B.texto, B.arquivo, B.cor, B.valor, A.descricao, A.ampliar_fotos, A.ordem, IFNULL(B.custo,A.custo) custo, IFNULL(B.selecionado, '0') selecionado, IFNULL(A.selecionado, '0') padrao, D.type FROM tb_produtos_atributos  A LEFT JOIN tb_produtos_personalizados_has_tb_produtos_atributos P ON P.id_atributo = A.id AND P.id_produto_personalizado = {$currentpage['id_personalizado']} LEFT JOIN tb_carrinho_atributos_historico B ON B.id_atributo = A.ID AND B.id_carrinho_produto_historico = $produto LEFT JOIN tb_carrinho_produtos_historico C ON C.id = B.id_carrinho_produto_historico AND C.session = '{$_SESSION["seo_session"]}' LEFT JOIN tb_produtos_atributos_tipos D ON D.id = A.id_tipo WHERE A.id_conjunto_atributo = $idConjunto GROUP BY A.id ORDER BY A.ordem");
            } else {
                $query = $sistema->DB_fetch_array("SELECT A.id, B.nome_conjunto, IFNULL(B.nome_atributo, A.nome) nome, A.imagem, B.texto, B.arquivo, B.cor, B.valor, A.descricao, A.ampliar_fotos, A.ordem, IFNULL(B.custo,A.custo) custo, IFNULL(B.selecionado, '0') selecionado, A.selecionado padrao, D.type FROM tb_produtos_atributos  A LEFT JOIN tb_carrinho_atributos_historico B ON B.id_atributo = A.ID AND B.id_carrinho_produto_historico = $produto LEFT JOIN tb_carrinho_produtos_historico C ON C.id = B.id_carrinho_produto_historico AND C.session = '{$_SESSION["seo_session"]}' LEFT JOIN tb_produtos_atributos_tipos D ON D.id = A.id_tipo WHERE A.id_conjunto_atributo = $idConjunto GROUP BY A.id ORDER BY A.ordem");
            }
            if ($query->num_rows)
                return $query;
        }

        function getDisable ($idAtributo) {
            global $sistema;
            $query = $sistema->DB_fetch_array("SELECT A.id_conjunto FROM tb_produtos_atributos_has_conjuntos_atributos A WHERE A.id_atributo = $idAtributo");
            $desabilitado = "";
            if ($query->num_rows) {
                $separador = "";
                foreach ($query->rows as $row) {
                    $desabilitado .= $separador.$row['id_conjunto'];
                    $separador = ",";
                }
            }

            return $desabilitado;
        }

    } else {
        //não é edição

        function getAtributos($idConjunto, $personalizado = false){
            global $sistema, $currentpage;
            if ($personalizado) {
                $query = $sistema->DB_fetch_array("SELECT IFNULL(B.selecionado, '0') selecionado, IFNULL(A.selecionado, '0') padrao, A.id, A.id_tipo, A.imagem, A.nome, A.descricao, A.ampliar_fotos, IFNULL(A.custo,'0') custo, C.type FROM tb_produtos_atributos A LEFT JOIN tb_produtos_personalizados_has_tb_produtos_atributos B ON B.id_atributo = A.id AND B.id_produto_personalizado = {$currentpage['id_personalizado']} LEFT JOIN tb_produtos_atributos_tipos C ON C.id = A.id_tipo WHERE A.id_conjunto_atributo = $idConjunto GROUP BY A.id ORDER BY A.ordem");
            } else {
                $query = $sistema->DB_fetch_array("SELECT A.*, A.selecionado padrao, B.type, IFNULL(A.custo,'0') custo FROM tb_produtos_atributos A LEFT JOIN tb_produtos_atributos_tipos B ON B.id = A.id_tipo WHERE A.id_conjunto_atributo = $idConjunto ORDER BY A.ordem");
            }
            if ($query->num_rows)
                return $query;
        }

        function getDisable ($idAtributo) {
            global $sistema;
            $query = $sistema->DB_fetch_array("SELECT A.id_conjunto FROM tb_produtos_atributos_has_conjuntos_atributos A WHERE A.id_atributo = $idAtributo");
            $desabilitado = "";
            if ($query->num_rows) {
                $separador = "";
                foreach ($query->rows as $row) {
                    $desabilitado .= $separador.$row['id_conjunto'];
                    $separador = ",";
                }
            }

            return $desabilitado;
        }
    }

    function getV($url) {
        $embed = explode("v=", $url);
        $embed = end($embed);
        $embed = explode("&", $embed);
        $embed = $embed[0];
        return $embed;
    }

    $clientes_vitrine = $sistema->DB_fetch_array("SELECT * FROM tb_clientes_vitrine WHERE stats = 1 ORDER BY ordem");

	include "_inc_headers.php";
?>
<script>
    var pagina_edit = "<?php echo $sistema->getParameter("edit"); ?>";
    var pagina_seo = "<?php echo $currentpage['seo_url']; ?>";
    var pesonalizado = <?php if($personalizado) echo 1; else echo 0; ?>;
    <?php if ($sistema->getParameter('setCarrinho')==1): ?>var setCarrinho = 1;<?php endif ?>
</script>
</head>
<body id="<?php echo $pagina; ?>"<?php if ($sistema->getParameter('setCarrinho')==1): ?> class="setCarrinho"<?php endif ?>>
<?php include "_inc_header.php"; ?>

<div class="breadcrumbs">
	<div class="comp-grid-main-in">
		<span><a href="<?php echo $sistema->root_path; ?>">Home</a></span>
		<span><a href="<?php echo $currentpage['seo_url'];?>"><?php echo $currentpage['nome'];?></a></span>
	</div>
</div>

<form enctype="multipart/form-data" method="post" class="form_produto">
    <input name="id_produto" type="hidden" value="<?php echo $currentpage['id_produto']; ?>">
    <input name="id_personalizado" type="hidden" value="<?php echo $currentpage['id_personalizado']; ?>">
    <input name="id_cliente" type="hidden" value="<?php // echo $_SESSION['id_cliente']; ?>">
    <input name="nome_produto" type="hidden" value="<?php echo $currentpage['nome']; ?>">
    <input name="produto" type="hidden" value="<?php echo $produto; ?>">
    <input name="custo" type="hidden" value="<?php echo $custo; ?>">
    <input name="id_seo" type="hidden" value="<?php echo $currentpage['id_seo']; ?>">
    <input name="retorno" type="hidden" value="<?php echo $sistema->seo_pages['carrinho']['seo_url']; ?>">
    <div class="comp-grid-main-in">
        <div class="produto">
            <?php if ($sistema->is_mobile): ?>
                <div class="main_info">
                    <h1><?php echo $currentpage['nome']; ?></h1>
                    <ul class="estrelas" style="display:inline-block;">
                        <?php
                            for ($i=0; $i < 5; $i++) {
                                if(floor($pontuacao) <= $i){
                                    $estrela = 'off';
                                    if($i < $pontuacao){
                                        $estrela = 'half';
                                    }
                                }else{
                                    $estrela = 'on';
                                }
                        ?>
                                <li><img src="img/estrela_avaliacao_<?php echo $estrela; ?>.png" alt="Estrela"></li>
                        <?php
                            }
                        ?>
                    </ul>
                    <span><?php echo $avaliacoes->num_rows; ?></span> avaliações <br><br>
                    <div class="img"><img src="<?php echo $sistema->getImageFileSized($currentpage['imagem'],790,435); ?>" alt="<?php echo $currentpage['nome']; ?>"></div>
                    <div class="descricao editable_content"><?php echo $sistema->trataTexto($currentpage['texto']); ?></div>
                    <?php if ($personalizado): ?>
                        <div class="fotos">
                            <a href="<?php echo $sistema->root_path.$main_produto['seo_url'];?>"><img src="img/banner_main_produto2.png" alt=""></a>
                        </div>
                    <?php else: ?>
                        <?php if ($fotos->num_rows) : ?>
                            <div class="fotos">
                                <div class="titulo">INSPIRE-SE: GALERIA DE IMAGENS</div>
                                <div class="roleta">
                                    <?php if (!empty($currentpage['video_desktop'])) : ?>
                                        <div class="video desktop" style="background-image: url(https://img.youtube.com/vi/<?=$currentpage['video_desktop']?>/0.jpg);background-size: cover"><div class="play"  data-izimodal-open="#modal-youtube-desktop"> </div></div>
                                        <div id="modal-youtube-desktop" class="modais" data-izimodal-transitionin="fadeInDown" data-izimodal-title="Youtube" data-izimodal-iframeURL="https://www.youtube.com/embed/<?=$currentpage['video_desktop']?>?autoplay=1&rel=0&showinfo=1"></div>
                                    <?php endif?>
                                    <?php if (!empty($currentpage['video_mobile'])) : ?>
                                        <div class="video mobile" style="background-image: url(https://img.youtube.com/vi/<?=$currentpage['video_mobile']?>/0.jpg);background-size: cover"><div class="play"  data-izimodal-open="#modal-youtube-mobile"> </div></div>
                                        <div id="modal-youtube-mobile" class="modais" data-izimodal-transitionin="fadeInDown" data-izimodal-title="Youtube" data-izimodal-iframeURL="https://www.youtube.com/embed/<?=$currentpage['video_mobile']?>?autoplay=1&rel=0&showinfo=1"></div>
                                    <?php endif?>
                                    <button type="button" class="prev"><img src="img/seta_galeria_left.png" alt="Anterior"></button>
                                    <button type="button" class="next"><img src="img/seta_galeria_right.png" alt="Próximo"></button>
                                    <div class="items">
                                        <?php $i = 1; foreach ($fotos->rows as $foto) : ?>
                                            <?php list($w, $h) = getimagesize($sistema->getImageFileSized($foto['url'],1200,800)); ?>
                                            <div class="item">
                                                <a href="<?php echo $sistema->getImageFileSized($foto['url'],1200,800); ?>" class='pswp1' title="<?php echo $foto['legenda']; ?>" data-index="<?php echo $i; ?>" data-width="<?php echo $w; ?>" data-height="<?php echo $h; ?>"><img src="<?php echo $sistema->getImageFileSized($foto['url'],150,150); ?>" alt="<?php echo $foto['legenda']; ?>"></a>
                                            </div>
                                        <?php $i++; endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endif ?>
                </div>
            <?php else: ?>
                <div class="main_info">
                    <div class="bloco-imagens">
                        <div class="img"><img src="<?php echo $sistema->getImageFileSized($currentpage['imagem'],790,435); ?>" alt="<?php echo $currentpage['nome']; ?>"></div>
                        <?php if ($personalizado): ?>
                            <div class="fotos">
                                <a href="<?php echo $sistema->root_path.$main_produto['seo_url'];?>"><img src="img/banner_main_produto2.png" alt=""></a>
                            </div>
                        <?php else: ?>
                            <?php if ($fotos->num_rows) : ?>
                                <div class="fotos">
                                    <div class="titulo">INSPIRE-SE: GALERIA DE IMAGENS</div>
                                    <div class="roleta">
                                        <?php if (!empty($currentpage['video_desktop'])) : ?>
                                            <div class="video desktop" style="background-image: url(https://img.youtube.com/vi/<?=$currentpage['video_desktop']?>/0.jpg);background-size: cover"><div class="play"  data-izimodal-open="#modal-youtube-desktop"> </div></div>
                                            <div id="modal-youtube-desktop" class="modais" data-izimodal-transitionin="fadeInDown" data-izimodal-title="Youtube" data-izimodal-iframeURL="https://www.youtube.com/embed/<?=$currentpage['video_desktop']?>?autoplay=1&rel=0&showinfo=1"></div>
                                        <?php endif?>
                                        <?php if (!empty($currentpage['video_mobile'])) : ?>
                                            <div class="video mobile" style="background-image: url(https://img.youtube.com/vi/<?=$currentpage['video_mobile']?>/0.jpg);background-size: cover"><div class="play"  data-izimodal-open="#modal-youtube-mobile"> </div></div>
                                            <div id="modal-youtube-mobile" class="modais" data-izimodal-transitionin="fadeInDown" data-izimodal-title="Youtube" data-izimodal-iframeURL="https://www.youtube.com/embed/<?=$currentpage['video_mobile']?>?autoplay=1&rel=0&showinfo=1"></div>
                                        <?php endif?>
                                        <button type="button" class="prev"><img src="img/seta_galeria_left.png" alt="Anterior"></button>
                                        <button type="button" class="next"><img src="img/seta_galeria_right.png" alt="Próximo"></button>
                                        <div class="items">
                                            <?php $i = 1; foreach ($fotos->rows as $foto) : ?>
                                                <?php list($w, $h) = getimagesize($sistema->getImageFileSized($foto['url'],1200,800)); ?>
                                                <div class="item">
                                                    <a href="<?php echo $sistema->getImageFileSized($foto['url'],1200,800); ?>" class='pswp1' title="<?php echo $foto['legenda']; ?>" data-index="<?php echo $i; ?>" data-width="<?php echo $w; ?>" data-height="<?php echo $h; ?>"><img src="<?php echo $sistema->getImageFileSized($foto['url'],150,150); ?>" alt="<?php echo $foto['legenda']; ?>"></a>
                                                </div>
                                            <?php $i++; endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endif ?>
                    </div>
                    <div class="bloco-textos">
                        <h1><?php echo $currentpage['nome']; ?></h1>
                        <ul class="estrelas" style="display:inline-block;">
                            <?php
                                for ($i=0; $i < 5; $i++) {
                                    if(floor($pontuacao) <= $i){
                                        $estrela = 'off';
                                        if($i < $pontuacao){
                                            $estrela = 'half';
                                        }
                                    }else{
                                        $estrela = 'on';
                                    }
                            ?>
                                    <li><img src="img/estrela_avaliacao_<?php echo $estrela; ?>.png" alt="Estrela"></li>
                            <?php
                                }
                            ?>
                        </ul>
                        <span><?php echo $avaliacoes->num_rows; ?></span> avaliações
                        <div class="descricao editable_content"><?php echo $sistema->trataTexto($currentpage['texto']); ?></div>
                        <div class="frete">
                            <div class="form">
                                <div class="label">Prazo de Entrega</div>
                                <a href="http://www.buscacep.correios.com.br/sistemas/buscacep/buscaCepEndereco.cfm" target="_blank">não sei meu CEP</a>
                                <div class="input">
                                    <div class="field"><input type="text" name="cep" id="cep" class="mask-cep" data-produto="<?php echo $currentpage['id_produto']; ?>" value="<?php if(isset($_SESSION['cep']) && $_SESSION['cep'] != "") echo $_SESSION['cep'];?>"></div>
                                    <button type="button" class="clickformfretesubmit">ok</button>
                                </div>
                                <p>*prazo após aprovação de arte e sujeito a confirmação na transportadora</p>
                            </div>
                            <div class="resultados">
                                
                            </div>
                            <div class="clear"></div>
                        </div>
                    </div>
                </div>
            <?php endif ?>
            <div class="clear"></div>
            <div class="curinga_info editable_content">
                <?php echo $sistema->trataTexto($currentpage['texto_adicional']);?>
            </div>

            <div class="comp-grid-row features">
                <div class="comp-grid-fourth two-two">
                    <div class="featured">
                        <div class="icon"><img src="img/icon_features_frete.png" alt="FRETE GRÁTIS BRASIL"></div>
                        <div class="info">
                            <span><?php echo $currentpage['titulo_box_frete']; ?></span>
                            <p><?php echo $currentpage['texto_box_frete']; ?></p>
                        </div>
                    </div>
                </div>
                <div class="comp-grid-fourth two-two">
                    <div class="featured">
                        <div class="icon"><img src="img/icon_features_parcelamento.png" alt="EM ATÉ 10X SEM JUROS"></div>
                        <div class="info">
                            <span>EM ATÉ 10X SEM JUROS</span>
                            <p>Todo site parcelado sem juros nos cartões. Aproveite.</p>
                        </div>
                    </div>
                </div>
                <div class="comp-grid-fourth two-two">
                    <div class="featured">
                        <div class="icon"><img src="img/icon_features_desconto.png" alt="5% DE DESCONTO"></div>
                        <div class="info">
                            <span>5% DE DESCONTO À VISTA</span>
                            <p>Desconto de 5% para compras à vista no boleto.</p>
                        </div>
                    </div>
                </div>
                <div class="comp-grid-fourth two-two">
                    <div class="featured">
                        <div class="icon"><img src="img/icon_features_cadeado.png" alt="SITE 100% SEGURO"></div>
                        <div class="info">
                            <span>SITE 100% SEGURO</span>
                            <p>Pode confiar, compre com tranquilidade.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php $custoAtributosSelecionados = 0; ?>
        <?php if ($conjuntos->num_rows) :?>
            <div class="personalizacoes">
                <div class="main_titles"><h2><?php echo $currentpage['titulo_itens_personalizacao']; ?></h2></div>
                <?php if ($personalizado): ?>
                    <button type="button" class="slidetoggle_conjuntos">EDITAR ITENS <span></span></button>
                <?php endif ?>
                <div class="conjuntos"<?php if($personalizado) echo " style='display:none;'"; ?>>
                    <?php $i = 1; foreach ($conjuntos->rows as $conjunto) : if($i<10) $i = "0".$i; ?>
                        <?php  $atributos = getAtributos($conjunto['id'], $personalizado); ?>

                        <div class="conjunto" data-nome="<?php echo $conjunto['nome']; ?>">
                            <div class="titulo"><span><?php echo $i; ?></span> <h2><?php echo $conjunto['nome']; ?></h2></div>
                            <div class="descricao"><?php echo $conjunto['descricao']; ?></div>
                            <div class="selection">
                                <div class="disabled-message"><i class="fa fa-exclamation-triangle"></i> <?php echo $conjunto['descricao_aviso_desabilitado'];?></div>
                                <select name="<?php echo $conjunto['id']; ?>">
                                    <?php foreach($atributos->rows as $atributo) :?>
                                        <?php $desabilitar = getDisable($atributo['id']); ?>
                                            <?php if ($atributo['selecionado'] == 1) :?>
                                                <option data-preco="<?php echo $atributo['custo']; ?>" <?php if ($atributo['type'] != "") echo 'data-type="'.$atributo['type'].'"'; ?> value="<?php echo $atributo['id']; ?>" selected="selected" <?php if ($desabilitar != "") :?> data-desabilitar="<?php echo $desabilitar ;?>" <?php endif;?> <?php if ($atributo['padrao'] == 1) :?>data-default="1" <?php endif; ?> data-descricao="<?php echo $atributo['nome']; ?>"><?php echo $atributo['nome']; ?> <?php if ($atributo['custo'] > 0) echo " + [R$ ".$sistema->formataMoedaShow($atributo['custo'])."]"; ?></option>
                                                <?php $custoAtributosSelecionados = $custoAtributosSelecionados + $atributo['custo']; ?>
                                            <?php else :?>
                                                <option data-preco="<?php echo $atributo['custo']; ?>" <?php if ($atributo['type'] != "") echo 'data-type="'.$atributo['type'].'"'; ?> value="<?php echo $atributo['id']; ?>" <?php if ($desabilitar != "") :?> data-desabilitar="<?php echo $desabilitar ;?>" <?php endif;?> <?php if ($atributo['padrao'] == 1) :?>data-default="1" <?php endif; ?> data-descricao="<?php echo $atributo['nome']; ?>"><?php echo $atributo['nome']; ?> <?php if ($atributo['custo'] > 0) echo " + [R$ ".$sistema->formataMoedaShow($atributo['custo'])."]"; ?></option>
                                            <?php endif; ?>
                                    <?php endforeach ; ?>
                                </select>
                                <?php foreach($atributos->rows as $atributo) :?>
                                    <?php if ($atributo['type'] == "text") : ?>
                                        <div class="box-selection type-text">
                                            <button type="button" class="close">X</button>
                                            <div class="option"><?php echo $atributo['nome']; ?></div>
                                            <div class="desc"><?php echo $atributo['descricao']; ?></div>
                                            <div class="input">
                                                <input type="text" name="texto-<?php echo $conjunto['id']; ?>" value="<?php if (isset($atributo['texto'])) : ?><?php echo $atributo['texto'];?><?php endif; ?>">
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($atributo['type'] == "file") : ?>
                                        <div class="box-selection type-file">
                                            <button type="button" class="close">X</button>
                                            <div class="option"><?php echo $atributo['nome']; ?></div>
                                            <div class="desc"><?php echo $atributo['descricao']; ?></div>
                                            <div class="input">
                                                <input type="file" name="arquivo-<?php echo $conjunto['id']; ?>" id="arquivo-<?php echo $conjunto['id']; ?>" class="inputfile" />
                                                <label for="arquivo-<?php echo $conjunto['id']; ?>" data-label="<?php echo $atributo['nome']; ?>"><i class="fa fa-upload fa-fw"></i> <span><?php echo $atributo['nome']; ?></span></label>
                                            </div>
                                            <?php if (isset($atributo['arquivo'])) : ?><?php echo $atributo['valor'];?><br><?php endif; ?>
                                            <input type="hidden" name="arquivo-nome-<?php echo $conjunto['id']; ?>" value="<?php if (isset($atributo['arquivo'])) : ?><?php echo $atributo['valor'];?><?php endif; ?>">
                                            <input type="hidden" name="arquivo-novo-<?php echo $conjunto['id']; ?>" value="<?php if (isset($atributo['arquivo'])) : ?><?php echo $atributo['arquivo'];?><?php endif; ?>">
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($atributo['type'] == "color") : ?>
                                        <div class="box-selection type-color">
                                            <button type="button" class="close">X</button>
                                            <div class="option"><?php echo $atributo['nome']; ?></div>
                                            <div class="desc"></div>
                                            <div class="input">
                                                <input type="text" name="cor-<?php echo $conjunto['id']; ?>" class="cp-spectrum" value="<?php if (isset($atributo['cor'])) : ?><?php echo $atributo['cor'];?><?php else: ?>rgb(0, 0, 0)<?php endif; ?>">
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                            <?php if ($atributos->num_rows) : ?>
                                <div class="imgs">
                                    <?php foreach($atributos->rows as $atributo) :?>
                                        <?php if ($atributo['selecionado'] == 1) :?>
                                            <span<?php if ($atributo['ampliar_fotos'] == 1) :?> data-ampliar="ampliar<?php echo $atributo['id'];?>"<?php endif; ?><?php if ($atributo['type'] == 'video') :?> data-video="<?php echo getV($atributo['descricao']);?>"<?php endif; ?> data-image="loaded" class="selected"><img src="<?php if ($atributo['imagem'] != "") echo $sistema->getImageFileSized($atributo['imagem'],210,142); ?>" alt="<?php echo $conjunto['nome'].": ".$atributo['nome']; ?>"></span>
                                        <?php else: ?>
                                            <span<?php if ($atributo['ampliar_fotos'] == 1) :?> data-ampliar="ampliar<?php echo $atributo['id'];?>"<?php endif; ?><?php if ($atributo['type'] == 'video') :?> data-video="<?php echo getV($atributo['descricao']);?>"<?php endif; ?> data-image="<?php if ($atributo['imagem'] != "") echo $sistema->getImageFileSized($atributo['imagem'],210,142); ?>"></span>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                                <div class="imgs-ampliadas">
                                    <?php foreach($atributos->rows as $atributo) :?>
                                        <?php if ($atributo['ampliar_fotos'] == 1): ?>
                                            <?php list($w, $h) = getimagesize($sistema->getImageFileSized($atributo['imagem'],1200,800)); ?>
                                            <a href="<?php echo $sistema->getImageFileSized($atributo['imagem'],1200,800); ?>" class="ampliar<?php echo $atributo['id'];?>" title="<?php echo $atributo['nome']; ?>" data-index="0" data-width="<?php echo $w; ?>" data-height="<?php echo $h; ?>"></a>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php $i++; endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="resumo_produto<?php if ($personalizado) echo ' personalizado';?>" <?php if (!$conjuntos->num_rows) echo 'style="display:none"';?>>
            <div class="main_titles">CONFIRMAÇÃO DOS ITENS QUE <strong>VOCÊ SELECIONOU</strong> <span class="toggle"><i class="fa fa-chevron-down" aria-hidden="true"></i></span></div>
            <!--<div class="nome">Mesas de Poker Profissional <br> Configurações:</div>-->
            <div class="configuracoes"> </div>
        </div>

        <?php if ($clientes_vitrine->num_rows) : ?>
            <div class="clientes">
                <div class="main_titles">ALGUNS DE NOSSOS CLIENTES</div>
                <div class="roleta">
                    <div class="setas">
                        <span class="left"><img src="img/seta_roleta_clientes_left.png" alt=""></span>
                        <span class="right"><img src="img/seta_roleta_clientes_right.png" alt=""></span>
                    </div>
                    <div class="items">
                        <?php foreach ($clientes_vitrine->rows as $cliente) :?>
                            <div class="item"><img src="<?php echo $sistema->getImageFileSized($cliente['imagem'],150,150);?>" alt="<?php echo $cliente['nome'];?>"></div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($avaliacoes->num_rows): ?>

            <div class="produto_avaliacoes">

                <span itemprop="aggregateRating" itemscope itemtype="http://schema.org/Product">
                    <meta itemprop="name" content="<?php echo $currentpage['nome']; ?>">
                    <meta itemprop="description" content="<?php echo $currentpage['seo_description']; ?>">
                    <span itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating">
                        <meta itemprop="reviewCount" content="<?php echo $avaliacoes->num_rows; ?>">
                        <meta itemprop="ratingValue" content="<?php echo $pontuacao; ?>">
                        <meta itemprop="bestRating" content="5">
                    </span>
                    <span itemprop="offers" itemscope itemtype="http://schema.org/Offer">
                        <meta itemprop="priceCurrency" content="BRL">
                        <meta itemprop="availability" content="http://schema.org/InStock">
                        <meta itemprop="price" content="<?php echo $custo ?>">
                        <meta itemprop="mainEntityOfPage" content="http://www.realpoker.com.br<?php echo $_SERVER['REQUEST_URI']; ?>" data-reactid="171">
                    </span>
                </span>

                <div class="main_titles">AVALIAÇÃO DOS CLIENTES QUE COMPRARAM
                    <div class="title_avaliacoes">
                        <ul class="estrelas">
                            <?php
                                for ($i=0; $i < 5; $i++) {
                                    if(floor($pontuacao) <= $i){
                                        $estrela = 'off';
                                        if($i < $pontuacao){
                                            $estrela = 'half';
                                        }
                                    }else{
                                        $estrela = 'on';
                                    }
                            ?>
                                    <li><img src="img/estrela_avaliacao_<?php echo $estrela; ?>.png" alt="Estrela"></li>
                            <?php
                                }
                            ?>
                        </ul>
                        <span><?php echo $avaliacoes->num_rows; ?></span> avaliações
                    </div>
                </div>
                <?php //echo "<pre>";print_r($avaliacoes);echo "</pre>"; ?>
                <button type="button" class="toggle">VER</button>
                <div class="clear"></div>
                <div class="avaliacoes_view">
                    <div class="main_avaliacao">
                        <div class="pontuacao"><?php echo $pontuacao; ?></div>
                        <ul class="estrelas">
                            <?php
                                for ($i=0; $i < 5; $i++) {
                                    if(floor($pontuacao) <= $i){
                                        $estrela = 'off';
                                        if($i < $pontuacao){
                                            $estrela = 'half';
                                        }
                                    }else{
                                        $estrela = 'on';
                                    }
                            ?>
                                    <li><img src="img/estrela_avaliacao_<?php echo $estrela; ?>.png" alt="Estrela"></li>
                            <?php
                                }
                            ?>
                        </ul>
                        <div class="count">(<?php echo $avaliacoes->num_rows; ?>)</div>
                    </div>
                    <div class="ordenacao">
                        <select name="ordenacao" id="ordenacao" data-id="<?php echo $id_produto; ?>">
                            <option value="1">Mais recentes</option>
                            <option value="2">Mais antigas</option>
                            <option value="3">Melhores</option>
                            <option value="4">Piores</option>
                        </select>
                    </div>
                    <a href="<?php echo $sistema->seo_pages['fale-conosco']['seo_url']; ?>" class="btn_avaliar">AVALIE ESSE PRODUTO</a>
                    <div class="clear"></div>
                    <div class="box_avaliacoes">
                        <?php include "_inc_avaliacoes.php"; ?>
                    </div>
                </div>
            </div>

        <?php endif ?>


        <?php if ($sistema->is_mobile): ?>
            <div class="frete">
                <div class="form">
                    <div class="label">Prazo de Entrega</div>
                    <a href="http://www.buscacep.correios.com.br/sistemas/buscacep/buscaCepEndereco.cfm" target="_blank">não sei meu CEP</a>
                    <div class="input">
                        <div class="field"><input type="text" name="cep" id="cep" class="mask-cep" data-produto="<?php echo $currentpage['id_produto']; ?>" value="<?php if(isset($_SESSION['cep']) && $_SESSION['cep'] != "") echo $_SESSION['cep'];?>"></div>
                        <button type="button" class="clickformfretesubmit">ok</button>
                    </div>
                    <p>*prazo após aprovação de arte e sujeito a confirmação na transportadora</p>
                </div>
                <div class="resultados">
                </div>
            </div>
        <?php endif ?>

        <div class="mobile_quantidade row">
            <div class="label">Digite a quantidade:</div>
            <div class="input"><input type="text" name="quantidade_mobile" value="<?php if (isset($currentpage['quantidade'])) echo $currentpage['quantidade']; else echo $currentpage['qtd_minima']; ?>" data-quantidade-minima="<?php echo $currentpage['qtd_minima']; ?>" data-valor-unitario="<?php echo $custo; ?>"></div>
        </div>

        <div class="mobile_valores">
            <div class="valor_unitario">VALOR UNITÁRIO <br> <span class="sem_desconto">R$ 0,00</span> <span class="valor_final">R$ 0,00</span></div>
            <div class="total">
                <div class="label">VALOR TOTAL</div>
                <div class="parcelamento">ATÉ 10X SEM JUROS DE <span>R$ 00,00</span></div>
                <div class="avista">OU <span>R$ 00,00</span></div>
            </div>
            <div class="descontos">
                <div class="avista">5% OFF À VISTA <span>R$ 00,00</span></div>
                <div class="pokerstars">POKERSTARS <span> US$ 00,00</span></div>
            </div>
        </div>

        <div class="mobile_comprar">
            <button type="button" class="add_cart clickformsubmit"><?php if ($produto != "") echo "ATUALIZAR"; else echo "COMPRAR"; ?></button>
        </div>

    </div>

    <div class="barra_preco fixa">
        <div class="comp-grid-main">
            <div class="quantidade">
                <div class="label">Digite a quantidade</div>
                <div class="input"><input type="text" name="quantidade" value="<?php if (isset($currentpage['quantidade'])) echo $currentpage['quantidade']; else echo $currentpage['qtd_minima']; ?>" data-quantidade-minima="<?php echo $currentpage['qtd_minima']; ?>" data-valor-unitario="<?php echo $custo; ?>"></div>
                <div class="valor_unitario">VALOR UNITÁRIO <br> <span class="sem_desconto">R$ 0,00</span> <span class="valor_final">R$ 0,00</span><div class="seta"><img src="img/setacarrinho.gif" alt=""></div></div>
                <?php $descontos = $product->getDescontosByProduto($currentpage['id_produto']); ?>
                <?php if ($descontos->num_rows) : ?>
                    <?php foreach ($descontos->rows as $desconto) : ?>
                        <?php if ($desconto['porcentagem'] == 1) {$desconto['porcentagem'] = "porcentagem";} else {$desconto['porcentagem'] = "fixo";} ?>
                        <input type="hidden" class="descontos" data-tipo="<?php echo $desconto['porcentagem'];?>" data-qtde="<?php echo $desconto['quantidade']; ?>" data-valor="<?php echo $desconto['valor']; ?>" data-id="<?php echo $desconto['id']; ?>">
                        <?php
                            if (!isset($desconto_valor)) {
                                if($currentpage['qtd_minima'] >= $desconto['quantidade']){
                                    if($desconto['porcentagem'] == "porcentagem"){
                                        $desconto_valor = $custo * $desconto['valor'] / 100;
                                    }else{
                                        $desconto_valor = $desconto['valor'];
                                    }
                                }
                            }
                        ?>
                    <?php endforeach ;?>
                <?php endif; ?>
                <?php if(!isset($desconto_valor)) $desconto_valor = 0; ?>
            </div>
            <div class="total">
                <div class="label">VALOR TOTAL</div>
                <div class="parcelamento">ATÉ 10X S/ JUROS DE <span>R$ 00,00</span></div>
                <div class="avista">OU <span>R$ 00,00</span></div>
            </div>
            <div class="descontos">
                <div class="avista">5% OFF À VISTA <span>R$ 00,00</span></div>
                <div class="pokerstars">POKERSTARS <span> US$ 00,00</span></div>
            </div>
            <div class="comprar">
                <button type="button" class="add_cart clickformsubmit"><?php if ($produto != "") echo "ATUALIZAR"; else echo "COMPRAR"; ?></button>
            </div>
        </div>
    </div>
    <div class="comp-grid-main-in">
        <?php if ($personalizado): ?>
            <?php
            $produtos_personalizados = $sistema->DB_fetch_array('SELECT a.id_produto, a.id, a.nome, a.imagem, CONCAT(b.seo_url_breadcrumbs,b.seo_url) seo_url, (e.custo + SUM(IFNULL(d.custo,0)) + e.frete_embutido) custo, e.qtd_minima FROM tb_produtos_personalizados a INNER JOIN tb_produtos_produtos e ON e.id = a.id_produto INNER JOIN tb_seo_paginas b ON a.id_seo = b.id INNER JOIN tb_produtos_personalizados_has_tb_produtos_atributos c ON a.id = c.id_produto_personalizado INNER JOIN tb_produtos_atributos d ON c.id_atributo = d.id WHERE a.apagado = 0 AND a.stats = 1 AND a.id_produto = '.$currentpage['id_produto'].' GROUP BY a.id ORDER BY custo');
            $produtos_personalizados = $produtos_personalizados->rows;

            if(count($produtos_personalizados)>4){
                $last_index = count($produtos_personalizados)-1;
                $produtos_relacionados = "";
                foreach ($produtos_personalizados as $key => $value) {
                    if ($currentpage['id_personalizado'] == $value['id']) {
                        if ($key==0) {
                            $produtos_relacionados = array(1,2,3);
                        }else if($key == $last_index){
                            $produtos_relacionados = array(($last_index-3),($last_index-2),($last_index-1));
                        }else if($key == $last_index-1){
                            $produtos_relacionados = array(($last_index-3),($last_index-2),$last_index);
                        }else if($key == $last_index-2){
                            $produtos_relacionados = array(($last_index-4),($last_index-3),$last_index);
                        }else{
                            $produtos_relacionados = array(($key-1),($key+1),($key+2));
                        }
                    }
                }
            ?>
                <div class="produtos_relacionados">
                    <div class="main_titles">PRODUTOS RELACIONADOS</div>
                    <div class="prods">
                        <?php foreach ($produtos_relacionados as $key => $val): ?>
                            <?php
                                $qr = $sistema->DB_fetch_array("SELECT * FROM tb_produtos_has_tb_descontos A INNER JOIN tb_produtos_descontos B ON B.id = A.id_desconto WHERE A.id_produto = ".$produtos_personalizados[$val]['id_produto']." ORDER BY B.quantidade LIMIT 1");
                                $valor = $produtos_personalizados[$val]['custo'];
                                if($qr->num_rows){
                                    if($produtos_personalizados[$val]['qtd_minima'] >= $qr->rows[0]['quantidade']){
                                        if($qr->rows[0]['porcentagem']){
                                            $valor = $valor - ($valor * $qr->rows[0]['valor'] / 100);
                                        }else{
                                            $valor = ($valor - $qr->rows[0]['valor']);
                                        }
                                    }
                                }
                            ?>
                            <div class="produto_box">
                                <div class="img"><img src="<?php echo $sistema->getImageFileSized($produtos_personalizados[$val]['imagem'],700,385); ?>" alt="<?php echo $produtos_personalizados[$val]['nome'] ?>"></div>
                                <div class="dados">
                                    <div class="nome"><?php echo $produtos_personalizados[$val]['nome'] ?></div>
                                    <div class="preco"><?php echo $sistema->formataMoedaShow($valor) ?></div>
                                    <a href="<?php echo $produtos_personalizados[$val]['seo_url'] ?>">DETALHES</a>
                                </div>
                            </div>
                        <?php endforeach ?>
                    </div>
                    <div class="clear"></div>
                </div>
            <?php }  ?>
        <?php endif ?>
    </div>
</form>
<?php
	include "_inc_footer.php";
	include "plugins/photoswipe/photoswipe.php";
	include "plugins/owl/owl.php";
	include "plugins/spectrum/spectrum.php";
	include "plugins/izimodal/izimodal.php";
?>
<?php if ($sistema->getParameter('setCarrinho')==1): ?><div class="redirect"></div><?php endif ?>
<script src="js/jquery.popup.js"></script>
<?php if ($personalizado): ?>
<script type="text/javascript">// <![CDATA[
var google_tag_params = {
ecomm_prodid: '<?php echo $currentpage["id_personalizado"]; ?>',
ecomm_pagetype: 'product',
ecomm_totalvalue: '<?php echo $custo - $desconto_valor + $custoAtributosSelecionados; ?>',
};
// ]]></script>
<?php endif ?>
</body>
</html>
