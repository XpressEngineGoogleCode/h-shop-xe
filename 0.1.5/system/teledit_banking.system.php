<?php

/**
 * vi:set ts=4 sw=4 expandtab enc=utf8:
 * @class  Payment System For KCP
 * @author Suhan, Moon(lunyx@me.com) 
 * @brief  KCP Caller
 **/

include("teledit_banking_lib/Function.php");

class teledit_banking extends PaymentSystem
{

    function teledit_banking($payment_info, $args) { 
        return parent::PaymentSystem($payment_info, $args); 
    }

	function getCallerForm()
	{
		$output = new Object();

		$TELEDIT_CPID = $this->payment_info->extra_1;           
		$TELEDIT_CPPWD = $this->payment_info->extra_2;
        $TELEDIT_CALLBACK_URL     = $this->callbackUrl . '&justclose=yes';

        // TeleditClient 실행
        $TransR = array();
        $TransR["CPID"] = $TELEDIT_CPID;
        $TransR["CPPWD"] = $TELEDIT_CPPWD;
        $TransR["CPName"] = iconv('utf-8', 'euc-kr', $this->payment_info->extra_3);

        $TransR["Amount"] = $this->args->buy_amount;    // 결제금액
        $TransR["OrderNum"] = $this->args->order_srl;   // 주문번호
        $TransR["UserIP"] = $_SERVER["REMOTE_ADDR"];
        /* 
         * [고정값] 계좌이체에 필요한 고정값들.(수정하시 마십시오.)
         */
        $TransR["REQDate"]		= date("YmdHis"); 
        $TransR["Command"]		= "GETTID";

        $TransR = teledit_server_request($TransR);
        if ($TransR["Result"] == "D000") {
            $formHidden = Array();
            $formHidden[] = Array("name"=>"TID",                 "id"=>"TID",                     "value"=>$TransR["TID"]);
            $formHidden[] = Array("name"=>"ReturnURL",            "id"=>"ReturnURL",              "value"=>$TELEDIT_CALLBACK_URL);
            $formHidden[] = Array("name"=>"OrderNum",            "id"=>"OrderNum",                "value"=>$this->args->order_srl);
            $formHidden[] = Array("name"=>"ProductInfo",          "id"=>"ProductInfo",            "value"=>$this->args->buy_info);
            $formHidden[] = Array("name"=>"SSN",                  "id"=>"SSN",                    "value"=>Context::get('SSN'));
            $formHidden[] = Array("name"=>"Buyer",                "id"=>"Buyer",                  "value"=>$this->args->buyer_name);
            $formHidden[] = Array("name"=>"BuyerID",              "id"=>"BuyerID",                "value"=>$this->args->buyer_id);
            $formHidden[] = Array("name"=>"ProductCode",          "id"=>"ProductCode",            "value"=>$this->args->buy_code);
            $formHidden[] = Array("name"=>"BuyerEmail",           "id"=>"BuyerID",                "value"=>$this->args->buyer_mail);
            $formHidden[] = Array("name"=>"result_link",          "id"=>"result_link",            "value"=>$this->args->result_page);
            $formHidden[] = Array("name"=>"Buffer",               "id"=>"Buffer",                 "value"=>$this->args->result_page);

            $output->add("formName", "RegistItem");
            $output->add("formContents", $formHidden);
            $output->add("formAction", sprintf("./?mid=%s&act=dispPaymentProcess&payment_mid=%s", Context::get("mid"), $this->args->payment_mid));
            
            return $output;
        }

        $ErrorCode      = $Res["Result"];
        $ErrorMessage   = $Res["ErrMsg"];
        $BackURL		= "#";
        $CancelURL      = "#";
        $AbleBack       = false;

        include("teledit_banking_lib/Error.php");

        return new Object(-1, "teledit 처리실패: " . $Res["ErrMsg"]);
	}

	function getJsFile()
	{
	}

    function getCallScript() 
    { 
        return "
            ssn_input=document.getElementById('ssn_input');
            ssn=document.getElementById('SSN');
            ssn.value = ssn_input.value;
            url = '" . TeleditGetURL("SUBMIT") . "';
            window.name = 'chgpwin';
            window.open('', 'chgchild', 'width=500,height=400,status=yes,scrollbars=no,resizable=no,menubar=no');
            obj=document.getElementById('RegistItem');
            obj.action = url;
            obj.target = 'chgchild'; 
            obj.acceptCharset = 'euc-kr';
            document.charset = 'euc-kr';
            obj.submit();
            document.charset = 'utf-8';
        "; 
    }
	function getPageLoadedScript() { return ""; }

	function setCallback()
	{
	    $TransTID = array();

        $TransTID["Command"]	= "CONFIRM";
        $TransTID["OrderNum"]	= Context::get('OrderNum');
        $TransTID["TID"]	=  Context::get('TID');

	    $ResultTID = teledit_server_request($TransTID);
	
	    if ($ResultTID["Result"] == "D000") {
            /* 
             * 결제 완료에 대한 코딩 삽입.
             *  - Noti의 결과에 따라 DB작업등의 코딩을 삽입하여 주십시오. 

             *  - In HTTP_POST_VARS
             *     TID	- 다날 거래ID
             *	    CPID - CPID
             *		REQDate - 거래요청시간
             *		BillDate - 거래완료시간
             *		OrderNum - CP측 주문번호 (Ready.asp에서 입력한값과 동일)
             *		ProductInfo - 상품정보 (Ready.asp에서 입력한값과 동일)
             *		Buyer - 사용자 정보 (Ready.asp에서 입력한값과 동일)
             *		BuyerID - 사용자 ID(Ready.asp에서 입력한값과 동일)
             *		FnaanceCode - 거래은행
             *		Result - 거래결과
             *		ErrMsg - 거래결과 메시지
             */

            $return_url = $_POST["Buffer"];
            $prodcode = Context::get('ProductCode');
            $buyer_id = Context::get('BuyerID');

            $formHidden = Array();
            $formHidden[] = Array("name"=>"payment_result_code",   "id"=>"payment_result_code",   "value"=>Context::get('Result'));
            $formHidden[] = Array("name"=>"payment_result_msg",    "id"=>"payment_result_msg",    "value"=>Context::get('ErrMsg'));
            $formHidden[] = Array("name"=>"payment_result_srl",    "id"=>"payment_result_srl",    "value"=>Context::get('Result'));
            $formHidden[] = Array("name"=>"payment_order_srl",     "id"=>"payment_order_srl",     "value"=>Context::get('OrderNum'));
            $formHidden[] = Array("name"=>"payment_finance_name",  "id"=>"payment_finance_name",  "value"=>"");
            $formHidden[] = Array("name"=>"payment_finance_authno","id"=>"payment_finance_authno","value"=>"");
            $formHidden[] = Array("name"=>"payment_accountno",     "id"=>"payment_accountno",     "value"=>"");
            $formHidden[] = Array("name"=>"payment_product_code",  "id"=>"payment_product_code",   "value"=>$prodcode);

            $args->order_srl           = Context::get('OrderNum');
            $args->result_code         = Context::get('Result');
            $args->result_message      = Context::get('TID');
            $args->system_srl          = Context::get('TID');
            
            $args->finance_name        = Context::get('FnaanceCode');
            $args->finance_description = "";
            $args->finance_message     = "";

            $args->current_state = "";
            $args->call_type = "AB";
            $args->product_code = $prodcode;
            $args->buyer_id = $buyer_id;
            
            $output = new Object();
            $output->add("formName", "RegistItem");
            $output->add("formContents", $formHidden);
            $output->add("formAction", Context::get('result_link'));
            $output->adds($args);

            return $output;
	    } else {
            $ErrorCode      = $ResultTID["Result"];
            $ErrorMessage   = $ResultTID["ErrMsg"];

    		include("teledit_banking_lib/Error.php");
        }

		return ;
	}
	
	function setProcess() {
	}

	function getTypeString($code)
	{
		switch($code)
		{
			case "CD" : return "100000000000"; break;
			case "AB" : return "010000000000"; break;
			case "BK" : return "001000000000"; break;
			case "OC" : return "000100000000"; break;
			case "HP" : return "000010000000"; break;
			case "CM" : return "000000001000"; break;
			case "GM" : return "000000001000"; break;
			case "7A" : return "000000000010"; break;
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
