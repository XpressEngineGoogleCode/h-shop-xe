<?php
    /**
     * @class  productController
     * @author 덧니희야 (deive@nate.com)
     * @brief  product 모듈의 Controller class
     **/

    class productController extends product {

        /**
         * @brief 초기화
         **/
        function init() {
        }

        /**
         * @brief 문서 입력
         **/
        function procProductInsertDocument() {
            // 권한 체크
            if(!$this->grant->write_document) return new Object(-1, 'msg_not_permitted');

            // 글작성시 필요한 변수를 세팅
            $obj = Context::getRequestVars();
            $obj->module_srl = $this->module_srl;
            if($obj->is_notice!='Y'||!$this->grant->manager) $obj->is_notice = 'N';

            settype($obj->title, "string");
            if($obj->title == '') $obj->title = cut_str(strip_tags($obj->content),20,'...');
            //그래도 없으면 Untitled
            if($obj->title == '') $obj->title = 'Untitled';

            // 관리자가 아니라면 게시글 색상/굵기 제거
            if(!$this->grant->manager) {
                unset($obj->title_color);
                unset($obj->title_bold);
            }

            // document module의 model 객체 생성
            $oDocumentModel = &getModel('document');
			$oProductModel = &getModel('product');

            // document module의 controller 객체 생성
            $oDocumentController = &getController('document');
			$oDocument = $oDocumentModel->getDocument($obj->document_srl, $this->grant->manager);
			
			// 옵션값 변환하기 2010.3.10
			if( $obj->option_name )
			{
				$obj->option = $this->convOptionFormToArray( $obj );
			}
			
			if($oDocument->isExists() && $oDocument->document_srl == $obj->document_srl)
			{
				//현재 존재시에..
				if(!$oDocument->isGranted()) return new Object(-1,'msg_not_permitted');
				$oProduct = $oProductModel->getProduct($obj->document_srl);
				$obj->product_srl = $oProduct->data->product_srl;

				if( $obj->thumb_sequence != null &&  $obj->module_srl != null && $obj->product_srl != null )
				{
					$oFileController = &getController('file');
					$oFileController->moveFile($obj->thumb_sequence, $obj->module_srl, $obj->product_srl);
					$oFileController->setFilesValid($obj->product_srl);
				}

				$this->updateProduct($obj);
				$this->procProductInsertOption($obj->option, $obj->document_srl);
//return new Object( -1,$this->procProductInsertOption($obj->option, $obj->document_srl) );
//return new Object(-1,$obj->document_srl);

                $output = $oDocumentController->updateDocument($oDocument, $obj);
				//기존 썸네일 삭제
				$path = "./files/cache/thumbnails/".getNumberingPath($oProduct->data->product_srl,2)."/";
				FileHandler::removeDir($path);
                $msg_code = 'success_updated';
			}
			else
			{
				$obj->product_srl = getNextSequence();
				$temp = $oDocumentController->insertDocument($obj);
				if( $obj->thumb_sequence != null &&  $obj->module_srl != null && $obj->product_srl != null )
				{
					$oFileController = &getController('file');
					$oFileController->moveFile($obj->thumb_sequence, $obj->module_srl, $obj->product_srl);
					$oFileController->setFilesValid($obj->product_srl);
				}
				$output = $this->insertProduct($obj, $temp->get('document_srl'));
				$this->procProductInsertOption($obj->option, $temp->get('document_srl'));
				$msg_code = 'success_registed';
			}
			if(!$output->toBool()) return $output;
            $this->add('mid', Context::get('mid'));
            $this->add('product_srl', $output->get('document_srl'));
            // 성공 메세지 등록
            $this->setMessage($msg_code);
        }

        /**
         * @brief 문서 삭제
         **/
        function procProductDeleteDocument() {
            // 문서 번호 확인
            $document_srl = Context::get('document_srl');

            // 문서 번호가 없다면 오류 발생
            if(!$document_srl) return $this->doError('msg_invalid_document');

            // document module model 객체 생성
            $oDocumentController = &getController('document');

			// 이미지 삭제
			$this->deleteProductImageByDocumentSrl($document_srl);
			
			// 옵션 삭제
			$this->procProductDeleteOptionDocumentSrl( $document_srl );

			// 상품 삭제
			$this->procProductDelete($document_srl);
            // 도큐먼트 삭제
            $output = $oDocumentController->deleteDocument($document_srl, $this->grant->manager);
            if(!$output->toBool()) return $output;
			
            // 성공 메세지 등록
            $this->add('mid', Context::get('mid'));
            $this->add('page', $output->get('page'));
            $this->setMessage('success_deleted');
        }

        /**
         * @brief 추천
         **/
        function procProductVoteDocument() {
            // document module controller 객체 생성
            $oDocumentController = &getController('document');

            $document_srl = Context::get('document_srl');
            return $oDocumentController->updateVotedCount($document_srl);
        }

        /**
         * @brief 문서와 댓글의 비밀번호를 확인
         **/
        function procProductVerificationPassword() {
            // 비밀번호와 문서 번호를 받음
            $password = Context::get('password');
            $document_srl = Context::get('document_srl');
            $comment_srl = Context::get('comment_srl');

            $oMemberModel = &getModel('member');

            // comment_srl이 있을 경우 댓글이 대상
            if($comment_srl) {
                // 문서번호에 해당하는 글이 있는지 확인
                $oCommentModel = &getModel('comment');
                $oComment = $oCommentModel->getComment($comment_srl);
                if(!$oComment->isExists()) return new Object(-1, 'msg_invalid_request');

                // 문서의 비밀번호와 입력한 비밀번호의 비교
                if(!$oMemberModel->isValidPassword($oComment->get('password'),$password)) return new Object(-1, 'msg_invalid_password');

                $oComment->setGrant();
            } else {
                // 문서번호에 해당하는 글이 있는지 확인
                $oDocumentModel = &getModel('document');
                $oDocument = $oDocumentModel->getDocument($document_srl);
                if(!$oDocument->isExists()) return new Object(-1, 'msg_invalid_request');

                // 문서의 비밀번호와 입력한 비밀번호의 비교
                if(!$oMemberModel->isValidPassword($oDocument->get('password'),$password)) return new Object(-1, 'msg_invalid_password');

                $oDocument->setGrant();
            }
        }

        /**
         * @brief 아이디 클릭시 나타나는 팝업메뉴에 "작성글 보기" 메뉴를 추가하는 trigger
         **/
        function triggerMemberMenu(&$obj) {
            $member_srl = Context::get('target_srl');
            $mid = Context::get('cur_mid');

            if(!$member_srl || !$mid) return new Object();

            $logged_info = Context::get('logged_info');

            // 호출된 모듈의 정보 구함
            $oModuleModel = &getModel('module');
            $cur_module_info = $oModuleModel->getModuleInfoByMid($mid);

            if($cur_module_info->module != 'product') return new Object();

            // 자신의 아이디를 클릭한 경우
            if($member_srl == $logged_info->member_srl) {
                $member_info = $logged_info;
            } else {
                $oMemberModel = &getModel('member');
                $member_info = $oMemberModel->getMemberInfoByMemberSrl($member_srl);
            }

            if(!$member_info->user_id) return new Object();

            // 아이디로 검색기능 추가
            $url = getUrl('','mid',$mid,'search_target','user_id','search_keyword',$member_info->user_id);
            $oMemberController = &getController('member');
            $oMemberController->addMemberPopupMenu($url, 'cmd_view_own_document', './modules/member/tpl/images/icon_view_written.gif');

            return new Object();
        }
		
        /**
         * @brief 상품정보 입력
         **/
        function insertProduct($obj, $document_srl) {
			$obj->target_srl = $document_srl;
			$obj->document_srl = $document_srl;
			$output = executeQuery('product.insertProduct', $obj);
            return $output;
        }
		
        /**
         * @brief 상품정보 수정
         **/
        function updateProduct($obj) {
			$obj->target_srl = $document_srl;
			$output = executeQuery('product.updateProduct', $obj);
            return $output;
		}
		
        /**
         * @brief 문서번호로 상품이미지 삭제
         **/
        function deleteProductImageByDocumentSrl($document_srl) {
		
			if($document_srl)
			{
	            $oProductModel = &getModel('product');
				$product = $oProductModel->getProduct($document_srl);

				if( $product->data != null )
				{
					$product_info = $product->data;
					$oFileModel = &getModel("file");
					$oFileController = &getController('file');
					$files = $oFileModel->getFiles($product_info->product_srl);

					if( $files != null ){
						foreach($files as $key => $val) {
							$oFileController->deleteFile($val->file_srl);
						}
					}
				}
			}else{
			}
			
        }
		
        /**
         * @brief 이미지번호로 상품이미지 삭제
         **/
        function deleteProductImageByImageSrl($image_srl) {
			if($image_srl)
			{
				$oProductModel = &getModel("product");
				$image = $oProductModel->getProductImageByImageSrl($image_srl);
				FileHandler::removeFile($image->file_path);
				
				// 썸네일도 삭제
				$path = "./files/product/image/".$image->document_srl."/thumbnail/";
				$file = strrchr($image->file_path,'/');
				$thumbnail_image = $path . $file;
				FileHandler::removeFile($thumbnail_image);
				
				$args->image_srl = $image_srl;
				executeQuery('product.deleteProductImage', $args);
			}
        }
		
        /**
         * @brief 상품삭제
         **/
        function procProductDelete($document_srl) {
			$args->target_srl = $document_srl;
			executeQuery('product.deleteProduct', $args);
		}		

        /**
         * @brief 코멘트 입력
         **/
        function procProductInsertComment() {
            // 권한 체크
            if(!$this->grant->write_comment) return new Object(-1, 'msg_not_permitted');

            // 댓글 입력에 필요한 데이터 추출
            $obj = Context::gets('document_srl','comment_srl','parent_srl','content','password','nick_name','nick_name','member_srl','email_address','homepage','is_secret','notify_message');
            $obj->module_srl = $this->module_srl;

            // 원글이 존재하는지 체크
            $oDocumentModel = &getModel('document');
            $oDocument = $oDocumentModel->getDocument($obj->document_srl);
            if(!$oDocument->isExists()) return new Object(-1,'msg_not_permitted');

            // 익명 설정일 경우 여러가지 요소를 미리 제거 (알림용 정보들 제거)
            if($this->module_info->use_anonymous == 'Y') {
                $obj->notify_message = 'N';
                $this->module_info->admin_mail = '';
            }

            // comment 모듈의 model 객체 생성
            $oCommentModel = &getModel('comment');

            // comment 모듈의 controller 객체 생성
            $oCommentController = &getController('comment');

            // comment_srl이 존재하는지 체크
            // 만일 comment_srl이 n/a라면 getNextSequence()로 값을 얻어온다.
            if(!$obj->comment_srl) {
                $obj->comment_srl = getNextSequence();
            } else {
                $comment = $oCommentModel->getComment($obj->comment_srl, $this->grant->manager);
            }

            // comment_srl이 없을 경우 신규 입력
            if($comment->comment_srl != $obj->comment_srl) {

                // parent_srl이 있으면 답변으로
                if($obj->parent_srl) {
                    $parent_comment = $oCommentModel->getComment($obj->parent_srl);
                    if(!$parent_comment->comment_srl) return new Object(-1, 'msg_invalid_request');

                    $output = $oCommentController->insertComment($obj);

                // 없으면 신규
                } else {
                    $output = $oCommentController->insertComment($obj);
                }

                // 문제가 없고 모듈 설정에 관리자 메일이 등록되어 있으면 메일 발송
                if($output->toBool() && $this->module_info->admin_mail) {
                    $oMail = new Mail();
                    $oMail->setTitle($oDocument->getTitleText());
                    $oMail->setContent( sprintf("From : <a href=\"%s#comment_%d\">%s#comment_%d</a><br/>\r\n%s", getFullUrl('','document_srl',$obj->document_srl),$obj->comment_srl, getFullUrl('','document_srl',$obj->document_srl), $obj->comment_srl, $obj->content));
                    $oMail->setSender($obj->user_name, $obj->email_address);

                    $target_mail = explode(',',$this->module_info->admin_mail);
                    for($i=0;$i<count($target_mail);$i++) {
                        $email_address = trim($target_mail[$i]);
                        if(!$email_address) continue;
                        $oMail->setReceiptor($email_address, $email_address);
                        $oMail->send();
                    }
                }

            // comment_srl이 있으면 수정으로
            } else {
				// 다시 권한체크
				if(!$comment->isGranted()) return new Object(-1,'msg_not_permitted');

                $obj->parent_srl = $comment->parent_srl;
                $output = $oCommentController->updateComment($obj, $this->grant->manager);
                $comment_srl = $obj->comment_srl;
            }
            if(!$output->toBool()) return $output;

            // 익명 사용시 글의 글쓴이 정보를 모두 제거 
            if($this->module_info->use_anonymous == 'Y' && Context::get('is_logged')) {
                $logged_info = Context::get('logged_info');
                $comment = $oCommentModel->getComment($output->get('comment_srl'), $this->grant->manager);
                $obj = $comment->getObjectVars();
                $obj->member_srl = -1*$logged_info->member_srl;
                $obj->email_address = $obj->homepage = $obj->user_id = '';
                $obj->user_name = $obj->nick_name = 'anonymous';
                $output = executeQuery('comment.updateComment', $obj);
                if(!$output->toBool()) return $output;
            }

            $this->setMessage('success_registed');
            $this->add('mid', Context::get('mid'));
            $this->add('document_srl', $obj->document_srl);
            $this->add('comment_srl', $obj->comment_srl);
		}		
		
        /**
         * @brief 코멘트 삭제
         **/
        function procProductDeleteComment() {
            // 댓글 번호 확인
            $comment_srl = Context::get('comment_srl');
            if(!$comment_srl) return $this->doError('msg_invalid_request');

            // comment 모듈의 controller 객체 생성
            $oCommentController = &getController('comment');

            $output = $oCommentController->deleteComment($comment_srl, $this->grant->manager);
            if(!$output->toBool()) return $output;

            $this->add('mid', Context::get('mid'));
            $this->add('page', Context::get('page'));
            $this->add('document_srl', $output->get('document_srl'));
            $this->setMessage('success_deleted');
        }
		
		
        /**
         * @brief 옵션관련
         **/
        function convOptionFormToArray( $obj ) {
			$option->option_type = explode( "|@|", $obj->option_type );
			$option->option_srl = explode( "|@|", $obj->option_srl_a );
			$option->option_parent_srl = explode( "|@|", $obj->option_parent_srl_a );
			$option->option_name = explode( "|@|", $obj->option_name );
			$option->option_price = explode( "|@|", $obj->option_price );
			$option->option_req = explode( "|@|", $obj->option_req );
			$option->at_index = explode( "|@|", $obj->at_index );
			return $option;
		}

        /**
         * @brief 문서에 옵션추가
         **/
        function procProductInsertOption( $obj_option, $document_srl ) {
			// 값받아오기
			$mid = Context::get('mid');
            // module model 객체 생성 
            $oModuleModel = &getModel('module');
            // module_srl이 넘어오면 해당 모듈의 정보를 미리 구해 놓음
            if($module_srl) {
                $module_info = $oModuleModel->getModuleInfoByMid($mid);
                if($module_info) {
					$this->module_info = $module_info;
                } 
            }
			
			if( !$obj_option )
			{
				$this->procProductDeleteOptionDocumentSrl( $document_srl );
				return true;
			}
			
			//없는항목 삭제
			$args->document_srl = $document_srl;
			$output = executeQuery('product.getProductOption', $args);
			if( $output->data )
			{
				foreach($output->data as $key => $val)
				{
					if( !in_array( (int)$val->option_srl, $obj_option->option_srl ) )
					{
						$this->procProductDeleteOption( $val->option_srl );
					}
				}
			}

			foreach( $obj_option->option_name as $key => $value )
			{
				$args = null;
				$args->option_srl = getNextSequence();
				$args->document_srl = $document_srl;
				$args->option_parent_srl = $obj_option->option_parent_srl[$key];
				$args->option_name = $obj_option->option_name[$key];
				$args->option_price = $obj_option->option_price[$key];
				if( $obj_option->option_req[$key] == "on" ){
					$args->option_req = "Y";
				}else{
					$args->option_req = "N";
				}
				$args->at_index	= $obj_option->at_index[$key];
				
				if( $obj_option->option_name[$key] != "" ){
					if( $obj_option->option_srl[$key] == 0 || $obj_option->option_srl[$key] == null )
					{
						// 추가
						if( $obj_option->option_type[$key] == "item" && $tmp_value ) $args->option_parent_srl = $tmp_value;
						$output = executeQuery('product.insertProductOption', $args);
						if( $obj_option->option_type[$key] == "option" ) $tmp_value = $args->option_srl;
					}else{
						// 업데이트
						$args->option_srl = $obj_option->option_srl[$key];
						executeQuery('product.updateProductOption', $args);
					}
				}
				
			}
			return true;
		}		

        /**
         * @brief 옵션삭제
         **/
        function procProductDeleteOption( $option_srl ) {
			// 값받아오기
			$args->option_srl = $option_srl;
			executeQuery('product.deleteProductOption', $args);
		}
		
        /**
         * @brief 옵션삭제 (문서번호로)
         **/
        function procProductDeleteOptionDocumentSrl( $document_srl ) {
			// 값받아오기
			$args->document_srl = $document_srl;
			executeQuery('product.deleteProductOptionDocumentSrl', $args);
		}
		
        /**
         * @brief 문서번호로 옵션삭제
         **/
        function procProductDeleteOptionByDocumentSrl($document_srl) {
			// 값받아오기
			if($document_srl){
				$args->document_srl = $document_srl;
				executeQuery('product.deleteProductOption', $args);
			}
		}		
		
    }
?>
