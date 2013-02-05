<?php
    /**
     * vi:set ts=4 sw=4 expandtab enc=utf8:
     * @class  Payment System View
     * @author Suhan, Moon(lunyx@me.com) 
     * @brief  Payment Page View
     **/
    class paymentView extends payment {
        function init() {
            $template_path = sprintf("%stpl/",$this->module_path);
            $this->setTemplatePath($template_path);
        }

        /**
         * @brief 주문번호 생성
         **/
        function makeOrderKey() {
            $randval = rand(100000, 999999);
            $usec = explode(" ", microtime());
            $str_usec = str_replace(".", "", strval($usec[0]));
            $str_usec = substr($str_usec, 0, 6);
            return date("YmdHis") . $str_usec . $randval;
        }


        /**
         * @brief  결제모듈 테스트용 페이지 출력
         **/
        function dispPaymentTestMode() {
            $payment_param = Context::get('order_srl');
            Context::set("payment_param", $payment_param);
            Context::set("qstr", $_SERVER['QUERY_STRING']);
            if($payment_param) {
                $args->mid = Context::get('mid');
                $args->order_srl = Context::get('order_srl');
                $args->buyer_name = Context::get('buyer_name');
                $args->buyer_call = Context::get('buyer_call');
                $args->buyer_mail = Context::get('buyer_mail');
                $args->buy_code = Context::get('buy_code');
                $args->buy_info = Context::get('buy_info');
                $args->buy_amount = Context::get('buy_amount');
                $args->call_type = Context::get('call_type');
                $args->result_page = Context::get('result_page');

                $oModel = &getModel('payment');
                $output = $oModel->getPaymentCaller($args);
                if (!$output->toBool())
                    return $output;
                Context::set("args", $args);
            }
            Context::set("tmp_order_srl", $this->makeOrderKey());
            $this->setTemplateFile('dispTestMode');
        }

        /**
         * @brief  결제모듈 테스트용 페이지 결과
         **/
        function dispPaymentTestModeResult() {
            $this->setTemplateFile('dispTestModeResult');
        }

        /**
         * @brief  결제모듈 리턴용 페이지
         **/
        function dispPaymentCallback() {
            $module_srl    = Context::get('mid');
            $oPaymentModel = &getModel('payment');
            $payment_info  = $oPaymentModel->getPaymentInfo($module_srl);
            
            require_once sprintf("%s/payment.system.php",  $this->mePath);
            require_once sprintf("%ssystem/%s.system.php", $this->mePath, $payment_info->current_extra);
            $oPaySystem = new $payment_info->current_extra($payment_info, null);
            $output = $oPaySystem->setCallback();

            if (!Context::get('justclose'))
            {
			    //2009.11.10 처리된걸 트리거요청
			    $trigger_output = ModuleHandler::triggerCall('payment.doPaymentComplete', 'after', $output);
            }
            
            if (substr($payment_info->current_extra, 0, 7) == 'teledit') {
                if ($output)
                {
                    Context::set("formAction",   $output->get('formAction'));
                    Context::set("formName",     $output->get('formName'));
                    Context::set("formContents", $output->get('formContents'));
                }
                $this->setTemplateFile('teledit_callback');
            } else {
                Context::setResponseMethod("TEXT");
                header("Content-Type: text/plain");
                $this->setTemplateFile('dispPaymentCallback');
            }
        }
        
        /**
         * @brief  결제모듈 처리용페이지
         **/
        function dispPaymentProcess() {
            $module_srl    = Context::get('payment_mid');//$_GET["mid"];
            $oPaymentModel = &getModel('payment');
            $payment_info  = $oPaymentModel->getPaymentInfo($module_srl);

            require_once sprintf("%s/payment.system.php",  $this->mePath);
            require_once sprintf("%ssystem/%s.system.php", $this->mePath, $payment_info->current_extra);
            $oPaySystem = new $payment_info->current_extra($payment_info, null);
            $output = $oPaySystem->setProcess($module_srl);
            if (!$output->toBool()) return $output;

			//2009.11.10 처리된걸 트리거요청( $output에 args를 adds시켜두어야할것)
			$trigger_output = ModuleHandler::triggerCall('payment.doPaymentComplete', 'after', $output);

            Context::set("formAction",   $output->get('formAction'));
            Context::set("formName",     $output->get('formName'));
            Context::set("formContents", $output->get('formContents'));
                        
            $this->setTemplateFile('dispPaymentProcess');
        }
    }
?>
