function fileQueueError(file, errorCode, message){
    try {
        var imageName = window.path+"/images/error.gif";
        var errorName = "";
        if (errorCode === SWFUpload.errorCode_QUEUE_LIMIT_EXCEEDED) {
            errorName = "您上传的文件过多！";
        }
        
        if (errorName !== "") {
            alert(errorName);
            return;
        }
        
        switch (errorCode) {
            case SWFUpload.QUEUE_ERROR.ZERO_BYTE_FILE:
                imageName = window.path+"/images/zerobyte.gif";
                break;
            case SWFUpload.QUEUE_ERROR.FILE_EXCEEDS_SIZE_LIMIT:
                imageName = window.path+"/images/toobig.gif";
                break;
            case SWFUpload.QUEUE_ERROR.ZERO_BYTE_FILE:
            case SWFUpload.QUEUE_ERROR.INVALID_FILETYPE:
            default:
                alert(message);
                break;
        }
        
        addImage(imageName);
        
    } 
    catch (ex) {
        this.debug(ex);
    }
    
}

function fileDialogComplete(numFilesSelected, numFilesQueued){
    try {
        if (numFilesQueued > 0) {
            this.startUpload();
        }
    } 
    catch (ex) {
        this.debug(ex);
    }
}

function uploadProgress(file, bytesLoaded){

    try {
        var percent = Math.ceil((bytesLoaded / file.size) * 100);
        
        var progress = new FileProgress(file, this.customSettings.upload_target);
        progress.setProgress(percent);
        if (percent === 100) {
            progress.setStatus("创建缩略图中");
            progress.toggleCancel(false, this);
        }
        else {
            progress.setStatus("上传中");
            progress.toggleCancel(true, this);
        }
    } 
    catch (ex) {
        this.debug(ex);
    }
}




function uploadComplete(file){
    try {

        if (this.getStats().files_queued > 0) {
            this.startUpload();
        }
        else {
            var progress = new FileProgress(file, this.customSettings.upload_target);
            progress.setComplete();
            progress.setStatus("所有图片上传成功！");
            progress.toggleCancel(false);
        }
    } 
    catch (ex) {
        this.debug(ex);
    }
}

function uploadError(file, errorCode, message){
    var imageName = window.path+"/images/error.gif";
    var progress;
    try {
        switch (errorCode) {
            case SWFUpload.UPLOAD_ERROR.FILE_CANCELLED:
                try {
                    progress = new FileProgress(file, this.customSettings.upload_target);
                    progress.setCancelled();
                    progress.setStatus("取消");
                    progress.toggleCancel(false);
                } 
                catch (ex1) {
                    this.debug(ex1);
                }
                break;
            case SWFUpload.UPLOAD_ERROR.UPLOAD_STOPPED:
                try {
                    progress = new FileProgress(file, this.customSettings.upload_target);
                    progress.setCancelled();
                    progress.setStatus("停止");
                    progress.toggleCancel(true);
                } 
                catch (ex2) {
                    this.debug(ex2);
                }
            case SWFUpload.UPLOAD_ERROR.UPLOAD_LIMIT_EXCEEDED:
                imageName = window.path+"/images/uploadlimit.gif";
                break;
            default:
                alert(message);
                break;
        }
        
        addImage(imageName);
        
    } 
    catch (ex3) {
        this.debug(ex3);
    }
    
}

function addImage(src){
	var srcinfo=eval('('+src+')');//转为js可解析的json格式
	//var srcinfo=src;
	var srcimg=srcinfo.imgpath; //提取图片路径
	var srcid=srcinfo.id;
    var newElement = "<li><img class='content'  src='" + srcimg + "' id='"+ srcid +"' style=\"width:110px;height:90px;\"><img class='button' src="+window.path+"/images/fancy_close.png></li>";
    $("#pic_list").append(newElement);
    $("img.button").last().bind("click", del);
    
}

function uploadSuccess(file, serverData){
	addImage(serverData);
	
	var srcinfo=eval('('+serverData+')');//转为js可解析的json格式
	var srcid=srcinfo.id; //提取图片路径
	var $svalue=$('#imgid').val();
	if($svalue==''){
		$('#imgid').val(srcid);
	}else{
		$('#imgid').val($svalue+","+srcid);
	}
	imgId();
}


var del = function(){
	//var fid = $(this).parent().prevAll().length + 1;
	var src=$(this).siblings('img').attr('src');
	var id=$(this).siblings('img').attr('id');
	var $svalue=$('#imgid').val();
	var fid = $(this).parent();
	$.ajax({
        type: "GET", //访问WebService使用Post方式请求
        url: window.url+"/del", //调用WebService的地址和方法名称组合---WsURL/方法名
        data: "id=" + id,
        success: function(data){
		//alert(data);
		var svalueArr = $svalue.split(',');
		for(var i=0;i<svalueArr.length;i++){
		 	if(svalueArr[i]==data){
				svalueArr.splice(i,1);
			}
		}
		var svaluestr='';
		for(var i=0;i<svalueArr.length;i++){
		 	svaluestr+=svalueArr[i]+',';
		}
		var strvalue=svaluestr.substring(0,svaluestr.length-1);	
		var $val=$svalue.replace(data,'');
			$('#imgid').val(strvalue);
			imgId();
        }
    });
    $(this).parent().remove();
	
}


function fadeIn(element, opacity){
    var reduceOpacityBy = 5;
    var rate = 30; // 15 fps
    if (opacity < 100) {
        opacity += reduceOpacityBy;
        if (opacity > 100) {
            opacity = 100;
        }
        
        if (element.filters) {
            try {
                element.filters.item("DXImageTransform.Microsoft.Alpha").opacity = opacity;
            } 
            catch (e) {
                element.style.filter = 'progid:DXImageTransform.Microsoft.Alpha(opacity=' + opacity + ')';
            }
        }
        else {
            element.style.opacity = opacity / 100;
        }
    }
    
    if (opacity < 100) {
        setTimeout(function(){
            fadeIn(element, opacity);
        }, rate);
    }
}

function FileProgress(file, targetID){
    this.fileProgressID = "divFileProgress";
    
    this.fileProgressWrapper = document.getElementById(this.fileProgressID);
    if (!this.fileProgressWrapper) {
        this.fileProgressWrapper = document.createElement("div");
        this.fileProgressWrapper.className = "progressWrapper";
        this.fileProgressWrapper.id = this.fileProgressID;
        
        this.fileProgressElement = document.createElement("div");
        this.fileProgressElement.className = "progressContainer";
        
        var progressCancel = document.createElement("a");
        progressCancel.className = "progressCancel";
        progressCancel.href = "#";
        progressCancel.style.visibility = "hidden";
        progressCancel.appendChild(document.createTextNode(" "));
        
        var progressText = document.createElement("div");
        progressText.className = "progressName";
        progressText.appendChild(document.createTextNode(file.name));
        
        var progressBar = document.createElement("div");
        progressBar.className = "progressBarInProgress";
        
        var progressStatus = document.createElement("div");
        progressStatus.className = "progressBarStatus";
        progressStatus.innerHTML = "&nbsp;";
        
        //this.fileProgressElement.appendChild(progressCancel);
        //this.fileProgressElement.appendChild(progressText);
       // this.fileProgressElement.appendChild(progressStatus);
       // this.fileProgressElement.appendChild(progressBar);
        
       // this.fileProgressWrapper.appendChild(this.fileProgressElement);
        
       // document.getElementById(targetID).appendChild(this.fileProgressWrapper);
       // fadeIn(this.fileProgressWrapper, 0);
        
    }
    else {
        this.fileProgressElement = this.fileProgressWrapper.firstChild;
        this.fileProgressElement.childNodes[1].firstChild.nodeValue = file.name;
    }
    
    this.height = this.fileProgressWrapper.offsetHeight;
    
}

FileProgress.prototype.setProgress = function(percentage){
    this.fileProgressElement.className = "progressContainer green";
    this.fileProgressElement.childNodes[3].className = "progressBarInProgress";
    this.fileProgressElement.childNodes[3].style.width = percentage + "%";
};
FileProgress.prototype.setComplete = function(){
    this.fileProgressElement.className = "progressContainer blue";
    this.fileProgressElement.childNodes[3].className = "progressBarComplete";
    this.fileProgressElement.childNodes[3].style.width = "";
    
};
FileProgress.prototype.setError = function(){
    this.fileProgressElement.className = "progressContainer red";
    this.fileProgressElement.childNodes[3].className = "progressBarError";
    this.fileProgressElement.childNodes[3].style.width = "";
    
};
FileProgress.prototype.setCancelled = function(){
    this.fileProgressElement.className = "progressContainer";
    this.fileProgressElement.childNodes[3].className = "progressBarError";
    this.fileProgressElement.childNodes[3].style.width = "";
    
};
FileProgress.prototype.setStatus = function(status){
    //this.fileProgressElement.childNodes[2].innerHTML = status;
};


FileProgress.prototype.toggleCancel = function(show, swfuploadInstance){
    this.fileProgressElement.childNodes[0].style.visibility = show ? "visible" : "hidden";
    if (swfuploadInstance) {
        var fileID = this.fileProgressID;
        this.fileProgressElement.childNodes[0].onclick = function(){
            swfuploadInstance.cancelUpload(fileID);
            return false;
        };
    }
};
