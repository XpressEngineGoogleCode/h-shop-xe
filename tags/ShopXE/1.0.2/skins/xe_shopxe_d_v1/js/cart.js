function releaseCheck(action)
{
	var obj = jQuery("#cart_form").get(0);

	var target_value = "";
	var volume_value = "";
	var option_value = "";

	jQuery("#cart_form input[name='cart']:checked").each(function (){
		if(target_value != "") target_value = target_value + "||";
		target_value = target_value + jQuery(this).val();

		if(volume_value != "") volume_value = volume_value + "||";
		volume_value = volume_value + jQuery("#cart_form input[name='volumn_"+jQuery(this).val()+"']").val();
		
		if(option_value != "") option_value = option_value + "||";
		option_value = option_value + jQuery("#cart_form input[name='option_"+jQuery(this).val()+"']").val();
	});
	
	obj.target_var.value = target_value;
	obj.volume_var.value = volume_value;
	obj.options_var.value = option_value;

	if(target_value == "")
	{
		alert("선택된 상품이 존재하지 않습니다.");
		return;
	}
	return procFilter(obj, action);
}

function updateValue(no, price, name)
{
	var obj = jQuery("#cart_form").get(0);
	var total_price = 0;

	jQuery("#cart_form input[id=pi"+no+"]").val( Format_comma(jQuery("#cart_form input[name="+name+"]").val()*price) );

	jQuery("#cart_form input[name=a_price]").each(function (){
		total_price += parseInt( Format_NoComma(jQuery(this).val()) );
	});

	jQuery("#cart_form input[name=total_price]").val( Format_comma(total_price) );

}

function completeInsertOrder()
{
	location.href = current_url.setQuery('act', 'dispShopxeInsertPurchase');
}

function completeDeleteCart()
{
	location.href = current_url;
}

//-----------------------------------------------------------------------------------------------
// 숫자에 3자리마다 콤마찍기(현금표시)
//-----------------------------------------------------------------------------------------------
function Format_comma(val1){
	var newValue = val1+""; //숫자를 문자열로 변환
	var len = newValue.length;  
	var ch="";
	var j=1;
	var formatValue="";

	// 콤마제거  
	newValue = newValue.replace(/\,/gi, ' ');

	// comma제거된 문자열 길이
	len = newValue.length;

	for(i=len ; i>0 ; i--){
	ch = newValue.substring(i-1,i);
	formatValue = ch + formatValue;
	if ((j%3) == 0 && i>1 ){
	formatValue=","+formatValue;
	}
	j++
	}
	return formatValue;
}

//-----------------------------------------------------------------------------------------------
// 콤마제거
//-----------------------------------------------------------------------------------------------
function Format_NoComma(val1){
	return (val1+"").replace(/\,/gi, '');
}
