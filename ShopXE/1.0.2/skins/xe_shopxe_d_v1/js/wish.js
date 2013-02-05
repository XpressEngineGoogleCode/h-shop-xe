function doWishDelete(product_srl)
{
	var params = new Object();
	params["product_srl"] = product_srl;
	var response_tags = new Array('error','message','tpl');
	exec_xml("shopxe", "procShopxeDeleteWish", params, completeDeleteWish, response_tags);
}
function completeDeleteWish() { location.href = current_url; }

function doAction(action)
{
	var obj = jQuery("#wish_form").get(0);

	var target_value = "";
	var volume_value = "";

	jQuery("input[name='cart']:checked").each(function (){
		if(target_value != "") target_value = target_value + "||";
		target_value = target_value + jQuery(this).val();

		if(volume_value != "") volume_value = volume_value + "||";
		volume_value = volume_value + "1";
	});
	
	obj.target_var.value = target_value;
	obj.volume_var.value = volume_value;

	if(target_value == "")
	{
		alert("선택된 상품이 존재하지 않습니다.");
		return;
	}

	return procFilter(obj, action);
}
function completeInsertCart() { location.href = current_url.setQuery('act', 'dispShopxeCartList'); }


function doInsertSingleCart(document_srl)
{
	var params = new Object();
	params["target_var"] = document_srl;
	params["volume_var"] = 1;
	params["options_var"] = null;
    var response_tags = new Array('error','message','tpl');
	exec_xml("shopxe", "insertCartCookie", params, completeInsertCart, response_tags);
}

function completeInsertCart()
{
	location.href = current_url.setQuery('act','dispShopxeCartList');
}
