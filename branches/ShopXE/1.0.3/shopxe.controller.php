<?php
	/**
     * @class  ShopXE Controller
	 * @author 덧니희야(deive@nate.com, http://www.iwebmall.co.kr, http://shop.xpressengine.net/) 
     * @brief  ShopXE Controller Class File
     **/

	class shopxeController extends shopxe
	{
		/**
		 * @brief Shopxe Controller Class 초기화함수
		 **/
        function init() {
		}
		
		/**
		 * @brief 환경설정 추가
		 **/
		function insertShopxeConfig($args) {
			$oModuleController = &getController('module');
			$oModuleController->insertModuleConfig('shopxe', $args);
		}

        /**
         * @brief 모듈입력
         **/
		function procShopxeInsertShop()
		{
            // module 모듈의 model/controller 객체 생성
            $oModuleController = &getController('module');
            $oModuleModel = &getModel('module');
            // Shop 모듈의 정보 설정
            $args = Context::getRequestVars();
            $args->module = 'shopxe';
            // module_srl이 넘어오면 원 모듈이 있는지 확인
            if($args->module_srl) {
                $module_info = $oModuleModel->getModuleInfoByModuleSrl($args->module_srl);
                if($module_info->module_srl != $args->module_srl) unset($args->module_srl);
            }
            // module_srl의 값에 따라 insert/update
            if(!$args->module_srl) {
                $output = $oModuleController->insertModule($args);
				$args->module_srl = $output->get('module_srl');
				$this->insertshopxeConfig($args);
                $msg_code = 'success_registed';
            } else {
                $output = $oModuleController->updateModule($args);
				$this->insertshopxeConfig($args);
                $msg_code = 'success_updated';
            }
            if(!$output->toBool()) return $output;
            $this->setMessage($msg_code);
		}

        /**
         * @brief 주문추가
         **/
		function procShopxeInsertPurchase()
		{
            // Shop 제품설명의 주문정보를 받아옴
            $args = Context::getRequestVars();
			$oShopxeModel = &getModel("shopxe");
			if($args->purchase_srl!=""){
				$args->purchase_srl = $oShopxeModel->UpdatePurchase($args);
			}else{
				$args->purchase_srl = $oShopxeModel->InsertPurchase($args);
			}
            $this->add('purchase_srl',$args->purchase_srl);
		}

        /**
         * @brief 주문수정
         **/
		function procShopxeUpdatePurchase()
		{
            $args = Context::getRequestVars();
			$oShopxeModel = &getModel("shopxe");
			if($args->purchase_srl!="" && ($args->transmit_info_business != null || $args->transmit_info_number != null )){
				$tmp_obj->transmit_info_business = $args->transmit_info_business;
				$tmp_obj->transmit_info_number = $args->transmit_info_number;
				$args->transmit_info = serialize($tmp_obj);
			}
			$obj->call_type = $args->payment_call;
			if( $args->step == 1 )
			{
				$obj->current_state = 2;
			}

			// 주문정보 수정시 회원포인트 복구시키기
			if( $args->purchase_srl)
			{
				$oModuleModel = &getModel('module');
				$shopxe_module_info = $oModuleModel->getModuleConfig('shopxe');
				if( $shopxe_module_info->point_use )
				{
					$output = $oShopxeModel->getPurchaseInfo($args->purchase_srl);
					// 예약정보의 회원번호로 포인트 복구
					if( $output->get('step') == 9 || $output->get('step') == 10 )
					{
						if($output->get('member_srl'))
						{
							$oPointController = &getController("point");
							// 삭제시 기존 포인트 복구
							$oPointController->setPoint($output->get('member_srl'),$output->get('discount_amount'),'add');
						}
					}
					// 구매확정시 포인트 적립
					if( $args->step == 5 && $output->get('step') != 5 )
					{
						if($output->get('member_srl'))
						{
							$purchase_items = $oShopxeModel->getPurchaseItems($output->get('purchase_srl'));
							if(!$purchase_items) return new Object(-1, 'shopxe_failed_purchase');
							$tmp_point = 0;
							foreach($purchase_items as $no => $val)
							{
								$output_item = $val->get('item');
								$tmp_point += $output_item->get('buyed_point');
							}
							$oPointController = &getController("point");
							$oPointController->setPoint($output->get('member_srl'),$tmp_point,'add');
						}
						$obj->current_state = 2;
					}
				}

			}

			$this->procShopxeUpdatePaymentSettlementInfo( $obj );
			$args->purchase_srl = $oShopxeModel->UpdatePurchase($args);
		}

		/**
         * @brief 주문삭제
         **/
		function procShopxeDeletePurchase()
		{
            $purchase_srl = Context::get("purchase_srl");
            $args->purchase_srl = $purchase_srl;
			
			if($args->purchase_srl)
			{
				$oShopxeModel = &getModel("shopxe");
				$output = $oShopxeModel->getPurchaseInfo($args->purchase_srl);

				// 예약정보의 회원번호로 포인트 복구
				if( $output->get('step') == 0 )
				{
					if($output->get('member_srl'))
					{
						$oPointController = &getController("point");
						// 삭제시 기존 포인트 복구
						$oPointController->setPoint($tmp->member_srl,$output->get('discount_amount'),'add');
					}
				}
			}

            $output = executeQuery('shopxe.deletePurchase', $args);
            $output = executeQuery('shopxe.deletePurchaseList', $args);
			$oPaymentController = &getController("payment");
			$oPaymentController->deleteSettle( $args->purchase_srl );
			return $output;
		}

        /**
         * @brief 장바구니 추가
         **/
		function procShopxeInsertCart()
		{
            $args = Context::getRequestVars();
			$oShopxeModel = &getModel("shopxe");
            $this->setMessage($msg_code);
		}
		
        /**
         * @brief 장바구니 수정
         **/
		function procShopxeUpdateCart()
		{
            $args = Context::getRequestVars();
            $this->setMessage($msg_code);
		}
		
        /**
         * @brief 장바구니 삭제
         **/
		function procShopxeDeleteCart()
		{
            $args = Context::getRequestVars();
			$oShopxeModel = &getModel("shopxe");
			$oShopxeModel->ListCookieDelete($args->cart_srls,'cart');
		}

		/**
         * @brief 결제결과 리턴
         **/
		function procShopxePurchasePaymentResult() {
            $args = Context::getRequestVars();
			$oShopxeModel = &getModel("shopxe");
			$args->purchase_srl = $args->payment_order_srl;
			print_r($args);
			// 결제성공시 처리구문
			if( $args->payment_result_code == '00' )
			{
				$args->step = 2;
				$oShopxeModel->UpdatePurchase($args);
			}else{
			}
			$url = Context::getRequestUri(RELEASE_SSL) . "?mid=".$this->mid."&act=dispShopxePurchasePaymentResult&purchase_srl=".$args->purchase_srl."&payment_result_code=".$args->payment_result_code."&payment_result_msg=".$args->payment_result_msg;
			header("location:" . $url);
			return new Object(0, 'success');
		}

		/**
		 * @brief 장바구니 쿠키추가
		 **/
		function insertCartCookie()
		{
			$args->document_srls = explode("||", Context::get("target_var"));
			$args->volumns       = explode("||", Context::get("volume_var"));
			$args->sel_options   = explode("||", Context::get("options_var"));
			$args->remid       = explode("||", Context::get("remid"));
			$cookie_value = $_COOKIE["shopxeCartArr"];
			if(!is_array($args->document_srls))
			{
				for($n = 0; $n < $args->volumns; $n++)
				{
					if($cookie_value) $cookie_value .= "\n";
					if( $args->sel_options )
					{
						$cookie_value .= $args->document_srls . "&&" . $args->sel_options;
					}else{
						$cookie_value .= $args->document_srls;
					}
					
					setcookie("shopxeCartArr", $cookie_value);
				}
			}
			else
			{
				foreach($args->document_srls as $key=>$value)
				{
					for($n = 0; $n < $args->volumns[$key]; $n++)
					{
						if($cookie_value) $cookie_value .= "\n";
						if( $args->sel_options[$key] )
						{
							$cookie_value .= $value . "&&" . $args->sel_options[$key];
						}else{
							$cookie_value .= $value;
						}
						//$cookie_value .= $value . "&&" . $args->sel_options[$key];
						setcookie("shopxeCartArr", $cookie_value);
					}
				}
			}
			$oModuleModel = &getModel('module');
			$shopxe_module_info = $oModuleModel->getModuleConfig('shopxe');
			return new Object(0, $shopxe_module_info->mid, null);
		}

		/**
		 * @brief 장바구니 쿠키삭제
		 **/
		function deleteCartCookie()
		{
			$cookie_value = $_COOKIE["shopxeCartArr"];
			$tmp_con = str_replace('-', ',', Context::get("target_var"));
			$tmp_con = str_replace('_', '&&', $tmp_con);
			$tmp_array = explode("||", $tmp_con);

			$args->document_srls = explode("&&", $tmp_array[0]);
			$args->options = explode("&&", $tmp_array[1]);
			$args->volumns       = explode("||", Context::get("volume_var"));
			$cookie_arr = explode("\n", $cookie_value);

			foreach($cookie_arr as $key => $value)
			{
				//$value = str_replace('&&', '', $value);
				//return new Object( -1, $value );

				if(!is_array($args->document_srls) && $value == $tmp_array)
				{
					array_splice($cookie_arr, $key, 1);
				}else if(in_array($value, $tmp_array))
				{
					$key2 = array_search($value, $cookie_arr); 
					array_splice($cookie_arr, $key2, 1);
				}
			}

			$cookie_value = "";
			foreach($cookie_arr as $key => $value)
			{
				if(!$value) continue;
				if($cookie_value) $cookie_value .= "\n";
				$cookie_value .= $value;
			}
			setcookie("shopxeCartArr", $cookie_value);
            return new Object(0, 'success');
		}

		/**
		 * @bref 장바구니용 쿠키 해제
		 **/
		function unsetCartCookie() {
			setcookie("shopxeCartArr");
			$_COOKIE["shopxeCartArr"] = "";
		}

        /**
         * @brief  위시리스트에 추가하기
         **/
		function procShopxeInsertWish()
		{
			$oModuleModel = &getModel('module');
			$shopxe_module_info = $oModuleModel->getModuleConfig('shopxe');
			if(!Context::get('is_logged')) return new Object(1, 'usr_login');
			$tmp = Context::get('logged_info');
			$args->member_srl  = $tmp->member_srl;
            $args->product_srl = Context::get('product_srl');
            $output = executeQuery('shopxe.getWish', $args);
			if($output->data) return new Object(0,$shopxe_module_info->mid, null);
			$args->wish_srl = getNextSequence();
            $output = executeQuery('shopxe.insertWish', $args);
			return new Object(0, $shopxe_module_info->mid, null);
		}

        /**
         * @brief  위시리스트에서 제거하기
         **/
		function procShopxeDeleteWish()
		{
			if(!Context::get('is_logged')) return new Object(1, 'usr_login');
			$tmp = Context::get("logged_info");
			$args->member_srl  = $tmp->member_srl;

			// 도큐먼트srl로 프로덕트srl가져오기
			$oProductModel = &getModel('product');

			if( Context::get('target_var') )
			{
				$tmp_array = explode("||", Context::get("target_var"));
				foreach( $tmp_array as $key => $value )
				{
					$tmp1 = $oProductModel->getProductByDocumentSrl( $value );
					$args->product_srl = $tmp1->data->product_srl;
					$output = executeQuery('shopxe.deleteWish', $args);
				}
				return new Object(0, 'success');
			}

			if( Context::get('product_srl') )
			{
	            $args->product_srl = Context::get('product_srl');
				$output = executeQuery('shopxe.deleteWish', $args);
				return new Object(0, 'success');
			}
		}

		/**
		 * @bref 구매용 쿠키 생성
		 **/
		function insertPurchaseCookie()
		{
			$this->unsetPurcharseCookie();
			$tmp_con = str_replace('-', ',', Context::get("target_var"));
			$tmp_con = str_replace('_', '&&', $tmp_con);
			$args->document_srls = explode("||", $tmp_con);
			$args->volumns       = explode("||", Context::get("volume_var"));
			$args->sel_options   = explode("||", Context::get("options_var"));

			if(!is_array($args->document_srls))
			{
				$cookie_value = "";
				for($n = 0; $n < $args->volumns; $n++)
				{
					if($cookie_value) $cookie_value .= "\n";
					$cookie_value .= $args->document_srls . "&&" . $args->sel_options;
					setcookie("shopxePurchaseArr", $cookie_value);
				}
			}else{
				$cookie_value = "";
				foreach($args->document_srls as $key=>$value)
				{
					for($n = 0; $n < $args->volumns[$key]; $n++)
					{
						if($cookie_value) $cookie_value .= "\n";
						$cookie_value .= $value . "&&" . $args->sel_options[$key];
						setcookie("shopxePurchaseArr", $cookie_value);
					}
				}
			}

			$oModuleModel = &getModel('module');
			$shopxe_module_info = $oModuleModel->getModuleConfig('shopxe');
			return new Object(0, $shopxe_module_info->mid, null);
		}

		/**
		 * @bref 구매용 쿠키 해제
		 **/
		function unsetPurcharseCookie() 
		{
			setcookie("shopxePurchaseArr");
			$_COOKIE["shopxePurchaseArr"] = "";
		}

		/**
		 * @brief 주문상태 수정
		 **/
		function updatePurchaseState($purcharse_srl, $step)
		{
			$args->purchase_srl = $purcharse_srl;
			$args->step = $step;
			$output = executeQuery('shopxe.updatePurchase', $args);
		}

		/**
		 * @brief 주문추가
		 **/
		function insertPurchase()
		{
			$oShopxeModel = &getModel("shopxe");
			$args = Context::getRequestVars();
			if($args->purchase_srl)
			{
				$output = $oShopxeModel->getPurchaseInfo($args->purchase_srl);
			}
			if($output)
			{
				$oEmailAddress = explode("@", Context::get('o_mail_address'));
				$rEmailAddress = explode("@", Context::get('r_mail_address'));
				$tmp = Context::get("logged_info");
				$args->member_srl      = $tmp->member_srl;
				$args->order_code      = $args->order_code;
				$args->o_mail_id       = $oEmailAddress[0];
				$args->o_mail_host     = $oEmailAddress[1];
				$args->o_phone         = Context::get('o_phone_0')."-".Context::get('o_phone_1')."-".Context::get('o_phone_2');
				$args->o_cellular      = Context::get('o_cellular_0')."-".Context::get('o_cellular_1')."-".Context::get('o_cellular_2');
				$args->r_mail_id       = $rEmailAddress[0];
				$args->r_mail_host     = $rEmailAddress[1];
				$args->r_phone         = Context::get('r_phone_0')."-".Context::get('r_phone_1')."-".Context::get('r_phone_2');
				$args->r_cellular      = Context::get('r_cellular_0')."-".Context::get('r_cellular_1')."-".Context::get('r_cellular_2');
				$args->order_amount    = $args->order_amount;
				$args->delivery_amount = Context::get('delivery_amount');
				$args->discount_amount = Context::get('discount_amount');
				$args->discount_info = '';
				$args->updated_at = date("YmdHis");
				if( Context::get('use_point') == "on" )
				{
					if(Context::get('sale_point'))
					{
						// 로그인 상태일때만 실행
						if($tmp->member_srl)
						{
							$oPointController = &getController("point");
							// 업데이트시 기존 포인트 사용금액 더하고 다시빼기
							$oPointController->setPoint($tmp->member_srl,$output->get('discount_amount'),'add');
							$oPointController->setPoint($tmp->member_srl,Context::get('sale_point'),'minus');
							$args->discount_amount += Context::get('sale_point');
						}
					}
				}
				$args->payment_amount  = $args->order_amount - $args->discount_amount + $args->delivery_amount;
				$output = executeQuery('shopxe.updatePurchase', $args);
				$this->add("purchase_srl", $args->purchase_srl);
			}
			else
			{

				$output = $oShopxeModel->getPurcharseCookie();

				$oEmailAddress = explode("@", Context::get('o_mail_address'));
				$rEmailAddress = explode("@", Context::get('r_mail_address'));
				$args->purchase_srl    = getNextSequence();
				$tmp = Context::get("logged_info");
				$args->member_srl      = $tmp->member_srl;
				$args->order_code      = $oShopxeModel->getNewOrderCode();
				$args->o_mail_id       = $oEmailAddress[0];
				$args->o_mail_host     = $oEmailAddress[1];
				$args->o_phone         = Context::get('o_phone_0')."-".Context::get('o_phone_1')."-".Context::get('o_phone_2');
				$args->o_cellular      = Context::get('o_cellular_0')."-".Context::get('o_cellular_1')."-".Context::get('o_cellular_2');
				$args->r_mail_id       = $rEmailAddress[0];
				$args->r_mail_host     = $rEmailAddress[1];
				$args->r_phone         = Context::get('r_phone_0')."-".Context::get('r_phone_1')."-".Context::get('r_phone_2');
				$args->r_cellular      = Context::get('r_cellular_0')."-".Context::get('r_cellular_1')."-".Context::get('r_cellular_2');
				$args->order_amount    = $args->order_amount;
				$args->delivery_amount = Context::get('delivery_amount');
				$args->discount_amount = Context::get('discount_amount');
				$args->discount_info = '';
				$args->created_at = date("YmdHis");
				$args->updated_at = date("YmdHis");
				
				if( Context::get('use_point') == "on" )
				{
					if(Context::get('sale_point'))
					{
						// 로그인 상태일때만 실행
						if($tmp->member_srl)
						{
							$oPointController = &getController("point");
							$oPointController->setPoint($tmp->member_srl,Context::get('sale_point'),'minus');
							$args->discount_amount += Context::get('sale_point');
						}
					}
				}
				foreach($output as $key => $value)
				{
					$args_sub->purchase_item_srl = getNextSequence();
					$args_sub->purchase_srl =  $args->purchase_srl;
					$tmp = $value->get('item');
					$args_sub->document_srl =  $tmp->get('document_srl');
					$args_sub->amount       =  $tmp->get('price_sale');
					$args_sub->amount       =  $tmp->get('price_sale');
					$args_sub->option       =  $value->get('options');
					$args_sub->volume       =  $value->get('volume');
					$output_sub = executeQuery('shopxe.insertPurchaseItem', $args_sub);
				}
				$args->payment_amount  = $args->order_amount - $args->discount_amount + $args->delivery_amount;
				$output = executeQuery('shopxe.insertPurchase', $args);
				$this->add("purchase_srl", $args->purchase_srl);
			}
			return new Object(0, 'success');
		}

		/**
		 * @brief 결제정보 업데이트
		 **/
		function procShopxeUpdatePaymentSettlementInfo( $args = null )
		{
			$oPaymentController = &getController("payment");
			if( $args == null )
			{
				$args = Context::getRequestVars();
			}

			// 환불처리시 결제처리 요청
			if( $args->current_state == 3 )
			{
				$oPaymentModel = &getModel('payment');
				$output = $oPaymentModel->getSettlementInfo($args->order_srl);
				$oModuleModel = &getModel('module');
				$shopxe_module_info = $oModuleModel->getModuleConfig('shopxe');
				$args->payment_mid = $shopxe_module_info->shopxe_payment;
				$args->tid = $output->system_srl; 
				$args->msg = "주문취소";

				$output = $oPaymentModel->getPaymentCencelCaller($args);
				/****************************************************************
				 * 5. 취소 결과                                           	*
				 * 결과코드 : $output->m_resultCode ("00"이면 취소 성공)  	*
				 * 결과내용 : $output->m_resultMsg (취소결과에 대한 설명) 	*
				 * 취소날짜 : $output->m_pgCancelDate (YYYYMMDD)          	*
				 * 취소시각 : $output->m_pgCancelTime (HHMMSS)            	*
				 * 현금영수증 취소 승인번호 : $output->m_rcash_cancel_noappl    *
				 * (현금영수증 발급 취소시에만 리턴됨)                          * 
				 ****************************************************************/

				 if( $output->m_resultCode = "01" )
				{
					return new Object( -1, "취소실패" );
				}else{
					$args->finance_message = $output->m_resultMsg . "(" .$output->m_pgCancelDate . ")" . " 취소되었습니다.";
				}
			}

			$output = $oPaymentController->updateSettle($args);

			return $output;
		}

		/**
		 * @brief 결제모듈 결제완료시 처리트리거
		 **/
		function triggerCompletePayment(&$obj)
		{
			$oPaymentModel = &getModel('payment');
			$oPaymentController = &getController("payment");
			$tmp = $obj->get('formContents');

			// 결제사 받아오기
			if( $obj->get('formName') == "IN4_PAYINFO" )
			{
				// ****************
				// 이니시스4일때
				// ****************
				if( $tmp[0][value] ==  '01' ){
					// 결제실패
					$output = $oPaymentModel->getSettlementInfo($tmp[3][value]);
					$oSettleInfo = new SettlementInfo();
					$oSettleInfo->setAttribute($output);
					
					// 이전에 결제항목이 있을때 수정
					if( $oSettleInfo->get('result_code') != '00' )
					{
						$this->updatePurchaseState( $tmp[3][value], 0 );
					}
				}elseif( $tmp[0][value] ==  '00' ){
					// 결제성공
					if( $tmp[2][value] == "VBank" )
					{
						//가상계좌 일때
					}else{
						//가상계좌 아닐때
						$this->updatePurchaseState($obj->get('formSrl'), 1);
					}
				}
			}elseif( $obj->get('formName') == "KCP_PAYINFO" ){
				// ****************
				// KCP 일때
				// ****************
				if( $tmp[0][value] ==  '0000' ){
					// 결제성공
					$output = $oPaymentModel->getSettlementInfo($obj->get('formSrl'));
					$oModuleModel = &getModel('module');
					$shopxe_module_info = $oModuleModel->getModuleConfig('shopxe');
					$args->payment_mid = $shopxe_module_info->shopxe_payment;

					if( $tmp[22][value] == "BK" )
					{
						//가상계좌 일때
						$args->current_state = 0;
					}else{
						//가상계좌 아닐때
						//주문내역 수정
						$this->updatePurchaseState($obj->get('formSrl'), 1);
						$args->current_state = 2;
						$output = $oPaymentController->updateSettle($args);
					}
				
				}else{
					// 결제실패
				}
		
			}else{
				// 기타
				$this->updatePurchaseState($tmp[3][value], 1);
			}


		}

	}
?>