<?php
    /**
     * vi:set ts=4 sw=4 expandtab enc=utf8:
     * @class  Payment System Controller
     * @author Suhan, Moon(lunyx@me.com) 
     * @brief  Payment Page Controller
     **/
    class paymentController extends payment {
        function init() {
        }

        function insertSettle( $args = null ) {
            $args->settlement_srl = getNextSequence();
            $args->pay_date       = date("YmdHis");
            $args->mid = Context::get('mid');
            $output = executeQuery('payment.insertSettlement', $args);
            
            return $output;
        }

        function updateSettle($req = null) {

			if( $req == null && Context::getRequestVars() )
			{
				$req = Context::getRequestVars();
			}

			$oModuleModel              = &getModel("payment");
            $args                      = $oModuleModel->getSettlementInfo($req->order_srl);

			if( is_array($args) )
			{
				$args = $args[0];
			}
			if($req->result_code)         $args->result_code         = $req->result_code;
            if($req->result_message)      $args->result_message      = $req->result_message;
            if($req->system_srl)          $args->system_srl          = $req->system_srl;
            if($req->finance_name)        $args->finance_name        = $req->finance_name;
            if($req->finance_description) $args->finance_description = $req->finance_description;
            if($req->finance_message)     $args->finance_message     = $req->finance_message;
            if($req->current_state)       $args->current_state       = $req->current_state;
            if($req->call_type)       $args->call_type       = $req->call_type;

            $args->pay_date            = date("YmdHis");

			$output       = executeQuery('payment.updateSettlementInfo', $args);

            return $output;
        }

		function deleteSettle( $order_srl ) {
            $args->order_srl = $order_srl;
            $output = executeQuery('payment.deleteSettlementInfo', $args);
            
            return $output;
        }
    }
?>
