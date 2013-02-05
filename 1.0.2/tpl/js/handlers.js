function _thfileQueErr(file, errorCode, message) {
	try {
		/*
		var imageName = "error.gif";
		var errorName = "";
		if (errorCode === SWFUpload.errorCode_QUEUE_LIMIT_EXCEEDED) {
			errorName = "You have attempted to queue too many files.";
		}

		if (errorName !== "") {
			alert(errorName);
			return;
		}

		switch (errorCode) {
		case SWFUpload.QUEUE_ERROR.ZERO_BYTE_FILE:
			imageName = "zerobyte.gif";
			break;
		case SWFUpload.QUEUE_ERROR.FILE_EXCEEDS_SIZE_LIMIT:
			imageName = "toobig.gif";
			break;
		case SWFUpload.QUEUE_ERROR.ZERO_BYTE_FILE:
		case SWFUpload.QUEUE_ERROR.INVALID_FILETYPE:
		default:
			alert(message);
			break;
		}

		addImage("images/" + imageName);
*/
	} catch (ex) {
		this.debug(ex);
	}

}

function _thfileDiagComplete(numFilesSelected, numFilesQueued) {
	try {
		if (numFilesQueued > 0) {
			jQuery("#uploaded_thumbnail").append("<option value=\"\"></option>");
			this.startUpload();
		}
	} catch (ex) {
		this.debug(ex);
	}
}

function _thupProgress(file, bytesLoaded) {

	try {
		var percent = Math.ceil((bytesLoaded / file.size) * 100);
		var obj = jQuery("#uploaded_thumbnail > option:eq(" + file["index"] + ")");
		

		if (percent === 100) 
		{
			obj.text(file["name"]);
			obj.val("{name:" + file["name"] + "}");
		}
		else
		{
			obj.text(file["name"] + " (" + percent + "%)");
		}

	} catch (ex) {
		this.debug(ex);
	}
}

function _thupSuccess(file, serverData) {
	try {
		jQuery("#ThumbPreview").html("<img src=\"\" />");
		/*
		var progress = new FileProgress(file,  this.customSettings.upload_target);

		if (serverData.substring(0, 7) === "FILEID:") {
			addImage("thumbnail.php?id=" + serverData.substring(7));

			progress.setStatus("Thumbnail Created.");
			progress.toggleCancel(false);
		} else {
			addImage("images/error.gif");
			progress.setStatus("Error.");
			progress.toggleCancel(false);
			alert(serverData);

		}*/


	} catch (ex) {
		this.debug(ex);
	}
}

function _thupComplete(file) {
	try {
		if (this.getStats().files_queued > 0) {
			jQuery("#uploaded_thumbnail").append("<option value=\"\"></option>");
			this.startUpload();
		}
	} catch (ex) {
		this.debug(ex);
	}
}

function _thupErr(file, errorCode, message) {
	var imageName =  "error.gif";
	var progress;
	try {
		/*
		switch (errorCode) {
		case SWFUpload.UPLOAD_ERROR.FILE_CANCELLED:
			try {
				progress = new FileProgress(file,  this.customSettings.upload_target);
				progress.setCancelled();
				progress.setStatus("Cancelled");
				progress.toggleCancel(false);
			}
			catch (ex1) {
				this.debug(ex1);
			}
			break;
		case SWFUpload.UPLOAD_ERROR.UPLOAD_STOPPED:
			try {
				progress = new FileProgress(file,  this.customSettings.upload_target);
				progress.setCancelled();
				progress.setStatus("Stopped");
				progress.toggleCancel(true);
			}
			catch (ex2) {
				this.debug(ex2);
			}
		case SWFUpload.UPLOAD_ERROR.UPLOAD_LIMIT_EXCEEDED:
			imageName = "uploadlimit.gif";
			break;
		default:
			alert(message);
			break;
		}*/

	} catch (ex3) {
		this.debug(ex3);
	}

}
