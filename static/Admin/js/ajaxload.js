(function($){
	$(document).ready(function(){
		var window_width = $(window).width();
		var window_height = $(window).height();
		$('.slide-show .page-num div').click(function(){
			var a = $(this).index();
			$(this).parent().find('div').removeClass('select');
			$(this).addClass('select');
			$('.slide-show .content ul li').fadeOut(1000).eq(a).fadeIn(500);
			$('.slide-show .content ul li .short-dis').slideUp().eq(a).slideDown(1000);
		});
		$('.originality-slideshow .num ul li').click(function(){
			var a = $(this).index();
			$(this).parent().find('li').removeClass('select');
			$(this).addClass('select');
			$('.originality-slideshow .content ul li').fadeOut(1000).eq(a).fadeIn(500);
			$('.originality-slideshow .content ul li .discription').slideUp().eq(a).slideDown(1000);
		});
		$('.originality .right ul li').each(function(){
			$(this).mouseenter(function(){
				$(this).find('.discription').animate({
					top:'-=60px'
				});
			});
			$(this).mouseleave(function(){
				$(this).find('.discription').animate({
					top:'+=60px'
				});
			});
		});
		$('.customer-head-img').hover(
			function(){
				$('.customer-head-img .edit').fadeIn();
			},
			function(){
				$('.customer-head-img .edit').fadeOut();
			}
		);
		$('.img-list ul li .pro-content').live('mouseenter',
			function(){
				$(this).find('.img-masker').fadeIn();
				$(this).find('.edit').fadeIn();
				$(this).find('.delete').fadeIn();
			}
		);
		$('.img-list ul li .pro-content').live('mouseleave',
			function(){
				$(this).find('.img-masker').fadeOut();
				$(this).find('.edit').fadeOut();
				$(this).find('.delete').fadeOut();
			}
		);
		$('.user-content .my-discuz ul li:odd').css('background','#fff');
		$('.operate ul li .display-1').live('click',function(){
			$('.img-grid').removeClass().addClass('img-list');
			$('.operate ul li span.list').removeClass('red-bg').addClass('grey-bg');
			$(this).removeClass('grey-bg').addClass('red-bg');
		});
		$('.operate ul li .display-2').live('click',function(){
			$('.img-list').removeClass().addClass('img-grid');
			$('.operate ul li span.list').removeClass('red-bg').addClass('grey-bg');
			$(this).removeClass('grey-bg').addClass('red-bg');
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
		//product detail slideshow
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
		};
		function left_change(){
			function delay(){
				var move = parseInt(Maincon.css('left'));
				(move==0)?LEFT_arrow_changed():'';
				(move==0)?RIGHT_arrow_changed():'';
			}
			setTimeout(delay,700);
		};
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
		$('#photograph .bottom-bar .step1 .right .man').hover(
			function(){
				$(this).find('img').attr('src','/city/static/images/photograph/man-2.png')
				$(this).find('.m1').css('color','#B91525');
			},
			function(){
				$(this).find('img').attr('src','/city/static/images/photograph/man-1.png')
				$(this).find('.m1').css('color','#999');
			}
		);
		$('#photograph .bottom-bar .step1 .right .woman').hover(
			function(){
				$(this).find('img').attr('src','/city/static/images/photograph/woman-2.png');
				$(this).find('.m2').css('color','#B91525');
			},
			function(){
				$(this).find('img').attr('src','/city/static/images/photograph/woman-1.png');
				$(this).find('.m2').css('color','#999');
			}
		);
		$('#photograph-step .top-bar .left-side ul li').hover(
			function(){
				var i = $(this).index()+1;
				var imgSrc = '/city/static/images/photograph/p'+i+'-2.jpg';
				$(this).find('img').attr('src',imgSrc);
				$(this).css({'border-color':'#B91525','box-shadow':'0px 0px 10px #B91525'});
				$(this).find('.grey-bg').addClass('red-bg').removeClass('grey-bg');
			},
			function(){
				var i = $(this).index()+1;
				var imgSrc = '/city/static/images/photograph/p'+i+'-1.jpg';
				$(this).find('img').attr('src',imgSrc);
				$(this).css({'border-color':'#ddd','box-shadow':'0px 0px 0px #fff'});
				$(this).find('.red-bg').addClass('grey-bg').removeClass('red-bg');
			}
		);
		// photograph slide show
		var _num = $('.role-slideshow .content ol li').length;
		var _obj = $('.role-slideshow .content ol');
		_obj.css({'width':_num*(166+10+2)+'px'});//li-w:166 border:2 magin:10
		var leftArrow = $('.role-slideshow .left-arrow');
		var rightArrow = $('.role-slideshow .right-arrow');
		leftArrow.live('click',function(){
			if(_obj.is(':animated')){return;}
			if(parseInt(_obj.css('left')) >= 0){
				$(this).css('background-position','0px 0px');
			}
			else{
				$(this).css('background-position','0px -71px');
				_obj.animate({
				left:'+=178px'
				},100,function(){
					rightArrow.css('background-position','-57px -71px');
				});
			}
		});
		rightArrow.live('click',function(){
			if(_obj.is(':animated')){return;}
			if(parseInt(_obj.css('left')) <= -(178*(_num-3))){
				$(this).css('background-position','-57px 0');
				leftArrow.css('background-position','0 -71px');
			}
			else{
				_obj.animate({
					left:'-=178px'
				},100,function(){
					leftArrow.css('background-position','0 -71px');
				});
			}
		});
		$('.height_weight .left .content').each(function(){
			var $key = $(this)
			$key.find('span').click(function(){
				$key.find('span').removeClass('select');
				$(this).addClass('select');
			});
		});
		//user info
		var customerInfo_w = $('.customer-info').width();
		var customerInfo_h = $('.customer-info').height();
		$('.customer-info').css({left:(window_width-customerInfo_w)/2+'px',top:(window_height-customerInfo_h)/2+'px'});
		var infopreview = $('.info-preview');
		infopreview.click(function(){
			$('.masker').fadeIn();
			$('.customer-info').fadeIn();
		});
		$('.masker').click(function(){
			$('.masker').fadeOut();
			$('.customer-info').fadeOut();
		});
		$('.customer-infomation .modification').click(function(){
			$(this).parent().find('.nick_name').html('<input type="text" style="width:250px; height:30px;" />');
			$(this).fadeOut();
		});
		//verification
		//email
		function infoText($obj,result,pass,error){
			if(result){
				$obj.parent().find('.warning-info').addClass('warning-info-pass').removeClass('warning-info-error').html(pass);
			}
			else {
				$obj.parent().find('.warning-info').addClass('warning-info-error').removeClass('warning-info-pass').html(error);
			} 
		}
		function checkEmail() {
			var emailReg = /^[\w-]+(\.[\w-]+)*@[\w-]+(\.[\w-]+)+$/; 
			result = emailReg.test($('#email').val());
			pass = '输入正确';
			error = '您输入的Email地址格式不正确！';
			infoText($(this),result,pass,error);
			return result;
		}
		function AjaxCheckMail(){
			if(!checkEmail()) { return; }
			var EmailObj = $('#email').val();
			if(EmailObj == ''){
				$(this).parent().find('.warning-info').addClass('warning-info-error').text('邮箱不能为空');
			}
			else{
				$.ajax({
					url:'http://localhost/city/user.php/index/ajax/act/isReg',
					type:'post',
					cache:false,
					dataType:'json',
					data:{account : EmailObj},
					success: function(data,textStatus){
						if(data.error == 0){
							$('#email').parent().find('.warning-info').addClass('warning-info-pass').text('邮箱可以使用，输入正确');
							return;
						}
						else{
							$('#email').parent().find('.warning-info').addClass('warning-info-error').text('该邮箱已被注册！');
						}
					},
					error: function(Request,Status,Error) {alert(Request + '|' + Status + '|' +Error);}
				});
			}
		};
		$('#email').focusout(checkEmail);
		$('#email').focusout(AjaxCheckMail);
		//login check mail
		function LogincheckEmail(){
			var emailReg = /^[\w-]+(\.[\w-]+)*@[\w-]+(\.[\w-]+)+$/; 
			result = emailReg.test($('#login_email').val());
			pass = '输入正确';
			error = '您输入的Email地址格式不正确！';
			infoText($(this),result,pass,error);
			return result;
		}
		$('#login_email').focusout(LogincheckEmail);
		//captcha
		function captcha(){
			var captcha = $('#captcha').val(); 
			result = (captcha != '' && captcha.length ==4) ? true : false;
			pass = '输入正确';
			error = '验证码格式不正确！';
			infoText($(this),result,pass,error);
			return result;
		}
		$('#captcha').keyup(captcha);
		//password
		function passWord(){
			var passWordReg = /^.{6,20}$/; 
			result = passWordReg.test($('#password').val());
			pass = '输入正确';
			error = '密码至少6位字符！';
			infoText($(this),result,pass,error);
			return result;
		}
		$('#password').focusout(passWord);
		//confirm password
		function cfm_password(){
			if($('#cfm_password').val() == ''){
				$('#cfm_password').parent().find('.warning-info').addClass('warning-info-error').text('密码不能为空！');
			}
			else{
				result = ($('#password').val() === $('#cfm_password').val());
				pass = '输入正确';
				error = '请确认两个密码是一致的。';
				infoText($(this),result,pass,error);
				return result;
			}
		}
		$('#cfm_password').focusout(cfm_password);
		//nickname
		function nickname(){
			var obj = $('#nickname').val();
			var num = obj.length;
			if(num >= 7){
				$('#nickname').parent().find('.warning-info').addClass('warning-info-error').text('昵称不能超过6个字！');
				return false;
			}
			else if(num == 0){
				$('#nickname').parent().find('.warning-info').addClass('warning-info-error').text('昵称不能为空！');
				return false;
			}
			else if(num > 0 && num < 7){
				$.ajax({
					url:'http://localhost/city/user.php/index/ajax/act/isReg',
					type:'post',
					cache:false,
					dataType:'json',
					data:{nickname : obj},
					success:function(data,textstatus){
						if(data.error == 0){
							$('#nickname').parent().find('.warning-info').removeClass('warning-info-error').addClass('warning-info-pass').text('昵称可以使用');
						}
						else{
							$('#nickname').parent().find('.warning-info').removeClass('warning-info-pass').addClass('warning-info-error').text('昵称已被注册！');
						}
					},
					error:function(Ruquest,Status,Error){
						 alert(Request + '|' + Status + '|' +Error);
					}
				});
				return true;
			}
		}
		$('#nickname').focusout(nickname);
		$('.regisiter input').bind('focusout keyup change',function(){
			var isEmpty = true;
			$('.regisiter .t-input :text').each(function(){
				if($(this).val() == '') { return isEmpty = false; }
			});
			if(isEmpty == true && checkEmail() && passWord() && cfm_password() && nickname() && $('.agreement input').attr('checked') == 'checked'){
				$('.regisiter-btn input').removeClass('grey-btn').addClass('red-btn').removeAttr('disabled');
			}
			else{
				$('.regisiter-btn input').removeClass('red-btn').addClass('grey-btn').attr('disabled');
			}
		});
		$('.login-content input').bind('focusout keyup change',function(){
			var isEmpty = true;
			$('.login-content .t-input :text').each(function(){
				if($(this).val() == ''){
					return isEmpty = false;
				}
			});
			if(isEmpty == true && login_email && password && captcha){
				$('.login-btn input').removeClass('grey-btn').addClass('red-btn').removeAttr('disabled');
			}
			else{
				$('.login-btn input').removeClass('red-btn').addClass('grey-btn').attr('disabled');
			}
		});
	})
})(jQuery);