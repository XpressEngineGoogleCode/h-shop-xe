function toPrice( num )
{
	var sign="";
	if(isNaN(num)) {
		alert("숫자만 입력할 수 있습니다");
		return 0;
	}
	if(num==0){ return num; }
	
	if(num<0){
		num=num*(-1);
		sign="-";
	} else num=num*1;
	num = new String(num)
	var temp="";
	var pos=3;
	num_len=num.length;
	while (num_len>0){
		num_len=num_len-pos;
		if(num_len<0) {
			pos=num_len+pos;
			num_len=0;
		}
		temp=","+num.substr(num_len,pos)+temp;
	}
	return sign+temp.substr(1);
}

function doChangeDeliveryType(){
	var ret_price_str = null;
	
	if(	jQuery('[name=delivery_type] option:selected').val() == 1 )
	{
		ret_price_str = parseInt(jQuery('[name=order_amount]').val()) + parseInt(jQuery('[name=delivery_amount]').val	());
		
	}else{
		ret_price_str = parseInt(jQuery('[name=order_amount]').val());
	}
	jQuery('[name=total_price_text]').text(toPrice(ret_price_str));
}

function doCheckOrdToRct(){
	var fo_obj = xGetElementById('purchase_form');
	
	if( fo_obj.ordtorct_check.checked ){
		fo_obj.rct_name.value = fo_obj.ord_name.value;
		fo_obj.rct_phone.value = fo_obj.ord_phone.value;
		fo_obj.rct_cellular.value = fo_obj.ord_cellular.value;
		
		fo_obj.addr.value = fo_obj.ord_addr_1.value;
		fo_obj.addr_1.value = fo_obj.ord_addr_2.value;
		if(fo_obj.ord_addr_1.value != "")
		{
			jQuery("#zone_address_1_addr").css("display", "block");
			jQuery("#zone_address_search_addr").css("display", "none");
		}
	}else{
		fo_obj.rct_name.value = "";
		fo_obj.rct_phone.value = "";
		fo_obj.rct_cellular.value = "";
		fo_obj.addr.value = "";
		fo_obj.addr_1.value = "";
		jQuery("#zone_address_1_addr").css("display", "none");
		jQuery("#zone_address_search_addr").css("display", "block");
	}
}

// Shop 주문 후
function completeInsertPurchase(ret_obj)
{
    var error = ret_obj['error'];
    var message = ret_obj['message'];
    var purchase_srl = ret_obj['purchase_srl'];

	var url = current_url.setQuery('act','dispShopxePurchasePayment').setQuery('purchase_srl',purchase_srl);
    location.href = url;
}

// 주문삭제후
function completeDeletePurchase(ret_obj)
{
    var error = ret_obj['error'];
    var message = ret_obj['message'];
    var page = ret_obj['page'];

	var url = current_url.setQuery('act','dispShopxePurchaseList');
    location.href = url;
}

// Shop 주문시 옵션값 넘기기
function changeOptionToString()
{
	var tmp_count = 0;
	var total_price = Number(jQuery("[name=product_price]").val());
	
    jQuery('[name=options] option:selected').each(function() {
		if( tmp_count == 0 ) {
			jQuery("[name=option]").val(jQuery(this).val());
		}else{
			jQuery("[name=option]").val(jQuery("[name=option]").val() + "&&" + jQuery(this).val());
		}
		total_price += Number(jQuery(this).val().substring( jQuery(this).val().lastIndexOf("::")+2, jQuery(this).val().length ));
		tmp_count++;
    });
	jQuery("[name=amount]").val(total_price);
}

function doDeleteChekeCart(){
    var cart = new Array();
    jQuery('#cart_form input[name=cart]:checked').each(function() {
        cart[cart.length] = jQuery(this).val();
    });
    if(cart.length<1){
		alert('삭제하실 상품을 선택하십시요.');
		return false;
	}
	
	var form_obj = xGetElementById('cart_form');
	form_obj.cart_srls.value = cart.join(',');
	procFilter(form_obj, delete_cart);
}

function completeDeleteCart(ret_obj){
    var error = ret_obj['error'];
    var message = ret_obj['message'];
    alert(message);

    location.href = current_url;
}

/* 주문,장바구니,관심물품 등록하기 */
function doShopxeInsertOrder(obj,type) {
	obj.form.insert_type.value = type;
	changeOptionToString();
	var form_obj = jQuery("#purchase_form").get(0);
	return procFilter(form_obj, insert_order);
}

// Shop 주문 후
function completeInsertOrder(ret_obj)
{
    var insert_type = ret_obj['insert_type'];
	var shopxe_mid = ret_obj['shopxe_mid'];
    var error = ret_obj['error'];
    var message = ret_obj['message'];
    var document_srl = ret_obj['document_srl'];

	var url = current_url.setQuery('mid',shopxe_mid).setQuery('insert_type',insert_type);
	switch(insert_type){
		case "purchase":
			url = url.setQuery('act','dispShopxeInsertPurchase');
			break;
		case "cart":
			url = url.setQuery('act','dispShopxeCartList');
			break;
		case "wish":
			url = url.setQuery('act','dispShopxeWishList');
			break;
	}
    location.href = url;
}

/*************************
* 결제내용 변경 스크립트
************************/
function doUpdateOrderState( order_srl, state, remid )
{
	var params = new Object();
	
	params["order_srl"] = order_srl;
	params["result_code"] = "00";
	params["result_message"] = "통장입금으로 선택되었습니다.";
	params["finance_name"] = "은행이름";
	params["finance_description"] = "계좌";
	params["finance_message"] = "메세지";
	params["current_state"] = state;
	params["call_type"] = "XX";

	var response_tags = new Array('error','message','tpl',remid);
	exec_xml("payment", "updateSettle", params, completeUpdateOrderState, response_tags);
}
function completeUpdateOrderState(ret_obj, response_tags, args, fo_obj) 
{
	var mid = ret_obj['message'];
	location.href = current_url.setQuery('document_srl','').setQuery('mid',mid).setQuery('act', 'dispShopxePurchaseResult').setQuery('remid',response_tags[3]);
}
