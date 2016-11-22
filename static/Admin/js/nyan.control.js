var SelectID = 1;
function replaceSelect(selector, Class) {
	var ID = 'zerockselect_' + SelectID++;
	var Span_Click = '<span id="' + ID + '"><span></span><ul></ul></span>';

	$(selector).hide().after(Span_Click);
	$('#' + ID + ' ul').hide();
	if (Class) { $('#' + ID).addClass(Class); }

	$('#' + ID).click(function () {
		$('#' + ID + ' ul').html("");
		$(selector).find('option').each(function () {
			$('#' + ID + ' ul').append('<li name="' + $(this).val() + '">' + $(this).text() + '</li>');
			if ($(this).attr('disabled')) { $('#' + ID + ' ul').find('li').last().addClass('disable'); }
		});

		$('#' + ID + ' ul').find('li').click(function () {
			if (!$(this).hasClass('disable')) {
				$('#' + ID).find('span').text($(this).text()).attr('name', $(this).attr('name'));
				$(selector).find('option').removeAttr('selected');
				$(selector).find('option[value=' + $(this).attr('name') + ']').attr('selected', 'selected');
				$(this).parent().hide();
				$(selector).change();
			}
			return false;
		});

		$('#' + ID + ' ul').show();
		return false;
	}).mouseleave(function () { $(this).find('ul').hide(); });

	if ($(selector).find('option[selected]').length > 0)
	{ $('#' + ID).find('span').text($(selector).find('option[selected]').text()); }
	else { $('#' + ID).find('span').text($(selector).find('option').first().text()); }
}

function replaceCKB() {
	$('input[type=checkbox]').hide().wrap('<span class="checkbox"></span>');
	$('input[type=checkbox]').each(function () {
		if ($(this).attr('checked')) { $(this).parent().addClass('checked'); }
		if ($(this).attr('disabled')) { $(this).parent().addClass('disable'); }
		$(this).parent().click(function () {
			if(!$(this).find('input[type=checkbox]').attr('disabled'))
			{
				if ($(this).hasClass('checked'))
				{ $(this).removeClass('checked'); $(this).find('input[type=checkbox]').removeAttr('checked'); }
				else { $(this).addClass('checked'); $(this).find('input[type=checkbox]').attr('checked', 'checked'); }
			}
		});
	});
}

function replaceRAD() {
	$('input[type=radio]').hide().wrap('<span class="checkbox"></span>');
	$('input[type=radio]').each(function () {
		if ($(this).attr('checked')) { $(this).parent().addClass('checked'); }
		if ($(this).attr('disable')) { $(this).parent().addClass('disable'); }
		$(this).parent().click(function () {
			if(!$(this).find('input[type=radio]').attr('disabled'))
			{
				$('input[type=radio][name=' + $(this).find('input[type=radio]').attr('name') + ']').removeAttr('checked');
				$(this).find('input[type=radio]').attr('checked', 'checked');
				$('input[type=radio][name=' + $(this).find('input[type=radio]').attr('name') + ']').each(function () {
					if ($(this).attr('checked')) { $(this).parent().addClass('checked'); }
					else { $(this).parent().removeClass('checked'); }
				});
			}
		});
	});
}

$(document).ready(function () {
	$("select").each(function () { replaceSelect('#' + $(this).attr('id'), $(this).attr('class')); });
	replaceCKB();
	replaceRAD();
});
