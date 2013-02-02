<?php
    /**
     * @file   ko.lang.php
     * @author 덧니희야 (deive@nate.com)
     * @brief  상품관리(product) 모듈의 기본 언어팩
     **/

    $lang->product = '상품관리';
    $lang->except_notice = '공지사항 제외';
    $lang->use_anonymous = '익명 사용';
    $lang->cmd_manage_menu = '메뉴관리';
    $lang->list_target_item = '대상 항목';
    $lang->list_display_item = '표시 항목';
    $lang->summary = '요약';
    $lang->thumbnail = '썸네일';
    $lang->last_post = '최종 글';

    // 항목
    $lang->search_result = '검색결과';
    $lang->consultation = '상담 기능';
    $lang->secret = '비밀글 기능';
    $lang->thisissecret = '비밀글입니다.';
    $lang->admin_mail = '관리자 메일';
    $lang->about_connect_shop = "쇼핑몰연계를 사용합니다";

    // 버튼에 사용되는 언어
    $lang->cmd_product_list = '상품관리 목록';
    $lang->cmd_module_config = '상품관리 공통 설정';
    $lang->cmd_view_info = '설정';
    $lang->cmd_list_setting = '목록설정';
    $lang->cmd_icon_management = "아이콘관리";
    $lang->cmd_connect_shop = "쇼핑몰연계";

	$lang->product_category = '분류선택';

    // 주절 주절..
    $lang->about_layout_setup = '레이아웃 코드를 직접 수정할 수 있습니다. 위젯 코드를 원하는 곳에 삽입하시거나 관리하세요';
    $lang->about_product_category = '분류를 만드실 수 있습니다.<br />분류가 오동작을 할 경우 캐시파일 재생성을 수동으로 해주시면 해결이 될 수 있습니다.';
    $lang->about_except_notice = '목록 상단에 늘 나타나는 공지사항을 일반 목록에서 공지사항을 출력하지 않도록 합니다.';
    $lang->about_use_anonymous = '글쓴이의 정보를 없애고 익명으로 상품관리 사용을 할 수 있게 합니다. 스킨설정에서 글쓴이 정보등을 보이지 않도록 하시면 더욱 유용합니다.';
    $lang->about_product = '상품관리을 생성하고 관리할 수 있는 상품관리 모듈입니다.';
    $lang->about_consultation = "상담 기능은 관리권한이 없는 회원은 자신이 쓴 글만 보이도록 하는 기능입니다\n단 상담기능 사용시 비회원 글쓰기는 자동으로 금지됩니다.";
    $lang->about_secret = '상품관리 및 댓글의 비밀글 사용할 수 있도록 합니다.';
    $lang->about_admin_mail = '주문이나 댓글이 등록될때 등록된 메일주소로 메일이 발송됩니다<br /> ,(콤마)로 연결시 다수의 메일주소로 발송할 수 있습니다.';
    $lang->about_list_config = '상품관리의 목록형식 사용시 원하는 항목들로 배치를 할 수 있습니다.<br />단 스킨에서 지원하지 않는 경우 지원되지 않을 수 있습니다<br />대상항목/ 표시항목의 항목을 더블클릭하면 추가/ 제거가 됩니다.';

    $lang->msg_not_enough_point = '포인트가 부족합니다';
	
	// 상품관리
	$lang->monetary_unit = "원"; //화폐단위
	$lang->use = "사용";
	$lang->selected = "선택해주세요.";
	$lang->at_index = "정렬";

	$lang->hit = "히트상품";
	$lang->hot = "핫상품";
	$lang->best = "베스트상품";

    $lang->cmd_manage_product = '상품관리';
    $lang->cmd_manage_product_image = '이미지관리';
    $lang->cmd_write_product = '상품등록';

	$lang->delivery = "배송비";
	$lang->delivery_info = "배송정보";
	$lang->delivery_guide = "기본 배송비를 입력해주세요. (상품등록시 별도로 지정해줄 수 있습니다.)";

	$lang->payment = "결제모듈";
	$lang->payment_guide = "결제모듈 이용여부 및 결제모듈 연동 ( <a href='./?module=admin&act=dispPaymentAdminPaymentList' > 제어판 > 연동설정 > 결제시스템</a> )";

	$lang->payment_select = "결제방법선택";
	$lang->payment_select_guide = "반드시 전자결제사와 계약된 내용만 체크하여 사용하십시요.";
	$lang->payment_select_CD = "신용카드";
	$lang->payment_select_AB = "계좌이체";
	$lang->payment_select_BK = "무통장";
	$lang->payment_select_HP = "휴대폰";
	$lang->payment_select_7A = "700 ARS";
	$lang->payment_select_OC = "OK Cashbag";
	$lang->payment_select_CM = "문화상품권";
	$lang->payment_select_GM = "게임문화상품권";

	$lang->shopxe_use = "쇼핑몰 모듈연동";
	$lang->shopxe_use_check = "사용 (선택하시면 쇼핑몰기능을 연동할 수 있습니다.)";
	$lang->shopxe_guide = "쇼핑몰(shopxe) 모듈 이용여부 ( <a href='./?module=admin&act=dispShopxeAdminIndex' >쇼핑몰모듈관리 바로가기</a> )";

	$lang->product_info = "상품정보";

	$lang->product_img = "이미지";
	$lang->product_img_width = "가로";
	$lang->product_img_height = "세로";
	$lang->product_img_guide = "제품의 이미지크기를 지정해주세요.";
	$lang->product_img_add = "이미지 추가";
	$lang->product_img_add_guide = "추가할 이미지를 선택해주세요.";

	$lang->product_file_insert = "파일 첨부";
	$lang->product_file_delete = "파일 삭제";
	$lang->product_file_thumbnail = "섬네일";
	$lang->product_file_thumbnail_chk = "이 파일을 섬네일로 지정합니다.";
	$lang->product_file_move = "위치이동";

	$lang->product_code = "상품코드";
	$lang->product_name = "상품명";
	$lang->product_transfer_price = "가격대체문구";
	$lang->product_origin_price = "출고가";
	$lang->product_stand_price = "일반판매가";
	$lang->product_sale_price = "기본판매가";
	$lang->price_sale = "판매가";
	$lang->product_product_price = "제품가격";
	$lang->product_give_point = "적립포인트";
	$lang->product_type = "판매형태";
	$lang->product_sales_type_0 = "판매(보기/주문)";
	$lang->product_sales_type_1 = "진열(보기)";
	$lang->product_sales_type_2 = "종료(비노출)";
	$lang->product_period = "판매기간";
	$lang->product_period_start_day = "시작일";
	$lang->product_period_finish_day = "종료일";
	
	$lang->product_out_of_stock = "품절";
	
	$lang->product_volume = "수량";
	$lang->product_purchase_volume = "주문수량";
	$lang->product_total_volume = "전체수량";
	$lang->product_stock_volume = "재고수량";
	$lang->product_volume_guide = "수량을 설정하면 주문수량이 0이 될경우 자동으로 품절표시됩니다.";

	$lang->payment_info = "결제안내";

	$lang->product_exchange = "교환/반품정보";
	$lang->product_maker = "제조사";
	$lang->product_brand = "브랜드";
	$lang->product_origin = "원산지";
	
	$lang->no_products = "판매중인 제품이 없습니다.";

	$lang->product_option_name = '옵션명';
	$lang->product_option_item = '항목';
	$lang->product_option_price = '추가가격';
	$lang->product_option_req = '필수';

?>
