<?php

	/**
	* @class  product
	* @author 덧니희야 (deive@nate.com)
	* @brief  product 모듈의 high class
	**/

	require_once "productitem.class.php";

	class product extends ModuleObject {

		var $isConnected = false;

        var $search_option = array('title','content','title_content','comment','user_name','nick_name','user_id','tag'); ///< 검색 옵션

        var $order_target = array('list_order', 'update_order', 'regdate', 'voted_count', 'readed_count', 'comment_count', 'title', 'price_sale'); // 정렬 옵션

        var $skin = "xe_product_d_v1"; ///< 스킨 이름
        var $list_count = 20; ///< 한 페이지에 나타날 글의 수
        var $page_count = 10; ///< 페이지의 수
        var $category_list = NULL; ///< 카테고리 목록


        /**
         * @brief 설치시 추가 작업이 필요할시 구현
         **/
        function moduleInstall() {
            // action forward에 등록 (관리자 모드에서 사용하기 위함)
            $oModuleController = &getController('module');
            $oModuleModel = &getModel('module');

			$oShopXeModel = &getModel('shopxe');
			if($oShopXeModel) $isConnected = true;

            // 기본 상품관리 생성
            $args->site_srl = 0;
            $output = executeQuery('module.getSite', $args);
            if(!$output->data->index_module_srl) {
                $args->mid = 'product';
                $args->module = 'product';
                $args->browser_title = 'Shopxe';
                $args->skin = 'xe_product_d_v1';
                $args->site_srl = 0;
                $output = $oModuleController->insertModule($args);
                $module_srl = $output->get('module_srl');
                $site_args->site_srl = 0;
                $site_args->index_module_srl = $module_srl;
                $oModuleController = &getController('module');
                $oModuleController->updateSite($site_args);
            }

            return new Object();
        }

        /**
         * @brief 업데이트 내용이 없는지 체크하는 method
         **/
        function checkUpdate() {
            $oDB = &DB::getInstance();
            $oModuleModel = &getModel('module');

			// 2010. 1. 28 상품관리(products)에 stock,event_item 항목추가
            if(!$oDB->isColumnExists("products","stock")) return true;
            if(!$oDB->isColumnExists("products","event_item")) return true;

			// 2010. 3. 27 상품관리 옵션추가에 option_req 항목추가
            if(!$oDB->isColumnExists("product_option","option_req")) return true;
			
			// 2010. 4. 3 옵션추가시 변경사항
			if(!$oDB->isColumnExists("product_option","option_srl")) return true;

            return false;
        }

        /**
         * @brief 업데이트 실행
         **/
        function moduleUpdate() {
            $oDB = &DB::getInstance();
            $oModuleModel = &getModel('module');
            $oModuleController = &getController('module');

            // 2010. 1. 28 상품관리(products)에 stock,event_item 항목추가
            if(!$oDB->isColumnExists("products","stock")) $oDB->addColumn('products',"stock","number",11,0);
            if(!$oDB->isColumnExists("products","event_item")) $oDB->addColumn('products',"event_item","varchar",255);

			// 2010. 3. 27 상품관리 옵션추가에 option_req 항목추가
            if(!$oDB->isColumnExists("product_option","option_req")) $oDB->addColumn('product_option',"option_req","char",1);

			// 2010. 4. 3
            $query = sprintf("alter table %s%s CHANGE %s `option_srl` BIGINT( 11 ) NOT NULL ", $oDB->prefix, 'product_option', "option_srl");
            $oDB->_query($query);

			// 2010. 4. 10 다락방서버/IIS 를 위한 null값 대체
            $query = sprintf("alter table %s%s CHANGE %s `list_order` int(11) default '0' ", $oDB->prefix, 'products', "list_order");
            $oDB->_query($query);
            $query = sprintf("alter table %s%s CHANGE %s `best_order` int(11) default '0' ", $oDB->prefix, 'products', "best_order");
            $oDB->_query($query);

            return new Object(0, 'success_updated');
        }

        /**
         * @brief 캐시 파일 재생성
         **/
        function recompileCache() {
        }

    }
?>
