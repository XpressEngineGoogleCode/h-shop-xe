function completeUpdatePurchaseStep()
{
	location.href = current_url.setQuery('act', 'dispShopxePurchaseList');
}

// 주문삭제후
function completeDeletePurchase(ret_obj)
{
    var error = ret_obj['error'];
    var message = ret_obj['message'];
    var page = ret_obj['page'];
    alert(message);

	var url = current_url.setQuery('act','dispShopxePurchaseList');
    location.href = url;
}

