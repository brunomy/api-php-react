<?php
    $pagina = "orcamento";

    // CURRENTPAGE DATA ---- //
    // $currentpage = $sistema->seo_pages[$pagina];
    // --------------------------------------------- //

    function avista($v) {
        // 5% de desconto
        return (float)$v * 0.95;
    }

    // Redirect para a home em qualquer erro
    $redirect = function() use ($sistema) {
        header("Location: {$sistema->root_path}");
        exit();
    };

    // Pega o code (hexadecimal -> decimal, e adiciona zeros à esquerda)
    if (!$sistema->getParameter('orcamento')) $redirect();
    $code = str_pad(
        hexdec($sistema->getParameter('orcamento')), 10, '0', STR_PAD_LEFT
    );

    // Pega o orçamento pelo code
    $query = $sistema->DB_fetch_array("SELECT 
                                            B.cep,
                                            A.*,
                                            DATE_FORMAT(A.orc_data, '%d/%m/%Y às %H:%i') data,
                                            B.nome cliente,
                                            C.nome vendedor,
                                            C.telefone
                                        FROM
                                            tb_pedidos_pedidos A
                                                LEFT JOIN
                                            tb_clientes_clientes B ON B.id = A.id_cliente
                                                LEFT JOIN
                                            tb_admin_users C ON C.id = A.id_vendedor
                                        WHERE
                                          A.code = '{$code}' LIMIT 1");



    if (!$query->num_rows) $redirect();
    $orcamento = $query->rows[0];

    // Pega os IDs de produtos pelo ID do orçamento
    $query = $sistema->DB_fetch_array("SELECT id, id_pedido, id_produto FROM tb_carrinho_produtos_historico WHERE id_pedido = {$orcamento['id']}");

    if (!$query->num_rows) $redirect();
    $prodIds = implode(',', array_map(function ($row) {
        return $row['id'];
    }, $query->rows));

    // Pega a lista de produtos com os dados necessários usando os IDs
    $produtos = $product->getCartProductsByPedido($orcamento['id'], $prodIds);

    // TODO: FIX - Cupom de porcentagem tem o valor alterado pelo frete
    $valor_total = (float)$orcamento['subtotal'] +
        (float)$orcamento['valor_frete'] - (float)$orcamento['descontos'];

    $cupom = 0;
    if ($orcamento['valor_cupom']) {
        if ($orcamento['tipo_cupom'] != 1)
            $cupom = $orcamento['valor_cupom'];
        else
            $cupom = (($valor_total * (float)$orcamento['valor_cupom']) / 100);
    }
    $subtotal_com_desconto = 0;
    $valor_total = $valor_total - $cupom;

    // Verifica se existe algum produto com desconto e se o orçamento foi editado
    $existeDesconto = false;
    $orcamentoEditado = false;
    if ($produtos->num_rows) {
        foreach ($produtos->rows as $produto) {
            if (isset($produto['desconto']) && $produto['desconto']) {
                $existeDesconto = true;
            }
            if (isset($produto['valor_editado']) && $produto['valor_editado'] != "") {
                $orcamentoEditado = true;
            }
        }
    }
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <base href="<?php echo $sistema->root_path; ?>" />
        <title>Orçamento de: <?php echo 'vendedor'; ?>, N.: <?php echo $orcamento['id']; ?> - Mesas de Poker Personalizadas - Real Poker</title>
        <meta charset="utf-8" />
        <link href="css/pedido.css" rel="stylesheet" />

        <style>
            img {
                max-width: 100%;
            }

            #orcamento {
                margin: auto;
                width: 100%;
                max-width: 1500px;
            }

            .background-cinza {
                background: #EFEFEF;
            }

            .cortado {
                text-decoration: line-through;
            }

            .desconto {
                color: red;
            }

            .total {
                color: green;
            }
        </style>

    </head>
    <body>
        <div id="orcamento">
            <p style="text-align: center;"><img alt="Header" src="/img/orcamento/header.png"></p>
            <div class="bordaTudo usersLsitarFinanItens" style="margin-top: 0;">
                <table cellpadding="0" cellspacing="0" width="100%">
                    <tbody>
                        <tr>
                            <td height="48" width="22%"><div align="left"><strong class="fontNegritoTudo">Orçamento: <?php echo $orcamento['id']; ?> - <?php echo $orcamento['cliente'];?> </strong></div></td>
                        </tr>
                    </tbody>
                </table>
                <table cellpadding="0" cellspacing="0" width="100%">
                    <tbody>
                        <tr>
                            <td height="48" width="22%"><div align="left"><strong class="fontNegritoTudo">DATA:</strong></div></td>
                            <td colspan="2" class="fontNegritoTudo" width="28%"><strong class="fontNegritoTudoBlue"><?php echo $orcamento['data']; ?></strong></td>
                            <td height="48" width="22%"><div align="left"><strong class="fontNegritoTudo">VENDEDOR (A):</strong></div></td>
                            <td colspan="2" class="fontNegritoTudo" width="28%"><strong class="fontNegritoTudoBlue"><?php echo $orcamento['vendedor']; ?></strong></td>
                        </tr>
                        <tr>
                            <td height="48" width="22%"><div align="left"><strong class="fontNegritoTudo">VALIDADE:</strong></div></td>
                            <td colspan="2" class="fontNegritoTudo" width="28%"><strong class="fontNegritoTudoBlue">5 dias</strong></td>
                            <!-- TODO: REFACTORAR - Telefone do cliente ou do vendedor? -->
                            <td height="48" width="22%"><div align="left"><strong class="fontNegritoTudo">TELEFONE:</strong></div></td>
                            <td colspan="2" class="fontNegritoTudo" width="28%"><strong class="fontNegritoTudoBlue"><?php echo $orcamento['telefone']; ?></strong></td>
                        </tr>
                    </tbody>
                </table>

                <br><br><br>
                <p style="text-align: center;"><img alt="Alguns de nossos clientes" src="/img/orcamento/clientes.png"></p>

                <table cellpadding="0" cellspacing="0" width="100%">
                    <tbody>
                        <tr>
                            <td class="separador">&nbsp;</td>
                        </tr>
                    </tbody>
                </table>
                <?php if ($produtos->num_rows) : ?>
                    <p style="text-align: center;"><img alt="Header" src="/img/orcamento/header.png"></p>
                    <table cellpadding="0" cellspacing="0" width="100%">
                        <tbody>
                            <tr>
                                <td colspan="8" height="48" width="22%"><div align="left"><strong class="fontNegritoTudo">Orçamento Resumido: <?php echo $orcamento['id']; ?> - <?php echo $orcamento['cliente'];?> </strong></div></td>
                            </tr>
                        </tbody>
                    </table>
                    <table cellpadding="0" cellspacing="0" width="100%">
                        <tbody>
                            <!-- Header -->
                            <tr>
                                <td width="5%" class="tal">Item</td>
                                <td width="40%" class="tal">Produto</td>
                                <td width="5%" class="tal">Qnt.</td>
                                <td width="10%" class="tal">Valor unitário</td>
                                <?php if ($existeDesconto) :?>
                                    <td width="10%" class="tal">Valor unitário com desconto</td>
                                <?php endif; ?>
                                <td width="10%" class="tal background-cinza">Total 10x nos cartões</td>
                                <td width="10%" class="tal">Valor unitário à vista</td>
                                <td width="10%" class="tal background-cinza">Total à vista</td>
                            </tr>
                            <!-- Lista de items -->
                            <?php foreach ($produtos->rows as $i => $produto) :?>
                                <?php 
                                    $valor_com_desconto = (float)$produto['valor_produto'] - (float)($produto['desconto'] ?? 0); 
                                    if($produto['valor_editado'] != "") $valor_com_desconto = $produto['valor_editado'];
                                    $subtotal_com_desconto = $subtotal_com_desconto + ($valor_com_desconto * $produto['quantidade']);
                                ?>
                                <tr>
                                    <td class="tal"><?php echo $i+1; ?></td>
                                    <td class="tal"><?php echo $produto['nome_produto']; ?></td>
                                    <td class="tal"><?php echo $produto['quantidade']; ?></td>
                                    <?php if ($existeDesconto) :?>
                                        <td class="tal cortado"><?php echo 'R$ ' . $sistema->formataMoedaShow($produto['valor_produto']); ?></td>
                                    <?php endif; ?>
                                    <td class="tal"><?php echo  ($valor_com_desconto == 0) ? "BRINDE" : 'R$ ' . $sistema->formataMoedaShow($valor_com_desconto); ?></td>
                                    <td class="tal background-cinza"><?php echo ($valor_com_desconto == 0) ? "BRINDE" : 'R$ ' . $sistema->formataMoedaShow($produto['quantidade'] * $valor_com_desconto); ?></td>
                                    <td class="tal"><?php echo ($valor_com_desconto == 0) ? "BRINDE" : 'R$ ' . $sistema->formataMoedaShow(avista($valor_com_desconto)); ?></td>
                                    <td class="tal background-cinza"><?php echo ($valor_com_desconto == 0) ? "BRINDE" : 'R$ ' . $sistema->formataMoedaShow($produto['quantidade'] * avista($valor_com_desconto)); ?> </td>
                                </tr>
                            <?php endforeach; ?>
                            <!-- Totais -->
                            <tr>
                                <td class="tal" colspan="<?php echo ($existeDesconto) ? '5' : '4'; ?>"><div align="left"><strong class="fontNegritoTudo">SUBTOTAL:</strong></div></td>
                                <td class="tal background-cinza"><?php echo 'R$ ' . $sistema->formataMoedaShow($subtotal_com_desconto); ?></td>
                                <td class="tal"></td>
                                <td class="tal background-cinza"><?php echo 'R$ ' . $sistema->formataMoedaShow($subtotal_com_desconto * 0.95); ?></td>
                            </tr>
                            <?php if ($cupom && !$orcamentoEditado) :?>
                                <tr>
                                    <td class="tal" colspan="<?php echo ($existeDesconto) ? '5' : '4'; ?>"><div align="left"><strong class="fontNegritoTudo">CUPOM: <?php echo $orcamento['mensagem_cupom']; ?></strong></div></td>
                                    <td class="tal background-cinza desconto"><strong><?php echo '- R$ ' . $sistema->formataMoedaShow($cupom); ?></strong></td>
                                    <td class="tal"></td>
                                    <td class="tal background-cinza desconto"><strong><?php echo '- R$ ' . $sistema->formataMoedaShow(avista($cupom)); ?></strong></td>
                                </tr>
                            <?php endif; ?>
                            <tr>

                                <td class="tal" colspan="<?php echo ($existeDesconto) ? '5' : '4'; ?>"><div align="left"><strong class="fontNegritoTudo">FRETE: <?php echo ($orcamento['frete']) ?: 'Frete à consultar'; ?></strong></div></td>
                                <td class="tal background-cinza"><?php echo  $orcamento['frete'] ?   $orcamento['valor_frete'] == 0 ? 'Grátis' : 'R$ ' . $sistema->formataMoedaShow(avista($orcamento['valor_frete'])) : '-'; ?></td>
                                <td class="tal"></td>
                                <td class="tal background-cinza"><?php echo   $orcamento['frete'] ?   $orcamento['valor_frete'] == 0 ? 'Grátis' : 'R$ ' . $sistema->formataMoedaShow(avista($orcamento['valor_frete'])) : '-'; ?></td>
                            </tr>
                            <tr>
                                <td class="tal" colspan="3"><div align="left"><strong class="fontNegritoTudo">TOTAL:</strong></div></td>
                                <td class="tal <?php if ($existeDesconto) echo 'cortado fontNegritoTudoRed'; ?>"><strong><?php echo 'R$ ' . $sistema->formataMoedaShow($orcamento['subtotal'] + ($orcamento['frete'] ? (float)$orcamento['valor_frete'] : 0)); ?></strong></td>
                                <?php if ($existeDesconto) :?>
                                    <td class="tal"><div align="left"><strong class="fontNegritoTudo">TOTAL 10x nos cartões:</strong></div></td>
                                <?php endif; ?>
                                <td class="tal background-cinza total"><strong><?php echo 'R$ ' . $sistema->formataMoedaShow($valor_total); ?></strong></td>
                                <td class="tal"><div align="left"><strong class="fontNegritoTudo">TOTAL à vista:</strong></div></td>
                                <td class="tal background-cinza total"><strong><?php echo 'R$ ' . $sistema->formataMoedaShow(avista($valor_total)); ?></strong></td>
                            </tr>
                        </tbody>
                    </table>

                    <?php if ($orcamento['frete']) :?>
                        <?php if ($orcamento['tipo_cupom'] == 1) echo '<div>* Cupom sujeito a alteração no momento da compra<div>'; ?>
                        <div>* Frete sujeito a alteração no momento da compra</div>
                    <?php endif; ?>

                    <table cellpadding="0" cellspacing="0" width="100%">
                        <tbody>
                            <tr>
                                <td class="separador">&nbsp;</td>
                            </tr>
                        </tbody>
                    </table>
                    <p style="text-align: center;"><img alt="Header" src="/img/orcamento/header.png"></p>
                    <table cellpadding="0" cellspacing="0" width="100%">
                        <tbody>
                            <tr>
                                <td height="48" width="22%"><div align="left"><strong class="fontNegritoTudo">Orçamento Detalhado: <?php echo $orcamento['id']; ?> - <?php echo $orcamento['cliente'];?> </strong></div></td>
                            </tr>
                        </tbody>
                    </table>

                    <?php foreach ($produtos->rows as $i => $produto) : ?>
                        <?php $deducoes = $product->getDeducoesByProduto($orcamento['id'], $produto['id']);?>
                        <?php $atributos = $product->getAtributosInfoByProduto($produto['id']); ?>
                        <!-- <table cellpadding="0" cellspacing="0" width="100%">
                            <tbody>
                                <tr>
                                    <td class="separador">&nbsp;</td>
                                </tr>
                            </tbody>
                        </table> -->
                        <table cellpadding="0" cellspacing="0" width="100%">
                            <tbody>
                                <tr>
                                    <td height="28"><strong class="fontNegritoTudo"><?php echo $i + 1; ?> - ESPECIFICAÇÃO DO PRODUTO: <strong class="fontNegritoTudoRed"><?php echo $sistema->tudoMaiusculo($produto['nome_produto']); ?></strong></strong></td>
                                </tr>
                            </tbody>
                        </table>

                        <table cellpadding="0" cellspacing="0" width="100%">
                            <tbody>
                                <tr>
                                    <td height="48" width="22%"><div align="left"><strong class="fontNegritoTudo">Valor do Produto</strong></div></td>
                                    <td class="fontNegritoTudo" width="78%"><strong class="fontNegritoTudoBlue">

                                        <?php if ($produto['valor_editado'] == ""){ ?>
                                            <?php if ($produto['desconto']): ?>
                                                De <span style="text-decoration: line-through;">R$ <?php echo $sistema->formataMoedaShow($produto['valor_produto']);?></span> por R$ <?php echo $sistema->formataMoedaShow($produto['valor_produto'] - $produto['desconto']); ?>
                                                <?php if ($produto['descricao_desconto'] != ""): ?>
                                                    (<?php echo $produto['descricao_desconto']; ?>)
                                                <?php endif ?>
                                            <?php else: ?>
                                                R$ <?php echo $sistema->formataMoedaShow($produto['valor_produto']); ?>
                                            <?php endif ?>
                                        <?php }else if($produto['valor_editado'] == 0){ ?>
                                            BRINDE
                                        <?php }else{ ?>
                                                De <span style="text-decoration: line-through;">R$ <?php echo $sistema->formataMoedaShow($produto['valor_produto']);?></span> por R$ <?php echo $sistema->formataMoedaShow($produto['valor_editado']); ?>
                                        <?php } ?>
                                        
                                    </strong></td>
                                </tr>
                                <tr>
                                    <td height="48" width="22%"><div align="left"><strong class="fontNegritoTudo">Quantidade</strong></div></td>
                                    <td class="fontNegritoTudo" width="78%"><strong class="fontNegritoTudoBlue"><?php echo $produto['quantidade']; ?></strong></td>
                                </tr>
                            </tbody>
                        </table>

                        <?php if ($atributos->num_rows) : ?>
                            <table cellpadding="0" cellspacing="0" width="100%">
                                <tbody>
                                    <tr>
                                        <td width="100%" valign="top" style="padding:0;margin:0; border-left:none; border-top:none">
                                            <?php foreach ($atributos->rows as $atributo) : ?>
                                                <div class="atributos">
                                                    <div style="padding:10px">
                                                        <div align="left"><strong class="fontNegritoTudoBlue"><?php echo mb_convert_case($atributo['nome_conjunto'], MB_CASE_TITLE, 'UTF-8') . ':'; ?></strong></div>
                                                        <div align="left"><strong class="fontNegritoTudo"><?php echo mb_convert_case($atributo['nome_atributo'], MB_CASE_UPPER, 'UTF-8'); ?> <?php if ($atributo['texto'] != "") echo "[{$atributo['texto']}]"; ?> <?php if ($atributo['cor'] != "") echo "<i style='display:inline-block;width:100px;padding:0 10px;color:#fff;background-color:{$atributo['cor']}'><i style='color: {$atributo['cor']};-webkit-filter: invert(100%);filter: invert(100%);'>{$atributo['cor']}</i></i>"; ?> <?php if ($atributo['arquivo'] != "" AND $produto['anexo'] == '') echo "<a style='font-size: 12px' target='_blank' href='".$sistema->root_path."uploads/".$atributo['arquivo']."'>[{$atributo['valor']}]</a>"; ?></strong></div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        <?php endif; ?>

                        <table cellpadding="0" cellspacing="0" width="100%">
                            <tbody>
                                <tr>
                                    <td class="separador">&nbsp;</td>
                                </tr>
                            </tbody>
                        </table>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        <!-- <p style="text-align: center;"><img alt="Footer" src="/img/orcamento/footer.png"></p> -->
    </body>
</html>
