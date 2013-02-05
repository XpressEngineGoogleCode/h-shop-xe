<?php

/**
 * @class  Payment System For Inipay 5
 * @author Suhee Lee
 * @brief  Inipay 5 Caller
 **/

class inipay5 extends PaymentSystem
{
	
	function inipay5($payment_info, $args) { return parent::PaymentSystem($payment_info, $args); }

	function getCallerForm()
	{
		$IN4_MID         = ($this->platform == "test") ? "INIpayTest" : $this->payment_info->extra_1;
		$IN4_ORDER_SRL   = $this->args->order_srl;
		$IN4_BUYERNAME   = $this->args->buyer_name;
		$IN4_BUYERCALL   = $this->args->buyer_call;
		$IN4_BUYERMAIL   = $this->args->buyer_mail;
		$IN4_BUYERADDR   = $this->args->buyer_addr;
		$IN4_USABLEPAY   = $this->getTypeString($this->args->call_type);
		$IN4_PAYAMOUNT   = $this->args->buy_amount;
		$IN4_RESULTPAGE  = $this->args->result_page;
		$IN4_PRODUCTINFO = $this->args->buy_info;
		$IN4_AMOUNT      = $this->args->buy_amount;
		
		$output = new Object();
		$formHidden = Array();

		$formHidden[] = Array("name"=>"gopaymethod",   "id"=>"gopaymethod",   "value"=>$IN4_USABLEPAY);
		$formHidden[] = Array("name"=>"goodname",      "id"=>"goodname",      "value"=>$IN4_PRODUCTINFO);
		$formHidden[] = Array("name"=>"price",         "id"=>"price",         "value"=>$IN4_AMOUNT);
		$formHidden[] = Array("name"=>"buyername",     "id"=>"buyername",     "value"=>$IN4_BUYERNAME);
		$formHidden[] = Array("name"=>"buyeremail",    "id"=>"buyeremail",    "value"=>$IN4_BUYERMAIL);
		$formHidden[] = Array("name"=>"parentemail",   "id"=>"parentemail",   "value"=>$IN4_BUYERMAIL);

		$formHidden[] = Array("name"=>"mid",           "id"=>"mid",           "value"=>$IN4_MID);
		$formHidden[] = Array("name"=>"currency",      "id"=>"currency",      "value"=>"WON");
		$formHidden[] = Array("name"=>"nointerest",    "id"=>"nointerest",    "value"=>"no");
		$formHidden[] = Array("name"=>"quotabase",     "id"=>"quotabase",     "value"=>"선택:일시불:3개월:4개월:5개월:6개월:7개월:8개월:9개월:10개월:11개월:12개월");
		$formHidden[] = Array("name"=>"acceptmethod",  "id"=>"acceptmethod",  "value"=>"SKIN(ORIGINAL):HPP(1):OCB");
		$formHidden[] = Array("name"=>"oid",           "id"=>"oid",           "value"=>$this->args->order_srl);

		$formHidden[] = Array("name"=>"quotainterest", "id"=>"quotainterest", "value"=>"");
		$formHidden[] = Array("name"=>"paymethod",     "id"=>"paymethod",     "value"=>"");
		$formHidden[] = Array("name"=>"cardcode",      "id"=>"cardcode",      "value"=>"");
		$formHidden[] = Array("name"=>"cardquota",     "id"=>"cardquota",     "value"=>"");
		$formHidden[] = Array("name"=>"rbankcode",     "id"=>"rbankcode",     "value"=>"");
		$formHidden[] = Array("name"=>"reqsign",       "id"=>"reqsign",       "value"=>"DONE");
		$formHidden[] = Array("name"=>"encrypted",     "id"=>"encrypted",     "value"=>"");
		$formHidden[] = Array("name"=>"sessionkey",    "id"=>"sessionkey",    "value"=>"");
		$formHidden[] = Array("name"=>"uid",           "id"=>"uid",           "value"=>"");
		$formHidden[] = Array("name"=>"sid",           "id"=>"sid",           "value"=>"");
		$formHidden[] = Array("name"=>"version",       "id"=>"version",       "value"=>"4000");
		$formHidden[] = Array("name"=>"clickcontrol",  "id"=>"clickcontrol",  "value"=>"");
		
		$formHidden[] = Array("name"=>"buyertel",      "id"=>"buyertel",      "value"=>$IN4_BUYERCALL);
		$formHidden[] = Array("name"=>"result_link",   "id"=>"result_link",   "value"=>$this->args->result_page);
		
		$output->add("formName", "IN4_PAYINFO");
		$output->add("formContents", $formHidden);
		$output->add("formAction", sprintf("./?mid=%s&act=dispPaymentProcess&payment_mid=%s", Context::get("mid"), $this->args->payment_mid));
		
		return $output;
	}

	function getJsFile()
	{
		return "http://plugin.inicis.com/pay40.js";
	}

	function getCallScript()
	{
		return "
			if(MakePayMessage(jQuery('#IN4_PAYINFO').get(0)))
			{
				jQuery('#mid').val(current_mid);
				jQuery('#IN4_PAYINFO').submit();
			}
			";
	}

	function getPageLoadedScript() { return "StartSmartUpdate();"; }

	function setCallback()
	{

		return ;
	}

	
	function setProcess( $mid = null )
	{

		$output           = new Object();

		$IN4_MID         = ($this->platform == "test") ? "INIpayTest" : $this->payment_info->extra_1;
		$IN4_MPW         = ($this->platform == "test") ? "1111" : $this->payment_info->extra_2;

		require "inipay4_lib/INIpay41Lib.php";

		$inipay = new INIpay41;

		$inipay->m_inipayHome = str_replace("inipay5.system.php", "", __FILE__)."inipay5"; 
		$inipay->m_type = "securepay"; 				// 고정 (절대 수정 불가)
		$inipay->m_pgId = "INIpay".$pgid; 			// 고정 (절대 수정 불가)
		$inipay->m_subPgIp = "203.238.3.10"; 			// 고정 (절대 수정 불가)
		$inipay->m_keyPw = $IN4_MPW; 				// 키패스워드(상점아이디에 따라 변경)
		$inipay->m_debug = "true"; 				// 로그모드("true"로 설정하면 상세로그가 생성됨.)
		$inipay->m_mid = $IN4_MID;	// 상점아이디
		$inipay->m_uid = Context::get("uid");	// INIpay User ID (절대 수정 불가)
		$inipay->m_goodName = Context::get("goodname");			// 상품명 
		$inipay->m_currency = Context::get("currency");			// 화폐단위

		$inipay->m_price = Context::get("price");				// 결제금액

		$inipay->m_buyerName = Context::get("buyername");			// 구매자 명
		$inipay->m_buyerTel = Context::get("buyertel");			// 구매자 연락처(휴대폰 번호 또는 유선전화번호)
		$inipay->m_buyerEmail = Context::get("buyeremail");			// 구매자 이메일 주소
		$inipay->m_payMethod = Context::get("paymethod");			// 지불방법 (절대 수정 불가)
		$inipay->m_encrypted = Context::get("encrypted");			// 암호문
		$inipay->m_sessionKey = Context::get("sessionkey");			// 암호문
		$inipay->m_url = Context::getDefaultUrl(); 	// 실제 서비스되는 상점 SITE URL로 변경할것
		$inipay->m_cardcode = Context::get("cardcode"); 			// 카드코드 리턴
		$inipay->m_ParentEmail = Context::get("parentemail");

		$inipay->m_recvName = Context::get("recvname");	// 수취인 명
		$inipay->m_recvTel = Context::get("recvtel");		// 수취인 연락처
		$inipay->m_recvAddr = Context::get("recvaddr");	// 수취인 주소
		$inipay->m_recvPostNum = Context::get("recvpostnum");  // 수취인 우편번호
		$inipay->m_recvMsg = Context::get("recvmsg");		// 전달 메세지
	
		$inipay->startAction();
		
		$finance_name        = iconv("EUC-KR", "UTF-8", ($bankname)?$bankname:(($card_name)?$card_name:$bank_name));
		$finance_description = $account;
		$finance_message     = ($app_no)?$app_no:$depositor;

		$args->order_srl           = $inipay->m_moid;
		$args->result_code         = $inipay->m_resultCode;
		$args->result_message      = iconv("EUC-KR", "UTF-8", $inipay->m_resultMsg);
		$args->system_srl          = $inipay->m_tid;
		
		$args->finance_name        = $finance_name;
		$args->finance_description = $finance_description;
		$args->finance_message     = $finance_message;

		$args->current_state       = ($inipay->m_payMethod == $this->getTypeString("CD") || $inipay->m_payMethod == $this->getTypeString("AB"))?"3":"2";
		
		$oPaymentController    = &getController("payment");
		$args->mid = $mid;
		$oPcOutput = $oPaymentController->updateSettle($args);

		if(!$oPcOutput->toBool())
		{
			$inipay->m_type = "cancel"; // 고정
			$inipay->m_msg = "DB FAIL"; // 취소사유
			$inipay->startAction();
			if($inipay->m_resultCode == "00")
			{
				$inipay->m_resultCode = "01";
				$inipay->m_resultMsg = "DB FAIL";
			}
		}

		$formHidden = Array();
		
		$formHidden[] = Array("name"=>"payment_result_code",   "id"=>"payment_result_code",   "value"=>$inipay->m_resultCode);
		$formHidden[] = Array("name"=>"payment_result_msg",    "id"=>"payment_result_msg",    "value"=>iconv("EUC-KR", "UTF-8", $inipay->m_resultMsg));
		$formHidden[] = Array("name"=>"payment_result_srl",    "id"=>"payment_result_srl",    "value"=>$inipay->m_tid);
		$formHidden[] = Array("name"=>"payment_order_srl",     "id"=>"payment_order_srl",     "value"=>$inipay->m_moid);
		$formHidden[] = Array("name"=>"payment_finance_name",  "id"=>"payment_finance_name",  "value"=>$finance_name);
		$formHidden[] = Array("name"=>"payment_finance_authno","id"=>"payment_finance_authno","value"=>$app_no);
		$formHidden[] = Array("name"=>"payment_accountno",     "id"=>"payment_accountno",     "value"=>$account);

		$output->add("formName", "IN4_PAYINFO");
		$output->add("formContents", $formHidden);
		$output->add("formAction", Context::get('result_link'));
		
		return $output;
	}


	function getTypeString($code)
	{
		switch($code)
		{
			case "CD" : return "Card"; break;
			case "AB" : return "DirectBank"; break;
			case "BK" : return "VBank"; break;
			case "HP" : return "HPP"; break;
			case "7A" : return "Ars1588Bill"; break;
			case "OC" : return "OCBPoint"; break;
			case "CM" : return "Culture"; break;
			case "GM" : return "dgcl"; break;
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
