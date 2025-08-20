(function($) {
	$.fn.popup = function(options, callback){
		
		return this.each(function(){
			var defaults = {
				width: 600,
				height: 400,
				bg_opacity: 0.75,
				closebutton: null,
				bg_click_close: true,
				type:"ajax"
			}
			
			var $p = $.extend(defaults, options);
			
			$p.obj = $(this);
			$p.background 	= $("<div style=\"display:none;\"></div>").appendTo("body");
			$p.popupbox 	= $("<div style=\"display:none;\"></div>").appendTo("body");
			
			
			$p.background.fadeTo(01, 0).css({width:"100%",height:$(document).height()+"px",cursor:"pointer",top:0,position:"absolute",background:"#000",zIndex:1000});
			$p.background.fadeTo("slow", $p.bg_opacity);
			if($p.bg_click_close) $p.background.click(function(){close_popup($p)});
			
			
			$p.half = $p.height/2;
			
			position($p);
			
			$p.posx = $p.width*.5;
			
			if($p.width == "100%"){
				$p.popupbox.fadeTo(01, 0).css({position:"absolute", top: ($p.posy+$p.scrollPosition)+"px", zIndex:1000, width:"100%", height:$p.height+"px"});
			}else{
				$p.popupbox.fadeTo(01, 0).css({position:"absolute", top: ($p.posy+$p.scrollPosition)+"px", left:"50%",marginLeft:-$p.posx+"px", zIndex:1000, width:$p.width+"px", height:$p.height+"px"});
			}
			
			$p.popupbox.fadeTo("slow", 1);
			
			
			
			if($p.type == "ajax"){
				//POR AJAX
				$p.popupbox.load($p.url, function(){
					
					if($.isFunction(callback)){
						callback.call($p.obj);
					}
					
					if(closebutton =! null){
						$p.popupbox.find($p.closebutton).click(function(){close_popup($p)});
					}
					
				});
			}else{
				//POR IFRAME
				$("<iframe src='"+$p.url+"' width='"+$p.width+"' height='"+$p.height+"' frameborder='0'></iframe>").appendTo(".popup_box");
			}
			
			
			$(window).scroll(function(){
				if($p.popupbox.length>0){
					position($p);
				}
			});
		});
		
		function close_popup($p){
			$p.popupbox.fadeOut(function(){$(this).remove();});
			$p.background.fadeOut(function(){$(this).remove();});
		}
		
		function position($p){
			
			$p.scrollPosition = $(window).scrollTop();
			console.log($p.scrollPosition);
			
			if($(window).height()>$p.height){
				$p.posy = ($(window).height()*.5) - $p.height*.5;
			}else{
				$p.posy = 0;
			}
			
			$p.popupbox.css("top",($p.posy+$p.scrollPosition)+"px");
		}
	};
})(jQuery);

