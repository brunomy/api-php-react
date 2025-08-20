<?php
    $menus = $sistema->DB_fetch_array("SELECT * FROM tb_links_links A WHERE A.id_pai IS NULL AND A.stats = 1 ORDER BY A.ordem");

    function getSubmenus($id) {
        global $sistema;
        $menus = $sistema->DB_fetch_array("SELECT * FROM tb_links_links A WHERE A.stats = 1 AND A.id_pai = $id ORDER BY A.ordem");
        return $menus;
    }

    $produtos_carrinho = $sistema->DB_fetch_array("SELECT A.*, B.icone, CONCAT(C.seo_url_breadcrumbs,C.seo_url) seo_url, B.qtd_minima FROM tb_carrinho_produtos_historico A INNER JOIN tb_produtos_produtos B ON B.id = A.id_produto INNER JOIN tb_seo_paginas C ON C.id = A.id_seo WHERE A.session = '{$_SESSION["seo_session"]}' AND A.id_pedido IS NULL ORDER BY A.id DESC");

    $informacao_destaque = $sistema->DB_fetch_object("SELECT * FROM tb_informacao_destaque A WHERE A.id = 1")->rows[0];

//if($_SESSION['dev_path'] != "") echo "<pre>";print_r($_SESSION);echo "</pre>";
?>

<header>

    <?php if($informacao_destaque->ativo):  ?>
     <div class="barra_topo_casa" style="background: <?=$informacao_destaque->bg_color?>">
            <div class="comp-grid-main">
                <div class="titulo" style="color: <?=$informacao_destaque->cor_texto?>"><?=$informacao_destaque->texto?></div>
                <div class="descricao" style="color: <?=$informacao_destaque->cor_descricao?>"><?=$informacao_destaque->descricao?></div>
                <a href="/poker-em-casa"><div class="botao" style="background-color: <?=$informacao_destaque->cor_botao?>;color: <?=$informacao_destaque->cor_texto_botao?>"><?=$informacao_destaque->texto_botao?></div></a>
            </div>
        </div>
    <?php endif ?>

    <div class="barra_topo1">
        <div class="comp-grid-main">
            <div class="configs"  <?php if($informacao_destaque->ativo) echo "style='top: 51px;'";?>>
                <div class="language">
                    <div class="selected">
                        <div class="flag"><img src="img/lang_br.png"></div>
                        <div class="lan">Português</div>
                        <i class="fa fa-caret-down"></i>
                    </div>
                    <div class="options">
                        <div class="option">
                            <div class="flag"><img src="img/lang_us.jpg" alt=""></div>
                            <div class="lan">English (soon)</div>
                        </div>
                        <div class="option">
                            <div class="flag"><img src="img/lang_es.jpg" alt=""></div>
                            <div class="lan">Español (luego)</div>
                        </div>
                    </div>
                </div>
                <div class="moeda">
                    <div class="selected">R$ <i class="fa fa-caret-down"></i></div>
                    <div class="options">
                        <div class="option">US$</div>
                    </div>
                </div>
            </div>
            <div class="wordseries" <?php if($informacao_destaque->ativo) echo "style='top: 99px;'";?>> FORNECEDOR OFICIAL <span>DE MESAS E FICHAS DO</span> <div class="bsop"><img src="img/logo-bsop.png" alt="Logomarca World Series"></div><div class="wsop"><img src="img/logo_world_series.png" alt="Logomarca World Series">CIRCUIT BRAZIL</div></div>
            <div class="features" <?php if (isset($_SESSION['cliente_logado'])) echo 'style="display:none"';?>>
                <a href="<?php echo $sistema->seo_pages['login']['seo_url']; ?>" class="login"><i class="fa fa-user fa-fw"></i> Login</a>
                <a href="<?php echo $sistema->seo_pages['cadastro']['seo_url']; ?>" class="cadastro"><i class="fa fa-arrow-right fa-fw"></i> Cadastre-se</a>
            </div>
            <div class="account" <?php if (isset($_SESSION['cliente_logado'])) echo 'style="display:block"';?>>
                <a href="<?php echo $sistema->seo_pages['minha-conta']['seo_url']; ?>"><i class="fa fa-user fa-fw"></i> Minha conta</a>
            </div>
        </div>
    </div>
    <div class="barra_topo2">
        <div class="comp-grid-main">
            <div class="logomarca"><a href=""><img src="<?php echo $sistema->getImageFileSized($empresa['logomarca'],370,141); ?>" alt="<?php echo $empresa['nome']; ?>"></a></div>
            <div class="carrinho">
                <?php $carrinho_geral = $product->getCarrinhoInfoTotal(); ?>
                <div class="resumo">Carrinho</div>
            </div>
            <div class="barra_busca">
                <div class="inputwrap">
                    <input type="text" name="busca" id="busca" placeholder="Digite o que você busca">
                    <a href="#"><svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="magnifying-glass" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="svg-inline--fa fa-magnifying-glass" style="line-height: 1;"><path fill="currentColor" d="M416 208c0 45.9-14.9 88.3-40 122.7L502.6 457.4c12.5 12.5 12.5 32.8 0 45.3s-32.8 12.5-45.3 0L330.7 376c-34.4 25.2-76.8 40-122.7 40C93.1 416 0 322.9 0 208S93.1 0 208 0S416 93.1 416 208zM208 352a144 144 0 1 0 0-288 144 144 0 1 0 0 288z" class=""></path></svg></a>
                </div>
            </div>
            <div class="barra_atendimentos">
                <a href="#" class="atendimento_televendas">
                    Tele Vendas
                    <span>passe o mouse</span>
                </a>
                <a href="<?php echo $whatsapp_numbers[0]['link']; ?>" target="_blank" class="atendimento_chat">
                    Atendimento
                    <span>via whatsapp</span>
                </a>
                <a href="institucional" class="atendimento_contato">
                    Quem Somos
                    <span>Institucional</span>
                </a>
            </div>
        </div>
    </div>
    <div class="barra_topo3">
        <div class="comp-grid-main">
            <div class="home"><a href="<?php echo $sistema->root_path;?>"><img src="img/icon_header_menu_home.png" alt="Home"></a></div>
            <button type="button" class="mobilenavbutton">MENU</button>
            <?php if ($menus->num_rows) : ?>
            <nav>
                <?php foreach ($menus->rows as $menu) :?>
                <?php
                    $classAberto = "";
                    $flagAberto = false;
                    if ($menu['menu_aberto']) {
                        $classAberto = "menu-aberto";
                    }
                ?>
                <?php if ($menu['link'] != "") : ?>
                    <li><a target="<?php echo $menu['target']; ?>" href="<?php echo $menu['link']; ?>"><?php echo $menu['nome']; ?></a></li>
                <?php else : ?>
                    <li class="<?php echo $classAberto;?>">
                        <span><?php echo $menu['nome']; ?></span>
                        <ul>
                        <?php
                            $submenus = getSubmenus($menu['id']);
                            if ($submenus->num_rows) : ?>
                            <?php foreach ($submenus->rows as $submenu) :
                                    if ($submenu['link'] != "") :?>
                                        <li><a target="<?php echo $submenu['target']; ?>" href="<?php echo $submenu['link']; ?>"><?php echo $submenu['nome']; ?></a></li>
                                    <?php else:?>
                                        <li>
                                            <span><?php echo $submenu['nome']; ?></span>
                                            <?php if ($classAberto != "") :?>
                                                <div class="img"><?php if ($submenu['imagem'] != ""): ?><img src="<?php echo $sistema->getImageFileSized($submenu['imagem'],300,165);?>" alt="<?php echo $submenu['nome']; ?>"><?php endif ?></div>
                                            <?php endif; ?>
                                            <ul>
                                            <?php
                                                $submenus = getSubmenus($submenu['id']);
                                                if ($submenus->num_rows) : ?>
                                                <?php foreach ($submenus->rows as $submenu) :
                                                        if ($submenu['link'] != "") :?>
                                                            <li><a target="<?php echo $submenu['target']; ?>" href="<?php echo $submenu['link']; ?>"><?php echo $submenu['nome']; ?></a></li>
                                                        <?php else:?>
                                                            <li>
                                                                <span><?php echo $submenu['nome']; ?></span>
                                                            </li>
                                                        <?php endif; ?>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </ul>
                                        </li>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </ul>
                    </li>
                <?php endif; ?>
                <?php endforeach; ?>
            </nav>
            <?php endif; ?>
        </div>
    </div>
    <div id="televendas">
        <button type="button" class="close">X</button>
        <div class="box brasil">
            <div class="titulo">CONTATO NO BRASIL</div>
            <?php foreach ($whatsapp_numbers as $whatsapp): ?>
                <span class="whatsapp">WhatsApp: <a href="<?php echo $whatsapp['link'] ?>" alt="_blank"><?php echo $whatsapp['number']; ?></a></span>
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
            </div>
        </div>
    </div>
    <div id="carrinho" class="mycart">
        <button type="button" class="close">X</button>
        <?php if ($produtos_carrinho->num_rows) : ?>
        <div class="itens">
            <?php foreach ($produtos_carrinho->rows as $carrinho) : ?>
            <?php $carrinho_produto = $product->getCarrinhoInfoByProduto($carrinho['id']); ?>
            <div class="item" data-id="<?php echo $carrinho['id']; ?>" data-qdte="<?php echo $carrinho['quantidade']; ?>" data-valor="<?php echo $carrinho_produto['valor']; ?>">
                <a href="<?php echo $carrinho['seo_url']; ?>/edit/<?php echo $carrinho['id']; ?>" class="img"><img src="<?php echo $sistema->getImageFileSized($carrinho['icone'],160,160); ?>" alt="<?php echo $carrinho['nome_produto']; ?>"></a>
                <div class="dados">
                    <div class="unid"><?php echo $carrinho['quantidade']; ?> Unidades</div>
                    <div class="nome"><?php echo '<span>('.$carrinho['quantidade'].')</span> '. $sistema->limitarPalavrasPorCaracteres($carrinho['nome_produto'],35); ?> </div>
                    <div class="preco">
                        <div class="sem_desconto">R$ 0,00</div>
                        <span class="valor_final">R$ 0,00</span>
                        <button type="button" class="excluir" data-id="<?php echo $carrinho['id']; ?>"><i class="fa fa-trash-o"></i></button>
                    </div>
                    <?php $descontos = $product->getDescontosByProduto($carrinho['id_produto']); ?>
                    <?php if ($descontos->num_rows) : ?>
                        <?php foreach ($descontos->rows as $desconto) : ?>
                            <?php if ($desconto['porcentagem'] == 1) {$desconto['porcentagem'] = "porcentagem";} else {$desconto['porcentagem'] = "fixo";} ?>
                            <input type="hidden" class="descontos" data-tipo="<?php echo $desconto['porcentagem'];?>" data-qtde="<?php echo $desconto['quantidade']; ?>" data-valor="<?php echo $desconto['valor']; ?>" data-id="<?php echo $desconto['id']; ?>">
                        <?php endforeach ;?>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach ; ?>
        </div>
        <div class="total">TOTAL: R$ <span class="valor_itens"><?php echo $sistema->formataMoedaShow($carrinho_geral['valor']); ?></span></div>
        <div class="actions">
            <a href="<?php echo $sistema->seo_pages['carrinho']['seo_url']; ?>" class="ver">VER CARRINHO</a>
            <a href="<?php echo $sistema->seo_pages['checkout']['seo_url']; ?>" class="finalizar">FINALIZAR COMPRA</a>
        </div>
        <?php endif; ?>
        <div class="vazio">
            Seu Carrinho Está Vazio.
        </div>
    </div>

    <?php if ($pagina=="checkout"): ?>
        <div id="login">
            <button type="button" class="close">X</button>
            <div class="titulo">ENTRE NA SUA CONTA</div>
            <form action="javascript:;" method="post" class="loginbox form_login">
                <div class="label">Endereço de e-mail <span>*</span></div>
                <input type="text" name="email" placeholder="Digite seu e-mail">
                <div class="label">Senha <span>*</span></div>
                <input type="password" name="senha" placeholder="Digite seu senha">
                <span class="esqueceu">Esqueci minha senha.</span>
                <button type="button" class="clickformsubmit">ENTRAR</button>
            </form>
        </div>
    <?php endif ?>
</header>
