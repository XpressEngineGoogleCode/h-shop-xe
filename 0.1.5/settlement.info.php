<?php
    /**
     * vi:set ts=4 sw=4 expandtab enc=utf8:
     **/
    class SettlementInfo extends Object {
        var $oContext;

        function SettlementInfo() {
            $this->oContext = Context::getInstance();
        }

        function setAttribute($attribute) { 
            $this->adds($attribute); 
        }

        function getType() {
            $variableName = sprintf("payment_type_%s", $this->get('call_type'));
            return $this->oContext->lang->$variableName;
        }

        function getState() {
            $curr = $this->get('current_state');
            if(!$curr) $curr = "0";
            $variableName = sprintf("payment_currState_%s", $curr);
            return $this->oContext->lang->$variableName;
        }
    }
?>
