
function banners_home(banner) {
    var $banners = {};

    $banners.ctrl = {};
    $banners.ctrl.index = 0;
    $banners.ctrl.length = ($(banner + " .display .banner").length - 1);
    $banners.ctrl.time = 8000;
    $banners.ctrl.tempimage = new Image();

    $banners.dom = {};
    $banners.dom.display = $(banner + " .display");

    $banners.ctrl.nav = {};
    $banners.ctrl.nav.numbers = false;
    $banners.ctrl.nav.arrows = false;

    //SE TIVER NUMERAÇÃO
    if ($(banner + " .numeros").length > 0) {

        $banners.ctrl.nav.numbers = true;
        $banners.dom.numbers = $(banner + " .numeros");

        $banners.dom.numbers.empty();
        for(i=0;i<=$banners.ctrl.length;i++){
            $('<span>'+i+'</span>').appendTo($banners.dom.numbers);
        }
        $banners.dom.numbers.find('span:first').addClass('atual');

        $banners.dom.numbers.find("span").click(function () {
            $banners.ctrl.index = $(this).index();
            change();
        });

    }

    //SE TIVER SETAS DE NAVEGAÇÃO
    if ($(banner + " .setas").length > 0) {

        if($banners.ctrl.length > 0){
            $banners.ctrl.nav.arrows = true;

            $banners.dom.bt_left = $(banner + " .setas .left");
            $banners.dom.bt_right = $(banner + " .setas .right");

            $banners.dom.bt_left.click(prev);
            $banners.dom.bt_right.click(next);
        }else{
            $(banner + " .setas").hide();
        }

    }

    //SE TIVER ANIMAÇÃO DE TEMPO 
    if($(banner + ' .time').length > 0){
        $banners.dom.timeanimation = $(banner + ' .time')
    }

    function next() {
        $banners.ctrl.index++;
        change();
    }

    function prev() {
        $banners.ctrl.index--;
        change();
    }

    function change() {

        clearInterval($banners.ctrl.interval);

        if ($banners.ctrl.index > $banners.ctrl.length)
            $banners.ctrl.index = 0;
        if ($banners.ctrl.index < 0)
            $banners.ctrl.index = $banners.ctrl.length;


        if ($banners.dom.display.find(".banner:eq(" + $banners.ctrl.index + ")").attr("data-image") == "loaded" || $banners.dom.display.find(".banner:eq(" + $banners.ctrl.index + ")").attr("data-image") == "video") {

            if ($banners.ctrl.nav.numbers) {

                $banners.dom.numbers.find("span.atual").removeClass("atual");
                $banners.dom.numbers.find("span:eq(" + $banners.ctrl.index + ")").addClass("atual");

            }

            $banners.dom.display.find(".banner:visible").fadeOut();
            $banners.dom.display.find(".banner:eq(" + $banners.ctrl.index + ")").fadeIn();
            $banners.ctrl.interval = setInterval(next, $banners.ctrl.time);

            timeCount();
            playInternalAnimation();


        } else {

            $banners.ctrl.tempimage.src = $banners.dom.display.find(".banner:eq(" + $banners.ctrl.index + ")").attr("data-image");
            load();

        }

    }

    function load() {
        if ($banners.ctrl.tempimage.complete) {
            $banners.dom.display.find(".banner:eq(" + $banners.ctrl.index + ")").css({'background-image': 'url("' + $banners.ctrl.tempimage.src + '")'}).attr("data-image", "loaded");
            change();
        } else {
            setTimeout(load, 2000);
        }
    }
    
    function timeCount(){
        $banners.dom.timeanimation.dequeue().css({'width':'0%'}).animate({'width':'100%'}, $banners.ctrl.time,'linear');
    }

    function playInternalAnimation(){
        if($banners.dom.display.find('.banner:eq('+ $banners.ctrl.index +') .box').length > 0){
            $banners.dom.display.find('.banner:eq('+ $banners.ctrl.index +') .box > div').each(function(i){
                $(this).css({'opacity':0,'margin-left':50}).delay(i*300).animate({'opacity':1, 'margin-left':0},600,'easeOutQuad');
                //$(this).hide().delay(i*500).fadeIn(800);
            });
        }        
    }


    if ($banners.ctrl.length>0) {
        playInternalAnimation();
        timeCount();
        $banners.ctrl.interval = setInterval(next, $banners.ctrl.time);
    }

    rsz_banners();

    $(window).resize(rsz_banners);


    function rsz_banners() {

        $rsz_ban.percent_w = $(window).width() * 100 / $rsz_ban.width;
        $rsz_ban.percent_h = $(window).height() * 100 / $rsz_ban.height;

        $('.banners').height($(window).height());

        if($rsz_ban.percent_w < $rsz_ban.percent_h) {
            $('.banners .display .banner').css({'background-size':'auto 100%'});
        }else{
            $('.banners .display .banner').css({'background-size':'100% auto'});
        }

        if($banners.dom.display.find(".banner[data-image=video]").length > 0){
            console.log('dd'+$banners.dom.display.find(".banner[data-image=video]").length)
            ///$banners.dom.display.find(".banner[data-image=video] video").width($(window).width()).height($(window).height());
        }


    }
}

var $rsz_ban = {
    width: 1920,
    height: 1080
}
