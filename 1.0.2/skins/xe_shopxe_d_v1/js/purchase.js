function orCopy(obj)
{
	var objData = new Object();
	if(obj.checked)
	{
		jQuery("input[rel='original']").each(function(){
			objData[jQuery(this).attr("title")] = jQuery(this).val();
		});
	}

	jQuery("input[rel='receive']").each(function(){
		if(objData[jQuery(this).attr("title")])
			jQuery(this).val(objData[jQuery(this).attr("title")]);
		else
			jQuery(this).val("");
	});

}

function beforeSubmit(obj)
{
	var obj = jQuery("#purchase_form").get(0);

	var target_value = "";
	var volume_value = "";
	var option_value = "";

	jQuery("#purchase_form input[name='cart']:checked").each(function (){
		if(target_value != "") target_value = target_value + "||";
		target_value = target_value + jQuery(this).val();

		if(volume_value != "") volume_value = volume_value + "||";
		volume_value = volume_value + jQuery("#purchase_form input[name='volumn_"+jQuery(this).val()+"']").val();
		
		if(option_value != "") option_value = option_value + "||";
		option_value = option_value + jQuery("#purchase_form input[name='option_"+jQuery(this).val()+"']").val();
	});
	
	obj.target_var.value = target_value;
	obj.volume_var.value = volume_value;
	obj.options_var.value = option_value;

	if(target_value == "")
	{
		alert("선택된 상품이 존재하지 않습니다.");
		return;
	}

	return procFilter(obj, insert_purchase);
}

function chkOn(obj)
{
	if(obj.checked)
	{
		jQuery(obj.rel).val("0");
		jQuery(obj.rel).show();
	}
	else
	{
		jQuery(obj.rel).val("");
		jQuery(obj.rel).hide();
	}
}

function onlyNumber()
{
	if(((window.event.keyCode > 64)&&(window.event.keyCode < 91))||((window.event.keyCode >= 106)&&(window.event.keyCode < 123)))
	{
		window.event.returnValue=false;
	}
}

function pointChk( tmp_val1, tmp_val2 )
{
	if ( eval( jQuery(tmp_val1).val() ) > eval(tmp_val2) )
	{
		alert("가용 포인트보다 많습니다.");
		jQuery(tmp_val1).val('');
		return false;
	}
}

function updateValue(no, price, name, delivery_use, delivery_free, delivery_price)
{
	var obj = jQuery("#purchase_form").get(0);
	var total_price = 0;

	jQuery("#purchase_form input[id=pi"+no+"]").val( Format_comma(jQuery("#purchase_form input[name="+name+"]").val()*price) );

	jQuery("#purchase_form input[name=a_price]").each(function (){
		total_price += parseInt( Format_NoComma(jQuery(this).val()) );
	});

	jQuery("#purchase_form input[name=total_price]").val( Format_comma(total_price) );

	// 주문될 금액변경
	if( delivery_use == 'Y' )
	{
		if( delivery_free <= total_price )
		{
			delivery_price = 0;
		}
	}
	jQuery("#purchase_form input[name=delivery_amount]").val( delivery_price );
	jQuery("#purchase_form input[name=order_amount]").val( total_price );
	jQuery("#purchase_form input[name=payment_amount]").val( (Number(total_price) + Number(jQuery("#purchase_form input[name=delivery_amount]").val())) );

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
