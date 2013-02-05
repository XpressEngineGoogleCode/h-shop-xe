<?php
/**
 * vi:set ts=4 sw=4 expandtab enc=utf8:
 * @class  Payment System For KCP
 * @author Suhan, Moon(lunyx@me.com) 
 * @brief  KCP Caller
 **/

class kcp extends PaymentSystem
{
    function kcp($payment_info, $args) {
        return parent::PaymentSystem($payment_info, $args); 
    }

    function getCallerForm() {
        $output = new Object();

        $SITE_CD              = ("test" == $this->platform)?"T0007":$this->payment_info->extra_1;           
        $SITE_KEY             = ("test" == $this->platform)?"4Ho4YsuOZlLXUZUdOxM1Q7X__":$this->payment_info->extra_2;
        $KCP_OID              = $this->args->order_srl;
        $KCP_BUYER            = $this->args->buyer_name;
        $KCP_BUYEREMAIL       = $this->args->buyer_mail;
        $KCP_BUYERPHONE       = $this->args->buyer_call;
        $KCP_PRODUCTINFO      = $this->args->buy_info;
		$KCP_PRODUCTCODE      = $this->args->buy_code;
        $KCP_AMOUNT           = $this->args->buy_amount;
        $KCP_CUSTOM_USABLEPAY = $this->getTypeString($this->args->call_type);

        $formHidden = Array();
        $formHidden[] = Array("name"=>"req_tx",              "id"=>"req_tx",              "value"=>"pay");
        $formHidden[] = Array("name"=>"site_name",           "id"=>"site_name",           "value"=>$this->payment_info->extra_3);   // 상점명
        $formHidden[] = Array("name"=>"site_cd",             "id"=>"site_cd",             "value"=>$SITE_CD);
        $formHidden[] = Array("name"=>"site_key",            "id"=>"site_key",            "value"=>$SITE_KEY);
        $formHidden[] = Array("name"=>"ordr_idxx",           "id"=>"ordr_idxx",           "value"=>$KCP_OID);
        $formHidden[] = Array("name"=>"pay_method",          "id"=>"pay_method",          "value"=>$KCP_CUSTOM_USABLEPAY);
        $formHidden[] = Array("name"=>"good_name",           "id"=>"good_name",           "value"=>$KCP_PRODUCTINFO);
		$formHidden[] = Array("name"=>"good_code",           "id"=>"good_code",           "value"=>$KCP_PRODUCTCODE);
        $formHidden[] = Array("name"=>"good_mny",            "id"=>"good_mny",            "value"=>$KCP_AMOUNT);
        $formHidden[] = Array("name"=>"buyr_name",           "id"=>"buyr_name",           "value"=>$KCP_BUYER);
        $formHidden[] = Array("name"=>"buyr_mail",           "id"=>"buyr_mail",           "value"=>$KCP_BUYEREMAIL);
        $formHidden[] = Array("name"=>"buyr_tel1",           "id"=>"buyr_tel1",           "value"=>$KCP_BUYERPHONE);
        $formHidden[] = Array("name"=>"buyr_tel2",           "id"=>"buyr_tel2",           "value"=>$KCP_BUYERPHONE);
        $formHidden[] = Array("name"=>"quotaopt",            "id"=>"quotaopt",            "value"=>"12");

        $formHidden[] = Array("name"=>"module_type",         "id"=>"module_type",         "value"=>"01");
        $formHidden[] = Array("name"=>"currency",            "id"=>"currency",            "value"=>"WON");
        $formHidden[] = Array("name"=>"save_ocb",            "id"=>"save_ocb",            "value"=>"Y");
        $formHidden[] = Array("name"=>"kcp_noint",           "id"=>"kcp_noint",           "value"=>"");
        $formHidden[] = Array("name"=>"kcp_noint_quota",     "id"=>"kcp_noint_quota",     "value"=>"");
        $formHidden[] = Array("name"=>"wish_vbank_list",     "id"=>"wish_vbank_list",     "value"=>"05:03:04:07:11:23:26:32:34:81:71");
        $formHidden[] = Array("name"=>"vcnt_expire_term",    "id"=>"vcnt_expire_term",    "value"=>"3");
        $formHidden[] = Array("name"=>"epnt_issu",           "id"=>"epnt_issu",           "value"=>"");
        
        $formHidden[] = Array("name"=>"res_cd",              "id"=>"res_cd",              "value"=>"");
        $formHidden[] = Array("name"=>"res_msg",             "id"=>"res_msg",             "value"=>"");
        $formHidden[] = Array("name"=>"tno",                 "id"=>"tno",                 "value"=>"");
        $formHidden[] = Array("name"=>"trace_no",            "id"=>"trace_no",            "value"=>"");
        $formHidden[] = Array("name"=>"enc_info",            "id"=>"enc_info",            "value"=>"");
        $formHidden[] = Array("name"=>"enc_data",            "id"=>"enc_data",            "value"=>"");
        $formHidden[] = Array("name"=>"ret_pay_method",      "id"=>"ret_pay_method",      "value"=>"");
        $formHidden[] = Array("name"=>"tran_cd",             "id"=>"tran_cd",             "value"=>"");
        $formHidden[] = Array("name"=>"bank_name",           "id"=>"bank_name",           "value"=>"");
        $formHidden[] = Array("name"=>"bank_issu",           "id"=>"bank_issu",           "value"=>"");
        $formHidden[] = Array("name"=>"use_pay_method",      "id"=>"use_pay_method",      "value"=>$this->args->call_type);
        $formHidden[] = Array("name"=>"result_link",         "id"=>"result_link",         "value"=>$this->args->result_page);

        $output->add("formName", "KCP_PAYINFO");
        $output->add("formContents", $formHidden);
        $output->add("formAction", sprintf("./?mid=%s&act=dispPaymentProcess&payment_mid=%s", Context::get("mid"), $this->args->payment_mid));
        
        return $output;
    }

    function getJsFile() {
        $jsAdderFile = "http";
        if($_SERVER["HTTPS"] != "off") $jsAdderFile .= "s";

        if($this->platform == "test")
            $jsAdderFile .= "://pay.kcp.co.kr/plugin/payplus_test_un.js";
        else
            $jsAdderFile .= "://pay.kcp.co.kr/plugin/payplus_un.js";


        return $jsAdderFile;
    }

    function getCallScript() {
        return "f = document.getElementById('KCP_PAYINFO'); if(MakePayMessage(f)) f.submit();"; 
    }

    function getPageLoadedScript() {
        return "StartSmartUpdate();"; 
    }

    function setCallback() {
        return ;
    }
    
    function setProcess( $mid = null ) {
        $output = new Object();
/*
        $logged_info = Context::get('logged_info');
        if (!$logged_info) return new Object(-1, 'msg_not_logged');

        $user_id = $logged_info->user_id;
*/
        $g_conf_home_dir  = str_replace("kcp.system.php", "", __FILE__)."kcp_lib/payplus"; 
        $g_conf_pa_url    = ("test" == $this->platform)?"testpaygw.kcp.co.kr":"paygw.kcp.co.kr";
   
        $g_conf_pa_port   = "8090";
        $g_conf_mode      = 0;
        $g_conf_log_level = "3";

        require "kcp_lib/pp_ax_hub_lib.php";

        $site_cd        = ("test" == $this->platform)?"T0007":$this->payment_info->extra_1;
        $site_key       = ("test" == $this->platform)?"4Ho4YsuOZlLXUZUdOxM1Q7X__":$this->payment_info->extra_2;
        
        $req_tx         = Context::get( "req_tx"         );
        $cust_ip        = getenv      ( "REMOTE_ADDR"    );
        
        $ordr_idxx      = Context::get( "ordr_idxx"      );
        $good_name      = iconv("UTF-8", "EUC-KR",Context::get( "good_name" ));
        $good_code      = Context::get( "good_code"      );
        $good_mny       = Context::get( "good_mny"       );
        $tran_cd        = Context::get( "tran_cd"        );

        $tno            = Context::get( "tno"            );

        $buyr_name      = iconv("UTF-8", "EUC-KR", Context::get( "buyr_name" ));
        $buyr_tel1      = Context::get( "buyr_tel1"      );
        $buyr_tel2      = Context::get( "buyr_tel2"      );
        $buyr_mail      = Context::get( "buyr_mail"      );

        $bank_issu      = Context::get( "bank_issu"      );

        $mod_type       = Context::get("mod_type"        );
        $mod_desc       = Context::get("mod_desc"        );

        $use_pay_method = Context::get( "use_pay_method" );
        $epnt_issu      = Context::get( "epnt_issu"      ); 
        $acnt_yn        = Context::get( "acnt_yn"        );

        $escw_used      = Context::get( "escw_used"      );
        $pay_mod        = Context::get( "pay_mod"        );
        $deli_term      = Context::get( "deli_term"      );
        $bask_cntx      = Context::get( "bask_cntx"      );
        $good_info      = Context::get( "good_info"      );
        $rcvr_name      = Context::get( "rcvr_name"      );
        $rcvr_tel1      = Context::get( "rcvr_tel1"      );
        $rcvr_tel2      = Context::get( "rcvr_tel2"      );
        $rcvr_mail      = Context::get( "rcvr_mail"      );
        $rcvr_zipx      = Context::get( "rcvr_zipx"      );
        $rcvr_add1      = Context::get( "rcvr_add1"      );
        $rcvr_add2      = Context::get( "rcvr_add2"      );

        $cash_yn        = Context::get( "cash_yn"        );
        $cash_tr_code   = Context::get( "cash_tr_code"   );
        $cash_id_info   = Context::get( "cash_id_info"   );
		$bankname		= Context::get( "bankname"   );
		$account		= Context::get( "account"   );

        $c_PayPlus = new C_PP_CLI;
        $c_PayPlus->mf_clear();

        if ( $req_tx == "pay" )
        {
            if ( $bank_issu == "SCOB" )
            {
                $tran_cd = "00200000";

                $c_PayPlus->mf_set_modx_data( "tno",           $tno       ); 
                $c_PayPlus->mf_set_modx_data( "mod_type",      "STAQ"     );
                $c_PayPlus->mf_set_modx_data( "mod_ip",        $cust_ip   );
                $c_PayPlus->mf_set_modx_data( "mod_ordr_idxx", $ordr_idxx );
            }
            else
            {
                $c_PayPlus->mf_set_encx_data( Context::get("enc_data" ), Context::get("enc_info" ) );
            }
        }

        else if ( $req_tx == "mod" )
        {
            $tran_cd = "00200000";

            $c_PayPlus->mf_set_modx_data( "tno",      $tno      );
            $c_PayPlus->mf_set_modx_data( "mod_type", $mod_type );
            $c_PayPlus->mf_set_modx_data( "mod_ip",   $cust_ip  );
            $c_PayPlus->mf_set_modx_data( "mod_desc", $mod_desc );
        }

        else if ( $req_tx == "mod_escrow" )
        {
            $tran_cd = "00200000";

            $c_PayPlus->mf_set_modx_data( "tno",        $tno            );
            $c_PayPlus->mf_set_modx_data( "mod_type",   $mod_type       );
            $c_PayPlus->mf_set_modx_data( "mod_ip",     $cust_ip        );
            $c_PayPlus->mf_set_modx_data( "mod_desc",   $mod_desc       ); 
            if ($mod_type == "STE1")
            {
                $c_PayPlus->mf_set_modx_data( "deli_numb",   Context::get("deli_numb" ) );
                $c_PayPlus->mf_set_modx_data( "deli_corp",   Context::get("deli_corp" ) );
            }
            else if ($mod_type == "STE2" || $mod_type == "STE4")
            {
                if ($acnt_yn == "Y")
                {
                    $c_PayPlus->mf_set_modx_data( "refund_account",   Context::get("refund_account" ) );
                    $c_PayPlus->mf_set_modx_data( "refund_nm",        Context::get("refund_nm"      ) );
                    $c_PayPlus->mf_set_modx_data( "bank_code",        Context::get("bank_code"      ) );
                }
            }
        }

        if ( $tran_cd != "" )
        {
            $c_PayPlus->mf_do_tx( $trace_no, $g_conf_home_dir, $site_cd, $site_key, $tran_cd, "",
                                  $g_conf_pa_url, $g_conf_pa_port, "payplus_cli_slib", $ordr_idxx,
                                  $cust_ip, $g_conf_log_level, 0, $g_conf_mode );
        }
        else
        {
            $c_PayPlus->m_res_cd  = "9562";
            $c_PayPlus->m_res_msg = "연동 오류 TRAN_CD[" . $tran_cd . "]";
        }

        $res_cd  = $c_PayPlus->m_res_cd;
        $res_msg = iconv("EUC-KR", "UTF-8", $c_PayPlus->mf_get_res_data("m_res_msg"));

        $oPaymentModel         = &getModel("payment");
        $oPaymentController    = &getController("payment");
        
        if($res_cd != "0000")
        {
            $output->setError(-1);
            $output->setMessage(sprintf("%s:%s", $res_cd, $res_msg));
            
            $args->order_srl           = $ordr_idxx;
            $args->result_code         = $res_cd;
            $args->result_message      = $res_msg;
            $args->current_state       = "1";
            $oPaymentController->updateSettle($args);
        }
        else
        {
            $tno     = $c_PayPlus->mf_get_res_data( "tno"    );  // KCP 거래 고유 번호
            $amount  = $c_PayPlus->mf_get_res_data( "amount" );  // KCP 실제 거래 금액
            $escw_yn = $c_PayPlus->mf_get_res_data( "escw_yn" ); // 에스크로 여부
            
            if($use_pay_method == $this->getTypeString("CD"))
            {
                // 신용카드 결제시
                $card_cd   = $c_PayPlus->mf_get_res_data( "card_cd"   ); // 카드 코드
                $card_name = iconv("UTF-8", "EUC-KR", $c_PayPlus->mf_get_res_data( "card_name" )); // 카드 종류
                $app_time  = $c_PayPlus->mf_get_res_data( "app_time"  ); // 승인 시간
                $app_no    = $c_PayPlus->mf_get_res_data( "app_no"    ); // 승인 번호
                $noinf     = $c_PayPlus->mf_get_res_data( "noinf"     ); // 무이자 여부 ( 'Y' : 무이자 )
                $quota     = $c_PayPlus->mf_get_res_data( "quota"     ); // 할부 개월

				$finance_name        = $card_name;
				$finance_description = "*";
				$finance_message     = $app_no;
	            $args->current_state = 2;
	            $args->call_type = 'CD';
			}
            
            if($use_pay_method == $this->getTypeString("AB"))
            {
                // 계좌이체 결제시
                $bank_name = iconv("UTF-8", "EUC-KR", $c_PayPlus->mf_get_res_data( "bank_name"  ));  // 은행명
                $bank_code = $c_PayPlus->mf_get_res_data( "bank_code"  );  // 은행코드

				$finance_name        = $bank_name;
				$finance_description = "*";
				$finance_message     = $bank_code;
	            $args->current_state = 2;
	            $args->call_type = 'AB';
			}

            if($use_pay_method == $this->getTypeString("BK"))
            {
                // 가상계좌 결제시
                $bankname  = iconv("EUC-KR", "UTF-8", $c_PayPlus->mf_get_res_data("bankname")); // 입금할 은행 이름
                $depositor = iconv("EUC-KR", "UTF-8", $c_PayPlus->mf_get_res_data( "depositor" )); // 입금할 계좌 예금주
                $account   = iconv("EUC-KR", "UTF-8", $c_PayPlus->mf_get_res_data( "account"   )); // 입금할 계좌 번호

				$finance_name        = $bankname;
				$finance_description = $account;
				$finance_message     = $depositor;
	            $args->current_state = 0;
	            $args->call_type = 'BK';
            }

            if($use_pay_method == $this->getTypeString("OC"))
            {
                // 포인트 결제시
                $pnt_amount   = $c_PayPlus->mf_get_res_data( "pnt_amount"   );
                $pnt_app_time = $c_PayPlus->mf_get_res_data( "pnt_app_time" );
                $pnt_app_no   = $c_PayPlus->mf_get_res_data( "pnt_app_no"   );
                $add_pnt      = $c_PayPlus->mf_get_res_data( "add_pnt"      );
                $use_pnt      = $c_PayPlus->mf_get_res_data( "use_pnt"      );
                $rsv_pnt      = $c_PayPlus->mf_get_res_data( "rsv_pnt"      );

				$finance_name        = "OK캐쉬백";
				$finance_description = "*";
				$finance_message     = $pnt_app_no;
	            $args->current_state = 2;
	            $args->call_type = 'OC';
            }

            if($use_pay_method == $this->getTypeString("HP"))
            {
                // 핸드폰 결제시
                $app_time = $c_PayPlus->mf_get_res_data( "hp_app_time"  ); // 승인 시간

				$finance_name        = "핸드폰";
				$finance_description = "*";
				$finance_message     = "*";
	            $args->current_state = 2;
	            $args->call_type = 'HP';
            }

            if($use_pay_method == $this->getTypeString("CM") || $use_pay_method == $this->getTypeString("GM"))
            {
                // 문화상품권 결제시
                $app_time = $c_PayPlus->mf_get_res_data( "tk_app_time"  ); // 승인 시간

				$finance_name        = "문화상품권";
				$finance_description = "*";
				$finance_message     = "*";
	            $args->current_state = 2;
	            $args->call_type = $this->getStringType($use_pay_method);
            }

            if($use_pay_method == $this->getTypeString("7A"))
            {
                // 700ARS 결제시
                $app_time = $c_PayPlus->mf_get_res_data( "ars_app_time" ); // 승인 시간

				$finance_name        = "700ARS";
				$finance_description = "*";
				$finance_message     = "*";
	            $args->current_state = 2;
	            $args->call_type = $this->getStringType($use_pay_method);
            }
            
            if ( $cash_yn == "Y" )
            {
                $cash_authno  = $c_PayPlus->mf_get_res_data( "cash_authno"  ); // 현금 영수증 승인 번호
            }

            $args->order_srl           = $ordr_idxx;
            $args->result_code         = $res_cd;
            $args->result_message      = iconv("EUC-KR", "UTF-8", $c_PayPlus->mf_get_res_data("res_msg"));
            $args->system_srl          = $tno;
            
            $args->finance_name        = $finance_name;
            $args->finance_description = $finance_description;
            $args->finance_message     = $finance_message;

            $args->product_code = $good_code;
            
            $args->mid = $mid;
            $args->buyer_id = $user_id;
            $oPcOutput = $oPaymentController->updateSettle($args);
            if(!$oPcOutput->toBool()) {
                $c_PayPlus->mf_clear();

                $bSucc_mod_type = ""; // 즉시 취소 시 사용하는 mod_type

                if ( $escw_yn == "Y" && $use_pay_method == "001000000000" ) {
                    $bSucc_mod_type = "STE5"; // 에스크로 가상계좌 건의 경우 가상계좌 발급취소(STE5)
                }
                else if ( $escw_yn == "Y" ) {
                    $bSucc_mod_type = "STE2"; // 에스크로 가상계좌 이외 건은 즉시취소(STE2)
                }
                else {
                    $bSucc_mod_type = "STSC"; // 에스크로 거래 건이 아닌 경우(일반건)(STSC)
                }

                $tran_cd = "00200000";

                $c_PayPlus->mf_set_modx_data( "tno",      $tno                         );  // KCP 원거래 거래번호
                $c_PayPlus->mf_set_modx_data( "mod_type", $bSucc_mod_type              );  // 원거래 변경 요청 종류
                $c_PayPlus->mf_set_modx_data( "mod_ip",   $cust_ip                     );  // 변경 요청자 IP
                $c_PayPlus->mf_set_modx_data( "mod_desc", "결과 처리 오류 - 자동 취소" );  // 변경 사유

                $c_PayPlus->mf_do_tx( "",  $g_conf_home_dir, $site_cd,
                                      $site_key,  $tran_cd,    "",
                                      $g_conf_pa_url,  $g_conf_pa_port,  "payplus_cli_slib",
                                      $ordr_idxx, $cust_ip,    $g_conf_log_level,
                                      0,    $g_conf_mode );

                $res_cd  = $c_PayPlus->m_res_cd;
                $res_msg = $c_PayPlus->m_res_msg;
            } else {
                $output->adds($args);
            }
        }

        $formHidden = Array();
        
        $formHidden[] = Array("name"=>"result_code",   "id"=>"result_code",   "value"=>$res_cd);
        $formHidden[] = Array("name"=>"result_msg",    "id"=>"result_msg",    "value"=>iconv("EUC-KR", "UTF-8", $res_msg));
        $formHidden[] = Array("name"=>"result_srl",    "id"=>"result_srl",    "value"=>$req_tx);
        $formHidden[] = Array("name"=>"order_srl",     "id"=>"order_srl",     "value"=>$ordr_idxx);
        $formHidden[] = Array("name"=>"finance_name",  "id"=>"finance_name",  "value"=>$finance_name);
        $formHidden[] = Array("name"=>"finance_authno","id"=>"finance_authno","value"=>$app_no);
        $formHidden[] = Array("name"=>"accountno",     "id"=>"accountno",     "value"=>$account);
        $formHidden[] = Array("name"=>"product_code",  "id"=>"product_code",  "value"=>$good_code);     // 2009-11-12 상품코드 추가함. by diver


        $formHidden[] = Array("name"=>"req_tx",              "id"=>"req_tx",              "value"=>$req_tx);
        $formHidden[] = Array("name"=>"use_pay_method",      "id"=>"use_pay_method",      "value"=>$use_pay_method);

        $formHidden[] = Array("name"=>"res_cd",              "id"=>"res_cd",              "value"=>$res_cd);
        $formHidden[] = Array("name"=>"res_msg",             "id"=>"res_msg",             "value"=>iconv("EUC-KR", "UTF-8", $res_msg));
        $formHidden[] = Array("name"=>"ordr_idxx",           "id"=>"ordr_idxx",           "value"=>$ordr_idxx);
        $formHidden[] = Array("name"=>"tno",                 "id"=>"tno",                 "value"=>$tno);
        $formHidden[] = Array("name"=>"good_mny",            "id"=>"good_mny",            "value"=>$good_mny);
        $formHidden[] = Array("name"=>"good_name",           "id"=>"good_name",           "value"=>$good_name);
        $formHidden[] = Array("name"=>"good_code",           "id"=>"good_code",           "value"=>$good_code);
        $formHidden[] = Array("name"=>"buyr_name",           "id"=>"buyr_name",           "value"=>iconv("EUC-KR", "UTF-8", $buyr_name));
        $formHidden[] = Array("name"=>"buyr_tel1",           "id"=>"buyr_tel1",           "value"=>$buyr_tel1);
        $formHidden[] = Array("name"=>"buyr_tel2",           "id"=>"buyr_tel2",           "value"=>$buyr_tel2);
        $formHidden[] = Array("name"=>"buyr_mail",           "id"=>"buyr_mail",           "value"=>$buyr_mail);

        $formHidden[] = Array("name"=>"escw_yn",             "id"=>"escw_yn",             "value"=>$escw_yn);
        $formHidden[] = Array("name"=>"pay_mod",             "id"=>"pay_mod",             "value"=>$this->getStringType($use_pay_method));
        $formHidden[] = Array("name"=>"deli_term",           "id"=>"deli_term",           "value"=>$deli_term);
        $formHidden[] = Array("name"=>"bask_cntx",           "id"=>"bask_cntx",           "value"=>$bask_cntx);
        $formHidden[] = Array("name"=>"good_info",           "id"=>"good_info",           "value"=>$good_info);
        $formHidden[] = Array("name"=>"rcvr_name",           "id"=>"rcvr_name",           "value"=>$rcvr_name);
        $formHidden[] = Array("name"=>"rcvr_tel1",           "id"=>"rcvr_tel1",           "value"=>$rcvr_tel1);
        $formHidden[] = Array("name"=>"rcvr_tel2",           "id"=>"rcvr_tel2",           "value"=>$rcvr_tel2);
        $formHidden[] = Array("name"=>"rcvr_mail",           "id"=>"rcvr_mail",           "value"=>$rcvr_mail);
        $formHidden[] = Array("name"=>"rcvr_zipx",           "id"=>"rcvr_zipx",           "value"=>$rcvr_zipx);
        $formHidden[] = Array("name"=>"rcvr_add1",           "id"=>"rcvr_add1",           "value"=>$rcvr_add1);
        $formHidden[] = Array("name"=>"rcvr_add2",           "id"=>"rcvr_add2",           "value"=>$rcvr_add2);

        $formHidden[] = Array("name"=>"card_cd",             "id"=>"card_cd",             "value"=>$card_cd);
        $formHidden[] = Array("name"=>"card_name",           "id"=>"card_name",           "value"=>$card_name);
        $formHidden[] = Array("name"=>"app_time",            "id"=>"app_time",            "value"=>$app_time);
        $formHidden[] = Array("name"=>"app_no",              "id"=>"app_no",              "value"=>$app_no);
        $formHidden[] = Array("name"=>"quota",               "id"=>"quota",               "value"=>$quota);

        $formHidden[] = Array("name"=>"bank_name",           "id"=>"bank_name",           "value"=>iconv("EUC-KR", "UTF-8", $c_PayPlus->mf_get_res_data( "bank_name" )));
        $formHidden[] = Array("name"=>"bank_code",           "id"=>"bank_code",           "value"=>iconv("EUC-KR", "UTF-8", $c_PayPlus->mf_get_res_data( "bank_code" )));

        $formHidden[] = Array("name"=>"bankname",            "id"=>"bankname",            "value"=>iconv("EUC-KR", "UTF-8", $c_PayPlus->mf_get_res_data( "bankname")));
        $formHidden[] = Array("name"=>"depositor",           "id"=>"depositor",           "value"=>iconv("EUC-KR", "UTF-8", $depositor));
        $formHidden[] = Array("name"=>"account",             "id"=>"account",             "value"=>$account);

        $formHidden[] = Array("name"=>"epnt_issu",           "id"=>"epnt_issu",           "value"=>$epnt_issu);
        $formHidden[] = Array("name"=>"pnt_app_time",        "id"=>"pnt_app_time",        "value"=>$pnt_app_time);
        $formHidden[] = Array("name"=>"pnt_app_no",          "id"=>"pnt_app_no",          "value"=>$pnt_app_no);
        $formHidden[] = Array("name"=>"pnt_amount",          "id"=>"pnt_amount",          "value"=>$pnt_amount);
        $formHidden[] = Array("name"=>"add_pnt",             "id"=>"add_pnt",             "value"=>$add_pnt);
        $formHidden[] = Array("name"=>"use_pnt",             "id"=>"use_pnt",             "value"=>$use_pnt);
        $formHidden[] = Array("name"=>"rsv_pnt",             "id"=>"rsv_pnt",             "value"=>$rsv_pnt);

        $formHidden[] = Array("name"=>"cash_yn",             "id"=>"cash_yn",             "value"=>$cash_yn);
        $formHidden[] = Array("name"=>"cash_authno",         "id"=>"cash_authno",         "value"=>$cash_authno);
        $formHidden[] = Array("name"=>"cash_tr_code",        "id"=>"cash_tr_code",        "value"=>$cash_tr_code);
        $formHidden[] = Array("name"=>"cash_id_info",        "id"=>"cash_id_info",        "value"=>$cash_id_info);

        $formHidden[] = Array("name"=>"pay_type",        "id"=>"pay_type",        "value"=>$use_pay_method);

        $output->add("formName", "KCP_PAYINFO");
        $output->add("formContents", $formHidden);
		$output->add("formSrl", iconv("EUC-KR", "UTF-8", $KCP_OID));
        $output->add("formAction", Context::get('result_link'));
        
        return $output;
    }

    function getTypeString($code) {
        switch($code) {
            case "CD" : return "100000000000"; break;	// 신용카드
            case "AB" : return "010000000000"; break;	// 계좌이체
            case "BK" : return "001000000000"; break;	// 가상계좌
            case "OC" : return "000100000000"; break;	// 포인트
            case "HP" : return "000010000000"; break;	// 휴대폰
            case "CM" : return "000000001000"; break;	// 상품권
            case "GM" : return "000000001000"; break;	// 상품권
            case "7A" : return "000000000010"; break;	// ARS
            default   : return ""; break;
        }
    }

    function getStringType($code) {
        switch($code) {
            case "100000000000" : return "CD"; break;	// 신용카드
            case "010000000000" : return "AB"; break;	// 계좌이체
            case "001000000000" : return "BK"; break;	// 가상계좌
            case "000100000000" : return "OC"; break;	// 포인트
            case "000010000000" : return "HP"; break;	// 휴대폰
            case "000000001000" : return "CM"; break;	// 상품권
            case "000000001000" : return "GM"; break;	// 상품권
            case "000000000010" : return "7A"; break;	// ARS
            default   : return ""; break;
        }
    }

    function getBankString($code) {
        switch($code) {
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

    function getCardString($code) {
        switch($code) {
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
