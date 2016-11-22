// 中二病发作没天理！

function mainmenu() {
	$(" #globalnav ul ").css({display: "none"}); // Opera Fix
	$(" #globalnav li").hover(function(e) {
		$(this).find('ul:first').css({visibility: "visible", display: "none"}).fadeIn(300);
	},function(){
		$(this).find('ul:first').css({visibility: "hidden"});
	});
}

function resizewarp() {
//	if($('body').width()>531){
		$('.warp').height($('body').innerHeight()-$('.header').outerHeight()-$('.footbar').outerHeight());
		$('.sidebar, .masterpanel, .nagiscroll').height($('.warp').height());
		$('.nagiscroll').jScrollPane();;
//		alert($('.sidebar').outerWidth())
//	} else {
//		$('.warp, .sidebar, .masterpanel').height('auto');
//	}
}

$(document).ready(function(e) {
	mainmenu();		
	resizewarp();			
	$('.bg').fadeTo(350,0.7);
	$('#ffsearch').val(str_ffsearch).focus(function(e) {
		if($(this).val()==str_ffsearch) {$(this).val("");}
	}).blur(function(e) {
		if($(this).val()=="") {$(this).val(str_ffsearch);}
	});
});

$(window).resize(function(e) {
	resizewarp();
});