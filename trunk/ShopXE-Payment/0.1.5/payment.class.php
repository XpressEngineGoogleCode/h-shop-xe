<?php
    /**
     * vi:set ts=4 sw=4 expandtab enc=utf8:
     * @class  Payment System
     * @author 제작 Suhan, Moon (lunyx@me.com) 수정 -덧니희야(deive@nate.com), 최대성(diver@coolsms.co.kr)
     * @brief  Payment Main System
     **/
    require_once(sprintf('%smodules/payment/payment.info.php', _XE_PATH_));
    require_once(sprintf('%smodules/payment/settlement.info.php', _XE_PATH_));

    class payment extends ModuleObject {
        var $install = null;

        var $PAYMENT_USABLE_CARD      = "CD";  // 신용카드
        var $PAYMENT_USABLE_ACCOUNT   = "AB";  // 계좌이체
        var $PAYMENT_USABLE_BANK      = "BK";  // 가상계좌
        var $PAYMENT_USABLE_CELLULAR  = "HP";  // 휴대폰
        var $PAYMENT_USABLE_700ARS    = "7A";  // 700 ARS
        var $PAYMENT_USABLE_OKCASHBAG = "OC";  // OK Cashbag
        var $PAYMENT_USABLE_CULTURE   = "CM";  // 문화상품권
        var $PAYMENT_USABLE_GAME      = "GM";  // 게임문화상품권

        var $mePath = "";

        function payment() {    
            $this->install = true;
            $this->mePath  = _XE_PATH_.ModuleHandler::getModulePath('payment');
        }

        function moduleInstall() {
            $oModuleController = &getController('module');    

            return new Object();
        }

        function checkUpdate() {
            $oModuleModel = &getModel('module');

            $oDB = &DB::getInstance();

            // 2009. 07. 29 payment_settlement에 pay_date추가
            if(!$oDB->isColumnExists("payment_settlement","pay_date")) return true;

            // 2010.04.13 : 결제처리시 추가변수 입력항목 추가
            if(!$oDB->isColumnExists("payment_settlement","extra_vars")) return true;

			// 2010.04.13 : 결제처리시 결제된 결제시스템의 mid를 추가
            if(!$oDB->isColumnExists("payment_settlement","payment_id")) return true;

			return false;
        }

        function moduleUpdate() {
            $oDB = &DB::getInstance();	
            /**
            * 2009.07.09 : 결제 처리일자 추가 
            **/
            if(!$oDB->isColumnExists("payment_settlement","pay_date")) {
                $oDB->addColumn('payment_settlement',"pay_date","date");
            }

            /**
            * 2010.04.13 : 결제처리시 추가변수 입력항목 추가
            **/
            if(!$oDB->isColumnExists("payment_settlement","extra_vars")) {
                $oDB->addColumn('payment_settlement',"extra_vars","text");
            }

            /**
            * 2010.04.13 : 결제처리시 결제된 결제시스템의 mid를 추가
            **/
            if(!$oDB->isColumnExists("payment_settlement","payment_id")) {
                $oDB->addColumn('payment_settlement',"payment_id","varchar",255);
            }

			return new Object(0, 'success_updated');
        }

        function recompileCache() {
        }
    }
?>
