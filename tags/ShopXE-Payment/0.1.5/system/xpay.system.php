<?php

/**
 * @class  Payment System For LG Dacom XPay Lite
 * @author Suhan, Moon(lunyx@me.com) 
 * @brief  xpay Control
 **/

class xpay extends PaymentSystem
{
	
	function xpay($payment_info, $args) { return parent::PaymentSystem($payment_info, $args); }

	function getCallerForm()
	{
		$output = new Object();

		$CST_MID              = $this->payment_info->extra_1;
		$LGD_MID              = (("test" == $this->platform)?"t":"").$CST_MID;
		$LGD_MERTKEY          = $this->payment_info->extra_2;
		$LGD_OID              = $this->args->order_srl;
		$LGD_BUYER            = $this->args->buyer_name;
		$LGD_BUYEREMAIL       = $this->args->buyer_mail;
		$LGD_PRODUCTINFO      = $this->args->buy_info;
		$LGD_AMOUNT           = $this->args->buy_amount;
		$LGD_CUSTOM_USABLEPAY = $this->getTypeString($this->args->call_type);
		$LGD_NOTEURL          = $this->callbackUrl;
	
		$LGD_TIMESTAMP        = date("YmdHis");
		$LGD_HASHDATA         = md5($LGD_MID.$LGD_OID.$LGD_AMOUNT.$LGD_TIMESTAMP.$LGD_MERTKEY);
		
		$formHidden   = Array();
		$formHidden[] = Array("name"=>"LGD_MID",                "id"=>"LGD_MID",                "value"=>$LGD_MID);
		$formHidden[] = Array("name"=>"LGD_OID",                "id"=>"LGD_OID",                "value"=>$LGD_OID);
		$formHidden[] = Array("name"=>"LGD_BUYER",              "id"=>"LGD_BUYER",              "value"=>$LGD_BUYER);
		$formHidden[] = Array("name"=>"LGD_CUSTOM_USABLEPAY",   "id"=>"LGD_CUSTOM_USABLEPAY",   "value"=>$LGD_CUSTOM_USABLEPAY);
		$formHidden[] = Array("name"=>"LGD_PRODUCTINFO",        "id"=>"LGD_PRODUCTINFO",        "value"=>$LGD_PRODUCTINFO);
		$formHidden[] = Array("name"=>"LGD_AMOUNT",             "id"=>"LGD_AMOUNT",             "value"=>$LGD_AMOUNT);
		$formHidden[] = Array("name"=>"LGD_BUYEREMAIL",         "id"=>"LGD_BUYEREMAIL",         "value"=>$LGD_BUYEREMAIL);
		$formHidden[] = Array("name"=>"LGD_TIMESTAMP",          "id"=>"LGD_TIMESTAMP",          "value"=>$LGD_TIMESTAMP);
		$formHidden[] = Array("name"=>"LGD_HASHDATA",           "id"=>"LGD_HASHDATA",           "value"=>$LGD_HASHDATA);
		$formHidden[] = Array("name"=>"LGD_NOTEURL",            "id"=>"LGD_NOTEURL",            "value"=>$LGD_NOTEURL);
		$formHidden[] = Array("name"=>"LGD_CASNOTEURL",         "id"=>"LGD_CASNOTEURL",         "value"=>$LGD_NOTEURL);
		$formHidden[] = Array("name"=>"LGD_VERSION",            "id"=>"LGD_VERSION",            "value"=>"PHP_XPay_lite_1.0");
		
		$formHidden[] = Array("name"=>"payment_result_code",    "id"=>"payment_result_code",    "value"=>"");
		$formHidden[] = Array("name"=>"payment_result_msg",     "id"=>"payment_result_msg",     "value"=>"");
		$formHidden[] = Array("name"=>"payment_result_srl",     "id"=>"payment_result_srl",     "value"=>"");
		$formHidden[] = Array("name"=>"payment_order_srl",      "id"=>"payment_order_srl",      "value"=>"");
		$formHidden[] = Array("name"=>"payment_finance_code",   "id"=>"payment_finance_code",   "value"=>"");
		$formHidden[] = Array("name"=>"payment_finance_name",   "id"=>"payment_finance_name",   "value"=>"");
		$formHidden[] = Array("name"=>"payment_finance_authno", "id"=>"payment_finance_authno", "value"=>"");
		$formHidden[] = Array("name"=>"payment_accountno",      "id"=>"payment_accountno",      "value"=>"");
		

		$output->add("formName", "LGD_PAYINFO");
		$output->add("formContents", $formHidden);
		$output->add("formAction", $this->args->result_page);

		return $output;
	}

	function getJsFile()
	{

		$jsAdderFile = "http";
		if($_SERVER["HTTPS"] == "on") $jsAdderFile .= "s";

		if($this->platform == "test")
		{
			if($_SERVER["HTTPS"] == "on") $jsAdderFile .= "://xpay.lgdacom.net:7433";
			else $jsAdderFile .= "://xpay.lgdacom.net:7080";
		}
		else
			$jsAdderFile .= ":///xpay.lgdacom.net";

		$jsAdderFile .= "/xpay/js/xpay_utf-8.js";

		return $jsAdderFile;

	}

	function getCallScript()
	{

		$formScript = "
			var ret = null;
			ret = xpay_check(jQuery('#LGD_PAYINFO').get(0), '".$this->platform."');

			if (ret=='00'){     //ActiveX 로딩 성공  

				jQuery('#payment_result_code'   ).val( dpop.getData('LGD_RESPCODE') );        //결과코드
				jQuery('#payment_result_msg'    ).val( dpop.getData('LGD_RESPMSG') );         //결과메세지 
				jQuery('#payment_result_srl'    ).val( dpop.getData('LGD_TID') );             //LG데이콤 거래번호
				jQuery('#payment_order_srl'     ).val( dpop.getData('LGD_OID') );             //주문번호 
				jQuery('#payment_finance_name'  ).val( dpop.getData('LGD_FINANCENAME') );     //결제기관이름
				jQuery('#payment_finance_authno').val( dpop.getData('LGD_FINANCEAUTHNUM') );  //결제사승인번호
				jQuery('#payment_accountno'     ).val( dpop.getData('LGD_ACCOUNTNUM') );      //입금할 계좌 (가상계좌)

				jQuery('#LGD_PAYINFO'           ).submit();

			} else {
				alert('LG데이콤 전자결제를 위한 ActiveX 설치 실패');
			}
		";

		return $formScript;

	}

	function getPageLoadedScript() { return null; }

	function setCallback()
	{
		$output                = new Object();

		$LGD_BUYER             = Context::get("LGD_BUYER");             // 구매자
		$LGD_PRODUCTINFO       = Context::get("LGD_PRODUCTINFO");       // 상품명
		$LGD_BUYERID           = Context::get("LGD_BUYERID");           // 구매자 ID
		$LGD_BUYERADDRESS      = Context::get("LGD_BUYERADDRESS");      // 구매자 주소
		$LGD_BUYERPHONE        = Context::get("LGD_BUYERPHONE");        // 구매자 전화번호
		$LGD_BUYEREMAIL        = Context::get("LGD_BUYEREMAIL");        // 구매자 이메일
		$LGD_BUYERSSN          = Context::get("LGD_BUYERSSN");          // 구매자 주민번호
		$LGD_PRODUCTCODE       = Context::get("LGD_PRODUCTCODE");       // 상품코드
		$LGD_RECEIVER          = Context::get("LGD_RECEIVER");          // 수취인
		$LGD_RECEIVERPHONE     = Context::get("LGD_RECEIVERPHONE");     // 수취인 전화번호
		$LGD_DELIVERYINFO      = Context::get("LGD_DELIVERYINFO");      // 배송지

		$LGD_RESPCODE          = Context::get("LGD_RESPCODE");          // 응답코드: 0000(성공) 그외 실패
		$LGD_RESPMSG           = Context::get("LGD_RESPMSG");           // 응답메세지
		$LGD_HASHDATA          = Context::get("LGD_HASHDATA");          // 해쉬값
		$LGD_TID               = Context::get("LGD_TID");               // 데이콤이 부여한 거래번호
		$LGD_MID               = Context::get("LGD_MID");               // 상점아이디 
		$LGD_OID               = Context::get("LGD_OID");               // 주문번호
		$LGD_AMOUNT            = Context::get("LGD_AMOUNT");            // 거래금액
		$LGD_PAYTYPE           = Context::get("LGD_PAYTYPE");           // 결제수단코드
		$LGD_PAYDATE           = Context::get("LGD_PAYDATE");           // 거래일시(승인일시/이체일시)

		$LGD_FINANCECODE       = Context::get("LGD_FINANCECODE");       // 결제기관코드(카드종류/은행코드)
		$LGD_FINANCENAME       = Context::get("LGD_FINANCENAME");       // 결제기관이름(카드이름/은행이름)

		$LGD_FINANCEAUTHNUM    = Context::get("LGD_FINANCEAUTHNUM");    // 승인번호(신용카드)
		$LGD_CARDNUM           = Context::get("LGD_CARDNUM");           // 카드번호(신용카드)
		$LGD_CARDINSTALLMONTH  = Context::get("LGD_CARDINSTALLMONTH");  // 할부개월수(신용카드)
		$LGD_CARDNOINTYN       = Context::get("LGD_CARDNOINTYN");       // 무이자할부여부(신용카드) - '1'이면 무이자할부 '0'이면 일반할부   
		$LGD_TRANSAMOUNT       = Context::get("LGD_TRANSAMOUNT");       // 환율적용금액(신용카드)
		$LGD_EXCHANGERATE      = Context::get("LGD_EXCHANGERATE");      // 환율(신용카드)

		$LGD_ACCOUNTNUM        = Context::get("LGD_ACCOUNTNUM");        // 계좌번호(계좌이체, 무통장입금)

		$LGD_PAYTELNUM         = Context::get("LGD_PAYTELNUM");         // 휴대폰번호(휴대폰)

		$LGD_CASFLAG           = Context::get("LGD_CASFLAG");           // 무통장입금 플래그(무통장입금) - 'R':계좌할당, 'I':입금, 'C':입금취소
		$LGD_CASTAMOUNT        = Context::get("LGD_CASTAMOUNT");        // 입금총액(무통장입금)
		$LGD_CASCAMOUNT        = Context::get("LGD_CASCAMOUNT");        // 현입금액(무통장입금)
		$LGD_CASSEQNO          = Context::get("LGD_CASSEQNO");          // 입금순서(무통장입금)
		$LGD_CASHRECEIPTNUM    = Context::get("LGD_CASHRECEIPTNUM");    // 현금영수증 승인번호
		$LGD_CASHRECEIPTKIND   = Context::get("LGD_CASHRECEIPTKIND");   // 현금영수증종류 (0: 소득공제용 , 1: 지출증빙용)
		$LGD_CASHRECEIPTSELFYN = Context::get("LGD_CASHRECEIPTSELFYN"); // 현금영수증자진발급제유무 Y: 자진발급제 적용, 그외 : 미적용

		$LGD_ESCROWYN          = Context::get("LGD_ESCROWYN");          // 에스크로 사용여부

		$LGD_TIMESTAMP         = Context::get("LGD_TIMESTAMP");         // 타임스탬프

		$LGD_OCBSAVEPOINT      = Context::get("LGD_OCBSAVEPOINT");      // OK캐쉬백 적립포인트
		$LGD_OCBTOTALPOINT     = Context::get("LGD_OCBTOTALPOINT");     // OK캐쉬백 누적포인트
		$LGD_OCBUSABLEPOINT    = Context::get("LGD_OCBUSABLEPOINT");    // OK캐쉬백 사용가능 포인트

		$oPaymentModel         = &getModel("payment");
		$oPaymentController    = &getController("payment");
		$LGD_MERTKEY           = $this->payment_info->extra_2;
		$LGD_HASHDATA2         = md5($LGD_MID.$LGD_OID.$LGD_AMOUNT.$LGD_RESPCODE.$LGD_TIMESTAMP.$LGD_MERTKEY); 
		
		$output->setMessage("OK");

		if ($LGD_HASHDATA2 != $LGD_HASHDATA)
		{
			$output->setError(-1);
			$output->setMessage("결제결과 상점 DB처리(NOTE_URL) 해쉬값 검증이 실패하였습니다.");
		}
		if($LGD_RESPCODE != "0000")
		{
			$output->setError(-1);
			$output->setMessage(sprintf("%s:%s", $LGD_RESPCODE, $LGD_RESPMSG));

			$args->order_srl           = $LGD_OID;
			$args->result_code         = $LGD_RESPCODE;
			$args->result_message      = iconv("EUC-KR", "UTF-8", $LGD_RESPMSG);
			$args->current_state       = "1";
			$oPaymentController->updateSettle($args);
		}
		else
		{
			$args->order_srl           = $LGD_OID;
			$args->result_code         = $LGD_RESPCODE;
			$args->result_message      = iconv("EUC-KR", "UTF-8", $LGD_RESPMSG);
			$args->system_srl          = $LGD_TID;
			$args->finance_name        = iconv("EUC-KR", "UTF-8", $LGD_FINANCENAME);
			$args->finance_description = (!$LGD_CARDNUM) ? $LGD_ACCOUNTNUM : $LGD_CARDNUM;
			$args->finance_message     = (!$LGD_FINANCEAUTHNUM) ? $LGD_ACCOUNTNUM : $LGD_FINANCEAUTHNUM;
			$args->current_state       = ($LGD_PAYTYPE == $this->getTypeString("CD") || $LGD_PAYTYPE == $this->getTypeString("AB"))?"3":"2";
			
			if($LGD_CASFLAG == "I") 
				$args->current_state     = "3";
			else if($LGD_CASFLAG == "C") 
				$args->current_state     = "1";
			else if($LGD_CASFLAG == "R") 
				$args->current_state     = "2";

			$oPaymentController->updateSettle($args);

			// 2009.11.10 [문수한] $output에 args값 얹혀보냄
			$output->adds($args);
		}
		

		Context::set("payment_callback_message", $output->message);

		return $output;
	}


	function getTypeString($code)
	{
		switch($code)
		{
			case "CD" : return "SC0010"; break;
			case "AB" : return "SC0030"; break;
			case "BK" : return "SC0040"; break;
			case "HP" : return "SC0060"; break;
			case "7A" : return "SC0070"; break;
			case "OC" : return "SC0090"; break;
			case "CM" : return "SC0111"; break;
			case "GM" : return "SC0112"; break;
			default   : return ""; break;
		}
	}

	function getBankString($code)
	{
		switch($code)
		{
			case "03" : return "기업"; break;
			case "05" : return "외환"; break;
			case "06" : return "국민"; break;
			case "11" : return "농협"; break;
			case "34" : return "광주"; break;
			case "37" : return "전북"; break;
			case "39" : return "경남"; break;
			case "71" : return "우체국"; break;
			case "20" : return "우리"; break;
			case "88" : return "신한"; break;
			case "23" : return "제일"; break;
			case "27" : return "씨티"; break;
			case "31" : return "대구"; break;
			case "32" : return "부산"; break;
			case "81" : return "하나"; break;
			case "48" : return "신협"; break;
			case "45" : return "새마을금고"; break;
			case "35" : return "제주"; break;
			case "07" : return "수협"; break;
			case "02" : return "산업"; break;
			default   : return ""; break;
		}
	}

	function getCardString($code)
	{
		switch($code)
		{
			case "11" : return "국민"; break;
			case "21" : return "외환"; break;
			case "29" : return "산은캐피탈"; break;
			case "31" : return "비씨"; break;
			case "32" : return "하나"; break;
			case "33" : return "우리(구.평화VISA)"; break;
			case "34" : return "수협"; break;
			case "35" : return "전북"; break;
			case "36" : return "씨티"; break;
			case "41" : return "신한(구.LG카드 포함)"; break;
			case "42" : return "제주"; break;
			case "46" : return "광주"; break;
			case "51" : return "삼성"; break;
			case "61" : return "현대"; break;
			case "71" : return "롯데"; break;
			case "4J" : return "해외JCB"; break;
			case "4V" : return "해외VISA"; break;
			case "4M" : return "해외MASTER"; break;
			case "6D" : return "해외DINERS"; break;
			default   : return ""; break;
		}
	}

}
?>