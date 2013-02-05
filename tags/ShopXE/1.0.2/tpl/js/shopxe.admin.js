// Shop 모듈추가 후
function completeInsertShop(ret_obj)
{
    var error = ret_obj['error'];
    var message = ret_obj['message'];
    alert(message);

	var url = current_url.setQuery('module','admin').setQuery('act','dispShopxeAdminShopManage');
    location.href = url;
}

// 주문삭제후
function completeDeletePurchase(ret_obj)
{
    var error = ret_obj['error'];
    var message = ret_obj['message'];
    var page = ret_obj['page'];
    alert(message);

	var url = current_url.setQuery('module','admin').setQuery('act','dispShopxeAdminPurchaseList');
    location.href = url;
}

// 주문수정후
function completeUpdatePurchase(ret_obj)
{
    var error = ret_obj['error'];
    var message = ret_obj['message'];
    var page = ret_obj['page'];
    alert(message);

	var url = current_url.setQuery('module','admin').setQuery('act','dispShopxeAdminPurchaseList');
    location.href = url;
}

// 결제정보 수정후
function completeUpdatePayment(ret_obj)
{
    var error = ret_obj['error'];
    var message = ret_obj['message'];
    var order_srl = ret_obj['order_srl'];
    alert(message);

	var url = current_url.setQuery('module','admin').setQuery('act','dispShopxeAdminPaymentManage');
    location.href = url;
}
