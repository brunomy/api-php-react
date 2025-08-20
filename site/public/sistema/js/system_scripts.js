//formata valor para Real
function formatReal(numero) {
    var numeroStr = '';
    numeroStr = numero.toString();
    if (numero - (Math.round(numero)) == 0) {
        numeroStr = numeroStr + '00';
        numeroStr = numeroStr.replace(/(\d{1})(\d{8})$/, "$1.$2");  //coloca ponto antes dos últimos 8 digitos
        numeroStr = numeroStr.replace(/(\d{1})(\d{5})$/, "$1.$2");  //coloca ponto antes dos últimos 5 digitos        
        numeroStr = numeroStr.replace(/(\d{1})(\d{1,2})$/, "$1,$2");	//coloca virgula antes dos últimos 2 digitos
        return numeroStr;
    }
    var parteDecial = numeroStr.slice(numeroStr.indexOf('.'), numeroStr.length);
    if (parteDecial.length == 2) {
        parteDecial = parteDecial + '0';
    }
    parteDecial = parteDecial.replace('.', ',');
    var parteInteira = numeroStr.slice(0, numeroStr.indexOf('.'));
    var vetorParteInteira = [];
    for (var i = 0; i < parteInteira.length; i++) {
        vetorParteInteira.push(parteInteira.slice(i, i + 1));
    }
    console.log(vetorParteInteira);
    var parteInteiraFinal = '';
    var comprimento = vetorParteInteira.length - 1;
    for (var i = 0; i < vetorParteInteira.length; i++) {
        if (((((comprimento - i) + 1) / 3) - (Math.floor((((comprimento - i) + 1) / 3)))) == 0 && (((comprimento - i) + 1) != vetorParteInteira.length)) {
            parteInteiraFinal = parteInteiraFinal + '.' + vetorParteInteira[i];
        } else {
            parteInteiraFinal = parteInteiraFinal + vetorParteInteira[i];
        }
    }
    var valorFinalCorrigido = parteInteiraFinal + parteDecial;
    return valorFinalCorrigido;
}
var lista_del = []; //array com ids a serem deletados

document.addEventListener("wheel", function(event){
    if(document.activeElement.type === "number"){
        document.activeElement.blur();
    }
});

$(document).ready(function (e) {

    if(typeof pagina !== "undefined"){

        if (pagina == "group" || pagina == "user") {

            //##########  PAGINA GRUPOS DE USUÁRIOS
            //###################################### ############################### ##########################

            $("#ler_todas").change(function () {
                if ($(this).is(":checked")) {
                    $("input.leitura").attr("checked", "checked");

                } else {
                    $("input.leitura").removeAttr("checked");

                }
            });
            $("#gravar_todas").change(function () {
                if ($(this).is(":checked")) {
                    $("input.gravacao").attr("checked", "checked");

                } else {
                    $("input.gravacao").removeAttr("checked");

                }
            });
            $("#editar_todas").change(function () {
                if ($(this).is(":checked")) {
                    $("input.edicao").attr("checked", "checked");

                } else {
                    $("input.edicao").removeAttr("checked");

                }
            });
            $("#excluir_todas").change(function () {
                if ($(this).is(":checked")) {
                    $("input.exclusao").attr("checked", "checked");

                } else {
                    $("input.exclusao").removeAttr("checked");

                }
            });

            //permissões individuais
            $("#pi").change(function () {
                if ($(this).is(":checked")) {
                    $(".pi").slideToggle("fast");
                    $("html, body, .main").animate({scrollTop: $("#pi").offset().top - 130}, 1000);
                } else {
                    $(".pi").slideToggle("fast");
                    $("html, body, .main").animate({scrollTop: $("#pi").offset().top - 130}, 1000);
                }
            });

            $("body").on("click", ".esta_funcao", function () {
                var base = $(this);

                if (base.is(":checked")) {
                    base.parents("tr").find("input").prop("checked", true);
                } else {
                    base.parents("tr").find("input").prop("checked", false);
                }

            });

        }
        
    }
    //##########  TODAS DAS PÁGINAS
    //###################################### ############################### ##########################



    //NOVA MÁSCARA PARA TELEFONES
    function maskTelefone(campo) {

        $(campo).mask("(##) ##########");
        var $telefone = $(campo).val();
        var $total = $telefone.length;
        if ($total >= 13) {
            var $last_telefone = "-" + $telefone.substr(-4);
            var $new_telefone = $telefone.substring(0, ($telefone.length - 4));
            $(campo).val($new_telefone + $last_telefone);
        }
    }


    $("body").on("keypres keyup blur", ".telefone, #telefone, #telefone_residencial, #celular, #fone", function () {
        maskTelefone($(this));
    });


    $('.select-all').attr('unselectable', 'on').css('user-select', 'none').on('selectstart', false);

    // reset panel position for page
    $('.admin-logout').click(function (e) {
        e.preventDefault();
        bootbox.confirm({
            message: "Deseja realmente sair do sistema ?",
            title: "Aviso!!!",
            className: "modal-style2",
            callback: function (result) {
                if (result) {
                    window.location.href = "login/sair";
                }
            }
        });
        plugin.centerModal();
    });


    //OCULTAR MENSAGEM DE VALIDAÇÃO DO FORMULÁRIO
    $('body').on('click', 'span.click-form', function () {
        $("input, textarea, select").css("border-color", "#ccc");
        $("span.click-form").hide();
    });

    /* verifica se informação existe em um array, retorna true se existir, caso contrário retorna false */
    Array.prototype.inArray = function (value) {
        var i;
        for (i = 0; i < this.length; i++) {
            if (this[i] == value) {
                return true;
            }
        }
        return false;
    };

    $(".google_analytics").clickform({'validateUrl': "controllers/" + $("#controller_ga").val() + '.php', 'submitButton': '.bt_google_analytics', 'blockScroll': true}, function (data) {
        if (data.type == "success") {
            window.location.reload();
        }
    });
    /*
     * INÍCIO DA ROTINA DELL ALL
     */

    //CRIA LISTA COM TODOS REGISTROS A SEREM APAGADOS



    //oculta por padrão o botão "Apagar registros selecionados"
    $(".fixed_options_footer, .fixed_option_space,  .del-all").hide();

    //marca/desmarca todos registros apresentados na página, para serem apagados
    $("#apagar_todos").change(function () {
        if ($(this).is(":checked")) {
            $("#apagar_geral").removeAttr("checked");
            $(".registros-geral").val(0);
            $("input.del-this").attr("checked", "checked");
        } else {
            $("input.del-this").removeAttr("checked");
        }

        percorreDelThis();

    });


    function percorreDelThis() {
        //percorre todos inputs da classe del-this para ler seus valores e lançá-los na input del-registros do form que solicitará apagá-los
        $("input.del-this").each(function () {

            var id = $(this).attr("id");
            var valor = $(this).val();

            //caso a input esteja selecionado, seu valor é gravado no array lista_del
            //caso contrário, remove o valor do array lista_del
            if ($("#" + id).is(':checked')) {
                if (!lista_del.inArray(valor)) {
                    lista_del.push(valor);
                }

            } else {
                var index = lista_del.indexOf(valor);
                lista_del.splice(index, 1);
            }

            //caso o array lista_del tenha algum id preechido, mostra o botão "Apagar registros selecionados"
            //caso contrário, ele fica oculto
            if (lista_del != "") {
                $(".fixed_options_footer, .fixed_option_space,  .del-all").fadeIn();
                if ($(".registros-geral").val() != 1) {
                    $("#apagar_todos").attr("checked", "checked");
                }
                //caso o controller seja diferente do controller padrão da página, o valor de data-controller é 
                //lançado no input data-controller do form que solicita apagar os registros
                $(".data-controller").val($(this).attr("data-controller"));
                $(".data-id-lista").val($(this).attr("data-id-lista"));
                $(".data-id-segmentacao").val($(this).attr("data-id-segmentacao"));
            } else {
                if ($(".registros-geral").val() != 1) {
                    $(".fixed_options_footer, .fixed_option_space,  .del-all").fadeOut();
                    $("#apagar_todos").removeAttr("checked");
                    $(".data-controller").val("");
                    $(".data-id-lista").val("");
                    $(".data-id-segmentacao").val("");
                }
            }

            //com o array formado, seu valor é lançado na input do form que solicitará apagar os registros
            $(".del-registros").val(lista_del);

        });
    }

    $("#apagar_geral").change(function () {
        if ($(this).is(":checked")) {
            $("#apagar_todos").removeAttr("checked");
            $(".registros-geral").val(1);
            $(".del-registros").val("");
            $(".fixed_options_footer, .fixed_option_space,  .del-all").fadeIn();
            $(".data-controller").val($(this).attr("data-controller"));
            $(".data-id-lista").val($(this).attr("data-id-lista"));
            $(".data-id-segmentacao").val($(this).attr("data-id-segmentacao"));
            $("input.del-this").attr("checked", "checked");
            percorreDelThis();

        } else {
            $("#apagar_todos").removeAttr("checked");
            $(".registros-geral").val(0);
            $(".del-registros").val("");
            $(".fixed_options_footer, .fixed_option_space,  .del-all").fadeOut();
            $(".data-controller").val("");
            $(".data-id-lista").val("");
            $(".data-id-segmentacao").val("");
            $("input.del-this").removeAttr("checked");
        }
        uniform();
    });



    //CRIA LISTA PARA APAGAR REGISTROS SELECIONADOS MANUALMENTE                               
    $('body').on('change', '.del-this', function (e) {
        e.preventDefault();

        if ($(".registros-geral").val() == 1) {
            $(".registros-geral").val(0);
            $("#apagar_geral").removeAttr("checked");
            uniform();
        }
        var id = this.id;
        var valor = this.value;

        //caso o registro seja marcado, a id desse registro é lançado no array lista_del
        //caso contrário, seu id é removido do array lista_del
        if ($("#" + id).is(':checked')) {
            lista_del.push(valor);
        } else {
            var index = lista_del.indexOf(valor);
            lista_del.splice(index, 1);
            $("#apagar_todos").removeAttr("checked");
            uniform();
        }

        //caso o array lista_del esteja preenchido, o botão "Apagar registros selecionados" aparece
        //caso contrário, ele se oculta
        if (lista_del != "") {
            $(".fixed_options_footer, .fixed_option_space, .del-all").fadeIn();
            //caso o controller seja diferente do controller padrão da página, o valor de data-controller é 
            //lançado no input data-controller do form que solicita apagar os registros
            $(".data-controller").val($(this).attr("data-controller"));
            $(".data-id-lista").val($(this).attr("data-id-lista"));
            $(".data-id-segmentacao").val($(this).attr("data-id-segmentacao"));
        } else {
            $(".fixed_options_footer, .fixed_option_space,  .del-all").fadeOut();
            $(".data-controller").val("");
            $(".data-id-lista").val("");
            $(".data-id-segmentacao").val("");
            $("#apagar_todos").removeAttr("checked");
            uniform();
        }

        //com o array formado, seu valor é lançado na input do form que solicitará apagar os registros
        $(".del-registros").val(lista_del);

    });
    //AÇÃO DO FORM QUE SOLICITA APAGAR TODOS REGISTROS SELECIONADOS PELO USUÁRIO
    $('body').on('click', '.bt_system_delete_all', function (e) {
        e.preventDefault();
        if (confirm("Deseja realmente apagar todos registros selecionados?")) {
            var registros = $(".del-registros").val().split(','); //valor vindo da input del-registros, array do lista_del
            var ids = []; //aqui será guardado as ids dos registros que não foram possíveis de serem apagados
            var mensagem = null; //mensagem, caso exita, a ser apresentado como alert

            //caso exista um controller alternativo especificado, ele substituirá o controller padrão da página
            var controle = $(".data-controller").val();
            var lista = $(".data-id-lista").val();
            var segmentacao = $(".data-id-segmentacao").val();

            var filtro_segmentacoes = $("#segmentacoes").val();
            var filtro_segmentacoes_agrupar = $("#segmentacoes_agrupar").val();
            var filtro_listas = $("#listas").val();
            var filtro_pesquisa = $(".dataTables_filter input").val();

            if (controle)
                controller = controle;
            else
                controller = controller + "/del";


            var apagar_geral = $(".registros-geral").val();

            if (apagar_geral != 1) {
                //laço com método post ajax que enviará ao controller os registros que deverão ser apagados
                for (var i = 0; i < registros.length; i++) {
                    jQuery.ajax({
                        url: system_path + controller + '/id/' + registros[i] + '/lista/' + lista,
                        type: 'POST',
                        data: {segmentacao: segmentacao, apagar_geral: apagar_geral, filtro_segmentacoes: filtro_segmentacoes, filtro_segmentacoes_agrupar: filtro_segmentacoes_agrupar, filtro_listas: filtro_listas, filtro_pesquisa: filtro_pesquisa},
                        async: false,
                        jsonp: false,
                        success: function (dt) {
                            //caso o controller emita a mensagem "error", significa que o registro não pôde ser apagado 
                            //por ter vinculo com outro registro, nesse caso sua id é adicionada ao array ids
                            if (dt.indexOf("error") != -1) {
                                ids.push(registros[i]);
                            }

                        },
                        complete: function () {

                        }
                    });

                }

                //caso todos registros tenha dado erro ao apagar
                if (ids.length == i)
                    mensagem = "Nenhum registro selecionado foi apagado. Verifique se não estão vinculados a outros registros";
                //caso tenha dado erro ao apagar 1 registro e a quantidade de erro é menor que todos itens solicitados para ser apagados
                else if (ids.length == 1 && ids.length < i)
                    mensagem = "Não foi possivel apagar o registro de id (" + ids + ").\n Verifique se ele não está vinculado a outro registro.";
                //caso tenha dado erro ao apagar 2 ou mais registros e a quantidade de erro é menor que todos itens solicitados para ser apagados    
                else if (ids.length > 1 && ids.length < i)
                    mensagem = "Não foi possivel apagar os registros de ids (" + ids + ").\n Verifique se eles não estão vinculados a outro registro.";

                //caso uma mensagem tenha sido criada
                if (mensagem != null)
                    alert(mensagem);

                //a página só será atualizada caso o número de erros seja diferente do número de solicitações
                if (ids.length != i)
                    window.location.reload();
            } else {

                jQuery.ajax({
                    url: system_path + controller + '/del/id/' + registros[i] + '/lista/' + lista,
                    type: 'POST',
                    data: {segmentacao: segmentacao, apagar_geral: apagar_geral, filtro_segmentacoes: filtro_segmentacoes, filtro_segmentacoes_agrupar: filtro_segmentacoes_agrupar, filtro_listas: filtro_listas, filtro_pesquisa: filtro_pesquisa},
                    async: true,
                    jsonp: true,
                    success: function (dt) {
                        //caso o controller emita a mensagem "error", significa que o registro não pôde ser apagado 
                        //por ter vinculo com outro registro, nesse caso sua id é adicionada ao array ids
                        if (dt.indexOf("error") != -1) {
                            alert("Não foi possivel apagar os registros.\n Verifique se eles não estão vinculados a outro registro.");
                        }

                    },
                    complete: function () {
                        window.location.reload();
                    }
                });

            }
            return true;
        } else {
            //caso o usuário desista de apagar os registros selecionados
            return false;
        }
    });

    /*
     * TERMINA AQUI A ROTINA DELL ALL
     */


    // STATUS
    //------------------------------------------------------------------
    $('body').on('click', '.bt_system_stats', function (e) {
        var $this = $(this);
        $.post("general/status", {"t": $(this).attr('data-table'), "a": $(this).attr("data-action"), "i": $(this).attr("data-id"), "p": $(this).attr("data-permit")}, function (data) {
            if (data == "ativar") {
                $this.html('<img src="images/status_vermelho.png" alt="Ativar">');
                $this.attr("data-action", "ativar");
            } else if (data == "desativar") {
                $this.html('<img src="images/status_verde.png" alt="Ativar">');
                $this.attr("data-action", "desativar");
            } else {
                alert(data);
            }
        });
    });


    // APAGAR
    //------------------------------------------------------------------
    $('body').on('click', '.bt_system_delete', function (e) {
        if (confirm("Deseja realmente apagar esse registro?")) {
            $.post(system_path + $(this).attr("data-controller") + '/del/id/' + $(this).attr("data-id") + '/lista/' + $(this).attr("data-id-lista"), {'retorno': $(this).attr("data-retorno"), "segmentacao": $(this).attr("data-id-segmentacao")}, function (response) {
                if (response.indexOf("error") != -1) {
                    alert("Não foi possivel apagar este registro.\n Verifique se ele não está vinculado a outro registro.");
                } else {
                    window.location.href = response;
                }
            });
            return true;
        } else {
            return false;
        }
    });

    if ($("form[name=main]").length > 0) {
        $("form[name=main]").clickform({"validateUrl": controller}, function (data) {
            if (data.type == "success") {
                window.location.href = retorno;
            }
        });
    }

    //REORDENAR 
    //------------------------------------------------------------------
    if ($("table.sortable").length > 0) {

        var sortable_array = [];

        $(".sortable_save .sortablesubmit").live("click", function () {
            var id = this.id;
            if (id != "")
                controller = id;
            $("table.sortable").fadeTo(500, 0.5, function () {
                $.post(system_path + controller + "/order", {"array": sortable_array}, function (data) {
                    window.location.reload();
                });
            });
        });


        $("table.sortable").sortable({
            opacity: 0.7, cursor: 'move', axis: 'y', items: "tbody tr", update: function (e, ui) {
                sortable_array = $(this).sortable("toArray");
                $(".fixed_options_footer, .fixed_option_space, .sortable_save").show();
            }
        });
    }



    // BUSCA CIDADES
    //------------------------------------------------------------------

    if ($(".getcidades").length) {
        $(".getcidades").change(function () {
            if ($(this).val() === parseInt($(this).val())) {
                var id = $(this).val();
            } else {
                var id = $(this).find("option:selected").attr("data-id");
            }
            $("select[name=cidade]").load("views_ajax/utils-cidades.php?id=" + id, function () {

                $("select[name=cidade]").select2();
            });
        });
    }


    //###################################### ############################### ##########################

});

//plugin pra uniformizar checkbox e radios pra v2
function uniform() {

    var chboxes = $('input[type=checkbox]:not(.no_script)');
    var radios = $('input[type=radio]');

    chboxes.each(function (index) {
        if (typeof $(this).data('class') == "undefined") {
            chboxClass = "checkbox-custom";
        } else {
            chboxClass = $(this).data('class');
        }
        if (typeof $(this).attr('id') == "undefined") {
            chboxId = "chbox" + index;
            $(this).attr('id', chboxId);
        } else {
            chboxId = $(this).attr('id');
        }
        if (typeof $(this).data('label') == "undefined") {
            chboxLabeltxt = "";
        } else {
            chboxLabeltxt = $(this).data('label');
        }
        if (!$(this).parent().hasClass(chboxClass) && !$(this).parent().hasClass('toggle')) {
            $(this).wrap('<div class="' + chboxClass + ' noStyleInputCheckbox">');
            $(this).parent().append('<label for="' + chboxId + '">' + chboxLabeltxt + '</label>');
        }
    });

    radios.each(function (index) {
        if (typeof $(this).data('class') == "undefined") {
            radioClass = "radio-custom";
        } else {
            radioClass = $(this).data('class');
        }
        if (typeof $(this).attr('id') == "undefined") {
            radioId = "radio" + index;
            $(this).attr('id', radioId);
        } else {
            radioId = $(this).attr('id');
        }
        if (typeof $(this).data('label') == "undefined") {
            radioLabeltxt = "";
        } else {
            radioLabeltxt = $(this).data('label');
        }
        if (!$(this).parent().hasClass(radioClass) && !$(this).parent().hasClass('toggle')) {
            $(this).wrap('<div class="' + radioClass + ' noStyleInputCheckbox">');
            $(this).parent().append('<label for="' + radioId + '">' + radioLabeltxt + '</label>');
        }
    });
}

function tinymceInit(config) {

    var defaults = {
        upload: true,
        css: 'tinymce.css',
        galeria: false,
        status_pagamento: false
    }

    var config = $.extend(defaults, config);
    var galerias = "";
    var pedidoNome = "";
    var pedidoId = "";

    init_object = {
        entity_encoding: "raw",
        selector: "textarea.tinymce",
        setup: function (editor) {

            editor.addButton('customiza', {
                type: 'menuButton',
                text: 'Customização',
                icon: false,
                menu: [
                    {text: 'Bloco Separador', onclick: function () {
                        editor.insertContent('<div class="clear"></div>');
                    }},
                    {text: 'Bloco 850px', onclick: function () {
                        editor.focus();
                        editor.selection.setContent('<div class="bloco_menor">' + editor.selection.getContent() + '</div>');
                    }}
                ]
            });
            editor.addButton('imgAlign', {
                type: 'menuButton',
                text: 'Imagem',
                icon: false,
                menu: [
                    {text: 'Esquerda', onclick: function () {
                        if(editor.selection.getContent().startsWith('<img ')){
                            str = $('<div></div>').html(editor.selection.getContent()).find('img').removeClass('imgFull').end().html();
                            str = $('<div></div>').html(str).find('img').removeClass('imgRight').end().html();
                            str = $('<div></div>').html(str).find('img').removeClass('imgLeft').end().html();
                            str = $('<div></div>').html(str).find('img').addClass('imgLeft').end().html();
                            editor.selection.setContent(str);
                            console.log(str);
                        }
                    }},
                    {text: 'Direita', onclick: function () {
                        if(editor.selection.getContent().startsWith('<img ')){
                            str = $('<div></div>').html(editor.selection.getContent()).find('img').removeClass('imgFull').end().html();
                            str = $('<div></div>').html(str).find('img').removeClass('imgRight').end().html();
                            str = $('<div></div>').html(str).find('img').removeClass('imgLeft').end().html();
                            str = $('<div></div>').html(str).find('img').addClass('imgRight').end().html();
                            editor.selection.setContent(str);
                            console.log(str);
                        }
                    }},
                    {text: 'Full', onclick: function () {
                        if(editor.selection.getContent().startsWith('<img ')){
                            str = $('<div></div>').html(editor.selection.getContent()).find('img').removeClass('imgFull').end().html();
                            str = $('<div></div>').html(str).find('img').removeClass('imgRight').end().html();
                            str = $('<div></div>').html(str).find('img').removeClass('imgLeft').end().html();
                            str = $('<div></div>').html(str).find('img').addClass('imgFull').end().html();
                            editor.selection.setContent(str);
                            console.log(str);
                        }
                    }}
                ]
            });

            if (config.galeria) {
                galerias = {text: 'Galeria de Fotos', onclick: function () {
                        editor.insertContent('[({GALERIA})]');
                    }}
            }

            if (config.status_pagamento) {
                pedidoNome = {text: 'Nome', onclick: function () {
                        editor.insertContent('[({NOME})]');
                    }}

                pedidoId = {text: 'Id', onclick: function () {
                        editor.insertContent('[({ID})]');
                    }}
            }

            editor.addButton('iserir', {
                type: 'menuButton',
                text: 'Inserir',
                icon: false,
                menu: [
                    galerias,
                    pedidoNome,
                    pedidoId
                ]
            });
        },
        plugins: [
            "advlist autolink lists link image charmap print preview anchor",
            "searchreplace visualblocks code fullscreen",
            "insertdatetime media table contextmenu paste",
            "textcolor"
        ],
        style_formats: [
            {title: 'Título h1', block: 'h1'},
            {title: 'Título h2', block: 'h2'},
            {title: 'Título h3', block: 'h3'}
        ],
        content_css: [system_path + "css/" + config.css]

    }


    if (config.upload) {
        init_object.file_browser_callback = elFinderBrowser;
        init_object.toolbar = "insertfile undo redo | iserir | customiza | imgAlign | styleselect | fontsizeselect | bold italic | removeformat | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | forecolor backcolor";
    } else {
        init_object.toolbar = "insertfile undo redo | iserir | customiza | imgAlign | styleselect | fontsizeselect | bold italic | removeformat | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link | forecolor backcolor";
    }

    tinymce.init(init_object);

}

function elFinderBrowser(field_name, url, type, win) {
    tinymce.activeEditor.windowManager.open({
        file: 'System/includes/elfinder_tinymce_popup.php?root_path=' + root_path, // use an absolute path!
        title: 'elFinder 2.0',
        width: 900,
        height: 600,
        resizable: 'noe'
    }, {
        setUrl: function (url) {
            win.document.getElementById(field_name).value = url;
        }
    });
    return false;
}


//EVENTO COM TECLA DE ATALHOS
function saveKey(active) {
    if (active === true) {
        $(document).on("keydown", function (e) {
            if (e.altKey && e.keyCode == 83) {
                $(".clickformsubmit").trigger('click');
            }
        });
    }
}



function othersKey(active) {
    if (active === true) {
        $(document).on("keydown", function (e) {
            if (e.altKey && e.keyCode == 38) {
                $("#back-to-top").trigger('click');
            }

            else if (e.altKey && e.keyCode == 40) {
                var n = $(document).height();
                $('html, body').animate({scrollTop: n}, 'slow');
            }
        });
    }
}

othersKey(true);

function clean_registros() {
    lista_del = [];
    $(".fixed_options_footer, .fixed_option_space,  .del-all").fadeOut();
    $("#apagar_todos").removeAttr("checked");
    $("#apagar_geral").removeAttr("checked");
    $(".del-registros").val("");
    $(".registros-geral").val("0");
    $(".data-controller").val("");
    $(".data-id-lista").val("");
    $(".data-id-segmentacao").val("");
    uniform();
}