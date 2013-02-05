<?php
    /**
     * @class  productView
     * @author 덧니희야 (deive@nate.com)
     * @brief  product 모듈의 View class
     **/

    class productView extends product {

        /**
         * @brief 초기화
         * product 모듈은 일반 사용과 관리자용으로 나누어진다.\n
         **/
        function init() {
            /**
             * 기본 모듈 정보들 설정 (list_count, page_count는 제품설명 모듈 전용 정보이고 기본 값에 대한 처리를 함)
             **/
            if($this->module_info->list_count) $this->list_count = $this->module_info->list_count;
            if($this->module_info->search_list_count) $this->search_list_count = $this->module_info->search_list_count;
            if($this->module_info->page_count) $this->page_count = $this->module_info->page_count;
            $this->except_notice = $this->module_info->except_notice == 'N' ? false : true;

            /**
             * 상담 기능 체크. 현재 제품설명의 관리자이면 상담기능을 off시킴
             * 현재 사용자가 비로그인 사용자라면 글쓰기/댓글쓰기/목록보기/글보기 권한을 제거함
             **/
            if($this->module_info->consultation == 'Y' && !$this->grant->manager) {
                $this->consultation = true; 
                if(!Context::get('is_logged')) $this->grant->list = $this->grant->write_document = $this->grant->write_comment = $this->grant->view = false;
            } else {
                $this->consultation = false;
            }

            /**
             * 스킨 경로를 미리 template_path 라는 변수로 설정함
             * 스킨이 존재하지 않는다면 xe_product로 변경
             **/
            $template_path = sprintf("%sskins/%s/", $this->module_path, $this->module_info->skin);
            if(!is_dir($template_path)||!$this->module_info->skin) {
                $this->module_info->skin = 'xe_product';
                $template_path = sprintf("%sskins/%s/", $this->module_path, $this->module_info->skin);
            }
            $this->setTemplatePath($template_path);

            /**
             * 확장 변수 사용시 미리 확장변수의 대상 키들을 가져와서 context set
             **/
            $oDocumentModel = &getModel('document');
            $extra_keys = $oDocumentModel->getExtraKeys($this->module_info->module_srl);
            Context::set('extra_keys', $extra_keys);

            /** 
             * 제품설명 전반적으로 사용되는 javascript, JS 필터 추가
             **/
            Context::addJsFilter($this->module_path.'tpl/filter', 'input_password.xml');
            Context::addJsFile($this->module_path.'tpl/js/product.js');

            Context::set('this',$this);

			// 스타일지정
			$this->module_info->default_style = Context::get('listStyle');
        }

        /**
         * @brief 목록 및 선택된 글 출력
         **/
        function dispProductContent() {
            /**
             * 목록보기 권한 체크 (모든 권한은 ModuleObject에서 xml 정보와 module_info의 grant 값을 비교하여 미리 설정하여 놓음)
             **/
            if(!$this->grant->access || !$this->grant->list) return $this->dispProductMessage('msg_not_permitted');

            /**
             * 카테고리를 사용하는지 확인후 사용시 카테고리 목록을 구해와서 Context에 세팅
             **/
            $this->dispProductCategoryList();

            /**
             * 목록이 노출될때 같이 나오는 검색 옵션을 정리하여 스킨에서 쓸 수 있도록 context set
             * 확장변수에서 검색 선택된 항목이 있으면 역시 추가
             **/
            // 템플릿에서 사용할 검색옵션 세팅 (검색옵션 key값은 미리 선언되어 있는데 이에 대한 언어별 변경을 함)
            foreach($this->search_option as $opt) $search_option[$opt] = Context::getLang($opt);
            $extra_keys = Context::get('extra_keys');
            if($extra_keys) {
                foreach($extra_keys as $key => $val) {
                    if($val->search == 'Y') $search_option['extra_vars'.$val->idx] = $val->name;
                }
            }
            Context::set('search_option', $search_option);

            // 게시글을 가져옴
            $this->dispProductContentView();

            // 공지사항 목록을 구해서 context set (공지사항을 매페이지 제일 상단에 위치하기 위해서)
            $this->dispProductNoticeList();

            // 목록
            $this->dispProductContentList();

            /** 
             * 사용되는 javascript 필터 추가
             **/
            Context::addJsFilter($this->module_path.'tpl/filter', 'search.xml');

            // template_file을 list.html로 지정
            $this->setTemplateFile('list');
        }

        /**
         * @brief 카테고리 항목을 구해와서 스킨에서 사용할 수 있도록 세팅
         **/
        function dispProductCategoryList(){
            // 카테고리를 사용할때에만 데이터를 추출
            if($this->module_info->use_category=='Y') {
                $oDocumentModel = &getModel('document');
                Context::set('category_list', $oDocumentModel->getCategoryList($this->module_srl));
            }
        }

        /**
         * @brief 선택된 게시글이 있을 경우 글을 가져와서 스킨에서 사용하도록 세팅
         **/
        function dispProductContentView(){

			// 요청된 변수 값들을 정리
            $document_srl = Context::get('document_srl');
            $page = Context::get('page');

            // document model 객체 생성 
            $oDocumentModel = &getModel('document');
            $oProductModel  = &getModel('product');

            /**
             * 요청된 문서 번호가 있다면 문서를 구함
             **/
            if($document_srl) {

				$oProduct = new productItem();
				$oProduct->setDocument($document_srl);
                $oDocument = $oProduct->oDocument;

                // 해당 문서가 존재할 경우 필요한 처리를 함
                if($oDocument->isExists()) {

                    // 글과 요청된 모듈이 다르다면 오류 표시
                    if($oDocument->get('module_srl')!=$this->module_info->module_srl ) return $this->stop('msg_invalid_request');

                    // 관리 권한이 있다면 권한을 부여
                    if($this->grant->manager) $oDocument->setGrant();
					
					$tmp = $oProductModel->getProduct($document_srl);
					$oProducts = $tmp->data;
					$oFileModel = &getModel("file");
					$oThumbnails = $oFileModel->getFiles($oProducts->product_srl);
					
                } else {
                    Context::set('document_srl','',true);
                    $this->alertMessage('msg_not_founded');
                }

            /**
             * 요청된 문서 번호가 아예 없다면 빈 문서 객체 생성
             **/
            } else {
                $oDocument = $oDocumentModel->getDocument(0);
            }

            /**
             * 글 보기 권한을 체크해서 권한이 없으면 오류 메세지 출력하도록 처리
             **/
            if($oDocument->isExists()) {
                if(!$this->grant->view && !$oDocument->isGranted()) {
                    $oDocument = $oDocumentModel->getDocument(0);
                    Context::set('document_srl','',true);
                    $this->alertMessage('msg_not_permitted');
                } else {
                    // 브라우저 타이틀에 글의 제목을 추가
                    Context::addBrowserTitle($oDocument->getTitleText());

                    // 조회수 증가 (비밀글일 경우 권한 체크)
                    if(!$oDocument->isSecret() || $oDocument->isGranted()) $oDocument->updateReadedCount();

                    // 비밀글일때 컨텐츠를 보여주지 말자.
                    if($oDocument->isSecret() && !$oDocument->isGranted()) $oDocument->add('content',Context::getLang('thisissecret'));
                }
            }

            // 스킨에서 사용할 oDocument 변수 세팅
            $oDocument->add('module_srl', $this->module_srl);
            Context::set('oDocument', $oDocument);
            Context::set('oProduct', $oProduct);
            Context::set('thumbnails', $oThumbnails);

            /** 
             * 사용되는 javascript 필터 추가
             **/
            Context::addJsFilter($this->module_path.'tpl/filter', 'insert_comment.xml');
        
//            return new Object();
        }

        /**
         * @brief 선택된 글이 있을 경우 첨부파일에 대한 정보를 API 에서 사용할 수 있도록 세팅
         **/
        function dispProductContentFileList(){
            $oDocumentModel = &getModel('document');
            $document_srl = Context::get('document_srl');
            $oDocument = $oDocumentModel->getDocument($document_srl);
            Context::set('file_list',$oDocument->getUploadedFiles());
        }

        /**
         * @brief 선택된 글이 있을 경우 그 글의 댓글 목록을 API 에서 사용할 수 있도록 세팅
         **/
        function dispProductContentCommentList(){
            $oDocumentModel = &getModel('document');
            $document_srl = Context::get('document_srl');
            $oDocument = $oDocumentModel->getDocument($document_srl);
            $comment_list = $oDocument->getComments();

            // 비밀글일때 컨텐츠를 보여주지 말자.
            foreach($comment_list as $key => $val){
                if(!$val->isAccessible()){
                    $val->add('content',Context::getLang('thisissecret'));
                }
            }
            Context::set('comment_list',$comment_list);
        }

        /**
         * @brief 공지사항이 있을 경우 API에서 사용할 수 있게 하기 위해서 세팅
         **/
        function dispProductNoticeList(){
            $oDocumentModel = &getModel('document');
            $args->module_srl = $this->module_srl; 
            $notice_output = $oDocumentModel->getNoticeList($args);
            Context::set('notice_list', $notice_output->data);
        }

        /**
         * @brief 게시글 목록
         **/
        function dispProductContentList(){
            // 만약 목록 보기 권한이 없을 경우 목록을 보여주지 않음
            if(!$this->grant->list) {
                Context::set('document_list', array());
                Context::set('total_count', 0);
                Context::set('total_page', 1);
                Context::set('page', 1);
                Context::set('page_navigation', new PageHandler(0,0,1,10));
                return;
            }

            $oDocumentModel = &getModel('document');
            $oProductModel = &getModel('product');

            // 목록을 구하기 위한 대상 모듈/ 페이지 수/ 목록 수/ 페이지 목록 수에 대한 옵션 설정
            $args->module_srl = $this->module_srl; 
            $args->page = Context::get('page');
            $args->list_count = $this->list_count; 
            $args->page_count = $this->page_count; 

            // 검색과 정렬을 위한 변수 설정
            $args->search_target = Context::get('search_target'); 
            $args->search_keyword = Context::get('search_keyword'); 

            // 카테고리를 사용한다면 카테고리 값을 받음
            if($this->module_info->use_category=='Y') $args->category_srl = Context::get('category'); ///< 카테고리 사용시 선택된 카테고리

            // 지정된 정렬값이 없다면 스킨에서 설정한 정렬 값을 이용함
            $args->sort_index = Context::get('sort_index');
            $args->order_type = Context::get('order_type');
            if(!in_array($args->sort_index, $this->order_target)) $args->sort_index = $this->module_info->order_target?$this->module_info->order_target:'list_order';
            if(!in_array($args->order_type, array('asc','desc'))) $args->order_type = $this->module_info->order_type?$this->module_info->order_type:'asc';

            // 특정 문서의 permalink로 직접 접속할 경우 page값을 직접 구함
            $_get = $_GET;
            if(count($_get)==2) unset($_get['mid']);
            if(count($_get)==1 && ($_GET['document_srl'] || $_GET['entry'])) {
                $oDocument = $oDocumentModel->getDocument(Context::get('document_srl'));
                if($oDocument->isExists() && !$oDocument->isNotice()) {
                    $page = $oDocumentModel->getDocumentPage($oDocument, $args);
                    Context::set('page', $page);
                    $args->page = $page;
                }
            }

            if($args->category_srl || $args->search_keyword) $args->list_count = $this->search_list_count;

            $output = $oProductModel->getProductList($args, $this->except_notice);
			
            Context::set('product_list', $output->data);
            Context::set('total_count', $output->total_count);
            Context::set('total_page', $output->total_page);
            Context::set('page', $output->page);
            Context::set('page_navigation', $output->page_navigation);

            $oProductModel = &getModel('product');
            Context::set('list_config', $oProductModel->getListConfig($this->module_info->module_srl));
        }

        /**
         * @brief 글 작성 화면 출력
         **/
        function dispProductWrite() {
            // 권한 체크
            if(!$this->grant->write_document) return $this->dispProductMessage('msg_not_permitted');

            $oDocumentModel = &getModel('document');
            $oProductModel = &getModel('product');

            /**
             * 카테고리를 사용하는지 확인후 사용시 카테고리 목록을 구해와서 Context에 세팅, 권한도 함께 체크
             **/
            if($this->module_info->use_category=='Y') {
                // 로그인한 사용자의 그룹 정보를 구함
                if(Context::get('is_logged')) {
                    $logged_info = Context::get('logged_info');
                    $group_srls = array_keys($logged_info->group_list);
                } else {
                    $group_srls = array();
                }
                $group_srls_count = count($group_srls);

                // 카테고리 목록을 구하고 권한을 체크
                $normal_category_list = $oDocumentModel->getCategoryList($this->module_srl);
                if(count($normal_category_list)) {
                    foreach($normal_category_list as $category_srl => $category) {
                        $is_granted = true;
                        if($category->group_srls) {
                            $category_group_srls = explode(',',$category->group_srls);
                            $is_granted = false;
                            if(count(array_intersect($group_srls, $category_group_srls))) $is_granted = true; 

                        }
                        if($is_granted) $category_list[$category_srl] = $category;
                    }
                }
                Context::set('category_list', $category_list);
            }

            // GET parameter에서 document_srl을 가져옴
            $document_srl = Context::get('document_srl');
            $oDocument = $oDocumentModel->getDocument(0, $this->grant->manager);
            $oDocument->setDocument($document_srl);
            $oDocument->add('module_srl', $this->module_srl);

            // 글을 수정하려고 할 경우 권한이 없는 경우 비밀번호 입력화면으로
            if($oDocument->isExists()&&!$oDocument->isGranted()) return $this->setTemplateFile('input_password_form');
            if(!$oDocument->isExists()) {
                $oModuleModel = &getModel('module');
                $point_config = $oModuleModel->getModulePartConfig('point',$this->module_srl);
                $logged_info = Context::get('logged_info');
                $oPointModel = &getModel('point');
                $pointForInsert = $point_config["insert_document"];
                if($pointForInsert < 0) {
                    if( !$logged_info ) return $this->dispProductMessage('msg_not_permitted');
                    else if (($oPointModel->getPoint($logged_info->member_srl) + $pointForInsert )< 0 ) return $this->dispProductMessage('msg_not_enough_point');
                }
            }

            Context::set('document_srl',$document_srl);
            Context::set('oDocument', $oDocument);
            
            
			Context::set('title_icons', null);
			Context::set('isShopConnected', "1");
            
			// 상품정보 불러오기
			if($document_srl) {
				//$product = $oProductModel->getProduct($document_srl);
				//Context::set('product',$product->data);
				$oProduct = new productItem();
				$oProduct->setDocument($document_srl);
				
				Context::set('product',$oProduct->oProduct);
				Context::set('options',$oProduct->options);
			}

            // 확장변수처리를 위해 xml_js_filter를 직접 header에 적용
            $oDocumentController = &getController('document');
            $oDocumentController->addXmlJsFilter($this->module_info->module_srl);

            // 존재하는 글이면 확장변수 값을 context set
            if($oDocument->isExists()) Context::set('extra_keys', $oDocument->getExtraVars());

            /** 
             * 사용되는 javascript 필터 추가
             **/
            Context::addJsFilter($this->module_path.'tpl/filter', 'insert.xml');
			
			if( $this->grant->manager )
			{
	            $this->setTemplateFile('write_form');
			}
        }
		
		function getThumbManager($upload_target_srl = 0, $master_image)
		{
			$oFileController = &getController('file');
			$oFileController->setUploadInfo("2", $upload_target_srl);
			
			$oTemplate = &TemplateHandler::getInstance();
			Context::set('master_image',$master_image);
			Context::set('file_config',$oFileModel->getFileConfig($module_srl));
            $tpl = $oTemplate->compile($this->module_path.'tpl', 'thumb_manager.html');
			return $tpl;
		}

        /**
         * @brief 문서 삭제 화면 출력
         **/
        function dispProductDelete() {
            // 권한 체크
            if(!$this->grant->write_document) return $this->dispProductMessage('msg_not_permitted');

            // 삭제할 문서번호를 가져온다
            $document_srl = Context::get('document_srl');

            // 지정된 글이 있는지 확인
            if($document_srl) {
                $oDocumentModel = &getModel('document');
                $oDocument = $oDocumentModel->getDocument($document_srl);
            }

            // 삭제하려는 글이 없으면 에러
            if(!$oDocument->isExists()) return $this->dispProductContent();

            // 권한이 없는 경우 비밀번호 입력화면으로
            if(!$oDocument->isGranted()) return $this->setTemplateFile('input_password_form');

            Context::set('oDocument',$oDocument);

            /** 
             * 필요한 필터 추가
             **/
            Context::addJsFilter($this->module_path.'tpl/filter', 'delete_document.xml');

            $this->setTemplateFile('delete_form');
        }

        /**
         * @brief 댓글의 답글 화면 출력
         **/
        function dispProductWriteComment() {
            $document_srl = Context::get('document_srl');

            // 권한 체크
            if(!$this->grant->write_comment) return $this->dispProductMessage('msg_not_permitted');

            // 원본글을 구함
            $oDocumentModel = &getModel('document');
            $oDocument = $oDocumentModel->getDocument($document_srl);
            if(!$oDocument->isExists()) return $this->dispProductMessage('msg_invalid_request');

            // 해당 댓글를 찾아본다 (comment_form을 같이 쓰기 위해서 빈 객체 생성)
            $oCommentModel = &getModel('comment');
            $oSourceComment = $oComment = $oCommentModel->getComment(0);
            $oComment->add('document_srl', $document_srl);
            $oComment->add('module_srl', $this->module_srl);

            // 필요한 정보들 세팅
            Context::set('oDocument',$oDocument);
            Context::set('oSourceComment',$oSourceComment);
            Context::set('oComment',$oComment);

            /** 
             * 필요한 필터 추가
             **/
            Context::addJsFilter($this->module_path.'tpl/filter', 'insert_comment.xml');

            $this->setTemplateFile('comment_form');
        }

        /**
         * @brief 댓글의 답글 화면 출력
         **/
        function dispProductReplyComment() {
            // 권한 체크
            if(!$this->grant->write_comment) return $this->dispProductMessage('msg_not_permitted');

            // 목록 구현에 필요한 변수들을 가져온다
            $parent_srl = Context::get('comment_srl');

            // 지정된 원 댓글이 없다면 오류
            if(!$parent_srl) return new Object(-1, 'msg_invalid_request');

            // 해당 댓글를 찾아본다
            $oCommentModel = &getModel('comment');
            $oSourceComment = $oCommentModel->getComment($parent_srl, $this->grant->manager);

            // 댓글이 없다면 오류
            if(!$oSourceComment->isExists()) return $this->dispProductMessage('msg_invalid_request');
            if(Context::get('document_srl') && $oSourceComment->get('document_srl') != Context::get('document_srl')) return $this->dispProductMessage('msg_invalid_request');

            // 대상 댓글을 생성
            $oComment = $oCommentModel->getComment();
            $oComment->add('parent_srl', $parent_srl);
            $oComment->add('document_srl', $oSourceComment->get('document_srl'));

            // 필요한 정보들 세팅
            Context::set('oSourceComment',$oSourceComment);
            Context::set('oComment',$oComment);
            Context::set('module_srl',$this->module_info->module_srl);

            /** 
             * 사용되는 javascript 필터 추가
             **/
            Context::addJsFilter($this->module_path.'tpl/filter', 'insert_comment.xml');

            $this->setTemplateFile('comment_form');
        }

        /**
         * @brief 댓글 수정 폼 출력
         **/
        function dispProductModifyComment() {
            // 권한 체크
            if(!$this->grant->write_comment) return $this->dispProductMessage('msg_not_permitted');

            // 목록 구현에 필요한 변수들을 가져온다
            $document_srl = Context::get('document_srl');
            $comment_srl = Context::get('comment_srl');

            // 지정된 댓글이 없다면 오류
            if(!$comment_srl) return new Object(-1, 'msg_invalid_request');

            // 해당 댓글를 찾아본다
            $oCommentModel = &getModel('comment');
            $oComment = $oCommentModel->getComment($comment_srl, $this->grant->manager);

            // 댓글이 없다면 오류
            if(!$oComment->isExists()) return $this->dispProductMessage('msg_invalid_request');

            // 글을 수정하려고 할 경우 권한이 없는 경우 비밀번호 입력화면으로
            if(!$oComment->isGranted()) return $this->setTemplateFile('input_password_form');

            // 필요한 정보들 세팅
            Context::set('oSourceComment', $oCommentModel->getComment());
            Context::set('oComment', $oComment);

            /** 
             * 사용되는 javascript 필터 추가
             **/
            Context::addJsFilter($this->module_path.'tpl/filter', 'insert_comment.xml');

            $this->setTemplateFile('comment_form');
        }

        /**
         * @brief 댓글 삭제 화면 출력
         **/
        function dispProductDeleteComment() {
            // 권한 체크
            if(!$this->grant->write_comment) return $this->dispProductMessage('msg_not_permitted');

            // 삭제할 댓글번호를 가져온다
            $comment_srl = Context::get('comment_srl');

            // 삭제하려는 댓글이 있는지 확인
            if($comment_srl) {
                $oCommentModel = &getModel('comment');
                $oComment = $oCommentModel->getComment($comment_srl, $this->grant->manager);
            }

            // 삭제하려는 글이 없으면 에러
            if(!$oComment->isExists() ) return $this->dispProductContent();

            // 권한이 없는 경우 비밀번호 입력화면으로
            if(!$oComment->isGranted()) return $this->setTemplateFile('input_password_form');

            Context::set('oComment',$oComment);

            /** 
             * 필요한 필터 추가
             **/
            Context::addJsFilter($this->module_path.'tpl/filter', 'delete_comment.xml');

            $this->setTemplateFile('delete_comment_form');
        }

        /**
         * @brief 엮인글 삭제 화면 출력
         **/
        function dispProductDeleteTrackback() {
            // 삭제할 댓글번호를 가져온다
            $trackback_srl = Context::get('trackback_srl');

            // 삭제하려는 댓글가 있는지 확인
            $oTrackbackModel = &getModel('trackback');
            $output = $oTrackbackModel->getTrackback($trackback_srl);
            $trackback = $output->data;

            // 삭제하려는 글이 없으면 에러
            if(!$trackback) return $this->dispProductContent();

            Context::set('trackback',$trackback);

            /** 
             * 필요한 필터 추가
             **/
            Context::addJsFilter($this->module_path.'tpl/filter', 'delete_trackback.xml');

            $this->setTemplateFile('delete_trackback_form');
        }

        /**
         * @brief 메세지 출력
         **/
        function dispProductMessage($msg_code) {
            $msg = Context::getLang($msg_code);
            if(!$msg) $msg = $msg_code;
            Context::set('message', $msg);
            $this->setTemplateFile('message');
        }

        /**
         * @brief 오류메세지를 system alert로 출력하는 method
         * 특별한 오류를 알려주어야 하는데 별도의 디자인까지는 필요 없을 경우 페이지를 모두 그린후에
         * 오류를 출력하도록 함
         **/
        function alertMessage($message) {
            $script =  sprintf('<script type="text/javascript"> xAddEventListener(window,"load", function() { alert("%s"); } );</script>', Context::getLang($message));
            Context::addHtmlHeader( $script );
        }

        /**
         * @brief 이미지추가
         **/
        function dispProductInsertImage() {
            // 요청된 변수 값들을 정리
            $document_srl = Context::get('document_srl');
            $page = Context::get('page');
			
			if($document_srl)
			{
				$oProductModel = &getModel("product");
				$image_output = $oProductModel->getProductImageByDocumentSrl($document_srl);
				Context::set('images_info', $image_output->data);
			}

            /** 
             * 필요한 필터 추가
             **/
            Context::addJsFilter($this->module_path.'tpl/filter', 'update_image.xml');
			Context::set('document_srl',$document_srl);
			
			$this->setTemplateFile('insert_image');
		}
		
        /**
         * @brief 옵션추가
         **/
        function dispProductInsertOption() {
            // 요청된 변수 값들을 정리
            $document_srl = Context::get('document_srl');
            $page = Context::get('page');
			
			if($document_srl)
			{
				$oProductModel = &getModel("product");
				$option_output = $oProductModel->getProductOptionByDocumentSrl($document_srl);

				Context::set('options', $option_output);
			}

            /** 
             * 필요한 필터 추가
             **/
            Context::addJsFilter($this->module_path.'tpl/filter', 'insert_option.xml');
            Context::addJsFilter($this->module_path.'tpl/filter', 'delete_option.xml');
			Context::set('document_srl',$document_srl);
			$this->setTemplateFile('insert_option');
		}

        /**
         * @brief 관리자가 선택한 문서에 대한 관리
         **/
        function dispProdcutManageDocument() {
            if(!Context::get('is_logged')) return new Object(-1,'msg_not_permitted');

            // 선택한 목록을 세션에서 가져옴
            $flag_list = $_SESSION['document_management'];
            if(count($flag_list)) {
                foreach($flag_list as $key => $val) {
                    if(!is_bool($val)) continue;
                    $document_srl_list[] = $key;
                }
            }

            if(count($document_srl_list)) {
                $oDocumentModel = &getModel('document');
                $document_list = $oDocumentModel->getDocuments($document_srl_list, $this->grant->is_admin);
                Context::set('document_list', $document_list);
            }

            $oModuleModel = &getModel('module');

            // 모듈 카테고리 목록과 모듈 목록의 조합
            if(count($module_list)>1) Context::set('module_list', $module_categories);

            // 팝업 레이아웃 선택
            $this->setLayoutPath('./common/tpl');
            $this->setLayoutFile('popup_layout');

            $this->setTemplatePath($this->module_path.'tpl');
            $this->setTemplateFile('checked_list');
        }


    }
?>
