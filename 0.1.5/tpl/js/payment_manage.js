
function Page_Loaded()
{
	jQuery("#CurrentExtra").change(function () {
		var cv = jQuery("#CurrentExtra").val();
		if(cv == "") 
			jQuery("#extraContainer").css("display", "none");
		else
		{
			var response_tags = new Array('error','message','isTestModeUsable', 'getDescription', 'getParamArgs');
			var params = new Array();
			params['module_extra'] = cv;
			exec_xml('payment','procPaymentAdminConfigXml', params, dataLoaded, response_tags);
		}


		jQuery("div.extraVariable").css("display", "none");
		jQuery("input[name='payment_description']").val(jQuery("#CurrentExtra > option:selected").text());

	}).change();

}

function dataLoaded(ret_obj, response_tags)
{
	//isTestModeUsable
	jQuery("#extraContainer").css("display", "block");
	jQuery("div.extraDescription").html(ret_obj["getDescription"]);
	if(ret_obj["isTestModeUsable"] == "Y") jQuery("#is_test").css("display", "block");
	else 
	{
		jQuery("#is_test").css("display", "none");
		jQuery("input[name='payment_testmode']").get(0).checked = false;
	}

	for(i = 0; i < 5; i++)
	{
		if(ret_obj["getParamArgs"].item[i].body){
			jQuery("#extra_" + i).css("display", "block");
			jQuery("#lbl_ex" + i).html(ret_obj["getParamArgs"].item[i].body);
		}
		else
		{
			jQuery("#extra_" + i).css("display", "none");
		}
	}
}


jQuery(document).ready(Page_Loaded);


function completePaymentAddress(ret_obj, message)
{
	alert(ret_obj['message']);
	return false;
}

function completePaymentRemove(ret_obj, message)
{
	alert(ret_obj['message']);
	location.replace(current_url.setQuery('act','dispPaymentAdminPaymentList').setQuery('payment_srl','').setQuery('module_srl', ''));
}

function completePaymentRegisted(ret_obj, message)
{
	alert(ret_obj['message']);
	location.replace( current_url.setQuery('module_srl',ret_obj['module_srl']) );
}