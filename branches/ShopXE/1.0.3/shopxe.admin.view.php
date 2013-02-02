<?php
	/**
     * @class  ShopXE Admin View
	 * @author 덧니희야(deive@nate.com, http://www.iwebmall.co.kr, http://shop.xpressengine.net/) 
     * @brief  ShopXE Admin View Class File
     **/
	 
	class shopxeAdminView extends shopxe
	{

		/**
		*@brief 초기화
		**/
		function init()
		{
			$oModuleModel = &getModel('module');
			$this->module_info = $oModuleModel->getModuleConfig('shopxe');
			Context::set('module_info',$this->module_info);
            // 모듈 카테고리 목록을 구함
            $module_category = $oModuleModel->getModuleCategories();
            Context::set('module_category', $module_category);
			Context::set('paymentState', $this->paymentState);
			Context::set('paymentType', $this->paymentType);
            $this->setTemplatePath($this->module_path."/tpl/");
            $template_path = sprintf("%stpl/",$this->module_path);
            $this->setTemplatePath($template_path);
		}

		/**
		*@brief 주문상태 / 환경정보 / 로그분석등의 표면정보제공 및 연동모듈 바로가기 기능
		*@file : index.html
		**/
		function dispShopxeAdminIndex() 
		{
            $oModuleModel = &getModel('module');
			$oShopxeModel = &getModel('shopxe');
			$oPaymentModel = &getAdminModel('payment');
			// 버젼 가져오기
			$current_version = $oShopxeModel->getShopxeVersion();
			$server_version = $this->getServerInfo( 'version' );
			Context::set('current_version', $current_version);
			Context::set('server_version', $server_version);
			// 최근 주문목록 가져오기 (5개)
			$output = $oShopxeModel->getPurchaseInfoList(5, 1, 'created_at');
			// 주문번호로 결제타입 문자열로 변경
			if( $output->data != null ) {
				foreach($output->data as $no => $val){
					$output->data[$no]->payment_state = $oShopxeModel->getPaymentStateToString( $val->step );
				}
			}
			Context::set('purchase_list', $output->data);
			// 최근 결제목록 가져오기 (5개)
			$output = $oPaymentModel->getSettlementList(5, 1, 'pay_date');
			Context::set('payment_list', $output->data);
			// 템플릿 파일 지정
			$this->setTemplateFile('index');
		}

		/**
		*@brief 쇼핑몰 환경값 설정
		*@file : shop_manage.html
		**/
		function dispShopxeAdminShopManage() 
		{
			// 결제모듈을 구해옴
			if($this->is_payment){
				$oPaymentModel = &getAdminModel('payment');
				$payment_list = $oPaymentModel->getPaymentList(20, 1, 'regdate');
				Context::set('payment_list', $payment_list->data);
			}
			// 스킨 목록을 구해옴
            $oModuleModel = &getModel('module');
            $skin_list = $oModuleModel->getSkins($this->module_path);
            Context::set('skin_list',$skin_list);
            // 레이아웃 목록을 구해옴
            $oLayoutMode = &getModel('layout');
            $layout_list = $oLayoutMode->getLayoutList();
            Context::set('layout_list', $layout_list);
			// 템플릿 파일 지정
			$this->setTemplateFile('shop_manage');
		}

		/**
		*@brief 주문목록 관리자용
		*@file : purchase_list.html
		**/
		function dispShopxeAdminPurchaseList() 
		{
			// 검색값 가져오기
			$search_target = Context::get("search_target");
			$search_keyword = Context::get("search_keyword");
			$search_type = Context::get("search_type");
			//$arg->min_step = Context::get("search_step");
			$arg->step = Context::get("search_step");
			$page = (Context::get("page"))? Context::get("page") : "1";
			$arg->created_at = Context::get("created_at");
			$arg->created_at_end = Context::get("created_at_end");
			Context::set("created_at",$arg->created_at);
			Context::set("created_at_end",$arg->created_at_end);
			$oShopxeModel = &getModel("shopxe");
			$output = $oShopxeModel->getPurchaseInfoList(20, $page, 'created_at', $arg, $search_target, $search_keyword);
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

			Context::set('row_list', $output->data);
            Context::set('total_count', $output->total_count);
            Context::set('total_page', $output->total_page);
            Context::set('page', $output->page);
			Context::set('page_navigation', $output->page_navigation);
			// 템플릿 파일 지정
			$this->setTemplateFile('purchase_list');
		}

		/**
		*@brief 주문내용 관리
		*@file : purchase_manage.html
		**/
		function dispShopxeAdminPurchaseManage() 
		{
            $purchase_srl = Context::get("purchase_srl");
			$oShopxeModel = &getModel("shopxe");
			$purchase_info = $oShopxeModel->getPurchaseInfo($purchase_srl);
			$purchase_items = $oShopxeModel->getPurchaseItems($purchase_srl);
			Context::set('purchase_info', $purchase_info);
			Context::set('purchase_items', $purchase_items);
			// 템플릿 파일 지정
			$this->setTemplateFile('purchase_manage');
		}

		/**
		*@brief 주문내용 삭제
		*@file : purchase_delete.html
		**/
		function dispShopxeAdminDeletePurchase() 
		{
            $purchase_srl = Context::get("purchase_srl");
			$oShopxeModel = &getModel("shopxe");
			$purchase_info = $oShopxeModel->getPurchaseInfo($purchase_srl);
			$purchase_items = $oShopxeModel->getPurchaseItems($purchase_srl);
			$purchase_info->data->purchase_items = $oShopxeModel->getPurchaseItems( $purchase_srl );
			Context::set('purchase_info', $purchase_info);
			Context::set('purchase_items', $purchase_items);
			// 템플릿 파일 지정
			$this->setTemplateFile('purchase_delete');
		}

		/**
		*@brief 결제목록 관리
		*@file : payment_list.html
		**/
		function dispShopxeAdminPaymentList() 
		{
			$oPaymentModel = &getModel("payment");
			$page = (Context::get("page"))? Context::get("page") : "1";
			$oPaymentModel = &getAdminModel('payment');
			$output = $oPaymentModel->getSettlementList(20, $page, 'pay_date');
			Context::set('row_list', $output->data);
			Context::set('page_navigation', $output->page_navigation);
			// 템플릿 파일 지정
			$this->setTemplateFile('payment_list');
		}

		/**
		*@brief 결제내용
		*@file : payment_list.html
		**/
		function dispShopxeAdminPaymentManage() 
		{
            $oPaymentModel = &getModel('payment');
            $output = $oPaymentModel->getSettlementInfo(Context::get("order_srl"));
            $oSettleInfo = new SettlementInfo();
            $oSettleInfo->setAttribute($output);
            Context::set('settle_info', $oSettleInfo);

			// 템플릿 파일 지정
			$this->setTemplateFile('payment_manage');
		}

		/**
		*@brief 스킨관리
		**/
		function dispShopxeAdminSkinInfo() 
		{
            // 공통 모듈 권한 설정 페이지 호출
            $oModuleAdminModel = &getAdminModel('module');
            $skin_content = $oModuleAdminModel->getModuleSkinHTML($this->module_info->module_srl);
            Context::set('skin_content', $skin_content);
			// 템플릿 파일 지정
			$this->setTemplateFile('skin_info');
		}

		/**
		*@brief 로그분석 (미구현)
		**/
		function dispShopxeAdminLogInfo() 
		{

			// 템플릿 파일 지정
			$this->setTemplateFile('log_info');
		}

	}
?>