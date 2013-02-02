

var productThumbManager = {
	w_obj:null,
	swfUp:null,
	swfParams:null,
	initalize: function (w_obj)
	{
		if(this.w_obj != null) return;
		this.w_obj = w_obj;
		var btnCommand = jQuery("#" + w_obj["finder"]);
		btnCommand.append("<span id=\"dm_" + w_obj["finder"] + "\"></span>");

		this.swfParams = {
			upload_url: request_uri.replace(/^https/i,'http'),
			post_params: {
				"mid" : current_mid,
				"act" : "procFileUpload",
				"editor_sequence" : "2",
				"uploadTargetSrl" : this.w_obj["product_srl"]
			},

			file_types : "*.jpg; *.jpeg; *.gif; *.bmp; *.png",
			file_types_description : "Image Files",

	        file_queued_handler : null,
			file_queue_error_handler : productThumbManager.fileQueError,
			file_dialog_complete_handler : productThumbManager.fileDialogComplete,
	        upload_start_handler : null,
			upload_progress_handler : productThumbManager.uploadProgress,
			upload_error_handler : productThumbManager.uploadError,
			upload_success_handler : productThumbManager.uploadSuccess,
			upload_complete_handler : productThumbManager.uploadComplete,
			queue_complete_handler : null,

			// Button settings
			button_window_mode: 'transparent',
			button_placeholder_id: "dm_" + w_obj["finder"],
			button_width: btnCommand.width(),
			button_height: btnCommand.height(),
			
			// Flash Settings
			flash_url : "/modules/editor/tpl/images/SWFUpload.swf",

			// Debug Settings
			debug: false
		};

		this.swfUp = new SWFUpload(this.swfParams);

		var swfObjs = jQuery("#" + this.swfUp.movieName);
		swfObjs.css("display", "block").css("cursor", "pointer").css("position", "absolute");
		swfObjs.css("left", "0").css("top", "-3px");
		swfObjs.width(btnCommand.width()).height(btnCommand.height());

		jQuery("#" + w_obj["list"]).change(function ()
		{
			if(!jQuery(this).val())
			{
				return;
			}
			if(jQuery("#master_image").val() == jQuery(this).val())
			{
				jQuery("#isThumbnailCBox").attr("checked", true);
			}
			else
			{
				jQuery("#isThumbnailCBox").attr("checked", false);
			}

			jQuery("#ThumbPreview > img").attr("src", productThumbManager.uploadedFiles[jQuery(this).val()].download_url).css("display", "block");
		});

		jQuery("#isThumbnailCBox").click(function ()
		{
			if(!jQuery(this).attr("checked")) return;

			if( jQuery("#" + productThumbManager.w_obj["list"]).get(0).options.length ){
				var opts = jQuery("#" + productThumbManager.w_obj["list"]).get(0).options;
				jQuery("#master_image").val(opts[opts.selectedIndex].value);
				//jQuery("#isThumbnailCBox").attr("disabled", true);
			}else{
				return false;
			}
		});
		
		this.reloadFileList();
	},
		
	fileQueueError: function (file, errorCode, message) {
		try {
			switch(errorCode) {
				case SWFUpload.QUEUE_ERROR.QUEUE_LIMIT_EXCEEDED :
					alert("You have attempted to queue too many files.\n" + (message === 0 ? "You have reached the upload limit." : "You may select " + (message > 1 ? "up to " + message + " files." : "one file.")));
					break;
				case SWFUpload.QUEUE_ERROR.FILE_EXCEEDS_SIZE_LIMIT:
					alert("Error Code: File too big, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
					break;
				case SWFUpload.QUEUE_ERROR.ZERO_BYTE_FILE:
					alert("Error Code: Zero byte file, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
					break;
				case SWFUpload.QUEUE_ERROR.INVALID_FILETYPE:
					alert("Error Code: Invalid File Type, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
					break;
				default:
					alert("Error Code: " + errorCode + ", File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
					break;
			}
		} catch(ex) {
			this.debug(ex);
		}
	},
			
	fileDialogComplete: function (numFilesSelected, numFilesQueued) {
		try {
			this.startUpload();
		} catch (ex)  {
			this.debug(ex);
		}
	}
	,
	uploadProgress: function (file, bytesLoaded, bytesTotal) {
		try {
			var obj = xGetElementById(this.w_obj["list"]);

			var percent = Math.ceil((bytesLoaded / bytesTotal) * 100);
			var filename = file.name;
			if(filename.length>20) filename = filename.substr(0,20)+'...';

			var text = filename + ' ('+percent+'%)';
			if(!obj.options.length || obj.options[obj.options.length-1].value != file.id) {
				var opt_obj = new Option(text, file.id, true, true);
				obj.options[obj.options.length] = opt_obj;
			} else {
				obj.options[obj.options.length-1].text = text;
			}
		} catch (ex)  {
			this.debug(ex);
		}
	},
	
	uploadError: function (file, errorCode, message) {
		try {
			switch (errorCode) {
			case SWFUpload.UPLOAD_ERROR.HTTP_ERROR:
				alert("Error Code: HTTP Error, File name: " + file.name + ", Message: " + message);
				break;
			case SWFUpload.UPLOAD_ERROR.UPLOAD_FAILED:
				alert("Error Code: Upload Failed, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
				break;
			case SWFUpload.UPLOAD_ERROR.IO_ERROR:
				alert("Error Code: IO Error, File name: " + file.name + ", Message: " + message);
				break;
			case SWFUpload.UPLOAD_ERROR.SECURITY_ERROR:
				alert("Error Code: Security Error, File name: " + file.name + ", Message: " + message);
				break;
			case SWFUpload.UPLOAD_ERROR.UPLOAD_LIMIT_EXCEEDED:
				alert("Error Code: Upload Limit Exceeded, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
				break;
			case SWFUpload.UPLOAD_ERROR.FILE_VALIDATION_FAILED:
				alert("Error Code: File Validation Failed, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
				break;
			case SWFUpload.UPLOAD_ERROR.FILE_CANCELLED:
				// If there aren't any files left (they were all cancelled) disable the cancel button
				break;
			case SWFUpload.UPLOAD_ERROR.UPLOAD_STOPPED:
				break;
			default:
				alert("Error Code: " + errorCode + ", File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
				break;
			}
		} catch (ex) {
			this.debug(ex);
		}
	},

	uploadComplete: function (file) {
		try {
			productThumbManager.reloadFileList();
		} catch(e) {
			this.debug(ex);
		}
	},

	uploadSuccess: function (file, serverData) {
		try {
			if(this.getStats().files_queued !== 0) this.startUpload();
		} catch (ex)  {
			this.debug(ex);
		}
	},

	reloadFileList: function () {
		var params = new Array();
		params["file_list_area_id"] = this.w_obj["list"];
		params["editor_sequence"] = "2";
		params["upload_target_srl"] = this.w_obj["upload_target_srl"];
		params["mid"] = current_mid;
		var response_tags = new Array("error","message","files","upload_status","upload_target_srl","editor_sequence","left_size");
		exec_xml("file","getFileList", params, this.completeReloadFileList, response_tags, this.w_obj);
	},
	uploadedFiles:new Object(),

	completeReloadFileList: function (ret_obj, response_tags, w_obj) {
		var upload_target_srl = ret_obj['upload_target_srl'];
		var editor_sequence = ret_obj['editor_sequence'];
		var files = ret_obj['files'];
		var listObj = xGetElementById(w_obj["list"]);
		var left_size = parseInt(parseInt(ret_obj["left_size"],10)/1024,10);
		while(listObj.options.length) {
			listObj.remove(0);
		}

		if(files && typeof(files['item'])!='undefined') {
			var item = files['item'];
			if(typeof(item.length)=='undefined' || item.length<1) item = new Array(item);
			if(item.length) {
				for(var i=0;i<item.length;i++) {
					var file_srl = item[i].file_srl;
					productThumbManager.uploadedFiles[file_srl] = item[i];
					var opt = new Option(item[i].source_filename+" ("+item[i].disp_file_size+")", file_srl, true, true);
					listObj.options[listObj.options.length] = opt;
				}
			}
		}
		
		productThumbManager.w_obj["upload_target_srl"] = upload_target_srl;
		jQuery("#thumb_sequence").val(upload_target_srl);

		jQuery("#" + productThumbManager.w_obj["list"]).change();
	},
	
	MoveToBottom: function () 
	{ 
		var opts = jQuery("#" + this.w_obj["list"]).get(0).options;
		for(i = 0; i < opts.length - opts.selectedIndex; i++) this.MoveToDown();
	},
	MoveToUp: function () { this.ToMove(jQuery("#" + this.w_obj["list"]).get(0).options.selectedIndex - 1); },
	MoveToDown: function ()	{ this.ToMove(jQuery("#" + this.w_obj["list"]).get(0).options.selectedIndex + 1); },
	MoveToTop: function () { 
		var opts = jQuery("#" + this.w_obj["list"]).get(0).options;
		for(i = 0; i < opts.selectedIndex + 1; i++) this.MoveToUp();
	},

	ToMove: function (step)
	{
		if(step > jQuery("#" + this.w_obj["list"]).get(0).options.length + 1) return;
		if(step < 0) return;

		var tmp_value;
		var tmp_text;
		var new_sel=jQuery("#" + this.w_obj["list"]).get(0);
		var idx=new_sel.options.selectedIndex;
				 
		tmp_value=new_sel.options[idx].value;
		tmp_text=new_sel.options[idx].text;
		 
		new_sel.options[idx].value=new_sel.options[step].value;
		new_sel.options[idx].text=new_sel.options[step].text;
		 
		new_sel.options[step].value=tmp_value;
		new_sel.options[step].text=tmp_text;
		 
		new_sel.options[step].selected=true;
	}


};
