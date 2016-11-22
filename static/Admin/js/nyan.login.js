$(document).ready(function (e) {
	$('.bg').fadeTo(500, 0.7);
	$('#username,#password').keydown(function () {
		$(this).parent().find('h4').hide();
	}).keyup(function () {
		if ($(this).val() == '') { $(this).parent().find('h4').show(); }
	});
	$('#loginin').click(function () {
		var post = true;
		$('#info_name,#info_password').hide();
		if ($('#username').val() == '') { $('#info_name').show(); post = false; }
		if ($('#password').val() == '') { $('#info_password').show(); post = false; }
		if (!post) { return false; }
	});
});