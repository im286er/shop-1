$(document).ready(function () {
	$('#orderlist,#deletelist').sortable({ handle: "img", connectWith: "#orderlist,#deletelist"});

	$('#controls div.save').click(function () {
		var IsJsonCreated = true;
		setInfo('');
		$('#orderlist li').removeClass('highlight');
		var jsonPhotoInfo = '[';
		var photocount = $('#orderlist li').length;
		$('#orderlist li').each(function () {
			var Name = $(this).find('input[name=photo_name]').val();
			var Remark = $(this).find('input[name=photo_remark]').val();
			if (Name.length > 50 || Remark.length > 50) {
				IsJsonCreated = false; jsonPhotoInfo = '';
				$(this).addClass('highlight');
				setInfo('当前被高亮的相片标题或描述字数过长');
			}
			if (IsJsonCreated) {
				jsonPhotoInfo +=
					'{"id":"' + $(this).find('input[name=photo_id]').val() +
					'" , "title":"' + JsonEncode(Name) +
					'" , "remark":"' + JsonEncode(Remark) +
					'" , "dispweight":"' + photocount-- + '"},';
			}
		});
		if (IsJsonCreated) {
			if (jsonPhotoInfo.length > 1) { jsonPhotoInfo = jsonPhotoInfo.substr(0, jsonPhotoInfo.length - 1); }
			jsonPhotoInfo += ']';
			$('#photolist input[name=photolist]').val(jsonPhotoInfo);
			$('#photolist').submit();
		}
	});

	//miaomin @2013/2/27
	$('#orderlist li').click(function(){
		var PhotoID = $(this).find('div.photoinfo input[name=photo_id]').val();
		$('#orderlist li').each(function () {
			$(this).removeClass('highlight');	
		});
		$(this).addClass('highlight');
		
	});
	
	$('#cover_btn').click(function(){
		var PhotoID = 0;
		$('#orderlist li').each(function () {
			if ($(this).hasClass('highlight')){
				PhotoID = $(this).find('div.photoinfo input[name=photo_id]').val();	
			}
		});
		if (PhotoID){
			$.ajax({
				type: 'post',
				url: SetCoverUrl,
				dataType: 'json',
				data: { id: PhotoID },
				success: function (data, textStatus) {
					if (data.isSuccess) { alert('封面设置成功!');}
					else { alert('封面设置失败\n' + data.Message);}
				},
				error: function (XmlHttpRequest, textStatus, errorThrown)
				{ alert('封面设置失败\n' + textStatus + '\n' + errorThrown);}
			});	
		}else{
			alert('请先选择封面图片\n');
		}
	});
	
	$('#orderlist li div.deletephoto').click(function () {
		if (confirm("确认要删除该相片吗?")) {
			var PhotodLi = $(this).parent();
			var PhotoID = $(this).parent().find('div.photoinfo input[name=photo_id]').val();
			$.ajax({
				type: 'post',
				url: DeleteUrl,
				dataType: 'json',
				data: { id: PhotoID },
				beforeSend: function (XmlHttpRequest) { PhotodLi.hide(); },
				success: function (data, textStatus) {
					if (data.isSuccess) { alert('图片删除成功!'); PhotodLi.remove(); }
					else { alert('图片删除失败\n' + data.Message); PhotodLi.show(); }
				},
				error: function (XmlHttpRequest, textStatus, errorThrown)
				{ alert('图片删除失败\n' + textStatus + '\n' + errorThrown); PhotodLi.show(); }
			});
		}
	});

	function setInfo(Text) { $('#controls div.info').text(Text); }
});

function JsonEncode(str) {
	if (str.length == 0) { return ''; }
	str = str.replace(/\'/g, "&#39;");
	str = str.replace(/\"/g, "&quot;");
	return str;
}

function JsonDecode(str) {
	if (str.length == 0) { return ''; }
	str = str.replace(/&#39;/g, "\'");
	str = str.replace(/&quot;/g, "\"");
	return str;
}

function HtmlEncode(str) {
	var s = "";
	if (str.length == 0) return "";
	s = str.replace(/&/g, "&amp;");
	s = s.replace(/</g, "&lt;");
	s = s.replace(/>/g, "&gt;");
	s = s.replace(/ /g, "&nbsp;");
	s = s.replace(/\'/g, "&#39;");
	s = s.replace(/\"/g, "&quot;");
	//s = s.replace(/\n/g, "<br>");
	return s;
}

function HtmlDecode(str) {
	var s = "";
	if (str.length == 0) return "";
	s = str.replace(/&amp;/g, "&");
	s = s.replace(/&lt;/g, "<");
	s = s.replace(/&gt;/g, ">");
	s = s.replace(/&nbsp;/g, " ");
	s = s.replace(/&#39;/g, "\'");
	s = s.replace(/&quot;/g, "\"");
	s = s.replace(/<br>/g, "\n");
	return s;
}