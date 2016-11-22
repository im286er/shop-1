var swfu;
$(document).ready(
	function () {
		swfu = new SWFUpload({
			upload_url: UploadUrl,
			flash_url: WebRootUrl + "/static/js/swfupload/Flash/swfupload.swf",
			file_post_name: "Filedata",
			post_params: {
				pid: ProductID,
				sessionid: SessionID
			},
			debug: false,

			file_size_limit: "4 MB",
			file_types: "*.jpg;*.gif;*.jpeg;*.png",
			file_types_description: "Image Files",
			file_upload_limit: "0",
			file_queue_limit: "0",

			button_width: 100,
			button_height: 30,
			//button_image_url: WebRootUrl + "/static/images/upload_button.jpg",
			button_text: '<span class="buttonplaceholder">选择文件</span>',
			button_text_style: '.buttonplaceholder { color: #ffffff; font-weight: bold; text-align: center; }',
			button_placeholder_id: "span_buttonplaceholder",
			button_text_top_padding: 5,
			button_text_left_padding: 0,
			button_window_mode: SWFUpload.WINDOW_MODE.TRANSPARENT,
			button_cursor: SWFUpload.CURSOR.HAND,

			file_dialog_start_handler: fileDialogStart,
			file_dialog_complete_handler: fileDialogComplete,
			file_queue_error_handler: fileQueueError,
			file_queued_handler: fileQueued,
			//upload_start_handler: uploadStart,
			upload_progress_handler: uploadProgress,
			upload_error_handler: uploadError,
			upload_success_handler: uploadSuccess,
			upload_complete_handler: uploadComplete
		});

		$("#span_buttonupload").click(function () { uploadFiles(); });
	});

function fileDialogStart() { $('#upload_list .info').html(''); setUploadInfo(''); }

function fileDialogComplete(numFilesSelected, numFilesQueued) {
	if (numFilesSelected > 0) {
		var queuelimit = parseInt(swfu.getSetting('file_queue_limit').toString());
		if ((numFilesSelected > queuelimit && queuelimit != 0)) { appendError('选择的文件数量超出最大限制'); }
		if (parseInt(swfu.getStats()['files_queued']) == 0) { $('#upload_list .info').html('请选择文件'); }
	}
	setUploadInfo("选择了" + numFilesSelected + "个文件，其中" + numFilesQueued + "个文件添加到上传队列中");
}

function fileQueued(file) {
	$('#upload_list').append('<div id="' + file.id + '" class="fileitem"><span>X</span><b>' + file.name + '</b><i>' + formatFileSize(file.size) + '</i></div>')
	.find('div>span').last().click(function () {
		if (!swfu.getStats()['in_progress']) { $(this).parent().remove(); swfu.cancelUpload(file.id); }
		if (parseInt(swfu.getStats()['files_queued']) == 0) { $('#upload_list .info').html('请选择文件'); }
	});
}

function fileQueueError(file, errorCode, message) {
	var ErrorMessage = '';
	switch (errorCode) {
		case SWFUpload.QUEUE_ERROR.FILE_EXCEEDS_SIZE_LIMIT: { ErrorMessage = '文件大小超出最大限制'; break; }
		case SWFUpload.QUEUE_ERROR.INVALID_FILETYPE: { ErrorMessage = '文件类型错误'; break; }
		case SWFUpload.QUEUE_ERROR.QUEUE_LIMIT_EXCEEDED: { ErrorMessage = '超出一次可上传的文件量'; break; }
		case SWFUpload.QUEUE_ERROR.ZERO_BYTE_FILE: { ErrorMessage = '文件为空'; break; }
	}
	appendError('文件[' + file.name + ']无法上传<br/>' + ErrorMessage + '');
}

function uploadFiles() {
	var Stats = swfu.getStats();
	if (parseInt(Stats['files_queued'].toString()) > 0) {
		swfu.setButtonDisabled(true); $('#span_buttonupload').unbind('click');
		$('#span_buttonupload,#span_buttonplaceholder').addClass('disabled').attr('disabled', 'disabled');
		$('#upload_list div:not(.uploaderror) span').hide()
		setUploadInfo('开始上传文件，当前有1个文件正在上传');
		swfu.startUpload();
	}
	else { appendError('当前列表中没有文件'); }
}

//function uploadStart(file) {}

var MaxLength = 400;
function uploadProgress(file, bytesComplete, totalBytes) {
	var Percent = bytesComplete / totalBytes;
	$('#' + file.id + ' i').width(MaxLength * (Percent)).text(formatFileSize(bytesComplete) + ' / ' + formatFileSize(totalBytes) + ' | ' + ((Percent) * 100).toFixed(2) + "%");
}

function uploadError(file, errorCode, message) 
{ $('#' + file.id).addClass('uploaderror').find('i').text(message); }

function uploadSuccess(file, data)
{
	try
	{
		data = eval("("+data+")");
		if(data.isSuccess)
		{
			$('#' + file.id).find('i').text('上传成功!');
			setTimeout(function () { $('#' + file.id).fadeOut(500); }, 1000);
		}
		else if(data.Message) { $('#' + file.id).addClass('uploaderror').find('i').text(data.Message); }
		else { $('#' + file.id).addClass('uploaderror').find('i').text('未知错误'); }
	}
	catch(err)
	{ $('#' + file.id).addClass('uploaderror').find('i').text('未知错误'); }
}

function uploadComplete(file) {
	var Stats = swfu.getStats();
	if (parseInt(Stats['files_queued'].toString()) > 0) {
		setUploadInfo("当前有1个文件正在上传，" + Stats['successful_uploads'] + "个文件上传成功，" + Stats['files_queued'] + "个文件等待上传");
		swfu.startUpload();
	}
	else {
		setUploadInfo("上传完成，" + Stats['successful_uploads'] + "个文件上传成功," + Stats['upload_errors'] + "个文件上传失败");
		swfu.setButtonDisabled(false); $('#span_buttonupload').click(function () { uploadFiles(); });
		Stats['successful_uploads'] = Stats['upload_errors'] = Stats['upload_cancelled'] = Stats['queue_errors'] = 0; swfu.setStats(Stats);
		$('#span_buttonupload,#span_buttonplaceholder').removeAttr('disabled').removeClass('disabled');
		$('#upload_list div span').show();
		$('#upload_list .info').html('请选择文件');
	}
}

function setUploadInfo(Text) { $('#upload_info').html(Text); }
var ErrorID = 0;
function appendError(ErrorMessage) {
	var eID = 'uploaderror_' + ErrorID++;
	$('#upload_error').append('<div id="' + eID + '"><span>X</span>' + ErrorMessage + '</div>').find('div>span').last().click(function () { $(this).parent().remove() });
	setTimeout(function () { $('#' + eID).fadeOut(500); setTimeout(function () { $('#' + eID).remove(); }, 500); }, 2000);
}

function formatFileSize(FileSize) {
	var SizeArray = Array('Byte', 'KB', 'MB', 'GB', 'TB', 'PB', 'SB');
	for (var i = 1; i <= SizeArray.length + 1; i++) {
		if (Math.pow(1024, i) > FileSize)
		{ return parseFloat(FileSize / Math.pow(1024, i - 1)).toFixed(2) + ' ' + SizeArray[i - 1]; }
	}
	return FileSize;
}