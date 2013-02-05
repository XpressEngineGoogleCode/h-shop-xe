<?php
    /**
     * vi:set ts=4 sw=4 expandtab enc=utf8:
     * @class  Payment System Model
     * @author Suhan, Moon(lunyx@me.com) 
     * @brief  Payment Page Model
     **/
    class paymentModel extends payment {
        /**
         * @brief 모듈설정값을 읽어온다.
         * @param[in] module_flag 숫자로 구성된 값이면 module_srl으로 취급, 아니면 모듈이름으로 취급.
         **/
        function getPaymentInfo($module_flag) {
            if (ereg("[0-9]", $module_flag))
                $args->module_srl = $module_flag;
            else
                $args->module_id  = $module_flag;

            $output = executeQuery('payment.getPaymentInfo', $args);
            if (!$output->toBool() || !$output->data)
                return;
            
            return $output->data;
        }
        
        function getSettlementInfo($order_srl) {
            $args->order_srl  = $order_srl;

            $output = executeQuery('payment.getSettlementInfo', $args);
            if(!$output->toBool() || !$output->data) return;
            
            return $output->data;
        }
        
        /**
         * @brief 해당 결제연동 객체를 생성하여 폼HTML 코드를 body 말미에 삽입하고 결제시작 자바스크립트 코드를 head에 삽입한다.
         * @param[in] args
         *  $args->payment_mid  : 결제시스템의 모듈이름
         *  $args->order_srl    : 주문번호
         *  $args->buyer_name   : 주문자 이름
         *  $args->buyer_call   : 주문자 전화번호
         *  $args->buyer_mail   : 주문자 이메일주소
         *  $args->buy_code     : 상품코드
         *  $args->buy_info     : 상품정보(상품명)
         *  $args->buy_amount   : 결제금액
         *  $args->call_type    : 
         *  $args->result_page  : 콜백 URL
         **/
        function getPaymentCaller($args) {
            $oPaymentController = &getController("payment");
            $output = $oPaymentController->insertSettle($args);

            // payment_mid를 이용해서 설정값을 읽어온다.
            $payment_info = $this->getPaymentInfo($args->payment_mid);
            if (!$payment_info)
                return new Object(-1, 'msg_unknown_mid');

            require_once sprintf("%s%s/payment.system.php", _XE_PATH_, ModuleHandler::getModulePath('payment'));
            require_once sprintf("%s%ssystem/%s.system.php", _XE_PATH_, ModuleHandler::getModulePath('payment'), $payment_info->current_extra);
            
            // 결제연동 객체생성
            $oCurrentPayment = new $payment_info->current_extra($payment_info, $args);
            
            // 자바스크립트 파일 head에 포함하기
            $jsFile = $oCurrentPayment->getJsFile();
            if (is_array($jsFile)) {
                foreach($jsFile as $dat) {
					$headerString .= "<script type=\"text/javascript\" src=\"".$dat."\"></script>\n";
                    //Context::addJsFile($dat, false);
                }
            } else {
				$headerString = "<script type=\"text/javascript\" src=\"".$jsFile."\"></script>\n";
                Context::addJsFile($jsFile, false);
            }

            // 자바스크립트 결제시작함수
            $headerString .= "<script type=\"text/javascript\">
                function startPayment() { %s }
                %s
            </script>";

            $output = $oCurrentPayment->getCallerForm();
            if (!$output->toBool()) return $output;
            $formHeader = "<form method=\"post\" id=\"%s\" name=\"%s\" action=\"%s\">\n";
            $formInput  = "<input type=\"hidden\" id=\"%s\" name=\"%s\" value=\"%s\">\n";

            // 자바스크립트 삽입
            Context::addHtmlHeader(sprintf($headerString, $oCurrentPayment->getCallScript(), $oCurrentPayment->getPageLoadedScript()));
            
            // form 태그 시작
            Context::addHtmlFooter(sprintf($formHeader, $output->get("formName"), $output->get("formName"), $output->get("formAction")));

            // form 데이터
            foreach ($output->get("formContents") as $key => $val) {
                Context::addHtmlFooter(sprintf($formInput, $val["id"], $val["name"], $val["value"]));
            }

            // form 태그 끝
            Context::addHtmlFooter("</form>");

            return new Object();
        }

        function getPaymentCencelCaller($args) {
            $oPaymentController = &getController("payment");

            // payment_mid를 이용해서 설정값을 읽어온다.
            $payment_info = $this->getPaymentInfo($args->payment_mid);
            if (!$payment_info)
                return new Object(-1, 'msg_unknown_mid');

            require_once sprintf("%s%s/payment.system.php", _XE_PATH_, ModuleHandler::getModulePath('payment'));
            require_once sprintf("%s%ssystem/%s.system.php", _XE_PATH_, ModuleHandler::getModulePath('payment'), $payment_info->current_extra);
            
            // 결제연동 객체생성
            $oCurrentPayment = new $payment_info->current_extra($payment_info, $args);
            

            $output = $oCurrentPayment->setCancel($args->tid, $args->msg);
            return $output;
        }

	}
?>
