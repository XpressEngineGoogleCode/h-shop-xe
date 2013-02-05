<?php
    /**
     * vi:set ts=4 sw=4 expandtab enc=utf8:
     * @class  Payment System Parent Class
     * @author Suhan, Moon(lunyx@me.com) 
     * @brief  extend only
     **/
    class PaymentSystem {
        var $payment_info;
        var $platform;
        var $callbackUrl;
        var $args;
        
        function PaymentSystem($payment_info, $args) {
            $this->args         = $args;
            $this->payment_info = $payment_info;
            $this->platform     = ($payment_info->is_test == "Y") ? "test" : "service";
            $this->callbackUrl  = sprintf("%s?mid=%s&act=dispPaymentCallback", Context::getDefaultUrl(), $payment_info->mid);
        }

        function getJsFile() { }
        function getCallerForm() { }
        function getCallScript() { }
        function getPageLoadedScript() { }

        function setCallback() { }

        function getTypeString() { }
        function getBankString() { }
        function getCardString() { }

        function setCancel() { }
    }
?>
