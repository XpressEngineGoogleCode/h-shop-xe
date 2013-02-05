
// 옵션추가
// 덧니희야 2010. 3. 9

var option_list_idx = 1;

function addOption( type, idx, opt_srl, price, parent_srl, name, req ){

	var tmp_html_text = "";
	if( !opt_srl ) opt_srl = "0";
	if( !idx ) idx = 1;
	if( !price ) price = "0";
	if( !name ) name = "";
	if( !req || req == "N" ){
		req = "";
	}else{
		req = "checked='checked'";
	}

	if( type == "option" ){
		tmp_html_text = "<tbody id='option_"+option_list_idx+"' class='itemList'><tr class='isub'><input type='hidden' name='option_type' value='option'/><input type='hidden' name='option_srl_a' value='"+opt_srl+"'/><input type='hidden' name='option_parent_srl_a' value='"+parent_srl+"' /><input type='hidden' name='option_price' value='0'/><input type='hidden' name='at_index' value=''/><td><input type='text' name='option_name' value='"+name+"' class='inputTypeText w150'/></td><td><a href='#' onclick='addOption( \"item\", "+option_list_idx+", 0, 0, "+opt_srl+" ); return false;' class='button blue'><span>항목추가</span></a></td><td></td><td><input name='option_req' type='checkbox' "+req+" /></td><td><a href='#' onclick='delOptionList( this ); return false;' class='button red'><span>옵션삭제</span></a></td></tr></tbody>";
		jQuery(tmp_html_text).appendTo("#productOptionList"); 
		option_list_idx++;
	}
	
	if( type == "item" ){
		if( !parent_srl ){
			tmp_psrl = option_list_idx;
		}else{
			tmp_psrl = parent_srl;
		}
		
		tmp_html_text = "<tr><input type='hidden' name='option_type' value='item'/><input type='hidden' name='option_srl_a' value='"+opt_srl+"'/><input type='hidden' name='option_parent_srl_a' value='"+tmp_psrl+"' /><input type='hidden' name='at_index' value='0'/><td></td><td>▶ <input type='text' name='option_name' value='"+name+"' class='inputTypeText w150'/></td><td><input type='text' name='option_price' value='"+price+"' class='inputTypeText w150'/></td><td><input name='option_req' type='hidden' /></td><td><a href='#' onclick='delOption( this ); return false;' class='button'><span>삭제</span></a></td></tr>";
		jQuery(tmp_html_text).appendTo("#option_"+idx); 
	}
}

function delOptionList( target ){
	jQuery(target).parent().parent().parent().remove();
}

function delOption( target ){
	jQuery(target).parent().parent().remove();
}