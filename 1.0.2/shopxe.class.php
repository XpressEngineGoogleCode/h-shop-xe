<?php
	/**
	 * @class  ShopXE
	 * @author 덧니희야(deive@nate.com, http://www.iwebmall.co.kr, http://shop.xpressengine.net/) 
	 * @brief  ShopXE Main Class File
	 **/

	class shopxe extends ModuleObject 
	{
		/*************************************************************************************************
		 * @brief  클래스 전역변수
		 *************************************************************************************************/
		var $currVer = '1.0.2';
		var $is_product = false;
		var $is_payment = false;
		var $is_popup = false;
		var $shop_cookie_time = 3600;
		var $mePath = "";
		
		// 주문상태
		var $paymentState		= array( "주문신청", "결제완료", "배송준비", "배송중", "배송완료", "구매확정",
										 "주문중", "주문취소", "환불신청", "환불완료", "판매거부", "반품신청", "반품완료", "교환신청", "교환완료" );
		// 결제방법
		var $paymentType		= array( 'XX'=>'통장입금', 'CD'=>'신용카드', 'AB'=>'계좌이체', 'BK'=>'가상계좌', 'HP'=>'휴대폰', '7A'=>'700 ARS', 'OC'=>'OK Cashbag', 'CM'=>'문화상품권', 'GM'=>'게임문화상품권' );

		// 결제방법
		var $paymentTax		= array( "무신청", "현금영수증", "세금계산서" );

		// 배송비
		var $deliveryType		= array( '1'=>'선불', '2'=>'후불' );
		

		/*************************************************************************************************
		 * @brief  클래스 XE구조 전역함수
		 *************************************************************************************************/
		 
		/**
		 * @brief Shopxe 클래스 초기화함수
		 **/
		function shopxe() 
		{
			$this->checkModule();
			$this->setLinkedModule();
			$this->mePath  = _XE_PATH_.ModuleHandler::getModulePath('shopxe');
		}

		/**
		 * @brief 설치시 추가 작업이 필요할시 구현
		 **/
		function moduleInstall() 
		{
			// 상품관리 모듈체크
			if(!is_dir('./modules/product/'))
			{
				$this->stop("required_product");
			}

			$oModuleModel = &getModel('module');
			$oModuleController = &getController('module');
			$oShopxeController = &getController('shopxe');

			$module_info = $oModuleModel->getModuleConfig('shopxe');
			if($module_info->mid) {
					$_o = executeQuery('module.getMidInfo', $module_info);
					if(!$_o->data) unset($module_info);
			}
			
			// 모듈이 없을시 기본형태 생성
			if(!$module_info->mid) {
					$args->module = 'shopxe';
					$args->browser_title = 'ShopXE';
					$args->skin = 'xe_shopxe_d_v1';
					$args->is_default = 'N';
					$args->mid = 'shopxe';
					$args->module_srl = getNextSequence();
					$output = $oModuleController->insertModule($args);

					$shop_args = $args;
					$oShopxeController->insertshopxeConfig($shop_args);
			}
			
			$oModuleController = &getController('module');
			$oModuleController->insertTrigger('payment.doPaymentComplete', 'shopxe', 'controller', 'triggerCompletePayment', 'after');
			
			return true;
		}

		/**
		 * @brief 설치가 이상이 없는지 체크하는 method
		 **/
		function checkUpdate() 
		{
			return false;
		}

		/**
		 * @brief 업데이트 실행
		 **/
		function moduleUpdate() 
		{
			$this->moduleInstall();
			return new Object(0, 'success_updated');
		}

		/**
		 * @brief 캐시 파일 재생성
		 **/
		function recompileCache() 
		{
		}

		/*************************************************************************************************
		 * @brief  기능추가함수
		 *************************************************************************************************/
		// 설치된 XE 버젼 Integer값 구하기
		function getXEVersion()
		{
			return $this->getVersionInt(__ZBXE_VERSION__);
		}

		// 버젼 비교를 위해 major.minor.build 문자열버젼을 Integer로 변환
		function getVersionInt($version_str)
		{
			$version = split("\.", $version_str);
			$major = intval($version[0]) * 10000;
			$minor = intval($version[1]) * 100;
			$build = intval($version[2]);

			// 예) 1.2.4 버젼의 integer값은 10204가 된다.
			$version_int = $major + $minor + $build;
			return $version_int;
		}

		// XE 버젼체크하여 이하면 경고문구 출력
		function checkModule() 
		{
			if ($this->getXEVersion() <= $this->getVersionInt("1.2.5"))
			{
				$this->stop('msg_xe_version_danger');
				return false;
			}
			return true;
		}

		// ShopXE 버젼체크
		function checkShopXEVersion() 
		{
			if ($this->getVersionInt($this->currVer) <= $this->getVersionInt($this->getServerInfo('version')))
			{
				return true;
			}else{
				return false;
			}
		}

		function getServerInfo( $type = 'version' )
		{
			$newest_news_url = 'http://www.iwebmall.co.kr/version/latest.php';
			$cache_file = sprintf("%sfiles/cache/shop_news.%s.cache.php", _XE_PATH_,Context::getLangType());
			if(!file_exists($cache_file) || filemtime($cache_file)+ 60*1 < time()) {
				// Ensure to access the administration page even though news cannot be displayed
				FileHandler::writeFile($cache_file,'');
				FileHandler::getRemoteFile($newest_news_url, $cache_file, null, 1, 'GET', 'text/html', array('REQUESTURL'=>getFullUrl('')));
			}

			if(file_exists($cache_file)) {
				$oXml = new XmlParser();
				$buff = $oXml->parse(FileHandler::readFile($cache_file));

				switch( $type ){
					case "version" :
						return $buff->shop_news->attrs->released_version;
						break;
					case "download_link" :
						return $buff->shop_news->attrs->download_link;
						break;
					case "item" :
						break;
				}
			}
		}
		
		// 연동모듈 체크
		function setLinkedModule()
		{
			// 1. 상품관리/제품설명 모듈
			if(is_dir('./modules/product/')) {
				$this->is_product = true;
			}
			// 2. 결제모듈
			if(is_dir('./modules/payment/')) {
				$this->is_payment = true;
			}
			// 3. 팝업모듈
			if(is_dir('./modules/pop_up/')) {
				$this->is_popup = true;
			}

			Context::set('is_product',$this->is_product);
			Context::set('is_payment',$this->is_payment);
			Context::set('is_popup',$this->is_popup);

			return true;
		}

		function getLinkedProductModule()
		{
			return $this->is_product;
		}

		function getLinkedPaymentModule()
		{
			return $this->is_payment;
		}

		function getLinkedPopupModule()
		{
			return $this->is_popup;
		}
	}
?>