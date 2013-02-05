<?php
	/**
     * @class  ShopXE View
	 * @author 덧니희야(deive@nate.com, http://www.iwebmall.co.kr, http://shop.xpressengine.net/) 
     * @brief  ShopXE View Class File
     **/

	class shopxeView extends shopxe 
	{

		function init()
		{
			$oModuleModel = &getModel('module');
			$this->module_info = $oModuleModel->getModuleConfig('shopxe');
			Context::set('module_info',$this->module_info);
			$remid = Context::get('remid');
			Context::set('remid',$remid);
		
			/**
			 * 스킨 경로를 미리 template_path 라는 변수로 설정함
			 * 스킨이 존재하지 않는다면 xe_product로 변경
			 **/
			$template_path = sprintf("%sskins/%s/",$this->module_path, $this->module_info->skin);
			if(!is_dir($template_path)||!$this->module_info->skin) {
				$this->module_info->skin = 'xe_shopxe';
				$template_path = sprintf("%sskins/%s/",$this->module_path, $this->module_info->skin);
			}
			$this->setTemplatePath($template_path);
		}

		function dispShopxePurchaseManage()
		{
		}

		/****************************************************
		* 주문하기 공간입니다.
		*----------------------------------------------------
		* Developer : Suhan Moon <suhan.moon@autobrain.net>
		* Date : 2009-11-09
		*****************************************************/

		function dispShopxeInsertPurchase()
		{

			$purchase_srl = Context::get("purchase_srl");
			$is_logged    = Context::get('is_logged');
			$logged_info  = Context::get('logged_info');
			Context::set("logged_info", $logged_info);
			
			$oShopxeModel = &getModel("shopxe");
			if($purchase_srl)
			{
				$result = $oShopxeModel->getPurchaseInfo($purchase_srl);
				$purchase_items = $oShopxeModel->getPurchaseItems($purchase_srl);
			}else{
				$purchase_items = $oShopxeModel->getPurcharseCookie();
			}

			if($result && $result->get('member_srl') != $logged_info->member_srl)
				return $this->stop('access_denied');

			if(!$is_logged && !Context::get('ignore_login'))
			{
				Context::set("currCommand", "purchase");
				$this->setTemplateFile('login_form');
				return;
			}

			if(!$result)
			{
				$result = new Object();
				$result->add("order_code", $oShopxeModel->getNewOrderCode());
				if($is_logged)
				{
					$result->add("o_user_name",    $logged_info->user_name);
					$result->add("o_phone",        $logged_info->phone);
					$result->add("o_cellular",     $logged_info->cellular);
					$result->add("o_mail_id",      $logged_info->email_id);
					$result->add("o_mail_host",    $logged_info->email_host);
					$result->add("o_mail_address", $logged_info->email_address);
					$result->add("o_addr",         $logged_info->addr);
					
				}
			}

			$total_price = 0;
			foreach($purchase_items as $key => $value)
			{
				$tmp = $value->get('item');
				$total_price += $value->get('volume') * $tmp->get('price_sale');
			}

			$allow_point = 0;
			if($is_logged)
			{
				$oPointModel = &getModel("point");
				$allow_point += $oPointModel->getPoint($logged_info->member_srl);
				//로그인정보
				$oMemberModel = &getModel('member');
				$member_info = $oMemberModel->getMemberInfoByMemberSrl($logged_info->member_srl);
				$extend_form_list = $oMemberModel->getCombineJoinForm($member_info);

				Context::set("member_info", $member_info);
				if($extend_form_list){
					// 추가 가입폼 목록을 받음
					Context::set('extend_form_list', $extend_form_list);
					foreach($extend_form_list as $key => $val)
					{
						if($val->is_active=='Y')
						{
							switch($val->column_name)
							{
								case $this->module_info->phone_fieldname:
									Context::set("m_phone", $val->value);
									break;
								case $this->module_info->cellular_fieldname:
									Context::set("m_cellular", $val->value);
									break;
								case $this->module_info->addr_fieldname:
									Context::set("m_addr", $val->value);
									break;
							}
						}
					}
				}
			}
			Context::set("oShopxeModel",$oShopxeModel);
			Context::set("allow_point", $allow_point);
			Context::set("purchase_items", $purchase_items);
			Context::set("purchase_info", $result);

			$this->setTemplateFile('purchase');
		}


		function dispShopxeUpdatePurchaseStep()
		{
			Context::set("purchase_srl",Context::get("purchase_srl"));
			Context::set("step",Context::get("step"));
			
			$this->setTemplateFile('purchase_step_update');
		}

		function dispShopxeDeletePurchase()
		{
			$purchase_srl = Context::get("purchase_srl");
			$oShopxeModel = &getModel("shopxe");
			if($purchase_srl)
				$result = $oShopxeModel->getPurchaseInfo($purchase_srl);

			Context::set("purchase_info",$result);
			Context::set("purchase_srl",Context::get("purchase_srl"));
			$this->setTemplateFile('purchase_delete');
		}

        /**
         * @brief 결제화면
         **/
		function dispShopxePurchasePayment()
		{
			$oShopxeModel   = &getModel("shopxe");
			$purchase_info  = $oShopxeModel->getPurchaseInfo(Context::get("purchase_srl"));
			$purchase_items = $oShopxeModel->getPurchaseItems(Context::get("purchase_srl"));
			if(!$purchase_info) return new Object(-1, 'shopxe_failed_purchase');
			$oShopxeController = &getController("shopxe");
			$oShopxeController->unsetCartCookie();

			Context::set('purchase_info', $purchase_info);
			Context::set('purchase_items', $purchase_items);

			$oPaymentModel = &getAdminModel('payment');
			if( $this->module_info->shopxe_payment != null )
			{
				// 결제모듈 연동되었을시
				$args = new StdClass(); 
				$args->payment_mid = $this->module_info->shopxe_payment;
				$args->order_srl  = Context::get("purchase_srl");
				//$purchase_info->get('order_code'); XE의 시퀀스 기능사용하지 않고 주문번호 생성시 코드받기
				$args->buyer_name = $purchase_info->get('o_user_name');
				$args->buyer_call = $purchase_info->get('o_cellular');
				$args->buyer_mail = $purchase_info->get('o_mail_id')."@".$purchase_info->get('o_mail_host');
				$args->buy_code   = $purchase_info->get('purchase_srl');
				$tmp = $purchase_items[0]->get('item');
				$args->buy_info   = $tmp->get('title');
				$args->buy_amount = $purchase_info->get('payment_amount');
				$args->call_type  = $purchase_info->get('payment_call');
				// 결제후 이동페이지 설정
				$args->result_page = getNotEncodedUrl('mid', Context::get('mid'), 'act', 'dispShopxePurchaseResult', 'purchase_srl', Context::get("purchase_srl"));
				$oPaymentModel = &getModel('payment');
				$oPaymentModel->getPaymentCaller($args);
				Context::set('buy_amount',$buy_amount);
			}else{
				// 결제모듈 없을때
			}

			$this->setTemplateFile('payment');
		}
		
        /**
         * @brief 결제후 결과페이지 (+결과처리)
         **/
		function dispShopxePurchaseResult()
		{

			$oShopxeModel   = &getModel("shopxe");
			$purchase_info  = $oShopxeModel->getPurchaseInfo(Context::get("purchase_srl"));
			$purchase_items = $oShopxeModel->getPurchaseItems(Context::get("purchase_srl"));

			Context::set('purchase_info',  $purchase_info);
			Context::set('purchase_items', $purchase_items);

			//결제결과처리
			$args = Context::getRequestVars();
			if( $purchase_info )
			{
				// 주문정보가 있을때 처리
				$oPaymentModel = &getModel("payment");
				$settlement_info = $oPaymentModel->getSettlementInfo($purchase_info->get('purchase_srl'));
				Context::set("settlement_info", $settlement_info);
			}
			if( !$args->payment_result_code )
			{
				// 결제정보가 결과처리
				$args = $settlement_info;
			}
			Context::set('webParams', $args);

			$oContext = Context::getInstance();
			$variableName = sprintf("payment_type_%s", $settlement_info->call_type);
			Context::set("payment_type", $oContext->lang->$variableName);

			$this->setTemplateFile('payment_result');
		}
		
		function dispShopxePurchaseList()
		{
			$oShopxeModel = &getModel("shopxe");
			$oPaymentModel = &getModel("payment");
			$is_logged    = Context::get('is_logged');
			$logged_info  = Context::get('logged_info');

			$date_week   = date("Ymd", mktime(0, 0, 0, date("m"), date("d")-7, date("Y")));
			$date_month  = date("Ymd", mktime(0, 0, 0, date("m")-1, date("d"), date("Y")));
			$date_3month = date("Ymd", mktime(0, 0, 0, date("m")-3, date("d"), date("Y")));

			Context::set("date_week",   $date_week);
			Context::set("date_month",  $date_month);
			Context::set("date_3month", $date_3month);
			
			if( $logged_info == null && !$_COOKIE["shopxePurchaseList"]){
				Context::set("currCommand", "view");
				$this->setTemplateFile('login_form');
				return;
			}else{
				$cookieArgs = explode("|@|", $_COOKIE["shopxePurchaseList"]);
				$args->member_srl = $logged_info->member_srl;
				$args->user_name  = $cookieArgs[0];
				$args->password   = $cookieArgs[1];
				$args->max_step   = 100;
				$args->created_at = Context::get('created_at');
				$args->created_at_end = Context::get('created_at_end');

				$output = $oShopxeModel->getPurchaseInfoList(20, $page, 'created_at', $args);
				if( $output->data != null ) {
					foreach($output->data as $no => $val){
						$output->data[$no]->payment_state = $oShopxeModel->getPaymentStateToString( $val->step );
						$output->data[$no]->payment_type = $oShopxeModel->getPaymentTypeToString( $val->payment_call );
						$output->data[$no]->payment = $oShopxeModel->getPaymentSrl( $val->payment_srl );
						$output->data[$no]->purchase_items = $oShopxeModel->getPurchaseItems( $val->purchase_srl );
						if( $val->delivery_type == '1' ) {
							$output->data[$no]->order_amount += $val->delivery_amount;
							$output->data[$no]->delivery_type = $oShopxeModel->getDeliveryTypeToString( $val->delivery_type )."(+".number_format($output->data[$no]->delivery_amount)."원)";
						}else{
							$output->data[$no]->delivery_type = $oShopxeModel->getDeliveryTypeToString( $val->delivery_type );
						}
					}
				}
				Context::set('v_created_at', Context::get('created_at'));
				Context::set('v_created_at_end', Context::get('created_at_end'));
				Context::set('purchase_list',$output->data);
				Context::set('oPaymentModel',$oPaymentModel);
				Context::set('page_navigation', $output->page_navigation);
				Context::set('paymentState', $this->paymentState);
				$this->setTemplateFile('purchase_list');
			}
		}

		function dispShopxeCancelList()
		{
			$oShopxeModel = &getModel("shopxe");
			$is_logged    = Context::get('is_logged');
			$logged_info  = Context::get('logged_info');
			
			$date_week   = date("Ymd", mktime(0, 0, 0, date("m"), date("d")-7, date("Y")));
			$date_month  = date("Ymd", mktime(0, 0, 0, date("m")-1, date("d"), date("Y")));
			$date_3month = date("Ymd", mktime(0, 0, 0, date("m")-3, date("d"), date("Y")));

			Context::set("date_week",   $date_week);
			Context::set("date_month",  $date_month);
			Context::set("date_3month", $date_3month);

			if( $logged_info == null && !$_COOKIE["shopxePurchaseList"]){

				Context::set("currCommand", "view");
				$this->setTemplateFile('login_form');
				return;

			}else{
				
				$cookieArgs = explode("|@|", $_COOKIE["shopxePurchaseList"]);
				$args->member_srl = $logged_info->member_srl;
				$args->user_name  = $cookieArgs[0];
				$args->password   = $cookieArgs[1];
				$args->min_step   = 5;

				$output = $oShopxeModel->getPurchaseInfoList(20, $page, 'created_at', $args);

				Context::set('purchase_list',$output->data);
				Context::set('page_navigation', $output->page_navigation);
				$this->setTemplateFile('cancel_list');
			}
		}

		/****************************************************
		* 관심상품 공간입니다.
		*----------------------------------------------------
		* Developer : Suhan Moon <suhan.moon@autobrain.net>
		* Date : 2009-11-09
		*****************************************************/

		function dispShopxeWishList()
		{
			$is_logged    = Context::get('is_logged');
			$logged_info  = Context::get('logged_info');
			Context::set("logged_info", $logged_info);
			if($result && $result->get('member_srl') != $logged_info->member_srl)
				return $this->stop('access_denied');
			if(!$is_logged && !Context::get('ignore_login'))
			{
				Context::set("currCommand", "wish_list");
				$this->setTemplateFile('login_form');
				return;
			}
			$remid  = Context::get('remid');
			Context::set('remid',$remid);
			$oShopxeModel = &getModel("shopxe");
			Context::set("wish_list", $oShopxeModel->getWishList());
			$this->setTemplateFile('wish_list');
		}

		/****************************************************
		* 장바구니 공간입니다.
		*----------------------------------------------------
		* Developer : Suhan Moon <suhan.moon@autobrain.net>
		* Date : 2009-11-09
		*****************************************************/
	
		function dispShopxeCartList()
		{
			$remid  = Context::get('remid');
			Context::set('remid',$remid);
			$oShopxeModel = &getModel("shopxe");
			Context::set('cart_list',$oShopxeModel->getCartCookie());
			$this->setTemplateFile('cart_list');
		}

	}
?>