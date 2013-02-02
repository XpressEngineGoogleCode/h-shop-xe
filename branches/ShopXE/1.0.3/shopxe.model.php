<?php
	/**
     * @class  ShopXE Model
	 * @author 덧니희야(deive@nate.com, http://www.iwebmall.co.kr, http://shop.xpressengine.net/) 
     * @brief  ShopXE Model Class File
     **/

	class shopxeModel extends shopxe
	{
	
		/**
         * @brief 초기화
         **/
		function init() { }

		/**
         * @brief 모듈 버젼 가져오기
         **/
		function getShopxeVersion()
		{
			return $this->currVer;
		}

		/**
         * @brief 모듈 환경정보 가져오기
         **/
		function getShopxeConfig()
		{
			// 모듈srl 값이 없을때
			$oModuleModel = &getModel('module');
			$module_info = $oModuleModel->getModuleConfig('shopxe');
			return $module_info;
		}

        /**
         * @brief 주문추가
         **/
		function InsertPurchase($args)
		{
			$args->purchase_srl = getNextSequence();
			executeQuery('shopxe.insertPurchase', $args);
			$this->InsertPurchaseList($args->purchase_srl,$args->state);
			$this->ListCookieDelete('all');
			return $args->purchase_srl;
		}

        /**
         * @brief 주문수정
         **/
		function UpdatePurchase($args)
		{
			executeQuery('shopxe.updatePurchase', $args);
			return $args->purchase_srl;
		}

        /**
         * @brief 주문추가시 내역추가
         **/
		function InsertPurchaseList($srl,$tmp_string)
		{
			if($tmp_string != null) {
				$tmp_str = explode($this->cut_str2,$tmp_string);
				foreach($tmp_str as $val) {
					$tmp_str1 = null;
					$tmp_str2 = null;
					$tmp_str1 = explode($this->cut_str1,$val);
					$tmp_str2->purchase_srl = $srl;
					$tmp_str2->document_srl = $tmp_str1[0];
					$tmp_str2->option = $tmp_str1[1];
					$tmp_str2->volume = $tmp_str1[2];
					$tmp_str2->amount = $tmp_str1[3];
					executeQuery('shopxe.insertPurchaseList', $tmp_str2);
				}
				return true;
			} else {
				return false;
			}
		}
		
		/**
         * @brief 결제상태 문자열로 반환
         **/
        function getPaymentSrl( $payment_srl = null ) {
			if( $payment_srl != null ) {
				//return $this->paymentState[$tmp_number];
			}else{
				return "결제내역이 없습니다.";
			}
		}

		/**
         * @brief 결제상태 문자열로 반환
         **/
        function getPaymentStateToString( $tmp_number = null ) {
			if( $tmp_number != null ) {
				return $this->paymentState[$tmp_number];
			}else{
				return "결제내역이 없습니다.";
			}
		}

		/**
         * @brief 결제방법 문자열로 반환
         **/
        function getPaymentTypeToString( $tmp_str = null ) {
			if( $tmp_str != null ) {
				return $this->paymentType[$tmp_str];
			}else{
				return "선택되지 않았습니다.";
			}
		}

		/**
         * @brief 배송형태 문자열로 반환
         **/
        function getDeliveryTypeToString( $tmp_str = null ) {
			if( $tmp_str != null ) {
				return $this->deliveryType[$tmp_str];
			}else{
				return "선택되지 않았습니다.";
			}
		}

		/****************************************************
		* 장바구니 공간입니다.
		*----------------------------------------------------
		* Developer : Suhan Moon <suhan.moon@autobrain.net>
		* Date : 2009-11-09
		*****************************************************/
		function getCartCookie($isProductItem = true)
		{
			$output = array();
			$oProductModel = &getModel("product");
			$arrCart  = explode("\n", $_COOKIE["shopxeCartArr"]);
			foreach(array_count_values($arrCart) as $key => $value)
			{
				if($key == '') continue;
				$objRaw = new Object();
				$value_tmp = explode("&&", $key);
				$value_document_srl = $value_tmp[0];
				$value_options_srl = $value_tmp[1];
				if($isProductItem)
					$objRaw->add('item', $oProductModel->getProductItemByDocumentSrl($value_document_srl));
				else
					$objRaw->add('document_srl', $value_document_srl);

				$objRaw->add('options', $value_options_srl);
				$objRaw->add('volume', $value);

				if( $oProductModel->getProductItemByDocumentSrl($value_document_srl) != null )
				{
					array_push($output, $objRaw);
				}
			}
			return $output;
		}

		/****************************************************
		* 주문하기 공간입니다.
		*----------------------------------------------------
		* Developer : Suhan Moon <suhan.moon@autobrain.net>
		* Date : 2009-11-09
		*****************************************************/
		function getNewOrderCode()
		{
			$time = mktime();
			$code = '1'.$time.rand(10,99);
			return $code;
		}

		function getPurcharseCookie($isProductItem = true)
		{
			$output = array();
			$oProductModel = &getModel("product");

			$arrPurcharse = explode("\n", $_COOKIE["shopxePurchaseArr"]);

			foreach(array_count_values($arrPurcharse) as $key => $value)
			{
				if($key == '') continue;
				$objRaw = new Object();

				$value_tmp = explode("&&", $key);
				$value_document_srl = $value_tmp[0];
				$value_options_srl = $value_tmp[1];

				if($isProductItem)
					$objRaw->add('item', $oProductModel->getProductItemByDocumentSrl($value_document_srl));
				else
					$objRaw->add('document_srl', $value_document_srl);

				$objRaw->add('options', $value_options_srl);
				$objRaw->add('volume', $value);

				array_push($output, $objRaw);
			}
			return $output;
		}

		function getPurchaseInfo($purchase_srl)
		{
			$args->purchase_srl = $purchase_srl;
			$output = executeQuery('shopxe.getPurchase', $args);

			if(!$output->toBool() || !$output->data) return false;
			$tmp = $output->data;
			$tmp->transmit_info = unserialize($tmp->transmit_info);
			$output->data = $tmp;
			$result = new Object();
			$result->adds($output->data);

			return $result;
		}

		function getPurchaseItems($purchase_srl)
		{
			$args->purchase_srl = $purchase_srl;
			$output = executeQuery('shopxe.getPurchaseItem', $args);
			if(!$output->toBool() || !$output->data) return false;
			$oProductModel = &getModel("product");
			$result = array();
			if(is_array($output->data))
			{
				foreach($output->data as $key => $value)
				{
					$objRaw = new Object();
					$objRaw->add('item', $oProductModel->getProductItemByDocumentSrl($value->document_srl));
					$objRaw->add('options', $value->option);
					$objRaw->add('volume', $value->volume);
					array_push($result, $objRaw);
				}
			}
			else
			{
				$value = $output->data;
				$objRaw = new Object();
				$objRaw->add('item',   $oProductModel->getProductItemByDocumentSrl($value->document_srl));
				$objRaw->add('options', $value->option);
				$objRaw->add('volume', $value->volume);
				array_push($result, $objRaw);
			}
			return $result;
		}

        /**
         * @brief 전체주문내역 가져오기
         **/
		function getPurchaseInfoList( $list_count, $page_count, $order_type, $args = null, $search_target = null, $search_keyword = null )
		{
			//
			$args->list_count = $list_count;
			$args->page = $page_count;
			$args->sort_index = $order_type;
			$args->order_type = 'desc';
			if( $args->created_at != null ){
				$args->created_at = $args->created_at."000000";
			}
			if( $args->created_at_end != null ){
				$args->created_at_end = $args->created_at_end."999999";
			}
            if($search_target && $search_keyword) {
                switch($search_target) {
                    case 'purchase_srl' :
                            if($search_keyword) $search_keyword = str_replace(' ','%',$search_keyword);
                            $args->s_purchase_srl = $search_keyword;
                        break;
                    case 'name' :
                            if($search_keyword) $search_keyword = str_replace(' ','%',$search_keyword);
                            $args->s_o_user_name = $search_keyword;
                        break;
                    case 'o_phone' :
                            if($search_keyword) $search_keyword = str_replace(' ','%',$search_keyword);
                            $args->s_o_phone = $search_keyword;
                        break;
                    case 'o_cellular' :
                            if($search_keyword) $search_keyword = str_replace(' ','%',$search_keyword);
                            $args->s_o_cellular = $search_keyword;
                        break;
                }
            }
			$output = executeQueryArray('shopxe.getPurchaseInfoList', $args);
			debugPrint($output);
			if( $output->data != null )
			{
				foreach( $output->data as $key => $val )
				{
					$tmp = $output->data[$key];
					if( $tmp->transmit_info != null )
					{
						$tmp->transmit_info = unserialize($tmp->transmit_info);
						$output->data[$key] = $tmp;
					}
				}
			}
			//print_r($output);
			return $output;
		}

		/****************************************************
		* 관심상품 공간입니다.
		*----------------------------------------------------
		* Developer : Suhan Moon <suhan.moon@autobrain.net>
		* Date : 2009-11-09
		*****************************************************/
		function getWishList()
		{
			$tmp = Context::get("logged_info");
			$args->member_srl = $tmp->member_srl;
            $output = executeQueryArray('shopxe.getWish', $args);
			$oProductModel = &getModel("product");
			if(!$output->data) return null;
			$productItems = array();
			foreach($output->data as $key => $val)
			{
				if( $oProductModel->getProductByProductSrl($val->product_srl) != null )
				{
					array_push($productItems, $oProductModel->getProductByProductSrl($val->product_srl));
				}
			}
			return $productItems;
		}
	}
?>