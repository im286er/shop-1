(function($){
	$(document).ready(function(){
		if($.browser.msie && $.browser.version == 8.0){
			$('#main-container .top-label .discription .join-it .join-top').css('margin-top','28px');
		}
		else if($.browser.msie && $.browser.version == 7.0){
			$('#main-container .top-label .discription .join-it .join-top').css('margin-top','28px');
			$('#header .shopping-cart img').css('margin-top','-10px');
		}
		else if($.browser.msie && $.browser.version == 6.0){
			alert('您的浏览器版本过低，请升级您的浏览器！');
		}
		else if($.browser.msie){
			$('#photograph .bottom-bar').css('background','#ddd');
		}
	});
})(jQuery);