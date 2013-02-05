<?php

/**
 * vi:set ts=4 sw=4 expandtab enc=utf8:
 * @class  Payment System For KCP
 * @author Suhan, Moon(lunyx@me.com) 
 * @brief  KCP Caller
 **/

class teledit extends PaymentSystem
{

	function teledit($payment_info, $args) { return parent::PaymentSystem($payment_info, $args); }

	function getCallerForm()
	{
		$output = new Object();

		$TELEDIT_CPID = $this->payment_info->extra_1;           
		$TELEDIT_CPPWD = $this->payment_info->extra_2;
        $TELEDIT_CALLBACK_URL     = $this->callbackUrl;

        /*
         *  ItemCode : 다날에서 제공해 드린 상품코드 
         *    - ItemCode(상품코드)로 실물상품과 컨텐츠상품이 구분되므로 정확히 입력
         */
        $ItemCode = $this->payment_info->extra_3;

        // TeleditClient 실행
        include("teledit_lib/inc/function.php");
        $TransR = array();
        $TransR["ID"] = $TELEDIT_CPID;
        $TransR["PWD"] = $TELEDIT_CPPWD;
        $TransR["EMAIL"] = $this->args->buyer_mail;
        $TransR["USERID"] = iconv("utf-8", "euc-kr", $this->args->buyer_id);
        $TransR["IPADDR"] = $_SERVER["REMOTE_ADDR"];
        $TransR["OrderID"] = $this->args->order_srl;

        $TransR = MakeItemInfo($TransR, $this->args->buy_amount, $ItemCode, iconv("utf-8", "euc-kr", $this->args->buy_info));
        $Res = CallTeledit("RegistItem",$TransR, false);

        if ($Res["Result"] == "0")
        {
            $formHidden = Array();
            $formHidden[] = Array("name"=>"ServerInfo", "id"=>"ServerInfo", "value"=>$Res["ServerInfo"]);
            $formHidden[] = Array("name"=>"ItemName", "id"=>"ItemName", "value"=>$this->args->buy_info);
            $formHidden[] = Array("name"=>"ItemAmt", "id"=>"ItemAmt", "value"=>$this->args->buy_amount);
            $formHidden[] = Array("name"=>"Target", "id"=>"Target", "value"=>$TELEDIT_CALLBACK_URL);
            $formHidden[] = Array("name"=>"BackURL", "id"=>"BackURL", "value"=>"javascript:self.close();");
            $formHidden[] = Array("name"=>"ByProdCode", "id"=>"ByProdCode", "value"=>$this->args->buy_code);
            $formHidden[] = Array("name"=>"ByUserId", "id"=>"ByUserId", "value"=>$this->args->buyer_id);
            $formHidden[] = Array("name"=>"ByReturnUrl", "id"=>"ByReturnUrl", "value"=>$this->args->result_page);
            $formHidden[] = Array("name"=>"result_link", "id"=>"result_link", "value"=>$this->args->result_page);

            debugPrint(serialize($formHidden));
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

        include("teledit_lib/Error.php");

        return new Object(-1, "teledit 처리실패: " . $Res["ErrMsg"]);
	}

	function getJsFile()
	{
	}

    function getCallScript() 
    { 
        return "
            url = 'http://web.teledit.com/Danal/Teledit/Start.php3';
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
        global $HTTP_POST_VARS;

        include("teledit_lib/inc/function.php");

        $Trans = array();
        $BillErr = false;

        $Addition = array("ServerInfo","EncodedTID");

        $Trans = MakeAddtionalInput($Trans,$HTTP_POST_VARS,$Addition);

        // Confirm
        $Res = CallTeledit("Confirm",$Trans);

        if ($Res["Result"] == "0") {
            //Bill
            $Res2 = CallTeledit("Bill",$Trans);
            if($Res2["Result"] != "0")
                $BillErr = true;
        }

        if ($Res["Result"] == "0" && $Res2["Result"] == "0") {
            /**
             * 정적으로 결제가 완료되었습니다.
             *  - Field 설명
             *    Res -
             *     Result : 결제 결과 (0:성공)
             *     CAP : 거래후 거래한도금액
             *     TotalAmount : 결제된 금액
             *    Res2 -
             *     Result : 결제 결과 (0:성공)
             *     TID : 거래코드
             *    http variable -
             *     Carrier : 통신사 (SKT/KTF/LGT)
             *     OTP : 승인번호
             *     Info : 결제 전화번호, 주민등록번호 (|TelNum=xxxxxxxxxxx|Iden=xxxxxxxxxxxxx)
             *     By... : Ready.php3에서 입력한 변수
             *  - parsing info
             *     $uinfo = Parsor($Info,"|");
             *     $TelNum = $uinfo["TelNum"];
             *     $Iden = $uinfo["Iden"];
             **/

            $output = new Object();
            $formHidden = Array();
            
            $formHidden[] = Array("name"=>"payment_result_code",   "id"=>"payment_result_code",   "value"=>$Res["Result"]);
            $formHidden[] = Array("name"=>"payment_result_msg",    "id"=>"payment_result_msg",    "value"=>$Res["ErrMsg"]);
            $formHidden[] = Array("name"=>"payment_result_srl",    "id"=>"payment_result_srl",    "value"=>$Res["Result"]);
            $formHidden[] = Array("name"=>"payment_order_srl",     "id"=>"payment_order_srl",     "value"=>"");
            $formHidden[] = Array("name"=>"payment_finance_name",  "id"=>"payment_finance_name",  "value"=>"");
            $formHidden[] = Array("name"=>"payment_finance_authno","id"=>"payment_finance_authno","value"=>"");
            $formHidden[] = Array("name"=>"payment_accountno",     "id"=>"payment_accountno",     "value"=>"");
            $formHidden[] = Array("name"=>"payment_product_code",  "id"=>"payment_product_code",   "value"=>Context::get('ByProdCode'));


            $args->order_srl           = $_POST["OrderNum"];
            $args->result_code         = $Res["Result"];
            $args->result_message      = $Res2["TID"];
            $args->system_srl          = $Res2["TID"];
            $args->finance_name        = "";
            $args->finance_description = "";
            $args->finance_message     = "";
            $args->current_state = "";
            $args->product_code = Context::get('ByProdCode');
            $args->buyer_id = Context::get('ByUserId');

            $output->add("formName", "RegistItem");
            $output->add("formContents", $formHidden);
            $output->add("formAction", Context::get('result_link'));
            $output->adds($args);
            
            return $output;
        } else {
            if($BillErr)
                $Res = $Res2;

            $ErrorCode	= $Res["Result"];
            $ErrorMessage	= $Res["ErrMsg"];
            $BackURL	= "JavaScript:history.back()";
            $AbleBack       = false;

            include("teledit_lib/Error.php");
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
