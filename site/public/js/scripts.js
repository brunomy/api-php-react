// JavaScript Document

$rp_cmpnts = {};
$rp_cmpnts.scrollEvents = [];

$(window).resize(resizeAll);
$(document).ready(function () {

    // verififa se a quantidade de itens ultrapassa a altura da tela
    var totalHeightItens = 0;
    $("header #carrinho .itens .item").each(function(){
        totalHeightItens += $(this).height();
    });
    
    var screenHeight = $(window).height() - 380;
    if (totalHeightItens > screenHeight) $("header #carrinho").css({"padding-top":"30px"}).find(".itens").hide();
    

    $rp_cmpnts.cart = new carrinho();
    $rp_cmpnts.login = new loginbox();

    if (pagina == 'home') {

        //##########  PAGINA HOME
        //###################################### ############################### ##########################

        var banners = new banners_home(".comp-banners");


        // ROLETA PRODUTOS ------
        var responsive = {
            0: {
                'items': 2,
                'margin': 5
            },
            700: {
                'items': 3,
                'margin': 40
            },
            900: {
                'items': 4
            },
            1100: {
                'items': 5,
                'margin': 33
            }
        }

        //inicializa da roleta 
        var owl = $('.pronta_entrega .roleta .items');
        owl.owlCarousel({'loop': true, 'autoplay': true, 'responsive': responsive});

        $('.pronta_entrega .roleta .left').click(function () {
            owl.trigger('prev.owl.carousel');
        });

        $('.pronta_entrega .roleta .right').click(function () {
            owl.trigger('next.owl.carousel');
        });


        // ROLETA PRODUTOS ------
        var responsive = {
            0: {
                'items': 3,
                'margin': 15
            },
            900: {
                'items': 4
            },
            1100: {
                'items': 6
            }
        }

        //inicializa da roleta 
        var owl = $('.clientes .roleta .items');
        owl.owlCarousel({'loop': true, 'autoplay': true, 'responsive': responsive});

        $('.clientes .roleta .left').click(function () {
            owl.trigger('prev.owl.carousel');
        });

        $('.clientes .roleta .right').click(function () {
            owl.trigger('next.owl.carousel');
        });

    } else if (pagina == 'produto') {

        //##########  PAGINA PRODUTO
        //###################################### ############################### ##########################


        new photoswipe_init('.pswp1');

        // ROLETA GALERIA ------
        var responsive = {
            0: {
                'items': 3
            },
            750: {
                'items': 5
            },
            900: {
                'items': 6
            },
            1100: {
                'items': 7
            },
            1240: {
                'items': 5
            }
        }

        //inicializa da roleta 
        var owl = $('.produto .roleta .items');
        owl.owlCarousel({'loop': true, 'autoplay': true, 'responsive': responsive});

        $('.produto .roleta .prev').click(function () {
            owl.trigger('prev.owl.carousel');
        });

        $('.produto .roleta .next').click(function () {
            owl.trigger('next.owl.carousel');
        });

        $rp_cmpnts.produto = new produto();

        $(".form_produto").clickform({'validateUrl': 'script/product/setCarrinho', 'submitButton': '.clickformsubmit', 'blockScroll': true, 'conversionUrl': '_load_form_conversion_script.php?id=5'}, function (data) {
            if (data.type == "success") {
                window.location.href = data.response;
            }
        });

        $('.resumo_produto .main_titles').click(function () {
            if (mobileEvents) {
                $('.resumo_produto .configuracoes').slideToggle();
            }
        })

        scrollerListenerProduto();
        $rp_cmpnts.scrollEvents.push(scrollerListenerProduto);

        if($('button.slidetoggle_conjuntos').length > 0){
            $('button.slidetoggle_conjuntos').click(function(){
                $('.conjuntos').slideToggle();
            });
        }

        if($('.produto_avaliacoes').length > 0){

            $('.produto_avaliacoes button.toggle').click(function(e){
                e.preventDefault();
                $('.avaliacoes_view').slideToggle();
            });

            if($('.box_avaliacoes .paginacao').length > 0){
                $('body').on('click','a[data-params]',function(){
                    $('.box_avaliacoes').load('_inc_avaliacoes.php?'+$(this).attr('data-params'));
                });
            }

            $('#ordenacao').change(function(){
                $('.box_avaliacoes').load('_inc_avaliacoes.php?id='+$(this).attr('data-id')+'&order='+$(this).val());
            });
        }

        //inicializa da roleta 
        var owl = $('.clientes .roleta .items');
        owl.owlCarousel({'loop': true, 'autoplay': true, 'responsive': responsive});

        $('.clientes .roleta .left').click(function () {
            owl.trigger('prev.owl.carousel');
        });

        $('.clientes .roleta .right').click(function () {
            owl.trigger('next.owl.carousel');
        });

        if(typeof setCarrinho !== 'undefined' && setCarrinho == 1){
            $('.form_produto .barra_preco .clickformsubmit').trigger('click');
        }

        $('.frete .clickformfretesubmit').click(function () {
            $('.frete .resultados').load('script/frete/cep/?cep=' + $('.frete .form input').val() +'&produto='+ $('.frete .form input').attr('data-produto') + '&quantidade=' + $('input[name=quantidade]').val(), function(){
                $('.frete .resultados input').remove();
            });
        });

    } else if (pagina == 'carrinho') {

        //##########  PAGINA CARRINHO
        //###################################### ############################### ##########################

        $(document).on("click", ".checkout_carrinho .totals .descontos i", function (e) {
            $('body').popup({'url': '_inc_descontos_info.php', 'width': '100%', 'height': 80, 'closebutton': '.bt_fechar'});
        });

        $('.main_carrinho button.detalhes').click(function () {
            $(this).parents('.item').find('.configuracoes').slideToggle();
        });

        $('.checkout_forms.form_cupom .open_hidden').click(function () {
            $(this).parents('.form_cupom').find('.hidden').slideDown();
            $(this).hide();
        });

        $('form.form_cupom').clickform({'validateUrl': 'script/product/cupom', 'submitButton': '.clickformsubmit'}, function (data) {
            if (data.type == "success") {
                //$('form.form_cupom .cupom-de-desconto').attr('data-tipo', data.cupom.tipo);
                //$('form.form_cupom .cupom-de-desconto').attr('data-valor', data.cupom.valor);
                $('form.form_cupom .cupom-de-desconto').html('<span class="remove_cupom">X</span>' + data.message);

                $rp_cmpnts.cart.cupom.valor = data.cupom.valor;
                $rp_cmpnts.cart.cupom.tipo = data.cupom.tipo;

            }

            $rp_cmpnts.cart.renderizarPrecos();
        });

        $('form.form_frete .clickformsubmit').click(function () {
            $("input[type=hidden].frete_nome").val('');
            $("input[type=hidden].frete_prazo").val('');
            $("input[type=hidden].frete_valor").val('');

            $('form.form_frete .resultados').load('script/frete/cep/?cep=' + $('form.form_frete input').val());
        });

        $('form.form_orcamento').clickform({'validateUrl': 'scripts/form-orcamento.php', 'submitButton': '.clickformsubmit'}, function (data) {
            if (data.type == 'success') {
                $.redirect('home');
            }
        })

        $(document).on('keyup', 'input[name=quantidade]', function (e) {
            $rp_cmpnts.cart.itens[$(this).parents('.item').index()].qtde = $(this).val();
            $rp_cmpnts.cart.renderizarPrecos();

        });

        $('input[name=quantidade]').blur($rp_cmpnts.cart.renderizarPrecos);

        $(document).on('change', '.option-frete input', function (e) {
            $rp_cmpnts.cart.preco.frete = parseFloat($(this).val());
            $rp_cmpnts.cart.renderizarPrecos();
            $.post('script/frete/setTipoFrete', {tipo: $(this).attr('id')});

            $("input[type=hidden].frete_nome").val($(this).attr("data-nome"));
            $("input[type=hidden].frete_prazo").val($(this).attr("data-prazo"));
            $("input[type=hidden].frete_valor").val($(this).val());
        });

        $("body .option-frete input:checked").trigger("change");

        if ($('input[name=quantidade]').length == 0)
            buscaFrete();

    } else if (pagina == 'checkout') {

        //##########  PAGINA CHECKOUT
        //###################################### ############################### ##########################

        if($('input[name=tipo_cliente]').val()==''){
            $('body').popup({'url': '_inc_tipo_cliente.php', 'width': '100%', 'height': 200, 'closebutton': '.bt_close'}, function(){
                $('.tipo_cliente_popup a').click(function(){
                    $('input[name=tipo_cliente]').val($(this).attr('data-tipo_cliente'));
                    $.get('scripts/tipo_cliente.php?tipo_cliente='+$(this).attr('data-tipo_cliente'));
                    $('.tipo_cliente_popup .bt_close').trigger('click');
                });
            });
        }

        // EVENTOS DA CAIXA DE RESUMO----------------------

        var interval_checkout, over_checkout;

        $('button.checkout_resume, #checkout_resume').mouseenter(function (e) {
            over_checkout = true;
            $('#checkout_resume').fadeIn(200);
        }).mouseleave(function (e) {
            over_checkout = false;
            closeCheckout();
        });

        $('#checkout_resume .close').click(function () {
            over_checkout = false;
            $('#checkout_resume').fadeOut(200);
        });

        function closeCheckout() {
            clearInterval(interval_checkout);
            interval_checkout = setInterval(function () {
                clearInterval(interval_checkout);
                if (!over_checkout)
                    $('#checkout_resume').fadeOut(200);
            }, 400);
        }

        // -------------------------------------------------


        $('button.checkout_login').mouseenter($rp_cmpnts.login.checkoutLogin).mouseleave($rp_cmpnts.login.closeLogin);

        $('select.pessoa').change(function () {
            if ($(this).val() == 1) {
                $('.pessoa_fisica').show();
                $('.pessoa_juridica').hide();
            } else {
                $('.pessoa_juridica').show();
                $('.pessoa_fisica').hide();
            }
        });

        $('select.pessoa').trigger('change');

        $('input[name=cep]').keyup(function () {
            cep = $(this).val().replace(/[^0-9.]/g, '');
            cep = cep.replace('.', '');
            if (cep.length == 8) {

                $("input[type=hidden].frete_nome").val('');
                $("input[type=hidden].frete_prazo").val('');

                buscaFrete();
                preencherEndereco(cep);
            }
        });

        if (
                $('input[name=endereco]').val() == "" &&
                $('input[name=bairro]').val() == "" &&
                $('select[name=id_estado]').val() == "" &&
                $('select[name=id_cidade]').val() == ""
                ) {
            preencherEndereco($(".mask-cep").val());
        }

        $(document).on('change', '.option-frete input', function (e) {

            $.post('script/frete/setTipoFrete', {tipo: $(this).attr('id')});

            $("input[type=hidden].frete_nome").val($(this).attr("data-nome"));
            $("input[type=hidden].frete_prazo").val($(this).attr("data-prazo"));

            $rp_cmpnts.cart.preco.frete = parseFloat($(this).val());
            $rp_cmpnts.cart.renderizarPrecos();

        });

        $rp_cmpnts.cart.cupom.valor = $(".cupom-de-desconto").attr("data-valor");
        $rp_cmpnts.cart.cupom.tipo = $(".cupom-de-desconto").attr("data-tipo");
        $rp_cmpnts.cart.renderizarPrecos();


        $("body .option-frete input:checked").trigger("change");


        $('.metodo_pagamento .option_pgto .opcoes_parcelas .chamada').click(function () {
            $(this).parents('.opcoes_parcelas').find('ul').slideToggle();
        });

        $(".metodo_pagamento").clickform({'validateUrl': 'scripts/form-checkout.php', 'submitButton': '.clickformsubmit', 'blockScroll': true, 'conversionUrl': '_load_form_conversion_script.php?id=7'}, function (data) {
            if (data.type == "success") {

                if(data.metodo == 'cielo_transparente'){
                    abrepopup('_inc_cartao_cielo.php?id='+data.pedido, function(){
                        $(".form_cartao_cielo").clickform({'validateUrl': 'scripts/form-cielo-transparent.php', 'submitButton': '.clickformsubmit', 'blockScroll': true}, function (data) {
                            if (data.type == "success") {
                                window.location = 'minha-conta';
                            }
                        });
                    });
                }else if(data.metodo == 'rede_transparente'){
                    abrepopup('_inc_cartao_rede.php?id='+data.pedido, function(){
                        $(".form_cartao_cielo").clickform({'validateUrl': 'scripts/form-rede-transparent.php', 'submitButton': '.clickformsubmit', 'blockScroll': true}, function (data) {
                            if (data.type == "success") {
                                window.location = 'minha-conta';
                            }
                        });
                    });
                }else{
                    $.redirect('pagamento', {'pedido': data.pedido, 'metodo': data.metodo, 'id_cliente': data.id_cliente});
                }
            }
        });
    
    } else if (pagina == 'pagamento') {

        $('#chave_pix').click(function(){
            setTimeout(function(){
                textoCopiado = document.getElementById("chave_pix");
                textoCopiado.select();
                if(mobileEvents) textoCopiado.setSelectionRange(0, 99999); 
            },100);
        });

        $('.bt_copiar').click(function(){
            textoCopiado = document.getElementById("chave_pix");
            textoCopiado.select();
            if(mobileEvents) textoCopiado.setSelectionRange(0, 99999);
            document.execCommand("copy");
            alert("Chave PIX copiada!");
        });
    } else if (pagina == 'cadastro') {

        //##########  PAGINA CADASTRO
        //###################################### ############################### ##########################

        $('select.pessoa').change(function () {
            if ($(this).val() == 1) {
                $('.pessoa_fisica').show();
                $('.pessoa_juridica').hide();
            } else {
                $('.pessoa_juridica').show();
                $('.pessoa_fisica').hide();
            }
        });

        $('select.pessoa').trigger('change');

        /*$('input[name=cep]').keyup(function () {
            cep = $(this).val().replace(/[^0-9.]/g, '');
            cep = cep.replace('.', '');
            if (cep.length >= 8) {
                $.getScript('http://www.toolsweb.com.br/webservice/clienteWebService.php?cep=' + cep + '&formato=javascript', function () {
                    $('input[name=endereco]').val(decodeURIComponent(escape(resultadoCEP.tipoLogradouro + ' ' + resultadoCEP.logradouro)));
                    $('input[name=bairro]').val(decodeURIComponent(escape(resultadoCEP.bairro)));

                    $('.estado option').each(function () {
                        if ($(this).attr("data-uf") == resultadoCEP.estado) {
                            $(this).prop("selected", true);
                            $(".estado").attr('data-auto-select-cidade', decodeURIComponent(escape(resultadoCEP.cidade)));
                            $(".estado").trigger("change");
                        }
                    });

                    //cidade = resultadoCEP.cidade;

                });
            }
        });*/
        $('input[name=cep]').keyup(function () {
            cep = $(this).val().replace(/[^0-9.]/g, '');
            cep = cep.replace('.', '');
            if (cep.length == 8) {

                $("input[type=hidden].frete_nome").val('');
                $("input[type=hidden].frete_prazo").val('');

                buscaFrete();
                preencherEndereco(cep);
            }
        });


        $(".form_cadastro").clickform({'validateUrl': 'scripts/form-cadastro.php', 'submitButton': '.clickformsubmit', 'blockScroll': true, 'conversionUrl': '_load_form_conversion_script.php?id=2'}, function (data) {
            if (data.type == "success") {
                //window.location.reload(true);
                $('form.form_cadastro').each(function () {
                    this.reset();
                });
                window.location = 'minha-conta';
            }
        });


    } else if (pagina == 'minha-conta') {

        //##########  PAGINA MINHA CONTA
        //###################################### ############################### ##########################

        $('.tabela_pedidos button.detalhes').click(function () {
            $(this).parents('.pedido').find('.box_detalhes').slideToggle();
        });

        $(document).on("click", ".bt_pagar", function (e) {
            e.preventDefault();
            var pedido = $(this).attr("data-id-pedido");
            var id_cliente = $(this).attr("data-id-cliente");
            var metodo = $(this).attr("data-metodo");
            //path = path.replace('http://','https//');

            if(metodo == 'cielo_transparente'){
                abrepopup('_inc_cartao_cielo.php?id='+pedido, function(){
                    $(".form_cartao_cielo").clickform({'validateUrl': 'scripts/form-cielo-transparent.php', 'submitButton': '.clickformsubmit', 'blockScroll': true}, function (data) {
                        if (data.type == "success") {
                            window.location = 'minha-conta';
                        }
                    });
                });
            }else if(metodo == 'rede_transparente'){
                abrepopup('_inc_cartao_rede.php?id='+pedido, function(){
                    $(".form_cartao_cielo").clickform({'validateUrl': 'scripts/form-rede-transparent.php', 'submitButton': '.clickformsubmit', 'blockScroll': true}, function (data) {
                        if (data.type == "success") {
                            window.location = 'minha-conta';
                        }
                    });
                });
            }else{
                $.redirect(path + 'pagamento', {'pedido': pedido, 'metodo': metodo, 'id_cliente': id_cliente});
            }
        });

        if($(".bt_anexar").length){

            $(document).on("click", ".bt_anexar", function (e) {
                e.preventDefault();            
                var pedido = $(this).attr("data-id-pedido");
                $('body').popup({'url': '_inc_anexar_documentos.php?id='+pedido, 'width': '100%', 'height': 750, 'closebutton': '.close,.enviar_depois'}, function(){
                    $(".form_anexar").clickform({'validateUrl': 'scripts/form-documentos.php', 'submitButton': '.bt_submit', 'blockScroll': true, 'conversionUrl': '_load_form_conversion_script.php?id=4'}, function (data) {
                        if (data.type == "success") {                        
                            window.location.reload(true);
                        }
                    });
                });

            });

            $(".bt_anexar:first").trigger("click");
        }


    } else if (pagina == 'dados-pessoais') {

        //##########  PAGINA DADOS PESSOAIS
        //###################################### ############################### ##########################

        $('select.pessoa').change(function () {
            if ($(this).val() == 1) {
                $('.pessoa_fisica').show();
                $('.pessoa_juridica').hide();
            } else {
                $('.pessoa_juridica').show();
                $('.pessoa_fisica').hide();
            }
        });

        $('select.pessoa').trigger('change');


        $(".form_dados_pessoais").clickform({'validateUrl': 'scripts/form-dados-pessoais.php', 'submitButton': '.clickformsubmit', 'blockScroll': true}, function (data) {
            if (data.type == "success") {
                window.location.reload(true);
            }
        });

        $(document).on('click', '.excluir_conta', function (e) {
            $('body').popup({'url': '_inc_exclusao.php', 'width': '100%', 'height': 270, 'closebutton': '.bt_close'}, function () {
                $(".form_excluir").clickform({'validateUrl': 'scripts/form-exclusao.php', 'submitButton': '.clickformsubmit', 'blockScroll': true, 'conversionUrl': '_load_form_conversion_script.php?id=4'}, function (data) {
                    if (data.type == "success") {
                        window.location.href = "sair";
                    }
                });
            });
        });

    } else if (pagina == 'endereco') {

        //##########  PAGINA ALTERAR ENDEREÇO
        //###################################### ############################### ##########################

        /*$('input[name=cep]').keyup(function () {
            cep = $(this).val().replace(/[^0-9.]/g, '');
            cep = cep.replace('.', '');
            if (cep.length >= 8) {
                $.getScript('http://www.toolsweb.com.br/webservice/clienteWebService.php?cep=' + cep + '&formato=javascript', function () {
                    $('input[name=endereco]').val(decodeURIComponent(escape(resultadoCEP.tipoLogradouro + ' ' + resultadoCEP.logradouro)));
                    $('input[name=bairro]').val(decodeURIComponent(escape(resultadoCEP.bairro)));

                    $('.estado option').each(function () {
                        if ($(this).attr("data-uf") == resultadoCEP.estado) {
                            $(this).prop("selected", true);
                            $(".estado").attr('data-auto-select-cidade', decodeURIComponent(escape(resultadoCEP.cidade)));
                            $(".estado").trigger("change");
                        }
                    });

                });
            }
        });*/
        $('input[name=cep]').keyup(function () {
            cep = $(this).val().replace(/[^0-9.]/g, '');
            cep = cep.replace('.', '');
            if (cep.length == 8) {

                buscaFrete();
                preencherEndereco(cep);
            }
        });


        $(".form_endereco").clickform({'validateUrl': 'scripts/form_endereco.php', 'submitButton': '.clickformsubmit', 'blockScroll': true}, function (data) {
            if (data.type == "success") {
                window.location.reload(true);
            }
        });



    } else if (pagina == "senha") {

        //##########  PAGINA ALTERAR SENHA
        //###################################### ############################### ##########################

        $(".form_senha").clickform({'validateUrl': 'scripts/form_senha.php', 'submitButton': '.clickformsubmit', 'blockScroll': true}, function (data) {
            if (data.type == "success") {
                if (data.redirect != "")
                    window.location.href = data.redirect;
                else
                    window.location.reload(true);
            }
        });

    } else if (pagina == "fale-conosco") {


        //##########  PAGINA FALE CONOSCO
        //###################################### ############################### ##########################


        $(".form_contato").clickform({'validateUrl': 'scripts/form-contato.php', 'submitButton': '.clickformsubmit', 'blockScroll': true, 'conversionUrl': '_load_form_conversion_script.php?id=1'}, function (data) {
            if (data.type == "success") {
                //window.location.reload(true);
                $('form.form_contato').each(function () {
                    this.reset();
                });
            }
        });

    } else if (pagina == "wsop") {
        
        //##########  PAGINA WSOP
        //###################################### ############################### ##########################
        
        newsboxpopup = 0;
        
        $(".form_participe").clickform({'validateUrl': 'script/product/setCarrinho', 'submitButton': '.clickformsubmit', 'blockScroll': true, 'conversionUrl': '_load_form_conversion_script.php?id=5'}, function (data) {
            if (data.type == "success") {
                window.location.href = data.response;
            }
        });
        
        $(".form_participe2").clickform({'validateUrl': 'script/product/setCarrinho', 'submitButton': '.clickformsubmit', 'blockScroll': true, 'conversionUrl': '_load_form_conversion_script.php?id=5'}, function (data) {
            if (data.type == "success") {
                window.location.href = data.response;
            }
        });
    }

    //##########  TODAS DAS PÁGINAS
    //###################################### ############################### ##########################


    /*
    if (typeof idSeo != 'undefined') {

        $.get('http://ipinfo.io', function (dados) {
            $.post('scripts/localidade_analytics.php', {'id': idSeo, 'session': session, 'pais': dados.country, 'estado': dados.region, 'cidade': dados.city});
        }, 'jsonp');
        $.get('https://freegeoip.net/json/'+ip, function (dados) {
            $.post('scripts/localidade_analytics.php', {'id': idSeo, 'session': session, 'pais': dados.country_code, 'estado': dados.region_name, 'cidade': dados.city});
        });

    }
    */

    if ($(".wordseries").length) {
        $(".wordseries .bsop").fadeIn();
        setInterval(function(){
            if($(".wordseries .bsop:visible").length){
                $(".wordseries .bsop").fadeOut();
                $(".wordseries .wsop").fadeIn();
            }else{
                $(".wordseries .wsop").fadeOut();
                $(".wordseries .bsop").fadeIn();
            }
        },5000);
    }


    if ($(".estado").length) {
        if ($('.estado[data-auto-select-cidade]').length == 0) {
            $(".estado").attr('data-auto-select-cidade', '');
        }
        $(".estado").change(function () {
            if ($(this).val() != "") {
                var id = $(this).find("option:selected").attr("data-id");
                $(".cidade").load("scripts/utils-cidades.php?id=" + id, function () {
                    $('.cidade option[data-cidade="' + $(".estado").attr('data-auto-select-cidade') + '"]').prop("selected", true);
                    $('.cidade option[value="' + $(".estado").attr('data-auto-select-cidade') + '"]').prop("selected", true);
                });
            }
        });
    }


    $(".form_login").clickform({'validateUrl': 'scripts/form-login.php', 'submitButton': '.clickformsubmit', 'blockScroll': true, 'conversionUrl': '_load_form_conversion_script.php?id=6'}, function (data) {
        if (data.type == "success") {
            if (pagina == 'checkout') {
                window.location.reload(true);
            } else {
                window.location = '/minha-conta';
            }
        }
    });


    $('.editable_content iframe').each(function () {
        tempData = $(this).attr('src');
        tempData = tempData.search('youtube.com');
        if (tempData != "-1") {
            $(this).addClass('video-youtube');
        }
    });

    // BARRA DE BUSCA --------------------------------------------

        $('.inputwrap #busca').keypress(function (event) {
            if (event.keyCode == 13) {
                window.location = 'busca/'+$(this).val();
            }
        });

        $('.inputwrap a').click(function () {
            window.location = 'busca/'+$(this).parents('.inputwrap').find('#busca').val()
        });

    // -----------------------------------------------------------

    //editableContentImages();
    resizeAll();

    $rp_cmpnts.scrollEvents.push(scrollerListenetMenu);
    $(window).scroll(function () {
        for (i in $rp_cmpnts.scrollEvents) {
            $rp_cmpnts.scrollEvents[i]();
        }
    });
    for (i in $rp_cmpnts.scrollEvents) {
        $rp_cmpnts.scrollEvents[i]();
    }

    allEvents();


    //###################################### ############################### ##########################


});

function resizeAll() {

    console.log($(window).width());

    if ($(window).width() < 1235) {
        mobileEvents = true;
        $('.barra_busca .inputwrap input').attr('placeholder','Buscar...');
    } else {
        $('.barra_busca .inputwrap input').attr('placeholder','Digite o que você busca');
        mobileEvents = false;
    }

}


// Barra de preços fixa
function scrollerListenetMenu() {
    if ($(window).scrollTop() > ($('header').height() - $('header .barra_topo3').height())) {
        $('header').addClass('fixa');
    } else {
        $('header').removeClass('fixa');
    }
}

// Barra de preços fixa
function scrollerListenerProduto() {

    //DESKTOP E TABLET
    if ($(window).width() > 760) {

        if ($(window).scrollTop() > ($('body').height() - $(window).height() - $('footer').height() - $('.produtos_relacionados').height() + $('.barra_preco').height())) {
            $('.barra_preco').removeClass('fixa');
            $('.whatsapp_bar').css({'bottom':0});
            $('.whatsapp_bar2').css({'bottom':0});
        } else {
            $('.barra_preco').addClass('fixa');
            $('.whatsapp_bar').css({'bottom':76});
            $('.whatsapp_bar2').css({'bottom':76});
        }

    } else {

        if ($(window).scrollTop() > ($('body').height() - $(window).height() - $('footer').height() - $('.mobile_valores').height() - $('.mobile_quantidade').height() - $('.produtos_relacionados').height() + $('.barra_preco').height())) {
            $('.barra_preco').hide();
            $('.whatsapp_bar').css({'bottom':5});
            $('.whatsapp_bar2').css({'bottom':5});
        } else {
            $('.barra_preco').show();
            $('.whatsapp_bar').css({'bottom':70});
            $('.whatsapp_bar2').css({'bottom':70});
        }

    }

}


function editableContentImages() {
    $('.editable_content img.resize-off').css({'max-width': '100%', 'width': '100%'});
    if ($('.editable_content img').not('.editable_content img.resize-off').length > 0) {
        $(window).load(function () {
            var w, p;
            $('.editable_content img').not('.editable_content img.resize-off').each(function () {
                $(this).attr('data-width', $(this).width());
            });

            $(window).resize(resize_editable_imgs);
            resize_editable_imgs();
        });
    }
    function resize_editable_imgs() {
        if ($(window).width() < 1237 && $(window).width() > 749) {
            p = ($(window).width() * 100 / 1237) * .01;
            $('.editable_content img').not('.editable_content img.resize-off').each(function () {
                w = $(this).attr('data-width');
                $(this).removeAttr("width").removeAttr('height');
                $(this).width(Math.round(w * p));
            });
        } else if ($(window).width() < 750) {
            p = ($(window).width() * 100 / 1350) * .01;
            $('.editable_content img').not('.editable_content img.resize-off').each(function () {
                w = $(this).attr('data-width');
                $(this).removeAttr("width").removeAttr('height');
                $(this).width(Math.round(w * p));
            });
        } else {
            $('.editable_content img').not('.editable_content img.resize-off').each(function () {
                $(this).width($(this).attr('data-width'));
            });
        }
    }
}


function allEvents() {

    // ------   DEFAULTS INI  ------  //

    $(document).on('click', 'a[href=#]', function (e) {
        e.preventDefault();
    });

    $(document).on("click", "button", function (e) {
        e.preventDefault();
    });

    $(document).on("click", ".remove_cupom", function (e) {
        $(this).parents('.cupom-de-desconto').empty();
        $.post('script/product/clearCupom');
        $.post('scripts/minha_sessao.php');

        $rp_cmpnts.cart.atualizarCarrinho();
    });

    $(document).on("click", "header .barra_topo1 .configs .moeda", function (e) {
        $('body').popup({'url': '_inc_dollar.php', 'width': '100%', 'height': 80, 'closebutton': '.bt_fechar'});
    });

    $('#login .esqueceu').click(function () {
        $('body').popup({'url': '_inc_esqueceu_senha.php', 'width': '100%', 'height': 270, 'closebutton': '.bt_close'}, function () {
            // - CALLBACK
        });
    });

    $('input.mask-telefone').inputmask({'mask': '(99) 99999999[9]', 'greedy': false});
    $('input.mask-cep').inputmask({'mask': '99.999-999'});
    $('input.mask-cpf').inputmask({'mask': '999.999.999-99'});
    $('input.mask-cnpj').inputmask({'mask': '99.999.999/9999-99'});
    $('input.mask-numero').inputmask({'mask': '[9][9][9][9][9]', 'greedy': false});


    // ------   MENU INI  ------  //


    $(document).on('click', 'header .barra_topo3 .mobilenavbutton', function (e) {
        $('header .barra_topo3 nav').slideToggle(function () {

            //MENU ABERTO ABSOLUTO NO MOBILE
            if ($('header .barra_topo3 nav:visible').length > 0) {
                $('body').addClass('menu-over-all');
                $('header .barra_topo3').css({'top': $(document).scrollTop()});
            } else {
                $('body').removeClass('menu-over-all');
                $('header .barra_topo3').css({'top': 0});
            }

        });
    });

    $(document).on('click', 'header .barra_topo3 nav li span', function (e) {
        if (mobileEvents) {
            $(this).parent('li').find(' > ul').slideToggle();
            $(this).toggleClass('opened');
        }
    });

    // ------   TELEVENDAS INI  ------  //

    var interval_televendas, over_televendas;

    $('header .barra_atendimentos .atendimento_televendas, header #televendas').mouseenter(function () {
        if (is_desktop) {
            over_televendas = true;
            openTelevendas();
        }
    }).mouseleave(function () {
        over_televendas = false;
        closeTelevendas();
    });

    $('header .barra_atendimentos .atendimento_televendas').click(function () {
        if (mobileEvents && over_televendas) {
            over_televendas = false;
            closeTelevendas();
        } else if (mobileEvents && !over_televendas) {
            over_televendas = true;
            openTelevendas();
        }
    });

    $('header #televendas .close').click(function () {
        over_televendas = false;
        $('header #televendas').fadeOut(200);
    });

    function openTelevendas() {
        $('header #televendas').fadeIn(200);
    }

    function closeTelevendas() {
        clearInterval(interval_televendas);
        interval_televendas = setInterval(function () {
            clearInterval(interval_televendas);
            if (!over_televendas)
                $('header #televendas').fadeOut(200);
        }, 400);

    }

    // ------   CARRINHO INI  ------  //

    var interval_carrinho, over_carrinho;

    $('header .carrinho, header #carrinho').mouseenter(function (e) {
        over_carrinho = true;
        openCarrinho(e);
    }).mouseleave(function (e) {
        over_carrinho = false;
        closeCarrinho();
    });

    $('header #carrinho .close').click(function () {
        over_carrinho = false;
        $('header #carrinho').fadeOut(200);
    });

    $('.mycart .itens .excluir').click(function () {
        $rp_cmpnts.cart.excluirCarrinho($(this).attr('data-id'));
    });


    function openCarrinho(e) {
        $('header #carrinho').fadeIn(200);
    }

    function closeCarrinho() {
        clearInterval(interval_carrinho);
        interval_carrinho = setInterval(function () {
            clearInterval(interval_carrinho);
            if (!over_carrinho)
                $('header #carrinho').fadeOut(200);
        }, 400);
    }


    // ------   FORMULÁRIO NEWSLETTER  ------  //


    $(".form_newsletter").clickform({'validateUrl': 'scripts/form-newsletter.php', 'submitButton': '.clickformsubmit', 'blockScroll': true, 'conversionUrl': '_load_form_conversion_script.php?id=4'}, function (data) {
        if (data.type == "success") {
            //window.location.reload(true);
            $("#email").val('');
        }
    });



    // ------   FILE INPUTS + LABEL  ------  //

    var inputs = document.querySelectorAll('.inputfile');
    Array.prototype.forEach.call(inputs, function (input)
    {
        var label = input.nextElementSibling,
                labelVal = label.innerHTML;

        input.addEventListener('change', function (e) {
            var fileName = '';
            if (this.files && this.files.length > 1)
                fileName = (this.getAttribute('data-multiple-caption') || '').replace('{count}', this.files.length);
            else
                fileName = e.target.value.split('\\').pop();

            if (fileName)
                label.querySelector('span').innerHTML = fileName;
            else
                label.querySelector('span').innerHTML = labelVal;
        });
    });


    // ------   NEWSBOX POPUP  ------  //

    if (newsboxpopup) {
        setTimeout(function () {
            $('body').popup({'url': '_inc_newsboxpopup.php', 'width': '100%', 'height': 270, 'closebutton': '.bt_close'}, function () {
                setCookie('cookie_newsbox', '15dias', 15);
                $('.newsbox .link_cadastrado').click(function () {
                    setCookie('cookie_newsbox', '5anos', 1825);
                });
                $(".form_news").clickform({'validateUrl': 'scripts/form-newsletter.php', 'submitButton': '.clickformsubmit', 'blockScroll': true, 'conversionUrl': '_load_form_conversion_script.php?id=4'}, function (data) {
                    if (data.type == "success") {
                        setCookie('cookie_newsbox', '5anos', 1825);
                        $('.newsbox .bt_close:first').trigger('click');
                    }
                });
            });
        }, 20000);
    }

    // ------   TERMOS DE USO POPUP  ------  //
    $(document).on('click', '.link-termos', function (e) {
        $('body').popup({'url': '_inc_termos_uso.php', 'width': '100%', 'height': 500, 'closebutton': '.bt_close'});
    });

}

function loginbox() {

    var _self = this;
    var tempVar = '';
    var background = '';

    var interval_login, over_login;

    this.openLogin = openLogin;
    this.closeLogin = closeLogin;
    this.checkoutLogin = checkoutLogin;

    _construct();

    function _construct() {

        $('header .features .login, header #login').mouseenter(function (e) {
            _self.over_login = true;
            openLogin(e);
        }).mouseleave(function (e) {
            _self.over_login = false;
            closeLogin();
        });

        $('header #login .close').click(function () {
            _self.over_login = false;
            $('header #login').fadeOut(200);
        });

        if (openlogin) {
            _self.over_login = true;
            openLogin();
            background = $("<div style=\"display:none;\"></div>").appendTo("body");
            background.fadeTo(01, 0).css({width: "100%", height: $(document).height() + "px", cursor: "pointer", top: 0, position: "absolute", background: "#000", zIndex: 11});
            background.fadeTo("slow", 0.8);
            background.click(function () {
                closeLogin();
            });
        }

    }

    function openLogin() {
        $('header #login').fadeIn(200);
    }

    function closeLogin() {
        clearInterval(_self.interval_login);
        _self.interval_login = setInterval(function () {
            clearInterval(_self.interval_login);
            if (!_self.over_login) {
                $('header #login').fadeOut(200, function () {
                    $('header #login').removeAttr('style').removeClass('checkout');
                });
            }
        }, 400);

        background.fadeOut(function () {
            background.remove();
        });
    }

    function checkoutLogin() {
        $('header #login').addClass('checkout').css({'top': ($('button.checkout_login').offset().top + 55), 'left': $('button.checkout_login').offset().left, 'margin-left': 0});
        openLogin();
    }

}

function buscaFrete() {
    $('.form_frete .resultados').load('script/frete/cep/?cep=' + $(".mask-cep").val());
}

function carrinho() {

    var _self = this;
    var tempVar = '';

    this.itens = [];
    this.qtdItens = 0;
    this.preco = {};
    this.preco.subtotal = 0;
    this.preco.descontos = 0;
    this.preco.total = 0;
    this.preco.cupom = 0;
    this.preco.frete = 0;
    this.atualizarCarrinho = readCartFromHTML;
    this.renderizarPrecos = renderizarPrecos;
    this.excluirCarrinho = excluirCarrinho;

    readCartFromHTML();
    carrinhoVazio();

    function readCartFromHTML() {
        _self.itens = [];
        _self.qtdItens = 0;
        _self.preco.subtotal = 0;
        _self.cupom = {
            tipo: $('form.form_cupom .cupom-de-desconto').attr('data-tipo') || 0,
            valor: $('form.form_cupom .cupom-de-desconto').attr('data-valor') || 0
        };
        _self.preco.frete = 0;
        $('div#carrinho .itens .item').each(function (i) {
            _self.tempVar = []; //descontos
            $(this).find('input.descontos').each(function () {
                _self.tempVar.push({
                    id: $(this).attr('data-id'),
                    tipo: $(this).attr('data-tipo'),
                    qtde: $(this).attr('data-qtde'),
                    valor: $(this).attr('data-valor')
                });
            });
            _self.itens.push({
                id: $(this).attr('data-id'),
                qtde: $(this).attr('data-qdte'),
                valor: $(this).attr('data-valor'),
                descontos: _self.tempVar,
                desconto: 0
            });
        });

        renderizarPrecos();
    }


    function renderizarPrecos(event) {

        //valida quantidade mínima a partir do evento blur
        if (typeof event != 'undefined') {

            if (event.type == 'blur') {
                if (parseInt(event.currentTarget.value) < parseInt(event.currentTarget.getAttribute('data-quantidade-minima'))) {
                    alert('Quantidade mínima para este produto é ' + parseInt(event.currentTarget.getAttribute('data-quantidade-minima')));
                    event.currentTarget.value = event.currentTarget.getAttribute('data-quantidade-minima');
                    $rp_cmpnts.cart.itens[$(event.currentTarget).parents('.item').index()].qtde = event.currentTarget.value;
                }
                if (pagina == 'carrinho') {
                    $.post('script/product/setQtd', {'id': event.currentTarget.getAttribute('data-id'), 'qtd': event.currentTarget.value}).done(function () {
                        buscaFrete();
                    });
                }
            }

        }

        calculaPrecos();

        $('header .barra_topo2 .carrinho .resumo').html('Carrinho - R$ ' + numeroParaMoeda(_self.preco.subtotal - _self.preco.descontos));
        $('#carrinho .total span').html(numeroParaMoeda(_self.preco.subtotal - _self.preco.descontos));

        for (n in _self.itens) {

            $('#carrinho .itens .item:eq(' + n + ') .dados .preco .sem_desconto').html('De <span>R$ ' + numeroParaMoeda(_self.itens[n].valor) + '</span>');
            $('#carrinho .itens .item:eq(' + n + ') .dados .preco .valor_final').html('Por R$ ' + numeroParaMoeda(_self.itens[n].valor - _self.itens[n].desconto));
            $('#carrinho .itens .item:eq(' + n + ') .dados .nome span').html('(' + _self.itens[n].qtde + ')');

            if (_self.itens[n].desconto > 0) {
                $('#carrinho .itens .item:eq(' + n + ') .dados .preco .sem_desconto').show();
            } else {
                $('#carrinho .itens .item:eq(' + n + ') .dados .preco .sem_desconto').hide();
            }

        }

        if (pagina == 'carrinho') {

            for (n in _self.itens) {

                $('.main_carrinho .itens .item:eq(' + n + ') .valor_unitario .sem_desconto span').html('R$ ' + numeroParaMoeda(_self.itens[n].valor));
                $('.main_carrinho .itens .item:eq(' + n + ') .valor_unitario .valor_final').html('R$ ' + numeroParaMoeda(_self.itens[n].valor - _self.itens[n].desconto));
                $('.main_carrinho .itens .item:eq(' + n + ') .subtotal').html('R$ ' + numeroParaMoeda((_self.itens[n].valor - _self.itens[n].desconto) * _self.itens[n].qtde));

                if (_self.itens[n].desconto > 0) {
                    $('.main_carrinho .itens .item:eq(' + n + ') .valor_unitario .sem_desconto').show();
                } else {
                    $('.main_carrinho .itens .item:eq(' + n + ') .valor_unitario .sem_desconto').hide();
                }

            }

            $('.checkout_carrinho .totals .total_box .subtotal span').html('R$ ' + numeroParaMoeda(_self.preco.subtotal));

            if (_self.preco.descontos > 0) {
                $('.checkout_carrinho .totals .total_box .descontos').show();
                $('.checkout_carrinho .totals .total_box .descontos span').html('R$ ' + numeroParaMoeda(_self.preco.descontos));
            } else {
                $('.checkout_carrinho .totals .total_box .descontos').hide();
            }

            if (_self.preco.cupom > 0) {
                $('.checkout_carrinho .totals .total_box .cupom').show();
                $('.checkout_carrinho .totals .total_box .cupom span').html('R$ ' + numeroParaMoeda(_self.preco.cupom));
            } else {
                $('.checkout_carrinho .totals .total_box .cupom').hide();
            }

            if (_self.preco.frete > 0) {
                $('.checkout_carrinho .totals .total_box .frete').show();
                $('.checkout_carrinho .totals .total_box .frete span').html('R$ ' + numeroParaMoeda(_self.preco.frete));
            } else {
                $('.checkout_carrinho .totals .total_box .frete').hide();
            }

            $('.checkout_carrinho .totals .total_box .total_geral span').html('R$ ' + numeroParaMoeda(_self.preco.total));

            $('.checkout_carrinho .totals .parcelamento span').html('<strong>R$ ' + numeroParaMoeda(_self.preco.total / 10) + '</strong> OU R$ ' + numeroParaMoeda(_self.preco.total));
            $('.checkout_carrinho .totals .avista span').html('R$ ' + numeroParaMoeda(_self.preco.total * 0.95));
            $('.checkout_carrinho .totals .pokerstars span').html('US$ ' + numeroParaMoeda(_self.preco.total * 0.95 / cotacao_dollar));

        } else if (pagina == 'checkout') {

            $('#checkout_resume .itens').html($('header #carrinho .itens').html());
            $('#checkout_resume .itens button.excluir').remove();

            $('button.checkout_resume span').html('R$ ' + numeroParaMoeda(_self.preco.total));
            $('#checkout_resume .total .subtotal span').html('R$ ' + numeroParaMoeda(_self.preco.subtotal - _self.preco.descontos));
            if (_self.preco.cupom > 0) {
                $('#checkout_resume .total .cupom').show();
                $('#checkout_resume .total .cupom span').html('R$ ' + numeroParaMoeda(_self.preco.cupom));
            } else {
                $('#checkout_resume .total .cupom').hide();
            }
            if (_self.preco.frete > 0) {
                $('#checkout_resume .total .frete').show();
                $('#checkout_resume .total .frete span').html('R$ ' + numeroParaMoeda(_self.preco.frete));
            } else {
                $('#checkout_resume .total .frete').hide()
            }

            $('.metodo_pagamento .pgto_deposito .valor').html('R$ ' + numeroParaMoeda(_self.preco.total * 0.95) + ' (5% de desconto)');
            $('.metodo_pagamento .pgto_boleto .valor').html('R$ ' + numeroParaMoeda(_self.preco.total * 0.95) + ' (5% de desconto)');
            $('.metodo_pagamento .pgto_cielo .valor').html('R$ ' + numeroParaMoeda(_self.preco.total));
            $('.metodo_pagamento .pgto_cielo_transparente .valor').html('R$ ' + numeroParaMoeda(_self.preco.total));
            $('.metodo_pagamento .pgto_pagseguro .valor').html('R$ ' + numeroParaMoeda(_self.preco.total));
            $('.metodo_pagamento .pgto_pokerstars .valor').html('US$ ' + numeroParaMoeda(_self.preco.total * 0.95 / cotacao_dollar) + ' (5% de desconto)');



            //simulação de parcelamento cielo e pagseguro
            $('.metodo_pagamento .pgto_cielo .opcoes_parcelas ul,.metodo_pagamento .pgto_cielo_transparente .opcoes_parcelas ul, .metodo_pagamento .pgto_pagseguro .opcoes_parcelas ul').html('');

            for (c = 0; c <= 10; c++) {
                if (c == 0) {
                    $('<li>Débito - R$ ' + numeroParaMoeda(_self.preco.total) + ' (à vista)</li>').appendTo('.metodo_pagamento .pgto_cielo .opcoes_parcelas ul');
                    $('<li>Débito - R$ ' + numeroParaMoeda(_self.preco.total) + ' (à vista)</li>').appendTo('.metodo_pagamento .pgto_pagseguro .opcoes_parcelas ul');
                } else {
                    $('<li>' + c + 'x sem juros de R$ ' + numeroParaMoeda(_self.preco.total / c) + '</li>').appendTo('.metodo_pagamento .pgto_cielo .opcoes_parcelas ul');
                    $('<li>' + c + 'x sem juros de R$ ' + numeroParaMoeda(_self.preco.total / c) + '</li>').appendTo('.metodo_pagamento .pgto_cielo_transparente .opcoes_parcelas ul');
                    if (c <= 3)
                        $('<li>' + c + 'x sem juros de US$ ' + numeroParaMoeda(_self.preco.total / c) + '</li>').appendTo('.metodo_pagamento .pgto_pagseguro .opcoes_parcelas ul');
                }

            }


        }
    }

    function calculaPrecos() {

        //CALCULO DOS DESCONTOS
        _self.preco.descontos = 0;
        for (i in _self.itens) {
            _self.itens[i].desconto = 0;
            for (j in _self.itens[i].descontos) {
                _self.tempVar = 0;
                for (i2 in _self.itens) {
                    for (j2 in _self.itens[i2].descontos) {
                        if (_self.itens[i].descontos[j].id == _self.itens[i2].descontos[j2].id) {
                            _self.tempVar += parseInt(_self.itens[i2].qtde);
                        }
                    }
                }
                if (_self.tempVar >= _self.itens[i].descontos[j].qtde) {
                    _self.itens[i].desconto = _self.itens[i].descontos[j].valor;
                    if (_self.itens[i].descontos[j].tipo == 'porcentagem') {
                        _self.itens[i].desconto = _self.itens[i].valor * _self.itens[i].desconto / 100;
                    }
                }
            }

            _self.preco.descontos += _self.itens[i].desconto * _self.itens[i].qtde;


            /*
             //imprime no console o desconto de cada item
             if (_self.itens[i].desconto > 0) {
             if (_self.itens[i].desconto.tipo == 'porcentagem') {
             console.log('iten [' + i + '] do carrinho vai ter ' + parseInt(_self.itens[i].desconto.valor) + '% de desconto');
             } else {
             console.log('iten [' + i + '] do carrinho vai ter R$ ' + numeroParaMoeda(_self.itens[i].desconto.valor * _self.itens[i].qtde) + ' de desconto');
             }
             } else {
             console.log('iten [' + i + '] do carrinho não tem nenhum desconto');
             }
             */

        }

        _self.qtdItens = 0;
        _self.preco.subtotal = 0;
        for (i in _self.itens) {
            _self.qtdItens += _self.itens[i].qtde;
            _self.preco.subtotal += (parseInt(_self.itens[i].qtde) * parseFloat(_self.itens[i].valor));
        }
        _self.preco.total = parseFloat(_self.preco.subtotal - _self.preco.descontos);
        _self.preco.cupom = _self.cupom.valor;
        if (_self.cupom.tipo == 'porcentagem') {
            _self.preco.cupom = _self.preco.total * _self.cupom.valor / 100;
        }

        _self.preco.total = _self.preco.total - _self.preco.cupom + parseFloat(_self.preco.frete);
        //console.log(_self.preco.subtotal+' - '+_self.preco.descontos+' - '+_self.preco.cupom+' + '+_self.preco.frete+' = '+_self.preco.total);

    }

    function excluirCarrinho(id) {
        var carrinho_item = $('.mycart .itens .item[data-id=' + id + ']');
        carrinho_item.animate({'opacity': .4});
        $.getJSON('script/product/carrinhoExcluir', {'id': id}, function (data) {
            if (data.return) {
                carrinho_item.animate({'opacity': 0}, function () {
                    $(this).remove();
                    $rp_cmpnts.cart.atualizarCarrinho();
                    carrinhoVazio();
                    renderizarPrecos();
                    if (pagina == 'produto')
                        $rp_cmpnts.produto.renderizarPrecos();

                    ////se está na edição do produto que deseja apagar
                    if (typeof pagina_edit != "undefined" && pagina_edit == id) {
                        window.location.href = "produto/" + pagina_seo;
                    }

                });
                buscaFrete();
            } else {
                alert(data.error);
            }


        });
    }

    function carrinhoVazio() {

        if ($('.mycart .itens .item').length == 0) {
            $('.mycart .vazio').show();
            $('.mycart .itens, .mycart .actions, .mycart .total').hide();
        }

    }

}

function produto() {

    var _self = this;
    var tempVar = '';

    this.preco = {
        unitario: parseFloat($('input[name=quantidade]').attr('data-valor-unitario'))
    }

    this.renderizarPrecos = renderizarPrecos;

    init();

    function init() {

        //descontos possiveis
        _self.descontos = []; // precisam estar em ordem crescente!
        $('.barra_preco .quantidade input.descontos').each(function (i) {
            _self.descontos.push({
                id: $(this).attr('data-id'),
                tipo: $(this).attr('data-tipo'),
                qtde: $(this).attr('data-qtde'),
                valor: $(this).attr('data-valor')
            });
        });

        desabilitaConjunto(false);

        //inicializa evento de select que desabilita conjuntos
        $('.personalizacoes .conjuntos .conjunto .selection > select').change(function (e) {
            desabilitaConjunto(e);
        });

        //inicializa evento para file inputs que atualiza o resumo do produto
        $('.personalizacoes .conjuntos .conjunto .selection .box-selection input:file').change(renderizarConfiguracoes);

        //inicializa evento para inputs que atualiza o resumo do produto
        $('.personalizacoes .conjuntos .conjunto .selection .box-selection input').keyup(renderizarConfiguracoes);

        //inicializa evento para inputs que atualiza o preço ao trocar quantidade do produto
        $('input[name=quantidade]').keyup(renderizarPrecos).change(renderizarPrecos).blur(renderizarPrecos);
        $('input[name=quantidade_mobile]').keyup(renderizarPrecos).change(renderizarPrecos).blur(renderizarPrecos);

        $('.personalizacoes .conjuntos .conjunto .selection .box-selection .close').click(function () {
            $(this).parents('.box-selection').hide();
        });

        //verifica as imagens a serem ampliadas e incicia o plugin e cria evento click
        $('.imgs span[data-ampliar]').each(function () {
            new photoswipe_init('.' + $(this).attr('data-ampliar'));
            $(this).click(function () {
                $('a.' + $(this).attr('data-ampliar')).trigger('click');
            });
        });

        //verifica vídeos a serem ampliados e incicia o plugin e cria evento click
        $('.imgs span[data-video]').each(function () {
            $(this).click(function () {
                $('body').popup({'url': '_inc_video.php?id=' + $(this).attr('data-video'), 'width': ($(window).width() * 0.9), 'height': ($(window).width() * 0.4), 'closebutton': '.close'});
            });
        });

        //inicializa color-picker 
        $('input.cp-spectrum').spectrum({showPaletteOnly: true,
            togglePaletteOnly: true,
            togglePaletteMoreText: 'more',
            togglePaletteLessText: 'less',
            palette: [
                ["#000", "#444", "#666", "#999", "#ccc", "#eee", "#f3f3f3", "#fff"],
                ["#f00", "#f90", "#ff0", "#0f0", "#0ff", "#00f", "#90f", "#f0f"],
                ["#f4cccc", "#fce5cd", "#fff2cc", "#d9ead3", "#d0e0e3", "#cfe2f3", "#d9d2e9", "#ead1dc"],
                ["#ea9999", "#f9cb9c", "#ffe599", "#b6d7a8", "#a2c4c9", "#9fc5e8", "#b4a7d6", "#d5a6bd"],
                ["#e06666", "#f6b26b", "#ffd966", "#93c47d", "#76a5af", "#6fa8dc", "#8e7cc3", "#c27ba0"],
                ["#c00", "#e69138", "#f1c232", "#6aa84f", "#45818e", "#3d85c6", "#674ea7", "#a64d79"],
                ["#900", "#b45f06", "#bf9000", "#38761d", "#134f5c", "#0b5394", "#351c75", "#741b47"],
                ["#600", "#783f04", "#7f6000", "#274e13", "#0c343d", "#073763", "#20124d", "#4c1130"]
            ],
            change: renderizarConfiguracoes
        });

        //inicializa mascaras 
        //$('input[data-format=float]').inputmask('decimal', {'radixPoint':',', 'numericInput': true, 'digits': 2});
        //$('input[data-format=float]').inputmask({'mask':'[9][.9][9][9][.9][9]9,99', 'numericInput': true, 'greedy':false, 'placeholder': '0', 'rightAlign': true});
        //$('input[name=quantidade]').inputmask({'mask':'[9][9][9][9]99', 'numericInput': true, 'greedy':false, 'placeholder': '0'});
        $('input[name=quantidade]').inputmask('integer', {rightAlign: false, 'min': $('input[name=quantidade]').attr('data-quantidade-minima')});
        $('input[name=quantidade_mobile]').inputmask('integer', {rightAlign: false, 'min': $('input[name=quantidade_mobile]').attr('data-quantidade-minima')});

    }

    function desabilitaConjunto(event) {

        if (event != false) {
            if ($(event.currentTarget).find('option:selected[data-type]').attr('data-type') == 'text') {
                //$(event.currentTarget).parents('.selection').find('.box-selection').show();
            }
        }

        //habilita todos conjuntos
        $('.personalizacoes .conjuntos .conjunto .selection.disabled').removeClass('disabled');

        //percorre selects primarios cuja precisam desabilitar os conjuntos necessários
        $('.personalizacoes .conjuntos .conjunto .selection > select option:selected[data-desabilitar]').each(function () {

            //busca array para desebilitar
            _self.tempVar = $(this).attr('data-desabilitar').split(',');

            //percorre o(s) select(s) que precisa(m) dasabilitar
            for (i in _self.tempVar) {

                //desabilita select
                $('.personalizacoes .conjuntos .conjunto [name=' + _self.tempVar[i] + ']').parent('.selection').addClass('disabled');

                //volta o valor padrão do select
                $('.personalizacoes .conjuntos .conjunto select[name=' + _self.tempVar[i] + '] option[data-default]').prop('selected', true);

            }

        });

        //percorre selects primarios e atualiza/altera as imagens
        $('.personalizacoes .conjuntos .conjunto .selection > select').each(function () {

            //esconde imagens
            $(this).parents('.conjunto').find('.imgs span:visible').hide();

            //verifica se imagem está carregada
            if ($(this).parents('.conjunto').find('.imgs span:eq(' + $(this).find('option:selected').index() + ')').attr('data-image') != 'loaded') {
                $(this).parents('.conjunto').find('.imgs span:eq(' + $(this).find('option:selected').index() + ')').html('<img src="' + $(this).parents('.conjunto').find('.imgs span:eq(' + $(this).find('option:selected').index() + ')').attr('data-image') + '">');
                $(this).parents('.conjunto').find('.imgs span:eq(' + $(this).find('option:selected').index() + ')').attr('data-image', 'loaded');
            }

            //mostra imagem
            $(this).parents('.conjunto').find('.imgs span:eq(' + $(this).find('option:selected').index() + ')').css({'display': 'block'});

        });

        //habilitam as imagens que não dependem de select
        $('.personalizacoes .conjuntos .conjunto').not('.personalizacoes .conjuntos .conjunto').find('.imgs span').show();


        //fecha todos os box-selection
        $('.personalizacoes .conjuntos .conjunto .selection .box-selection').hide();

        //percorre selects primarios e abre os respectivos box-selection
        $('.personalizacoes .conjuntos .conjunto .selection > select option:selected[data-type]').each(function () {

            //abre box-selection do tipo selecionado
            $(this).parents('.selection').find('.box-selection.type-' + $(this).attr('data-type')).show();

        });

        //verifica se existe algum box-selection fechado (não selecionado) e limpa o campo interno
        $('.personalizacoes .conjuntos .conjunto .selection .box-selection:hidden').each(function () {
            /*
             _self.tempVar = $(this).find('input[type=file]');
             _self.tempVar.replaceWith(_self.tempVar.val('').clone(true));
             */

            //limpa o campo
            $(this).find('input').val('');

            //se for um file-input alterar o label também
            $(this).find('input[type=file] + label').find('span').html($(this).find('input[type=file] + label').attr('data-label'));
        });

        $('.barra_preco .quantidade').addClass('change');
        setTimeout(function () {
            $('.barra_preco .quantidade').removeClass('change');
        }, 500);


        renderizarConfiguracoes();
        renderizarPrecos();

    }

    function renderizarConfiguracoes() {

        //limpa as configurações (resumo do produto)
        $('.resumo_produto .configuracoes').html('');

        //percorre todos os conjuntos e atualiza o resumo do produto
        $('.personalizacoes .conjuntos .conjunto').each(function () {

            //valor do select
            _self.tempVar = $(this).find('select option:selected').attr('data-descricao');

            //se existir box-select, percorre e paga o valor preenchido
            $(this).find('.box-selection').each(function () {
                if ($(this).find('input').val() != "") {
                    _self.tempVar = $(this).find('input').val();
                }
            });

            $('<span><strong>' + $(this).attr('data-nome') + '</strong> ' + _self.tempVar + '</span>').appendTo('.resumo_produto .configuracoes');
        });

    }

    function renderizarPrecos(event) {

        //valida quantidade mínima a partir do evento blur
        if (typeof event != 'undefined') {
            if (event.currentTarget.name == "quantidade_mobile")
                $('input[name=quantidade]').val(event.currentTarget.value);
            if (event.currentTarget.name == "quantidade")
                $('input[name=quantidade_mobile]').val(event.currentTarget.value);
            if (event.type == 'blur') {
                if (parseInt($('input[name=quantidade]').val()) < parseInt($('input[name=quantidade]').attr('data-quantidade-minima'))) {
                    alert('Quantidade mínima para este produto é ' + parseInt($('input[name=quantidade]').attr('data-quantidade-minima')));
                    $('input[name=quantidade]').val(parseInt($('input[name=quantidade]').attr('data-quantidade-minima')));
                }
            }
        }

        _self.quantidade = parseInt($('input[name=quantidade]').val());

        _self.preco.personalizacoes = 0;

        //percorre todos os selects e soma preco
        $('.personalizacoes .conjuntos .conjunto .selection > select option:selected[data-preco]').each(function () {
            _self.preco.personalizacoes += parseFloat($(this).attr('data-preco'));
        });


        _self.preco.unitario_personalizado = parseFloat(_self.preco.unitario + _self.preco.personalizacoes);
        _self.preco.desconto = 0;

        //verifica se existe descontos similares no carrinho e soma quantidade de produtos
        _self.tempVar = _self.quantidade; // será somado com a quantidade existente no carrinho
        for (i in _self.descontos) {
            _self.tempVar = _self.quantidade;
            for (j in $rp_cmpnts.cart.itens) {
                for (n in $rp_cmpnts.cart.itens[j].descontos) {
                    if ($rp_cmpnts.cart.itens[j].descontos[n].id == _self.descontos[i].id) {
                        _self.tempVar += parseInt($rp_cmpnts.cart.itens[j].qtde);
                        //console.log('['+i+']['+j+']['+n+'] : '+_self.tempVar);
                    }
                }

            }
            //verifica qual desconto será dado            
            if (_self.tempVar >= _self.descontos[i].qtde) {
                _self.preco.desconto = _self.descontos[i].valor;
                if (_self.descontos[i].tipo == 'porcentagem') {
                    _self.preco.desconto = _self.preco.unitario_personalizado * _self.preco.desconto / 100;
                }
            }
        }

        //se tiver atualizando produto do carrinho
        if (pagina_edit != '') {
            //atualizar dados no carrinho
            $rp_cmpnts.cart.itens[$('#carrinho .itens .item[data-id=' + pagina_edit + ']').index()].qtde = _self.quantidade;
            $rp_cmpnts.cart.itens[$('#carrinho .itens .item[data-id=' + pagina_edit + ']').index()].valor = _self.preco.unitario_personalizado;
            $rp_cmpnts.cart.renderizarPrecos();
        }

        _self.preco.total = (_self.preco.unitario_personalizado - _self.preco.desconto) * _self.quantidade;
        _self.preco.parcelado = _self.preco.total / 10;
        _self.preco.avista = _self.preco.total * 0.95; // 5% de desconto
        _self.preco.pokerstars = _self.preco.avista / cotacao_dollar;


        $('.barra_preco .quantidade .valor_unitario span.sem_desconto').html("R$ " + numeroParaMoeda(_self.preco.unitario_personalizado));
        $('.barra_preco .quantidade .valor_unitario span.valor_final').html("R$ " + numeroParaMoeda(_self.preco.unitario_personalizado - _self.preco.desconto));
        $('.barra_preco .total .parcelamento span, .mobile_valores .total .parcelamento span').html("R$ " + numeroParaMoeda(_self.preco.parcelado));
        $('.barra_preco .total .avista span, .mobile_valores .total .avista span').html("R$ " + numeroParaMoeda(_self.preco.total));
        $('.barra_preco .descontos .avista span, .mobile_valores .descontos .avista span').html("R$ " + numeroParaMoeda(_self.preco.avista));
        $('.barra_preco .descontos .pokerstars span, .mobile_valores .descontos .pokerstars span').html("US$ " + numeroParaMoeda(_self.preco.pokerstars));

        $('.mobile_valores .valor_unitario span.sem_desconto').html("R$ " + numeroParaMoeda(_self.preco.unitario_personalizado));
        $('.mobile_valores .valor_unitario span.valor_final').html("R$ " + numeroParaMoeda(_self.preco.unitario_personalizado - _self.preco.desconto));

        if (_self.preco.desconto > 0) {

            if ($(window).width() > 760) {
                $('.barra_preco .quantidade .valor_unitario .sem_desconto').show();
            } else {
                $('.barra_preco .quantidade .valor_unitario .sem_desconto').hide();
            }

            $('.mobile_valores .valor_unitario span.sem_desconto').show();
        } else {
            $('.barra_preco .quantidade .valor_unitario .sem_desconto').hide();
            $('.mobile_valores .valor_unitario span.sem_desconto').hide();
        }

    }

}

function preencherEndereco(cep) {
    cep = limpaCep($(".mask-cep").val());
    if (cep.length == 8) {
        $.get('https://viacep.com.br/ws/' + cep + '/json/', function (resultadoCEP) {
            /*if (resultadoCEP.tipoLogradouro == '') {
                alert('Esse CEP não foi localizado em nossa base, por favor confira se o mesmo está correto.');
            }
            $('input[name=endereco]').val(decodeURIComponent(escape(resultadoCEP.tipoLogradouro + ' ' + resultadoCEP.logradouro)));
            $('input[name=bairro]').val(decodeURIComponent(escape(resultadoCEP.bairro)));

            $('.estado option').each(function () {
                if ($(this).attr("data-uf") == resultadoCEP.estado) {
                    $(this).prop("selected", true);
                    $(".estado").attr('data-auto-select-cidade', decodeURIComponent(escape(resultadoCEP.cidade)));
                    $(".estado").trigger("change");
                }
            });*/
            if (!("erro" in resultadoCEP)) {
                $('input[name=endereco]').val(resultadoCEP.logradouro);
                $('input[name=bairro]').val(resultadoCEP.bairro);
                $('.estado option').each(function () {
                    if ($(this).attr("data-uf") == resultadoCEP.uf) {
                        $(this).prop("selected", true);
                        $(".estado").attr('data-auto-select-cidade', resultadoCEP.localidade);
                        $(".estado").trigger("change");
                    }
                });
            }else{
                alert('Esse CEP não foi localizado em nossa base, por favor confira se o mesmo está correto.');
            }
        });
    }
}

function moedaParaNumero(valor) {
    return isNaN(valor) == false ? parseFloat(valor) : parseFloat(valor.replace("R$", "").replace(".", "").replace(",", "."));
}

function numeroParaMoeda(n, c, d, t) {

    /*
     n = numero a converter
     c = numero de casas decimais
     d = separador decimal
     t = separador milhar
     */

    c = isNaN(c = Math.abs(c)) ? 2 : c, d = d == undefined ? "," : d, t = t == undefined ? "." : t, s = n < 0 ? "-" : "", i = parseInt(n = Math.abs(+n || 0).toFixed(c)) + "", j = (j = i.length) > 3 ? j % 3 : 0;
    return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
}

function limpaCep(cep) {
    cep = cep.replace(/[^0-9.]/g, '');
    cep = cep.replace('.', '');
    return cep;
}

function setCookie(cname, cvalue, exdays) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
    var expires = "expires=" + d.toUTCString();
    document.cookie = cname + "=" + cvalue + "; " + expires;
}

function getCookie(cname) {
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}

var cmpnts = {};
function abrepopup(url, callback) {
    $('<div id="popupoverlay_container"></div>').appendTo('body');
    cmpnts.popup_container = $('#popupoverlay_container');
    cmpnts.popup_container.exit = function(){
        cmpnts.popup_container.popupoverlay('hide');
    }

    $('#popupoverlay_container').popupoverlay({
        transition: 'all 0.3s',
        scrolllock: true, // optional
        blur: false,
        escape: false,

        closetransitionend: function () {
            $('#popupoverlay_container, #popupoverlay_container_wrapper, #popupoverlay_container_background').remove();
        }
    });

    $('#popupoverlay_container').load(url, function () {
        $('#popupoverlay_container').popupoverlay('show');
        if ($.isFunction(callback))
            callback('#popupoverlay_container');
        $('#popupoverlay_container ._close, #popupoverlay_container .btn_cancel').click(function () {
            cmpnts.popup_container.exit();
            $("body .cf_errorbox").remove();
        });
    });
}
