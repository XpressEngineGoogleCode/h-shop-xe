<?php
    /**
     * vi:set ts=4 sw=4 expandtab enc=utf8:
     * @file   ko.lang.php
     * @author 문수한(lunyx@me.com) - Blue Leaf
     * @brief  payment 모듈의 기본 언어팩
     **/
    
    $lang->payment                = "결제 시스템";
    $lang->about_payment          = "결제창을 열어주는 모듈입니다.\n결제 관련 설정 및 결제 내역을 확인할 수 있습니다.";

    $lang->cmd_payment_config     = "기본 설정";
    $lang->cmd_settlement_list    = "결제 내역";
    $lang->cmd_payment_list       = "결제시스템 목록";

    $lang->payment_type           = "사용결제방법";
    $lang->payment_test           = "테스트모드";
    $lang->payment_extra          = "시스템";
    $lang->payment_system         = "시스템";

    $lang->payment_about_test     = "현 모듈을 테스트형식으로 가동합니다.";
    $lang->payment_about_system   = "사용할 결제창시스템을 선택합니다.";
    $lang->payment_about_type     = "사용하지 않는 기능은 체크해도 절대나오지않습니다.";

    $lang->payment_type_CD        = "신용카드";
    $lang->payment_type_AB        = "계좌이체";
    $lang->payment_type_BK        = "가상계좌";
    $lang->payment_type_HP        = "휴대폰";
    $lang->payment_type_7A        = "700ARS";
    $lang->payment_type_OC        = "OK캐쉬백";
    $lang->payment_type_CM        = "문화상품권";
    $lang->payment_type_GM        = "게임문화상품권";

    $lang->payment_order_srl      = "주문 번호";
    $lang->payment_state          = "결제 상태";
    $lang->payment_result_code    = "결과 코드";
    $lang->payment_result_message = "결과 메세지";

    $lang->payment_currState_0    = "결제대기";
    $lang->payment_currState_1    = "결제실패";
    $lang->payment_currState_2    = "결제접수";
    $lang->payment_currState_3    = "결제완료";
    $lang->payment_currState_4    = "결제취소";

    $lang->payment_system_code    = "결제사 코드";
    $lang->payment_finence_name   = "카드사 / 은행이름";
    $lang->payment_finence_desc   = "카드번호 / 계좌번호";
    $lang->payment_finence_msg    = "카드승인번호 / 입금자";

    $lang->payment_buyer          = "주문자 이름";
    $lang->payment_buyer_call     = "주문자 연락처";
    $lang->payment_buyer_mail     = "주문자 메일";
    $lang->payment_buyer_addr     = "주문자 주소";
    $lang->payment_item_code      = "상품 코드";
    $lang->payment_item_info      = "상품 정보";
    $lang->payment_amount         = "결제 금액";

    $lang->msg_unknown_mid = "등록되지 않은 결제모듈입니다.";
?>
