(function($){
	$(document).ready(function(){
		var num = $('#printer-pro-del .slideshow .content ul li').children().length;
		var obj = $('#printer-pro-del .slideshow .content ul li');
		var Maincon = $('#printer-pro-del .slideshow .content ul');
		var LEFTarrow = $('#printer-pro-del .slideshow .left-arrow');
		var RIGHTarrow = $('#printer-pro-del .slideshow .right-arrow');
		function LEFT_arrow_change(){LEFTarrow.css({'background-position':'0 -37px'});};
		function RIGHT_arrow_change(){RIGHTarrow.css({'background-position':'-25px 0'});};
		function RIGHT_arrow_changed(){RIGHTarrow.css({'background-position':'-25px -37px'});};
		function LEFT_arrow_changed(){LEFTarrow.css({'background-position':'0 0'});};
		function right_change(){
			function delay(){
				var move = parseInt(Maincon.css('left'));
				(move < 0)?LEFT_arrow_change():'';
				(move == -(num-4)*154)?RIGHT_arrow_change():'';
			}
			setTimeout(delay,700);
		}
		function left_change(){
			function delay(){
				var move = parseInt(Maincon.css('left'));
				(move==0)?LEFT_arrow_changed():'';
				(move==0)?RIGHT_arrow_changed():'';
			}
			setTimeout(delay,700);
		}
		$('#printer-pro-del .slideshow .content ul').css({'width':(130+20+4)*num});// img = 130 margin l+r = 20 border = 4
		LEFTarrow.live('click',function(){
			var move = parseInt(Maincon.css('left'));
			if(!Maincon.is(':animated')){
				if(move < 0){
					Maincon.animate({left: '+=154px'},600);
				}
				else{};
			}
			else{};
			left_change();
		});
		RIGHTarrow.live('click',function(){
			var move = parseInt(Maincon.css('left'));
			if(!Maincon.is(':animated')){
				if(move >= -(num-5)*154){
					Maincon.animate({left: '-=154px'},600);
				}
				else{}
			}
			else{}
			right_change();
		});
		$('#printer-pro-del .slideshow .content ul li').each(function(){
			$(this).click(function(){
				var _img = $(this).html();
				obj.removeClass('select');
				$(this).addClass('select');
				$('#printer-pro-del .slideshow .slideshow-img').html(_img);
			});
		});
		//3d printer product detail slideshow
		var ALL_width = $(window).width();
		var ALL_height = $(window).height();
		var obj_width = $('.light-box').width();
		var obj_height = $('.light-box').height();
		$('.light-box').css({'left':(ALL_width-obj_width)/2 + 'px'});
		$('.light-box').css({'top':(ALL_height-obj_height)/2 + 'px'});
		$('.d_close').click(function(){
			$('.masker').fadeOut('slow');
			$('.light-box').fadeOut('slow');
		});
		$('.masker').click(function(){
			$('.masker').fadeOut('slow');
			$('.light-box').fadeOut('slow');
		});
		//comm-light-box

		$('.change-material').live('click',function(){
			$('.masker').fadeIn('slow');
			$('#material-light-box').fadeIn('slow');
		});
		var _name = $('.material .change-material');
		var _img = $('.material .material-img');
		var _price = $('.material .material-price');
		$('#material-light-box .content ul li').each(function(){
			$(this).click(function(){
				var img = $(this).find('.material-img').html();
				var name = $(this).find('.material-name').html();
				var price = $(this).find('.material-price').html();
				_name.html(name);
				_img.html(img);
				_price.html(price);
				$('.light-box').fadeOut('slow');
				$('.masker').fadeOut('slow');
			});
		});
		//material-light-box
		
		$('.people-discuz ul li:even').css('background','#f8f8f8');
		$('.people-discuz ul li:odd').css('background','#f0f0f0');
	});
})(jQuery);