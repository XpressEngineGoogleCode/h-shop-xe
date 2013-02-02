<?php

/**
 * @class  Payment System For Inipay 4
 * @author Suhan, Moon(lunyx@me.com) 
 * @brief  Inipay 4 Caller
 **/

class inipay4 extends PaymentSystem
{
	
	function inipay4($payment_info, $args) { return parent::PaymentSystem($payment_info, $args); }

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
        $jsAdderFile = "http";
        if($_SERVER["HTTPS"] != "off") $jsAdderFile .= "s";

		// MS IE6 별도 자바스크립트 파일로드
        if( $_SERVER['HTTP_USER_AGENT'] == "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)" )
            $jsAdderFile .= "://plugin.inicis.com/pay40_uni.js";
        else
            $jsAdderFile .= "://plugin.inicis.com/pay40.js";

		return $jsAdderFile;
	}

	function getCallScript()
	{
		return "if(MakePayMessage(jQuery('#IN4_PAYINFO').get(0)))
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

		$inipay->m_inipayHome = str_replace("inipay4.system.php", "", __FILE__)."inipay4_lib"; 
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

		$inipay->m_buyerName = iconv("UTF-8", "EUC-KR", Context::get("buyername"));			// 구매자 명, 인코딩 변환
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
		

		$args->order_srl           = $inipay->m_moid;
		$args->result_code         = $inipay->m_resultCode;
		$args->result_message      = iconv("EUC-KR", "UTF-8", $inipay->m_resultMsg);
		$args->system_srl          = $inipay->m_tid;

		if( $inipay->m_payMethod == "VCard" || $inipay->m_payMethod == "Card" )
		{
			// 신용카드 - 안심/ISP
			$args->finance_name        = $this->getCardString($inipay->m_cardCode);
			$args->finance_description = $inipay->m_cardNumber;
			$args->finance_message     = $inipay->m_authCode;
		}elseif( $inipay->m_payMethod == "DirectBank" ){
			// 은행 실시간계좌이체
			$args->finance_name        = $this->getBankString($inipay->m_directbankcode);
			$args->finance_description = $inipay->m_nminput;
			$args->finance_message     = $inipay->m_nminput;
		}elseif( $inipay->m_payMethod == "HPP" ){
			// 핸드폰
			$args->finance_name        = "휴대폰";
			$args->finance_description = $inipay->m_nohpp;
			$args->finance_message     = $inipay->m_codegw;
		}elseif( $inipay->m_payMethod == "VBank" ){
			// 가상계좌
			$args->finance_name        = $this->getBankString($inipay->m_vcdbank);
			$args->finance_description = $inipay->m_vacct;
			$args->finance_message     = iconv("EUC-KR", "UTF-8", $inipay->m_nminput);
		}elseif( iconv("EUC-KR", "UTF-8", $inipay->m_payMethod) == "Ars1588Bill" ){
			// 1588 전화결제
			$args->finance_name        = "1588 전화결제";
			$args->finance_description = $inipay->m_noars;
			$args->finance_message     = $inipay->m_codegw;
		}elseif( iconv("EUC-KR", "UTF-8", $inipay->m_payMethod) == "PhoneBill" ){
			// 폰빌 전화결제
			$args->finance_name        = "폰빌 전화결제";
			$args->finance_description = $inipay->m_noars;
			$args->finance_message     = $inipay->m_codegw;
		}elseif( iconv("EUC-KR", "UTF-8", $inipay->m_payMethod) == "Culture" ){
			// 문화상품권
			$args->finance_name        = "문화상품권";
			$args->finance_description = $inipay->Cultureid;			
			$args->finance_message     = "-";
		}elseif( iconv("EUC-KR", "UTF-8", $inipay->m_payMethod) == "TEEN" ){
			// 틴캐쉬
			$args->finance_name        = "틴캐쉬";
			$args->finance_description = $inipay->Cultureid;			
			$args->finance_message     = "-";
		}elseif( iconv("EUC-KR", "UTF-8", $inipay->m_payMethod) == "DGCL" ){
			// 게임문화상품권
			$args->finance_name        = "게임문화상품권";
			$args->finance_description = "-";			
			$args->finance_message     = "-";
		}elseif( iconv("EUC-KR", "UTF-8", $inipay->m_payMethod) == "EDCL" ){
			// 교육문화상품권
			$args->finance_name        = "교육문화상품권";
			$args->finance_description = $inipay->Cultureid;			
			$args->finance_message     = "-";
		}elseif( iconv("EUC-KR", "UTF-8", $inipay->m_payMethod) == "SKTG " ){
			// SKT 상품권
			$args->finance_name        = "SKT 상품권";
			$args->finance_description = "-";			
			$args->finance_message     = "-";
		}elseif( iconv("EUC-KR", "UTF-8", $inipay->m_payMethod) == "HPMN " ){
			// 해피머니 상품권
			$args->finance_name        = "해피머니 상품권";
			$args->finance_description = "-";			
			$args->finance_message     = "-";
		}

		if( $inipay->m_payMethod == "VBank" ){
			$args->current_state = 0;
		}else{
			$args->current_state = 2;
		}
		//$args->current_state       = ($inipay->m_payMethod == $this->getTypeString("CD") || $inipay->m_payMethod == $this->getTypeString("AB"))?"3":"2";
		
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
		
		// 결과코드. 결제성공시 "00", 결제실패시 "01"이 반환됨. 주의 - 가상계좌에서 ResultCode 값이 "00" 이면, 결제 성공의 의미가 아닌 입금할 계좌번호 채번에 성공을 뜻함.
		$formHidden[] = Array("name"=>"result_code",   "id"=>"result_code",   "value"=>iconv("EUC-KR", "UTF-8", $inipay->m_resultCode));
		// 결과내용. 결과코드에 대한 설명
		$formHidden[] = Array("name"=>"result_msg",    "id"=>"result_msg",    "value"=>iconv("EUC-KR", "UTF-8", $inipay->m_resultMsg));
		// 결제종류 (은행,카드,핸드폰등)
		$formHidden[] = Array("name"=>"call_type",    "id"=>"call_type",    "value"=>iconv("EUC-KR", "UTF-8", $inipay->m_payMethod));
		// 결제 완료 금액 => 원상품가격과 결제결과금액과 비교하여 금액이 동일하지 않다면 결제 금액의 위변조가 의심됨으로 정상적인 처리가 되지않도록 처리 바랍니다. (거래 취소 처리) 
		$formHidden[] = Array("name"=>"result_price",    "id"=>"result_price",    "value"=>iconv("EUC-KR", "UTF-8", $inipay->m_resultprice));
		// 신용카드+OK Cashbag 복합결제시 신용카드 결제액 (복합결제시에만 반환됨).
		$formHidden[] = Array("name"=>"result_price1",    "id"=>"result_price1",    "value"=>iconv("EUC-KR", "UTF-8", $inipay->m_price1));
		// 신용카드+OK Cashbag 복합결제시 포인트 결제액 (복합결제시에만 반환됨).
		$formHidden[] = Array("name"=>"result_price2",    "id"=>"result_price2",    "value"=>iconv("EUC-KR", "UTF-8", $inipay->m_price2));
		// 신용카드 승인번호
		$formHidden[] = Array("name"=>"auth_code",    "id"=>"auth_code",    "value"=>iconv("EUC-KR", "UTF-8", $inipay->m_authCode));
		// 할부기간
		$formHidden[] = Array("name"=>"card_quota",    "id"=>"card_quota",    "value"=>iconv("EUC-KR", "UTF-8", $inipay->m_cardQuota));
		// 신용카드사 코드
		$formHidden[] = Array("name"=>"card_code",    "id"=>"card_code",    "value"=>iconv("EUC-KR", "UTF-8", $inipay->m_cardCode));
		// 신용카드 번호 / 결제에 사용된 신용카드 번호 (12자리만 리턴됨)
		$formHidden[] = Array("name"=>"card_number",    "id"=>"card_number",    "value"=>iconv("EUC-KR", "UTF-8", $inipay->m_cardNumber));
		// 이니시스에서 승인된 날짜 (YYYYMMDD)
		$formHidden[] = Array("name"=>"auth_dater",    "id"=>"auth_dater",    "value"=>iconv("EUC-KR", "UTF-8", $inipay->m_pgAuthDate));
		// 이니시스에서 승인된 시각 (HHMMSS)
		$formHidden[] = Array("name"=>"auth_time",    "id"=>"auth_time",    "value"=>iconv("EUC-KR", "UTF-8", $inipay->m_pgAuthTime));
		// OK Cashbag 적립 승인번호. OK Cashbag 적립요청시에만 반환됨
		$formHidden[] = Array("name"=>"ocb_save_auth_code",    "id"=>"ocb_save_auth_code",    "value"=>iconv("EUC-KR", "UTF-8", $inipay->m_ocbSaveAuthCode));
		// OK Cashbag 사용 승인번호. OK Cashbag 결제요청시에만 반환됨
		$formHidden[] = Array("name"=>"ocb_use_auth_code",    "id"=>"ocb_use_auth_code",    "value"=>iconv("EUC-KR", "UTF-8", $inipay->m_ocbUseAuthCode));
		// OK Cashbag 승인날짜. OK Cashbag 적립/결제요청시에만 반환됨
		$formHidden[] = Array("name"=>"ocb_save_auth_code",    "id"=>"ocb_save_auth_code",    "value"=>iconv("EUC-KR", "UTF-8", $inipay->m_ocbAuthDate));
		// 카드 발급사(은행) 코드. 카드사 직발행 카드가 아닌 계열카드인 경우, 2자리 신용카드사 코드와 더불어 자세한 카드 정보를 나타냅니다 (직발행 카드인 경우 "00"으로 반환됩니다). 02:한국산업은행, 03:기업은행, 04:국민은행, 05:외환은행, 06:주택은행, 07:수협중앙회, 11:농협중앙회, 12:단위농협, 16:축협중앙회, 20:우리은행, 21:구 조흥은행, 22:상업은행, 23:제일은행, 24:한일은행, 25:서울은행, 26:신한은행, 27:한미은행, 31:대구은행, 32:부산은행, 34:광주은행, 35:제주은행, 37:전북은행, 38:강원은행, 39:경남은행, 41:비씨카드, 53:씨티은행, 54:홍콩상하이은행, 71:우체국, 81:하나은행, 83:평화은행, 87:신세계, 88:신한은행(조흥 통합) 예> cardcode가 "11", cardissuercode가 "23"인 경우 - 제일은행에서 발급한 BC카드
		$formHidden[] = Array("name"=>"card_issuer_code",    "id"=>"card_issuer_code",    "value"=>iconv("EUC-KR", "UTF-8", $inipay->m_cardIssuerCode));
		// 당해 거래가 각종 이벤트에 적용된 거래인지를 나타냄. 0 : 미적용, 1 : 이니시스 무이자이벤트, 12 : 이니시스 무이자이벤트 + 상점 할인 이벤트, 14 : 이니시스 무이자이벤트 + 상점 카드 할인 이벤트, A1 : 상점 무이자 이벤트, A2 : 상점 할인 이벤트, A3 : 상점 무이자 이벤트 + 상점 할인 이벤트, A4 : 상점 무이자 이벤트 + 상점 카드 할인 이벤트, A5 : 상점 카드 할인 이벤트
		$formHidden[] = Array("name"=>"event_flag",    "id"=>"event_flag",    "value"=>iconv("EUC-KR", "UTF-8", $inipay->m_eventFlag));
		// 실시간계좌이체의 은행코드 04:국민은행, 03:기업은행, 05:외환은행, 07:수협중앙회, 11:농협, 20:우리은행, 23:SC제일은행, 31:대구은행, 32:부산은행, 34:광주은행, 35:제주은행, 37:전북은행, 39:경남은행, 71:우체국, 81:하나은행, 88:신한(조흥)은행 증권사 코드 D1:동양종합금융증권, D2:현대증권, D3:미래에셋증권, D4:한국투자증권, D5:우리투자증권, D6:하이투자증권, D7:HMC투자증권, D8:SK증권, D9:대신증권, DA:하나대투증권, DB:굿모닝신한증권, DC:동부증권, DD:유진투자증권, DE:메리츠증권, DF:신영증권 
		$formHidden[] = Array("name"=>"direct_bank_code",    "id"=>"direct_bank_code",    "value"=>iconv("EUC-KR", "UTF-8", $inipay->m_directbankcode));
		// 무통장 입금 예약에 사용된 주민등록번호
		$formHidden[] = Array("name"=>"perno",    "id"=>"perno",    "value"=>iconv("EUC-KR", "UTF-8", $inipay->m_perno));
		// 무통장 입금 예약의 입금할 계좌번호
		$formHidden[] = Array("name"=>"vacct",    "id"=>"vacct",    "value"=>iconv("EUC-KR", "UTF-8", $inipay->m_vacct));
		// 무통장 입금 예약의 입금할 은행코드
		$formHidden[] = Array("name"=>"vcdbank",    "id"=>"vcdbank",    "value"=>iconv("EUC-KR", "UTF-8", $inipay->m_vcdbank));
		// 무통장 입금 예약의 입금예정일 (YYYYMMDD)
		$formHidden[] = Array("name"=>"dtinput",    "id"=>"dtinput",    "value"=>iconv("EUC-KR", "UTF-8", $inipay->m_dtinput));
		// 무통장 입금 예약의 입금예정 시간 (HHmmss)
		$formHidden[] = Array("name"=>"tminput",    "id"=>"tminput",    "value"=>iconv("EUC-KR", "UTF-8", $inipay->m_tminput));
		// 무통장 입금 예약의 송금자 명
		$formHidden[] = Array("name"=>"nminput",    "id"=>"nminput",    "value"=>iconv("EUC-KR", "UTF-8", $inipay->m_nminput));
		// 무통장 입금 예약의 예금주 명
		$formHidden[] = Array("name"=>"nmvacct",    "id"=>"nmvacct",    "value"=>iconv("EUC-KR", "UTF-8", $inipay->m_nmvacct));
		// 휴대폰결제에 사용된 휴대폰 번호
		$formHidden[] = Array("name"=>"nohpp",    "id"=>"nohpp",    "value"=>iconv("EUC-KR", "UTF-8", $inipay->m_nohpp));
		// 전화결제에 사용된 전화번호
		$formHidden[] = Array("name"=>"noars",    "id"=>"noars",    "value"=>iconv("EUC-KR", "UTF-8", $inipay->m_noars));
		// 컬쳐랜드 ID(문화상품권 결제시) / 교육문화상품권 ID(교육문화 상품권 결제시) / 틴캐시(TeenCash) ID(틴캐시 결제시)
		$formHidden[] = Array("name"=>"culture_id",    "id"=>"culture_id",    "value"=>iconv("EUC-KR", "UTF-8", $inipay->m_Cultureid));
		// OK Cashbag 카드번호. OK Cashbag 사용,적립 및 시용카드 복합결제시에만 반환됨
		$formHidden[] = Array("name"=>"ocbcard_number",    "id"=>"ocbcard_number",    "value"=>iconv("EUC-KR", "UTF-8", $inipay->m_ocbcardnumber));
		// 결제 에러코드 (결과메세지 앞에 있는 에러코드)
		$formHidden[] = Array("name"=>"resulterr_code",    "id"=>"resulterr_code",    "value"=>iconv("EUC-KR", "UTF-8", $inipay->m_resulterrcode));
		// 전화결제 사업자 코드(휴대폰 포함)
		$formHidden[] = Array("name"=>"codegw",    "id"=>"codegw",    "value"=>iconv("EUC-KR", "UTF-8", $inipay->m_codegw));
		// SKT 상품권 결제방법  C: 카드형,  M: 모바일형
		$formHidden[] = Array("name"=>"sktg_method",    "id"=>"sktg_method",    "value"=>iconv("EUC-KR", "UTF-8", $inipay->m_sktg_method));
		// 틴캐시 결제후 잔액   교육문화 상품권 결제후 잔액
		$formHidden[] = Array("name"=>"remain_price",    "id"=>"remain_price",    "value"=>iconv("EUC-KR", "UTF-8", $inipay->m_remain_price));
		// 결제된 게임문화상품권/SKT상품권 카드의 총 합을 나타낸다.  (SKT 상품권 모바일형일경우 '1')
		$formHidden[] = Array("name"=>"dgcl_cardcount",    "id"=>"dgcl_cardcount",    "value"=>iconv("EUC-KR", "UTF-8", $inipay->m_dgcl_cardcount));
		// 해당 번호의 게임문화상품권/SKT상품권의 카드번호.  ex. dgcl_cardnum1 --> 첫번째 결제된 상품권의 카드번호
		$formHidden[] = Array("name"=>"dgcl_cardnum",    "id"=>"dgcl_cardnum",    "value"=>iconv("EUC-KR", "UTF-8", $inipay->m_dgcl_cardnum));
		// 해당 번호의 게임문화상품권/SKT상품권의 카드잔액.  ex. dgcl_remain_price1 --> 첫번째 결제된 상품권의 카드잔액
		$formHidden[] = Array("name"=>"dgcl_remain_price",    "id"=>"dgcl_remain_price",    "value"=>iconv("EUC-KR", "UTF-8", $inipay->m_dgcl_remain_price));
		// 해당 번호의 게임문화상품권/SKT상품권의 에러내용.  ex. dgcl_errmsg1 --> 첫번째 결제된 상품권의 에러메시지
		$formHidden[] = Array("name"=>"dgcl_errmsg",    "id"=>"dgcl_errmsg",    "value"=>iconv("EUC-KR", "UTF-8", $inipay->m_dgcl_errmsg));

		$formHidden[] = Array("name"=>"result_srl",    "id"=>"result_srl",    "value"=>iconv("EUC-KR", "UTF-8", $inipay->m_tid));
		$formHidden[] = Array("name"=>"order_srl",     "id"=>"order_srl",     "value"=>iconv("EUC-KR", "UTF-8", $inipay->m_moid));
		$formHidden[] = Array("name"=>"finance_name",  "id"=>"finance_name",  "value"=>iconv("EUC-KR", "UTF-8", $finance_name));
		$formHidden[] = Array("name"=>"finance_authno","id"=>"finance_authno","value"=>iconv("EUC-KR", "UTF-8", $app_no));
		$formHidden[] = Array("name"=>"accountno",     "id"=>"accountno",     "value"=>iconv("EUC-KR", "UTF-8", $account));

		$output->add("formName", "IN4_PAYINFO");
		$output->add("formContents", $formHidden);
		$output->add("formSrl", iconv("EUC-KR", "UTF-8", $inipay->m_moid));		
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
			case "04" : return "국민은행"; break;
			case "03" : return "기업은행"; break;
			case "05" : return "외환은행"; break;
			case "07" : return "수협중앙회"; break;
			case "11" : return "농협"; break;
			case "20" : return "우리은행"; break;
			case "23" : return "SC제일은행"; break;
			case "31" : return "대구은행"; break;
			case "32" : return "부산은행"; break;
			case "34" : return "광주은행"; break;
			case "35" : return "제주은행"; break;
			case "37" : return "전북은행"; break;
			case "39" : return "경남은행"; break;
			case "71" : return "우체국"; break;
			case "81" : return "하나은행"; break;
			case "88" : return "신한(조흥)은행"; break;
			case "D1" : return "동양종합금융증권"; break;
			case "D2" : return "현대증권"; break;
			case "D3" : return "미래에셋증권"; break;
			case "D4" : return "한국투자증권"; break;
			case "D5" : return "우리투자증권"; break;
			case "D6" : return "하이투자증권"; break;
			case "D7" : return "HMC투자증권"; break;
			case "D8" : return "SK증권"; break;
			case "D9" : return "대신증권"; break;
			case "DA" : return "하나대투증권"; break;
			case "DB" : return "굿모닝신한증권"; break;
			case "DC" : return "동부증권"; break;
			case "DD" : return "유진투자증권"; break;
			case "DE" : return "메리츠증권"; break;
			case "DF" : return "신영증권 "; break;
			default   : return ""; break;
		}
	}

	function getCardString($code)
	{
		switch($code)
		{
			case "06" : return "국민"; break;
			case "01" : return "외환"; break;
			case "03" : return "롯데"; break;
			case "04" : return "현대"; break;
			case "06" : return "국민"; break;
			case "11" : return "BC"; break;
			case "12" : return "삼성"; break;
			case "13" : return "LG"; break;
			case "14" : return "신한"; break;
			case "15" : return "한미"; break;
			case "16" : return "NH"; break;
			case "21" : return "해외비자"; break;
			case "22" : return "해외마스터"; break;
			case "23" : return "JCB"; break;
			case "24" : return "해외아멕스"; break;
			case "25" : return "해외다이너스"; break;
			default   : return ""; break;
		}
	}

	// 취소요청처리
	function setCancel( $tid, $msg )
	{
		$output           = new Object();

		$IN4_MID         = ($this->platform == "test") ? "INIpayTest" : $this->payment_info->extra_1;
		$IN4_MPW         = ($this->platform == "test") ? "1111" : $this->payment_info->extra_2;

		require "inipay4_lib/INIpay41Lib.php";

		$inipay = new INIpay41;

		/*********************
		 * 3. 취소 정보 설정 *
		 *********************/
		$inipay->m_inipayHome = str_replace("inipay4.system.php", "", __FILE__)."inipay4_lib"; // 이니페이 홈디렉터리
		$inipay->m_type = "cancel"; // 고정
		$inipay->m_subPgIp = "203.238.3.10"; // 고정
		/**************************************************************************************************
		 * m_keyPw 는 키패스워드 변수명입니다. 수정하시면 안됩니다. 1111의 부분만 수정해서 사용하시기 바랍니다.
		 * 키패스워드는 상점관리자 페이지(https://iniweb.inicis.com)의 비밀번호가 아닙니다. 주의해 주시기 바랍니다.
		 * 키패스워드는 숫자 4자리로만 구성됩니다. 이 값은 키파일 발급시 결정됩니다.
		 * 키패스워드 값을 확인하시려면 상점측에 발급된 키파일 안의 readme.txt 파일을 참조해 주십시오.
		 **************************************************************************************************/
		$inipay->m_keyPw = $IN4_MPW; // 키패스워드(상점아이디에 따라 변경)
		$inipay->m_debug = "true"; // 로그모드("true"로 설정하면 상세로그가 생성됨.)
		$inipay->m_mid = $IN4_MID; // 상점아이디
		$inipay->m_tid = $tid; // 취소할 거래의 거래아이디
		$inipay->m_cancelMsg = $msg; // 취소사유

		
		/****************
		 * 4. 취소 요청 *
		 ****************/
		$inipay->startAction();
		
		
		/****************************************************************
		 * 5. 취소 결과                                           	*
		 *                                                        	*
		 * 결과코드 : $inipay->m_resultCode ("00"이면 취소 성공)  	*
		 * 결과내용 : $inipay->m_resultMsg (취소결과에 대한 설명) 	*
		 * 취소날짜 : $inipay->m_pgCancelDate (YYYYMMDD)          	*
		 * 취소시각 : $inipay->m_pgCancelTime (HHMMSS)            	*
		 * 현금영수증 취소 승인번호 : $inipay->m_rcash_cancel_noappl    *
		 * (현금영수증 발급 취소시에만 리턴됨)                          * 
		 ****************************************************************/
		$output->add("payment", $inipay);

		return $inipay;
	}
}
?>
