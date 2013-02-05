jQuery(document).ready(Page_Loaded);
function Page_Loaded()
{
	jQuery("img[onclick!='']").css("cursor", "pointer");


	jQuery("input[name='title']").focus(evtTitleFocused);
	jQuery("input[name='title']").blur(evtTitleBlured);

	jQuery("input[name='title']").val(title_org);
	evtTitleBlured();

}

function evtTitleFocused()
{
	if(jQuery("input[name='title']").val() == lang_title_name)
	{
		jQuery("input[name='title']").css("color", "#000000");
		jQuery("input[name='title']").val("");
	}
}

function evtTitleBlured()
{
	if(jQuery("input[name='title']").val() == "")
	{
		jQuery("input[name='title']").css("color", "#999999");
		jQuery("input[name='title']").val(lang_title_name);
	}

}

