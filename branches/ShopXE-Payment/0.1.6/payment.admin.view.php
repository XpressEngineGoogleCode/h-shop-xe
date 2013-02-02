<?php
    /**
     * vi:set ts=4 sw=4 expandtab enc=utf8:
     * @class  Payment System Admin View
     * @author Suhan, Moon(lunyx@me.com) 
     * @brief  Payment Administrater Page View
     **/
    class paymentAdminView extends payment {
        function paymentAdminView() { 
        }

        function init() {
            $template_path = sprintf("%stpl/",$this->module_path);
            $this->setTemplatePath($template_path);	
        }

        /**
         * @brief 결제모듈 목록
         **/
        function dispPaymentAdminPaymentList() {
            $page = (Context::get("page"))? Context::get("page") : "1";
            $oPaymentModel = &getAdminModel('payment');
            $output = $oPaymentModel->getPaymentList(20, $page, 'regdate');
            Context::set('row_list', $output->data);
            Context::set('page_navigation', $output->page_navigation);

            $this->setTemplateFile('dispPaymentList');
        }
        
        /**
         * @brief 결제모듈 추가
         **/
        function dispPaymentAdminPaymentManage() {
            $module_srl = Context::get('module_srl');
            $oPaymentModel = &getModel('payment');
            $oPaymentAdminModel = &getAdminModel('payment');
            if($module_srl) {
                $payment_info = $oPaymentModel->getPaymentInfo($module_srl);
            
                $useType = null;
                $tmpData = explode(";", $payment_info->use_type);
                foreach($tmpData as $data) {
                    if($data != "") {
                        $paramName = sprintf("V%s", substr($data, 1, 2));
                        $useType->$paramName = true;
                    }
                }

                Context::set('payment_info',	$payment_info);
                Context::set('useType',         $useType);

            }
            Context::set('SystemList',		$oPaymentAdminModel->getExtraList());

            $this->setTemplateFile('dispPaymentManage');
        }
        
        /**
        *@brief 결제시스템 삭제
        **/
        function dispPaymentAdminPaymentRemove() {
            $module_srl = Context::get('module_srl');
            $oPaymentModel = &getModel('payment');
            $oPaymentAdminModel = &getAdminModel('payment');
            $payment_info = $oPaymentModel->getPaymentInfo($module_srl);
            Context::set('payment_info', $payment_info);
            $this->setTemplateFile('dispPaymentRemove');
        }

        /**
        *@brief 결제내역 목록
        **/
        function dispPaymentAdminSettleList() {
            $page = (Context::get("page"))? Context::get("page") : "1";
            $oPaymentModel = &getAdminModel('payment');
            $output = $oPaymentModel->getSettlementList(20, $page, 'pay_date');

            Context::set('row_list', $output->data);
            Context::set('page_navigation', $output->page_navigation);

            $this->setTemplateFile('dispSettlementList');
        }

        /**
        *@brief 결제내역 조회
        **/
        function dispPaymentAdminSettleInfo() {
            $oPaymentModel = &getModel('payment');
            $output = $oPaymentModel->getSettlementInfo(Context::get("order_srl"));
            $oSettleInfo = new SettlementInfo();
            $oSettleInfo->setAttribute($output);
            Context::set('settle_info', $oSettleInfo);

            $this->setTemplateFile('dispSettlementInfo');
        }
    }
?>
