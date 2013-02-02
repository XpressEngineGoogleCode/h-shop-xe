<?php
    /**
     * @class  productAdminController
     * @author 덧니희야 (deive@nate.com)
     * @brief  product 모듈의 admin controller class
     **/

    class productAdminController extends product {

        /**
         * @brief 초기화
         **/
        function init() {
        }

        /**
         * @brief 상품관리 추가
         **/
        function procProductAdminInsertProduct($args = null) {
            // module 모듈의 model/controller 객체 생성
            $oModuleController = &getController('module');
            $oModuleModel = &getModel('module');

            // 상품관리 모듈의 정보 설정
            $args = Context::getRequestVars();
            $args->module = 'product';
            $args->mid = $args->product_name;
            unset($args->product_name);

            // 기본 값외의 것들을 정리
            if($args->use_category!='Y') $args->use_category = 'N';
            if($args->except_notice!='Y') $args->except_notice = 'N';
            if($args->use_anonymous!='Y') $args->use_anonymous= 'N';
            if($args->consultation!='Y') $args->consultation = 'N';
            if(!in_array($args->order_target,$this->order_target)) $args->order_target = 'list_order';
            if(!in_array($args->order_type,array('asc','desc'))) $args->order_type = 'asc';
			
            // module_srl이 넘어오면 원 모듈이 있는지 확인
            if($args->module_srl) {
                $module_info = $oModuleModel->getModuleInfoByModuleSrl($args->module_srl);
                if($module_info->module_srl != $args->module_srl) unset($args->module_srl);
            }

            // module_srl의 값에 따라 insert/update
            if(!$args->module_srl) {
                $output = $oModuleController->insertModule($args);
				// 확장변수 강제입력
				$this->procProductAdminInsertExtraKey($output->get('module_srl'));
				//
                $msg_code = 'success_registed';
            } else {
                $output = $oModuleController->updateModule($args);
                $msg_code = 'success_updated';
            }

            if(!$output->toBool()) return $output;

            $this->add('page',Context::get('page'));
            $this->add('module_srl',$output->get('module_srl'));
            $this->setMessage($msg_code);
        }

        /**
         * @brief 상품관리 삭제
         **/
        function procProductAdminDeleteProduct() {
            $module_srl = Context::get('module_srl');

            // 원본을 구해온다
            $oModuleController = &getController('module');
            $output = $oModuleController->deleteModule($module_srl);
            if(!$output->toBool()) return $output;
			// 상품설명의 확장변수도 삭제
			$args->module_srl = $module_srl;
			$output = executeQueryArray('document.deleteDocumentExtraKeys', $args);
            if(!$output->toBool()) return $output;

            $this->add('module','product');
            $this->add('page',Context::get('page'));
            $this->setMessage('success_deleted');
        }

        /**
         * @brief 상품관리 목록 지정
         **/
        function procProductAdminInsertListConfig() {
            $module_srl = Context::get('module_srl');
            $list = explode(',',Context::get('list'));
            if(!count($list)) return new Object(-1, 'msg_invalid_request');

            $list_arr = array();
            foreach($list as $val) {
                $val = trim($val);
                if(!$val) continue;
                if(substr($val,0,10)=='extra_vars') $val = substr($val,10);
                $list_arr[] = $val;
            }

            $oModuleController = &getController('module');
            $oModuleController->insertModulePartConfig('product', $module_srl, $list_arr);
        }
		
        /**
         * @brief 상품관리 삭제
         **/
        function procProductAdminInsertExtraKey($module_srl) {
			// idx가 지정되어 있지 않으면 최고 값을 지정
			if(!$var_idx) {
				$obj->module_srl = $module_srl;
				$output = executeQuery('document.getDocumentMaxExtraKeyIdx', $obj);
				$var_idx = $output->data->var_idx+1;
			}
			
			if(!$output->toBool()) return $output;
			
		}
    }
?>
