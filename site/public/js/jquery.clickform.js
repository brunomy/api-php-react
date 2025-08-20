//v 2.2
(function ($) {
    $.fn.clickform = function (options, callback) {

        var ypos                            = 0;
        var $styles                         = {};
        $styles.blocker                     = {
                                                "position": "absolute",
                                                "top": 0,
                                                "width": "100%",
                                                "background": "#000000",
                                                "zIndex": 100002
                                            };
        $styles.blockerMessage              = {
                                                "position": "absolute",
                                                "text-align": "center",
                                                "zIndex": 100002
                                            };
        $styles.successSpan                 = {
                                                "display": "inline-block",
                                                "padding": "15px 20px 15px 35px",
                                                "background":"url(img/icon_clickform_success.png) no-repeat 12px 17px #6f8450",
                                                "border": "1px solid #3f561d",
                                                "-webkit-border-radius": "5px",
                                                "-moz-border-radius": "5px",
                                                "border-radius": "5px",
                                                "font-family": "Arial, Helvetica, sans-serif",
                                                "font-size": 14,
                                                "color": "#ffffff"
                                            };
        $styles.errorSpan                   = {
                                                "display": "inline-block",
                                                "padding": "15px 20px 15px 35px",
                                                "background":"url(img/icon_clickform_error.png) no-repeat 12px 17px #b9110d",
                                                "border": "1px solid #67110f",
                                                "-webkit-border-radius": "5px",
                                                "-moz-border-radius": "5px",
                                                "border-radius": "5px",
                                                "font-family": "Arial, Helvetica, sans-serif",
                                                "font-size": 14,
                                                "color": "#ffffff"
                                            };
        $styles.attentionSpan               = {
                                                "display": "inline-block",
                                                "padding": "15px 20px 15px 35px",
                                                "background": "url(img/icon_clickform_attention.png) no-repeat 12px 17px #f8d61a",
                                                "border": "1px solid #57481e",
                                                "-webkit-border-radius": "5px",
                                                "-moz-border-radius": "5px",
                                                "border-radius": "5px",
                                                "font-family": "Arial, Helvetica, sans-serif",
                                                "font-size": 14,
                                                "color": "#896112"
                                            };
        $styles.messageTipBox               = {
                                                "width": "100%",
                                                "margin-top": "-3px",
                                                "display": "inline-block",
                                                "vertical-align": "middle",
                                                "padding": "5px 8px",
                                                "background-color": "#b9110d",
                                                "-webkit-border-radius": 5,
                                                "-moz-border-radius": 5,
                                                "border-radius": 5,
                                                "-webkit-border-top-right-radius": 0,
                                                "-moz-border-radius-topright": 0,
                                                "border-top-right-radius": 0,
                                                "-webkit-border-top-left-radius": 0,
                                                "-moz-border-radius-topleft": 0,
                                                "border-top-left-radius": 0,
                                                "font-family": "Arial, Helvetica, sans-serif",
                                                "font-size": 14,
                                                "color": "#fff",
                                                "cursor": "pointer"
                                            };


        var $messageBoxStruture = "<div class='cf_errorbox' style='position:absolute;display:none;z-index:1000'><div class='cf_message'></div></div>";

        if (options == "update") {

            useTitles($(this));

        } else {

            this.each(function (i) {

                var defaults = {
                    validateUrl: null,
                    submitUrl: null,
                    conversionUrl: null,
                    useTitles: false,
                    activateEnterKey: false,
                    blockScroll: false,
                    clearFields: true,
                    opacity: .8,
                    submitButton: ".clickformsubmit",
                    waitTimeMessage: 2000
                }

                var config = $.extend(defaults, options);
                var $cf = {};
                $cf.config = config;
                $cf.obj = $(this);
                $cf.domFormObj = this;
                $cf.config.submitButton = $cf.obj.find($cf.config.submitButton);


                var $divs = {};
                $divs.blocker = "";
                $divs.blockerMessage = "";

                $cf.divs = $divs;

                if ($cf.config.useTitles)
                    useTitles($cf.obj);
                if (activateEnterKey)
                    activateEnterKey($cf);

                $cf.config.submitButton.on("click", function (e) {
                    e.preventDefault();
                    clearValidationMessage();
                    if ($cf.config.useTitles)
                        clearTitles($cf);

                    $.when(blockTheForm($cf)).then(function (response) {

                        if ($.isFunction($cf.config.validateUrl)) {

                            $.when($cf.config.validateUrl($cf.domFormObj)).then(function (response) {
                                $cf.response = response;
                                if ($cf.response.time != undefined)
                                    $cf.config.waitTimeMessage = $cf.response.time;
                                if (response.type == "validation") {
                                    $.when(unblockTheForm($cf)).then(validateMessage($cf));
                                } else {
                                    sucessErrorAttentionMessage($cf);
                                }
                            });

                        } else {
                            validateUrl($cf);
                        }

                    });

                });

            });
        }

        function clearValidationMessage() {
            $(".errorElement").removeClass("errorElement");
            $(".cf_errorbox").fadeOut(function () {
                $(this).remove();
            });
        }

        function validateMessage($cf) {

            $cf.obj.find("*[name=" + $cf.response.field + "]").addClass("errorElement").after($messageBoxStruture).trigger('focus');

            $(".cf_message").css($styles.messageTipBox).click(clearValidationMessage);
            $(".cf_message").html($cf.response.message);
            var marginleft = "0";
            var margintop = 0;
            if ($(".form_participe").length > 0) {
                marginleft = "75px";
                margintop = "32px";
            }
            $(".cf_errorbox").css({"width":$cf.obj.find("*[name=" + $cf.response.field + "]").outerWidth(true) - 17,"margin-top": margintop, "margin-left": marginleft}).fadeIn(function () {
                if (!$cf.config.blockScroll) {
                    var o = $(this);
                    var offset = o.offset();
                    $("html").scrollTop(offset.top - 50);
                }
            });
            if ($cf.config.useTitles)
                useTitles($cf.obj);

        }

        function sucessErrorAttentionMessage($cf) {
            //$cf.divs.blockerMessage.find('span').removeClass('loadingSpanAnimate');
            $cf.divs.blockerMessage.fadeOut(function () {
                $(this).remove();

                if ($cf.response.type == "success") {

                    if ($cf.response.message == null || $cf.response.message == "" || typeof $cf.response.message === "undefined") {
                        unblockTheForm($cf);

                        if ($cf.config.conversionUrl != null) {
                            $("<div id='load_conversion_script'></div>").appendTo("body");
                            $("#load_conversion_script").load($cf.config.conversionUrl, function () {
                                $("#load_conversion_script").remove();
                                if ($.isFunction(callback))
                                    callback($cf.response);
                                if ($cf.config.submitUrl != null) {
                                    $cf.obj.attr("action", $cf.config.submitUrl);
                                    $cf.obj.attr("target", "_top").submit();
                                }
                            });
                        }else{
                            if ($.isFunction(callback))
                                callback($cf.response);
                            if ($cf.config.submitUrl != null) {
                                $cf.obj.attr("action", $cf.config.submitUrl);
                                $cf.obj.attr("target", "_top").submit();
                            }
                        }

                    } else {
                        $cf.divs.blockerMessage = $("<div style=\"display:none;\"><span></span></div>");
                        $cf.divs.blocker.after($cf.divs.blockerMessage);
                        $cf.divs.blockerMessage.find("span").addClass("successSpan").html($cf.response.message);
                        $cf.divs.blockerMessage.css($styles.blockerMessage).css({"width": "100%"}).fadeTo(01, 0);
                        messageTopPosition($cf);
                        $cf.divs.blockerMessage.find("span").addClass('zoomInAnimation');
                        $cf.divs.blockerMessage.fadeTo("meddium", 1);
                        setTimeout(function () {
                            unblockTheForm($cf);

                            if ($cf.config.conversionUrl != null) {
                                $("<div id='load_conversion_script'></div>").appendTo("body");
                                $("#load_conversion_script").load($cf.config.conversionUrl, function () {
                                    $("#load_conversion_script").remove();
                                    if ($.isFunction(callback))
                                        callback($cf.response);
                                    if ($cf.config.submitUrl != null) {
                                        $cf.obj.attr("action", $cf.config.submitUrl);
                                        $cf.obj.attr("target", "_top").submit();
                                    }
                                });
                            }else{
                                if ($.isFunction(callback))
                                    callback($cf.response);
                                if ($cf.config.submitUrl != null) {
                                    $cf.obj.attr("action", $cf.config.submitUrl);
                                    $cf.obj.attr("target", "_top").submit();
                                }
                            }

                        }, $cf.config.waitTimeMessage);
                        if ($cf.config.useTitles)
                            useTitles($cf.obj);
                    }

                    if ($cf.config.submitUrl != null) {
                        $cf.obj.attr("action", $cf.config.submitUrl);
                        $cf.obj.attr("target", "_top").submit();
                    }
                    if ($cf.config.clearFields) {
                        $cf.obj.find("input[type=text], textarea").val("");
                        $cf.obj.find("select").find("option:eq(0)").attr("selected", "selected");
                        $cf.obj.find("input[type=checkbox], input[type=radio]").removeAttr("checked");
                        if ($cf.config.useTitles)
                            useTitles($cf.obj);
                    }


                } else {
                    $("input[type=text]").css("border-color", "");
                    $("input[type=password]").css("border-color", "");
                    $("span.click-form").remove();


                    $cf.divs.blockerMessage = $("<div style=\"display:none;\"><span></span></div>");
                    $cf.divs.blocker.after($cf.divs.blockerMessage);
                    switch ($cf.response.type) {
                        case "error":
                            $cf.divs.blockerMessage.find("span").css($styles.errorSpan).html($cf.response.message);
                            break;
                        case "attention":
                            $cf.divs.blockerMessage.find("span").addClass("attentionSpan").html($cf.response.message);

                            break;
                    }

                    $cf.divs.blockerMessage.css($styles.blockerMessage).css({"width": "100%"}).fadeTo(01, 0);
                    messageTopPosition($cf);
                    $cf.divs.blockerMessage.fadeTo("meddium", 1);
                    $cf.divs.blockerMessage.find("span").addClass('zoomInAnimation');

                    setTimeout(function () {
                        unblockTheForm($cf);
                        if ($.isFunction(callback))
                            callback($cf.response);
                    }, $cf.config.waitTimeMessage);

                    if ($cf.config.useTitles)
                        useTitles($cf.obj);
                }

            });


            //$("html, body, .main").animate({scrollTop: $("#" + $cf.response.field).offset().top - 120}, 'slow');

        }

        function blockTheForm($cf) {
            $('#clickformbodywrap').addClass('blur');
            $cf.divs.blockerMessage = $("<div style=\"display:none;\"><span>aguarde carregando</span></div>").prependTo("body");
            $cf.divs.blockerMessage.find("span").addClass('loadingSpan');
            $cf.divs.blockerMessage.css($styles.blockerMessage).css({"width": "100%"}).fadeTo(01, 0);
            $cf.divs.blockerMessage.fadeTo("meddium", 1);
            $cf.divs.blockerMessage.find("span").addClass('zoomInAnimation');
            messageTopPosition($cf);

            $cf.divs.blocker = $("<div style=\"display:none;\"></div>").prependTo("body");
            $cf.divs.blocker.css($styles.blocker).css({"width": $(document).width(), "height": $(document).height()}).fadeTo(01, 0);
            //if(!$cf.config.blockScroll) $("html").scrollTop(parseInt($cf.divs.blockerMessage.css("top").replace("px",""))+($cf.divs.blocker.height()*.5)-$cf.divs.blockerMessage.height()-30);

            $(window).scroll(function () {
                messageTopPosition($cf);
            });

            return $cf.divs.blocker.fadeTo("meddium", $cf.config.opacity);

        }

        function unblockTheForm($cf) {
            $('#clickformbodywrap').removeClass('blur');
            $cf.divs.blockerMessage.fadeOut(function () {
                $(this).remove();
                $cf.divs.blocker.remove();
            }).find('span').removeClass('loadingSpanAnimate');

            $("body").css({"overflow": "auto"});
            return $cf.divs.blocker.fadeOut();

        }

        function messageTopPosition($cf) {
            ypos = ($(window).height() * .5) - ($cf.divs.blockerMessage.outerHeight()) + $(document).scrollTop();
            if (ypos < 0)
                ypos = 0;
            $cf.divs.blockerMessage.css({"top": ypos});
        }

        function useTitles(form) {

            $(form).find("input[title], textarea[title]").each(function () {
                if ($(this).val() == "") {
                    $(this).val($(this).attr("title"));
                }
                $(this).unbind("focus");
                $(this).unbind("blur");
                $(this).focus(function () {
                    if ($(this).val() == $(this).attr("title")) {
                        $(this).val("");
                    }
                })
                        .blur(function () {
                            if ($(this).val() == "") {
                                $(this).val($(this).attr("title"));
                            }
                        });
            });

            $(form).find("input[type=password]").not("input.pwdclickformfocus").each(function () {
                $(this).after($(this).clone().attr("type", "text").addClass("pwdclickform"));
                $(this).remove();
                useTitles(form);
            });
            $(form).find("input.pwdclickform").each(function () {
                $(this).unbind("focus");
                $(this).unbind("blur");
                $(this).focus(function () {
                    if ($(this).val() == $(this).attr("title")) {
                        $(this).after($(this).clone().attr("type", "password").removeClass("pwdclickform").addClass("pwdclickformfocus"));
                        $(this).remove();
                        $("input.pwdclickformfocus").val("").trigger("focus");
                        $("input.pwdclickformfocus").unbind("blur");
                        $("input.pwdclickformfocus").blur(function () {
                            if ($(this).val() == "") {
                                $(this).removeClass("pwdclickformfocus");
                                useTitles(form);
                            }
                        });
                    }
                })
            });

        }

        function clearTitles($cf) {
            $cf.obj.find("input[title], textarea[title]").each(function () {
                if ($(this).val() == $(this).attr("title")) {
                    $(this).val("");
                }
            });
        }

        function activateEnterKey($cf) {
            $cf.obj.find("input[type=text],input[type=password]").keypress(function (event) {
                if (event.keyCode == 13) {
                    $cf.config.submitButton.trigger("click");
                    event.preventDefault();
                }
            });
        }

        function validateUrl($cf) {
            if(! $('#clickformiframe').length) {
                $("<iframe src='javascript:;' name='clickformiframe' id='clickformiframe' style='display:none;'></iframe>").appendTo("body");
            }

            $cf.obj.attr({"action":$cf.config.validateUrl,"target":"clickformiframe"});
            $cf.obj[0].submit();
            return setTimeout(() => loadingUrl($cf), 0);
        }

        function loadingUrl($cf) {
            var iframe = document.getElementById("clickformiframe");
            return iframe.onload = function () {
                returnValidated($cf);
            };
        }
        function returnValidated($cf){

            var ifrmcontent = $("iframe[name='clickformiframe']").contents().children().find("body").text();

            if(IsJsonString(ifrmcontent)){

                $cf.response = $.parseJSON(ifrmcontent);
                if ($cf.response.message != undefined) {
                    $cf.response.message = $cf.response.message.replace("[br]","<br>");
                }


                if($cf.response.time != undefined) $cf.config.waitTimeMessage = $cf.response.time;
                if($cf.response.type == "validation"){
                    $.when(unblockTheForm($cf)).then(validateMessage($cf));
                }else{
                    sucessErrorAttentionMessage($cf);
                }
                // $("iframe[name='clickformiframe']").remove();

            }else{

                $.post($cf.config.errorHandling, {"erro":ifrmcontent});
                $cf.response = {};
                $cf.response.type = "error";
                $cf.response.message = "ERROR : --{"+ifrmcontent+"}--";
                $cf.config.waitTimeMessage = 15000;
                sucessErrorAttentionMessage($cf);
                //$("iframe[name='clickformiframe']").remove();


            }

        }

        function IsJsonString(str) {
            try {
                JSON.parse(str);
            } catch (e) {
                return false;
            }
            return true;
        }

    };

    //var zoomAnimationInitalState = "-webkit-transform:scale(0.5);ms-transform:scale(0.5);o-transform:scale(0.5);transform:scale(0.5);webkit-transition:0.4s;-0-transition:0.4s;transition:0.4s;-webkit-filter: blur(5px);filter: blur(5px);";
    var zoomAnimationInitalState = "";

    var cssrules = "";

    cssrules += "#clickformbodywrap     {webkit-transition:0.4s;-0-transition:0.4s;transition:0.4s;}";

    //cssrules += "#clickformbodywrap.blur{-webkit-filter: blur(2px);filter: blur(2px);-webkit-transform:scale(1.02);ms-transform:scale(1.02);o-transform:scale(1.02);transform:scale(1.02);}";
    cssrules += "#clickformbodywrap.blur{}";

    cssrules += ".errorElement          {border-color: red !important;}";

    cssrules += ".loadingSpan           {display: inline-block;padding:15px 20px 15px 40px;background:url(img/icon_clickform_loader.gif) no-repeat 15px 18px #efefef;border:1px solid #dddddd;font-family:Arial, Helvetica, sans-serif;font-size:14px;color:#a7acae;"+zoomAnimationInitalState+"}";

    cssrules += ".attentionSpan         {display:inline-block;padding:15px 20px 15px 35px;background:url(img/icon_clickform_attention.png) no-repeat 12px 17px #f8d61a;border:1px solid #57481e;font-family:Arial, Helvetica, sans-serif;font-size: 14px;color:#896112;"+zoomAnimationInitalState+"}";

    cssrules += ".successSpan {display:inline-block;padding:15px 20px 15px 35px;background:url(img/icon_clickform_success.png) no-repeat 12px 17px #6f8450;border:1px solid #3f561d;font-family:Arial, Helvetica, sans-serif;font-size: 1;color:#ffffff;"+zoomAnimationInitalState+"}";

    //cssrules += ".zoomInAnimation    {-webkit-transform: scale(1);-ms-transform: scale(1);-o-transform: scale(1);transform: scale(1);-webkit-filter: blur(0);filter: blur(0);}";
    cssrules += ".zoomInAnimation    {}";

    var head = document.getElementsByTagName('head')[0];
    var style = document.createElement('style');
    var declarations = document.createTextNode(cssrules);
    style.type = 'text/css';

    if (style.styleSheet) {
      style.styleSheet.cssText = declarations.nodeValue;
    } else {
      style.appendChild(declarations);
    }

    head.appendChild(style);

    $("body").wrapInner('<div id="clickformbodywrap"></div>');


})(jQuery);

