/**
 * @file   modules/product/js/product.js
 * @author 덧니희야 (deive@nate.com)
 * @brief  product 모듈의 javascript
 **/

/* 글쓰기 작성후 */
function completeDocumentInserted(ret_obj) {
    var error = ret_obj['error'];
    var message = ret_obj['message'];
    var mid = ret_obj['mid'];
    var document_srl = ret_obj['document_srl'];
    var category_srl = ret_obj['category_srl'];

    //alert(message);

    var url;
    if(!document_srl)
    {
        url = current_url.setQuery('mid',mid).setQuery('act','');
    }
    else
    {
        url = current_url.setQuery('mid',mid).setQuery('document_srl',document_srl).setQuery('act','');
    }
    if(category_srl) url = url.setQuery('category',category_srl);
    location.href = url;
}

/* 글 삭제 */
function completeDeleteDocument(ret_obj) {
    var error = ret_obj['error'];
    var message = ret_obj['message'];
    var mid = ret_obj['mid'];
    var page = ret_obj['page'];

    var url = current_url.setQuery('mid',mid).setQuery('act','').setQuery('document_srl','');
    if(page) url = url.setQuery('page',page);

    //alert(message);

    location.href = url;
}

/* 검색 실행 */
function completeSearch(fo_obj, params) {
    fo_obj.submit();
}

function completeVote(ret_obj) {
    var error = ret_obj['error'];
    var message = ret_obj['message'];
    alert(message);
    location.href = location.href;
}

// 현재 페이지 reload
function completeReload(ret_obj) {
    var error = ret_obj['error'];
    var message = ret_obj['message'];

    location.href = location.href;
}

/* 댓글 글쓰기 작성후 */
function completeInsertComment(ret_obj) {
    var error = ret_obj['error'];
    var message = ret_obj['message'];
    var mid = ret_obj['mid'];
    var document_srl = ret_obj['document_srl'];
    var comment_srl = ret_obj['comment_srl'];

    var url = current_url.setQuery('mid',mid).setQuery('document_srl',document_srl).setQuery('act','');
    if(comment_srl) url = url.setQuery('rnd',comment_srl)+"#comment_"+comment_srl;

    //alert(message);

    location.href = url;
}

/* 댓글 삭제 */
function completeDeleteComment(ret_obj) {
    var error = ret_obj['error'];
    var message = ret_obj['message'];
    var mid = ret_obj['mid'];
    var document_srl = ret_obj['document_srl'];
    var page = ret_obj['page'];

    var url = current_url.setQuery('mid',mid).setQuery('document_srl',document_srl).setQuery('act','');
    if(page) url = url.setQuery('page',page);

    //alert(message);

    location.href = url;
}

/* 트랙백 삭제 */
function completeDeleteTrackback(ret_obj) {
    var error = ret_obj['error'];
    var message = ret_obj['message'];
    var mid = ret_obj['mid'];
    var document_srl = ret_obj['document_srl'];
    var page = ret_obj['page'];

    var url = current_url.setQuery('mid',mid).setQuery('document_srl',document_srl).setQuery('act','');
    if(page) url = url.setQuery('page',page);

    //alert(message);

    location.href = url;
}

/* 카테고리 이동 */
function doChangeCategory() {
    var category_srl = jQuery('#product_category option:selected').val();
    location.href = decodeURI(current_url).setQuery('category',category_srl);
}

/* 스크랩 */
function doScrap(document_srl) {
    var params = new Array();
    params["document_srl"] = document_srl;
    exec_xml("member","procMemberScrapDocument", params, null);
}

/**
 * @brief 이미지 순서변경
 **/
function doUpdateImageUpDown(image_srl, mode, message) {
    if(typeof(message)!='undefined'&&!confirm(message)) return;

    var fo_obj = xGetElementById('image_form');
    fo_obj.image_srl.value = image_srl;
    fo_obj.mode.value = mode;

    procFilter(fo_obj, update_image);
}

/**
 * @brief 이미지 정보수정 후
 **/
function completeUpdateProductImage(ret_obj) {
    var error = ret_obj['error'];
    var message = ret_obj['message'];
    var mid = ret_obj['mid'];
    var document_srl = ret_obj['document_srl'];
    alert(message);

    //var url = current_url.setQuery('mid',mid).setQuery('act','dispProductInsertImage').setQuery('document_srl',document_srl);
    //location.href = url;
	location.href = current_url;
}

function doViewHidden(tmp_form,tmp_value) {
	var target = xGetElementById(tmp_form); // id
	if(	tmp_value ){
		target.style.display = "none";
	}else{
		target.style.display = "";
	}
}

/**
 * @brief 이미지 정보수정 후
 **/
function completeUpdateProductImage(ret_obj) {
    var error = ret_obj['error'];
    var message = ret_obj['message'];
    var mid = ret_obj['mid'];
    var document_srl = ret_obj['document_srl'];
    alert(message);
	location.href = current_url;
}

/**
 * @brief 옵션 추가후
 **/
function completeInsertOption(ret_obj) {
    var error = ret_obj['error'];
    var message = ret_obj['message'];
    var mid = ret_obj['mid'];
    var document_srl = ret_obj['document_srl'];
    alert(message);
	location.href = current_url;
}

/**
 * @brief 옵션수정
 **/
function doUpdateOptionHead(tmp_srl,tmp_name) {
	var target = xGetElementById('fo_option_head_write');
	
	target.head_option_srl.value = tmp_srl;
	target.head_option_name.value = tmp_name;
	target.head_type.value = "update";
	target.head_option_name.focus();
}


/**
 * @brief 옵션수정
 **/
function doUpdateOptionItem(tmp_srl,tmp_parent_srl,tmp_name,tmp_price) {
	var target = xGetElementById('fo_option_item_write');
	
	target.option_srl.value = tmp_srl;
	target.option_name.value = tmp_name;
	target.option_price.value = tmp_price;
	target.item_type.value = "update";
	jQuery("#fo_option_item_write select option[value="+tmp_parent_srl+"]").attr("selected", "true");
	target.option_name.focus();
}

/**
 * @brief 옵션수정
 **/
function doDeleteOption(tmp_srl) {
	var target = xGetElementById('fo_option_delete');
	target.delete_option_srl.value = tmp_srl;
	return procFilter(target,delete_option);
}

/**
 * @brief 옵션 추가후
 **/
function completeDeleteOption(ret_obj) {
    var error = ret_obj['error'];
    var message = ret_obj['message'];
    var mid = ret_obj['mid'];
    var document_srl = ret_obj['document_srl'];
    alert(message);
	location.href = current_url;
}

/* 카테고리 이동 */
function doChangeCategory() {
    var sel_obj = xGetElementById("board_category");
    var sel_idx = sel_obj.selectedIndex;
    var category_srl = sel_obj.options[sel_idx].value;
    location.href = decodeURI(current_url).setQuery('category',category_srl);
}

/* 카테고리 보기 */
function showCategory()
{
	jQuery('#view_category').toggle();
}

/*************************
* 목록 - 장바구니
************************/

function doInsertCart( document_srl, remid )
{
	var result = true;
	var params = new Object();
	var options_arr = new Array();
	params["target_var"] = document_srl;
	if( jQuery("#orderForm input[name='volume']").val() )
	{
		params["volume_var"] = jQuery("#orderForm input[name='volume']").val();
	}else{
		params["volume_var"] = 1;
	}

	if( jQuery("#orderForm select[name='options_list']").length != 0 )
	{
		jQuery("#orderForm select[name='options_list']").each(function(i){
			// 필수옵션값 체크
			if( jQuery("#orderForm input[name='option_req']").eq(i).val() == "Y" )
			{
				if( !jQuery("#orderForm select[name='options_list']").eq(i).val() )
				{
					alert("옵션을 선택해주세요.");
					result = false;
					return false;
				}
			}

			// 옵션값 전달변수에 삽입
			options_arr[i] = jQuery("#orderForm select[name='options_list']").eq(i).val();
		});
		params["options_var"] = options_arr;
	}
	if( result == true )
	{
		var response_tags = new Array('error','message','tpl',remid);
		exec_xml("shopxe", "insertCartCookie", params, completeInsertCart, response_tags);
	}
}
function completeInsertCart(ret_obj, response_tags, args, fo_obj) 
{
	if(confirm("장바구니에 등록되었습니다. 장바구니로 이동하시겠습니까?"))
	{
		var mid = ret_obj['message'];
		location.href = current_url.setQuery('document_srl','').setQuery('mid',mid).setQuery('act','dispShopxeCartList').setQuery('remid',response_tags[3]);
	}
}

/*************************
* 목록 - 위시리스트
************************/
function doInsertWish( product_srl, remid )
{
	var params = new Object();
	params["product_srl"] = product_srl;
    var response_tags = new Array('error','message','tpl',remid);
	exec_xml("shopxe", "procShopxeInsertWish", params, completeInsertWish, response_tags);
}
function completeInsertWish(ret_obj, response_tags, args, fo_obj) 
{
	if(confirm("관심상품에 등록되었습니다. 관심상품으로 이동하시겠습니까?"))
	{
		var mid = ret_obj['message'];
		location.href = current_url.setQuery('document_srl','').setQuery('mid',mid).setQuery('act','dispShopxeWishList').setQuery('remid',response_tags[3]);
	}
}


/*************************
* 목록 - 바로구매
************************/
function doInsertOrderForm( document_srl, remid )
{
	var result = true;
	var params = new Object();
	var options_arr = new Array();
	
	params["target_var"] = document_srl;
	if( jQuery("#orderForm input[name='volume']").val() )
	{
		params["volume_var"] = jQuery("#orderForm input[name='volume']").val();
	}else{
		params["volume_var"] = 1;
	}
	
	if( jQuery("#orderForm select[name='options_list']").length != 0 )
	{
		jQuery("#orderForm select[name='options_list']").each(function(i){
			// 필수옵션값 체크
			if( jQuery("#orderForm input[name='option_req']").eq(i).val() == "Y" )
			{
				if( !jQuery("#orderForm select[name='options_list']").eq(i).val() )
				{
					alert("옵션을 선택해주세요.");
					result = false;
					return false;
				}
			}

			// 옵션값 전달변수에 삽입
			options_arr[i] = jQuery("#orderForm select[name='options_list']").eq(i).val();
		});
		params["options_var"] = options_arr;
	}
	if( result == true )
	{
		var response_tags = new Array('error','message','tpl',remid);
		exec_xml("shopxe", "insertPurchaseCookie", params, completeInsertOrderForm, response_tags);
	}
}
function completeInsertOrderForm(ret_obj, response_tags, args, fo_obj) 
{
	var mid = ret_obj['message'];
	location.href = current_url.setQuery('document_srl','').setQuery('mid',mid).setQuery('act', 'dispShopxeInsertPurchase').setQuery('remid',response_tags[3]);
}

