<?php
    /**
     * @class  productModel
     * @author 덧니희야 (deive@nate.com)
     * @brief  product 모듈의 Model class
     **/

    class productModel extends module {
        /**
         * @brief 초기화
         **/
        function init() {
        }

        /**
         * @brief 목록 설정 값을 가져옴
         **/
        function getListConfig($module_srl) {
            $oModuleModel = &getModel('module');
            $oDocumentModel = &getModel('document');

            // 저장된 목록 설정값을 구하고 없으면 기본 값으로 설정
            $list_config = $oModuleModel->getModulePartConfig('product', $module_srl);
            if(!$list_config || !count($list_config)) $list_config = array( 'no', 'product_img', 'product_name', 'price_sale', 'readed_count');

            // 사용자 선언 확장변수 구해와서 배열 변환후 return
            $inserted_extra_vars = $oDocumentModel->getExtraKeys($module_srl);

            foreach($list_config as $key) {
                if(preg_match('/^([0-9]+)$/',$key)) $output['extra_vars'.$key] = $inserted_extra_vars[$key];
                else $output[$key] = new ExtraItem($module_srl, -1, Context::getLang($key), $key, 'N', 'N', 'N', null);
            }
            return $output;
        }

        /** 
         * @brief 기본 목록 설정값을 return
         **/
        function getDefaultListConfig($module_srl) {
            // 가상번호, 제목, 등록일, 수정일, 닉네임, 아이디, 이름, 조회수, 추천수 추가
            $virtual_vars = array( 'no', 'product_img', 'product_name', 'price_sale', 'regdate', 'last_update', 'last_post', 'readed_count', 'voted_count', 'product_maker', 'product_brand', 'product_origin');
            foreach($virtual_vars as $key) {
                $extra_vars[$key] = new ExtraItem($module_srl, -1, Context::getLang($key), $key, 'N', 'N', 'N', null);
            }

            // 사용자 선언 확장변수 정리
            $oDocumentModel = &getModel('document');
            $inserted_extra_vars = $oDocumentModel->getExtraKeys($module_srl);

            if(count($inserted_extra_vars)) foreach($inserted_extra_vars as $obj) $extra_vars['extra_vars'.$obj->idx] = $obj;

            return $extra_vars;

        }

		function getProductList($args, $except_notice)
		{
			$output = $this->getDocumentList($args, $except_notice);
			
			if(!$output->data ) //결과가없을땐..
				return $output;
			else
			{
				foreach($output->data as $key => $val) {
					$productItem = new productItem();
					$productItem->setDocumentObject($val);
					$output->data[$key] = $productItem;
				}
			}
			return $output;
		}

		function getProductByDocumentSrl($document_srl)
		{
			$args->document_srl = $document_srl;
			$output = executeQuery("product.getProductByDocumentSrl", $args);
			return $output;
		}

		function getProductItemByDocumentSrl($document_srl)
		{
			$item = new productItem();
			$item->setDocument($document_srl);
			return $item;
		}

		function getProductByProductSrl($product_srl)
		{
			$args->product_srl = $product_srl;
			$output = executeQuery("product.getProductByProductSrl", $args);
			if(!$output->data) return null;
			$tmp = $output->data;
			$pItem = new productItem();
			$pItem->setDocument($tmp->target_srl);
			return $pItem;
		}
	
		/**
		 * @brief  상품정보 ($document_srl)
		 **/
		function getProduct($document_srl)
		{
			$args->document_srl = $document_srl;
			$output = executeQuery('product.getProduct', $args);
			return $output;
		}

		/**
		 * @brief  상품의 옵션 (document_srl)
		 **/
		function getProductOptionByDocumentSrl($document_srl)
		{
			$args->document_srl = $document_srl;
			$output = executeQueryArray('product.getProductOption', $args);
			// 출력순서 정렬
			if( $output->data != null ){
				$tmp_count = 0;
				foreach($output->data as $no => $val)
				{
					if($val->option_parent_srl==0) {
						$options[$tmp_count] = $val;
						$tmp_count++;
					}
				}
				foreach($options as $key => $val1)
				{
					foreach($output->data as $no => $val2)
					{
						if( $val1->option_srl == $val2->option_parent_srl ) {
							$options[$key]->item[$no] = $val2;
						}
					}
				}
			}
			return $options;
		}

		/**
         * @brief module_srl값을 가지는 문서의 목록을 가져옴
         **/
        function getDocumentList($obj, $except_notice = false, $load_extra_vars=true) {
            $oDocumentModel = &getModel('document');
            // 정렬 대상과 순서 체크
            if(!in_array($obj->sort_index, array('list_order','regdate','last_update','update_order','readed_count','voted_count','comment_count','trackback_count','uploaded_count','title','category_srl','price_sale'))) $obj->sort_index = 'list_order';
            if(!in_array($obj->order_type, array('desc','asc'))) $obj->order_type = 'asc';

            // module_srl 대신 mid가 넘어왔을 경우는 직접 module_srl을 구해줌
            if($obj->mid) {
                $oModuleModel = &getModel('module');
                $obj->module_srl = $oModuleModel->getModuleSrlByMid($obj->mid);
                unset($obj->mid);
            }

            // 넘어온 module_srl은 array일 수도 있기에 array인지를 체크
            if(is_array($obj->module_srl)) $args->module_srl = implode(',', $obj->module_srl);
            else $args->module_srl = $obj->module_srl;

            // 제외 module_srl에 대한 검사
            if(is_array($obj->exclude_module_srl)) $args->exclude_module_srl = implode(',', $obj->exclude_module_srl);
            else $args->exclude_module_srl = $obj->exclude_module_srl;

            // 변수 체크
            $args->category_srl = $obj->category_srl?$obj->category_srl:null;
            $args->sort_index = $obj->sort_index;
            $args->order_type = $obj->order_type;
            $args->page = $obj->page?$obj->page:1;
            $args->list_count = $obj->list_count?$obj->list_count:20;
            $args->page_count = $obj->page_count?$obj->page_count:10;
            $args->start_date = $obj->start_date?$obj->start_date:null;
            $args->end_date = $obj->end_date?$obj->end_date:null;
            $args->member_srl = $obj->member_srl;

            // 카테고리가 선택되어 있으면 하부 카테고리까지 모두 조건에 추가
            if($args->category_srl) {
                $category_list = $oDocumentModel->getCategoryList($args->module_srl);
                $category_info = $category_list[$args->category_srl];
                $category_info->childs[] = $args->category_srl;
                $args->category_srl = implode(',',$category_info->childs);
            }

            // 기본으로 사용할 query id 지정 (몇가지 검색 옵션에 따라 query id가 변경됨)
			if( $obj->sort_index == 'price_sale' )
			{
	            $query_id = 'product.getDocumentListByProducts';
			}else{
	            $query_id = 'document.getDocumentList';
			}

            // 내용검색일 경우 document division을 지정하여 검색하기 위한 처리
            $use_division = false;

            // 검색 옵션 정리
            $search_target = $obj->search_target;
            $search_keyword = $obj->search_keyword;
            if($search_target && $search_keyword) {
                switch($search_target) {
                    case 'title' :
                    case 'content' :
                            if($search_keyword) $search_keyword = str_replace(' ','%',$search_keyword);
                            $args->{"s_".$search_target} = $search_keyword;
                            $use_division = true;
                        break;
                    case 'title_content' :
                            if($search_keyword) $search_keyword = str_replace(' ','%',$search_keyword);
                            $args->s_title = $search_keyword;
                            $args->s_content = $search_keyword;
                            $use_division = true;
                        break;
                    case 'user_id' :
                            if($search_keyword) $search_keyword = str_replace(' ','%',$search_keyword);
                            $args->s_user_id = $search_keyword;
                            $args->sort_index = 'documents.'.$args->sort_index;
                        break;
                    case 'user_name' :
                    case 'nick_name' :
                    case 'email_address' :
                    case 'homepage' :
                            if($search_keyword) $search_keyword = str_replace(' ','%',$search_keyword);
                            $args->{"s_".$search_target} = $search_keyword;
                        break;
                    case 'is_notice' :
                    case 'is_secret' :
                            if($search_keyword=='N') $args->{"s_".$search_target} = 'N';
                            elseif($search_keyword=='Y') $args->{"s_".$search_target} = 'Y';
                            else $args->{"s_".$search_target} = '';
                        break;
                    case 'member_srl' :
                    case 'readed_count' :
                    case 'voted_count' :
                    case 'comment_count' :
                    case 'trackback_count' :
                    case 'uploaded_count' :
                            $args->{"s_".$search_target} = (int)$search_keyword;
                        break;
                    case 'regdate' :
                    case 'last_update' :
                    case 'ipaddress' :
                            $args->{"s_".$search_target} = $search_keyword;
                        break;
                    case 'comment' :
                            $args->s_comment = $search_keyword;
                            $query_id = 'document.getDocumentListWithinComment';
                            $use_division = true;
                        break;
                    case 'tag' :
                            $args->s_tags = str_replace(' ','%',$search_keyword);
                            $query_id = 'document.getDocumentListWithinTag';
                        break;
                    default :
                            if(strpos($search_target,'extra_vars')!==false) {
                                $args->var_idx = substr($search_target, strlen('extra_vars'));
                                $args->var_value = str_replace(' ','%',$search_keyword);
                                $args->sort_index = 'documents.'.$args->sort_index;
                                $query_id = 'document.getDocumentListWithExtraVars';
                            }
                        break;
                }
            }

            /**
             * division은 list_order의 asc 정렬일때만 사용할 수 있음
             **/
            if($args->sort_index != 'list_order' || $args->order_type != 'asc') $use_division = false;

            /**
             * 만약 use_division이 true일 경우 document division을 이용하도록 변경
             **/
            if($use_division) {
                // 시작 division
                $division = (int)Context::get('division');

                // division값이 없다면 제일 상위
                if(!$division) {
                    $division_args->module_srl = $args->module_srl;
                    $division_args->exclude_module_srl = $args->exclude_module_srl;
                    $division_args->list_count = 1;
                    $division_args->sort_index = $args->sort_index;
                    $division_args->order_type = $args->order_type;
                    $output = executeQuery("document.getDocumentList", $division_args);
                    if($output->data) {
                        $item = array_pop($output->data);
                        $division = $item->list_order;
                    }
                    $division_args = null;
                }

                // 마지막 division
                $last_division = (int)Context::get('last_division');

                // 지정된 division에서부터 5000개 후의 division값을 구함
                if(!$last_division) {
                    $last_division_args->module_srl = $args->module_srl;
                    $last_division_args->exclude_module_srl = $args->exclude_module_srl;
                    $last_division_args->list_count = 1;
                    $last_division_args->sort_index = $args->sort_index;
                    $last_division_args->order_type = $args->order_type;
                    $last_division_args->list_order = $division;
                    $last_division_args->page = 5001;
                    $output = executeQuery("document.getDocumentDivision", $last_division_args);
                    if($output->data) {
                        $item = array_pop($output->data);
                        $last_division = $item->list_order;
                    }

                }

                // last_division 이후로 글이 있는지 확인
                if($last_division) {
                    $last_division_args = null;
                    $last_division_args->module_srl = $args->module_srl;
                    $last_division_args->exclude_module_srl = $args->exclude_module_srl;
                    $last_division_args->list_order = $last_division;
                    $output = executeQuery("document.getDocumentDivisionCount", $last_division_args);
                    if($output->data->count<1) $last_division = null;
                }

                $args->division = $division;
                $args->last_division = $last_division;
                Context::set('division', $division);
                Context::set('last_division', $last_division);
            }

            // document.getDocumentList 쿼리 실행
            // 만약 query_id가 getDocumentListWithinComment 또는 getDocumentListWithinTag일 경우 group by 절 사용 때문에 쿼리를 한번더 수행
            if(in_array($query_id, array('document.getDocumentListWithinComment', 'document.getDocumentListWithinTag'))) {
                $group_args = clone($args);
                $group_args->sort_index = 'documents.'.$args->sort_index;
                $output = executeQueryArray($query_id, $group_args);
                if(!$output->toBool()||!count($output->data)) return $output;

                foreach($output->data as $key => $val) {
                    if($val->document_srl) $target_srls[] = $val->document_srl;
                }

                $page_navigation = $output->page_navigation;
                $keys = array_keys($output->data);
                $virtual_number = $keys[0];

                $target_args->document_srls = implode(',',$target_srls);
                $target_args->list_order = $args->sort_index;
                $target_args->order_type = $args->order_type;
                $target_args->list_count = $args->list_count;
                $target_args->page = 1;
                $output = executeQueryArray('document.getDocuments', $target_args);
                $output->page_navigation = $page_navigation;
                $output->total_count = $page_navigation->total_count;
                $output->total_page = $page_navigation->total_page;
                $output->page = $page_navigation->cur_page;
            } else {
                $output = executeQueryArray($query_id, $args);
            }
			
            // 결과가 없거나 오류 발생시 그냥 return
            if(!$output->toBool()||!count($output->data)) return $output;

            $idx = 0;
            $data = $output->data;
            unset($output->data);

            if(!isset($virtual_number))
            {
                $keys = array_keys($data);
                $virtual_number = $keys[0];
            }

            if($except_notice) {
                foreach($data as $key => $attribute) {
                    if($attribute->is_notice == 'Y') $virtual_number --;
                }
            }

            foreach($data as $key => $attribute) {
                if($except_notice && $attribute->is_notice == 'Y') continue;
                $document_srl = $attribute->document_srl;
                if(!$GLOBALS['XE_DOCUMENT_LIST'][$document_srl]) {
                    $oDocument = null;
                    $oDocument = new documentItem();
                    $oDocument->setAttribute($attribute, false);
                    if($is_admin) $oDocument->setGrant();
                    $GLOBALS['XE_DOCUMENT_LIST'][$document_srl] = $oDocument;
                }

                $output->data[$virtual_number] = $GLOBALS['XE_DOCUMENT_LIST'][$document_srl];
                $virtual_number --;

            }

			if($load_extra_vars) $oDocumentModel->setToAllDocumentExtraVars();

            if(count($output->data)) {
                foreach($output->data as $number => $document) {
                    $output->data[$number] = $GLOBALS['XE_DOCUMENT_LIST'][$document->document_srl];
                }
            }

            return $output;
        }


    }

?>
