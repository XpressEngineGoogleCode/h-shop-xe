<?php
    /**
     * vi:set ts=4 sw=4 expandtab enc=utf8:
     * @class  Payment System Administrator Model
     * @author Suhan, Moon(lunyx@me.com) 
     * @brief  Payment Administrator Page Model
     **/
    class paymentAdminModel extends payment {
        function init() {
        }
        
        /**
         * @brief  결제모듈 사용가능한 것을 다 불러옴.
         **/
        function getExtraList() {
            $output = XmlParser::loadXmlFile(_XE_PATH_.ModuleHandler::getModulePath("payment")."system/system.xml");
            return $output->systemlist->system;
        }

        function getPaymentList($list_count, $page, $sort_index) {
            if(!in_array($sort_index, array('module_srl'))) $sort_index = 'module_srl';
            $args->sort_index = $sort_index;
            $args->list_count = $list_count;
            $args->page = $page;
            $output = executeQueryArray('payment.getPaymentList', $args);

            if(!$output->toBool()) return $output;

            if(count($output->data)) {
                foreach($output->data as $key => $val) {
                    $oPayment = null;
                    $oPayment = new PaymentInfo();
                    $oPayment->setAttribute($val);
                    $output->data[$key] = null;
                    $output->data[$key] = $oPayment;
                }
            }
            return $output;
        }

        function getSettlementList($list_count, $page, $sort_index) {
            $args->sort_index = $sort_index;
            $args->list_count = $list_count;
            $args->page = $page;
            $output = executeQueryArray('payment.getSettlementList', $args);

            if(!$output->toBool()) return $output;

            if(count($output->data)) {
                foreach($output->data as $key => $val) {
                    $oSettlement = null;
                    $oSettlement = new SettlementInfo();
                    $oSettlement->setAttribute($val);
                    $output->data[$key] = null;
                    $output->data[$key] = $oSettlement;
                }
            }
            return $output;
        }
    }
?>
