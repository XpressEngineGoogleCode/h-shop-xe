<?php
    /**
    * vi:set ts=4 sw=4 expandtab enc=utf8:
    **/
    class paymentInfo extends Object {
        var $payment_srl = null;
        var $module_srl = null;
        var $member_srl = null;
        var $photo_src = null;
        var $payment_title = null;
        var $colorset = null;

        function paymentInfo($payment_srl = 0) { 
            if(!$payment_srl) return;

            $this->setPayment($payment_srl);
        }

        function setPayment($payment_srl) { 
            $this->module_srl = $this->payment_srl = $payment_srl;
            $this->_loadFromDB();
        }

        function _loadFromDB() {
            if(!$this->payment_srl) return;

            $args->module_srl = $this->payment_srl;
            $output = executeQuery('payment.getPaymentInfo', $args);
            if(!$output->toBool()||!$output->data) return;

            $this->setAttribute($output->data);
        }

        function setAttribute($attribute) {
            if(!$attribute->module_srl) {
                $this->payment_srl = null;
                return;
            }
            $this->module_srl = $this->payment_srl = $attribute->module_srl;
            $this->member_srl = $attribute->member_srl;
            $this->colorset = $attribute->colorset;

            $this->adds($attribute);
        }

        function isHome() {
            $module_info = Context::get('module_info');
            if($this->getModuleSrl() == $module_info->module_srl) return true;
            return false;
        }

        function getColorset() {
            if($this->isHome() || !$this->colorset) {
                $module_info = Context::get('module_info');
                return $module_info->colorset;
            }
            return $this->colorset;

        }

        function getBrowserTitle() {
            if(!$this->isExists()) return;
            return $this->get('browser_title');
        }

        function getPaymentTitle() {
            if(!$this->isExists()) return;
            return $this->get('payment_title');
        }

        function getPhotoSrc($width=96,$height=96) {
            if(!$this->isExists()) return;
            $oPaymentModel = &getModel('payment');
            return $oPaymentModel->getPaymentPhotoSrc($this->payment_srl, $width, $height);
        }

        function getMid() {
            if(!$this->isExists()) return;
            return $this->get('mid');
        }

        function getMemberSrl() {
            if(!$this->isExists()) return;
            return $this->get('member_srl');
        }

        function getModuleSrl() {
            if(!$this->isExists()) return;
            return $this->getPaymentSrl();
        }

        function getPaymentSrl() {
            if(!$this->isExists()) return;
            return $this->payment_srl;
        }

        function getPaymentMid() {
            if(!$this->isExists()) return;
            return $this->get('mid');
        }

        function getNickName() {
            if(!$this->isExists()) return;
            return $this->get('nick_name'); 
        }

        function getUserName() {
            if(!$this->isExists()) return;
            return $this->get('user_name'); 
        }

        function getUserID() {
            if(!$this->isExists()) return;
            return $this->get('getUserID'); 
        }

        function isExists() {
            return $this->payment_srl?true:false;
        }

        function isNoticeClosed() {
            if(!$this->isExists()) return;
            return $this->get('close_notice')=='Y'?true:false;
        }

        function getPrevDate($date) {
            if(!$this->isExists()) return;
            $args->cur_date = substr($date,0,8).'000000';
            if(!$this->isHome()) $args->module_srl = $this->getModuleSrl();
            $output = executeQuery('payment.getPrevDate', $args);
            return substr($output->data->prev_date,0,8);
        }

        function getNextDate($date) {
            if(!$this->isExists()) return;
            if(!$date) return;
            $args->cur_date = substr($date,0,8).'235959';
            if(!$this->isHome()) $args->module_srl = $this->getModuleSrl();
            $output = executeQuery('payment.getNextDate', $args);
            return substr($output->data->next_date,0,8);
        }

        function isMyPayment($payment = null) {
            if(!$this->isExists()) return;
            if(!Context::get('is_logged')) return false;
            $logged_info = Context::get('logged_info');
            if($payment) return $logged_info->member_srl == $payment->get('member_srl');
            return $logged_info->member_srl == $this->get('member_srl');
        }

        function isMyFavorite($payment) {
            if(!$this->isExists()) return false;
            if($payment->isMypayment()) return true;
            
            $args->module_srl = $this->getModuleSrl();
            $args->reg_payment_srl = $payment->getModuleSrl();
            $output = executeQuery('payment.getMyFavorite', $args);
            if($output->data->count>0) return true;
            return false;
        }

        function getPermanentUrl() {
            if(!$this->isExists()) return;
            return getUrl('','mid',$this->getMid());
        }

        function getTags() {
            //static $tags = null;
            if(!$this->isExists()) return;
            //if(!is_null($tags)) return $tags;

            $args->module_srl = $this->payment_srl;
            $output = executeQueryArray('payment.getPaymentTag', $args);
            if(!$output->toBool() || !$output->data) return array();

            $tags = array();
            foreach($output->data as $key => $val) $tags[] = $val->tag;
            return $tags;
        }

        function getTagsString() {
            if(!$this->isExists()) return;
            return join(',',$this->getTags());
        }

        function isRssEnabled() {
            static $open_rss = null;
            if(!$this->isExists()) return;
            if(is_null($open_rss)) {
                $oRssModel = &getModel('rss');
                $module_info = $oRssModel->getRssModuleConfig($this->getModuleSrl());
                $open_rss = $module_info->open_rss;
            }
            return $open_rss=='Y'?true:false;

        }

        /**
         * @brief 최신 업데이트 글 추출
         * mid : 대상 플래닛, null이면 전체 글 대상
         * date : 선택된 일자(필수값, 없으면 오늘을 대상으로 함)
         * page : 페이지 번호
         * list_count : 추출 대상 수
         **/
        function getNewestContentList($date = null, $page=1, $list_count = 10,$sort_index='documents.listorder',$order='asc') {
            if(!$this->isExists()) return;
            if(!$page) $page = 1;
            if(!$date) $date = date("Ymd");

            // 전체 글을 추출 (module='payment'에 대해서 추출해야 하기에 document 모델을 사용하지 않음)
            if(!$this->isHome()) $args->mid = $this->getMid();
            $args->date = $date;
            $args->page = $page;
            $args->sort_index = $sort_index;
            $args->order = $order;
            $args->list_count = $list_count;
            $output = executeQueryArray('payment.getPaymentNewestContentList', $args);
            if(!$output->toBool()) return $output;
            if(count($output->data)) {
                foreach($output->data as $key => $val) {
                    unset($oPayment);
                    $oPayment = new paymentItem();
                    $oPayment->setAttribute($val);
                    $output->data[$key] = $oPayment;
                }
            }
            return $output;
        }

        /**
         * @brief 댓글이 달렸는데 확인하지 않은 글
         **/
        function getCatchContentList($page=1) {
            if(!$page) $page = 1;

            $args->module_srl = $this->getModuleSrl();
            $args->page = $page;
            $output = executeQueryArray('payment.getCatchContentList', $args);
            if(!$output->toBool()) return $output;
            if(count($output->data)) {
                foreach($output->data as $key => $val) {
                    unset($oPayment);
                    $oPayment = new paymentItem();
                    $oPayment->setAttribute($val);
                    $output->data[$key] = $oPayment;
                }
            }
            return $output;
        }

        /**
         * @brief 내가 댓글을 단 글에 댓글이 달렸는데 확인을 하지 않은 글
         **/
        function getFishingContentList($page=1) {
            if(!$page) $page = 1;

            $args->module_srl = $this->getModuleSrl();
            $args->page = $page;
            $output = executeQueryArray('payment.getFishingContentList', $args);
            if(!$output->toBool()) return $output;
            if(count($output->data)) {
                foreach($output->data as $key => $val) {
                    unset($oPayment);
                    $oPayment = new paymentItem();
                    $oPayment->setAttribute($val);
                    $output->data[$key] = $oPayment;
                }
            }
            return $output;
        }


        /**
         * @brief @nickname 으로 소환 받은 글
         **/
        function getCallingContentList($page=1) {
            if(!$page) $page = 1;

            $args->module_srl = $this->getModuleSrl();
            $args->page = $page;
            $output = executeQueryArray('payment.getCallingContentList', $args);
            if(!$output->toBool()) return $output;
            if(count($output->data)) {
                foreach($output->data as $key => $val) {
                    unset($oPayment);
                    $oPayment = new paymentItem();
                    $oPayment->setAttribute($val);
                    $output->data[$key] = $oPayment;
                }
            }
            return $output;
        }

        /**
         * @brief 관심 태그로 등록된 글 가져오기
         **/
        function getInterestTagContentList($date, $page=1) {
            if(!$page) $page = 1;

            $args->module_srl = $this->getModuleSrl();
            $args->date = $date;
            $args->page = $page;
            $output = executeQueryArray('payment.getInterestTagContentList', $args);
            if(!$output->toBool()) return $output;
            if(count($output->data)) {
                foreach($output->data as $key => $val) {
                    unset($oPayment);
                    $oPayment = new paymentItem();
                    $oPayment->setAttribute($val);
                    $output->data[$key] = $oPayment;
                }
            }
            return $output;
        }

        /**
         * @brief 플래닛 마지막 글 쓴 날짜 return
         **/
        function getContentLastDay() {
            if(!$this->isExists()) return;
            if(!$this->isHome()) $args->module_srl = $this->getModuleSrl();

            $args->date = $date . '235959';
            $output = executeQuery('payment.getPaymentContentLastDay', $args);
            if(!$output->toBool() || !$output->data) return date("Ymd");
            $last_day = $output->data->last_date;
            if(!$last_day) $last_day = date("Ymd");
            $last_day = substr($last_day,0,8);
            if(!$last_day || $last_day > date("Ymd") ) return date("Ymd");
            return $last_day;
        }

        function getInterestTags() {
            if(!$this->isExists()) return;
            $opaymentModel = &getModel('payment');
            return $opaymentModel->getInterestTags($this->module_srl);
        }

         /**
         * @brief 전체 태그중 인기 태그 return
         * mid : 대상 플래닛 (null이면 전체 플래닛)
         * shuffle : 태그 결과값에 rank를 부여하고 shuffle
         * list_coutn : 추출 대상 수
         **/
        function getPopularTags($shuffle = false, $list_count = 100) {
            if(!$this->isExists()) return;
            if(!$this->isHome()) $args->mid = $this->getMid();

            $cache_file = sprintf('%sfiles/cache/payment/%s/%d.%d.txt', _XE_PATH_,getNumberingPath($this->getModuleSrl(),3), $shuffle?1:0,$list_count);
            if(!file_exists($cache_file)||filemtime($cache_file)<time()-60*5) {
                $args->list_count = $list_count;


                // 24시간 이내의 태그중에서 인기 태그를 추출
                $args->date = date("YmdHis", time()-60*60*24);

                $output = executeQueryArray('payment.getpaymentPopularTags',$args);
                if(!$output->toBool() || !$output->data) return array();

                $tags = array();
                $max = 0;
                $min = 99999999;
                foreach($output->data as $key => $val) {
                    $tag = $val->tag;
                    $count = $val->count;
                    if($max < $count) $max = $count;
                    if($min > $count) $min = $count;
                    $tags[] = $val;
                }

                if($shuffle) {
                    $mid2 = $min+(int)(($max-$min)/2);
                    $mid1 = $mid2+(int)(($max-$mid2)/2);
                    $mid3 = $min+(int)(($mid2-$min)/2);

                    $output = null;

                    foreach($tags as $key => $item) {
                        if($item->count > $mid1) $rank = 1;
                        elseif($item->count > $mid2) $rank = 2;
                        elseif($item->count > $mid3) $rank = 3;
                        else $rank= 4;

                        $tags[$key]->rank = $rank;
                    }
                    shuffle($tags);
                }
                FileHandler::writeFile($cache_file, serialize($tags));
            } else {
                $tags = unserialize(FileHandler::readFile($cache_file));
            }

            return $tags;
        }

        function getMe2dayUID() {
            return $this->get('me2day_uid');
        }

        function getMe2dayUKey() {
            return $this->get('me2day_ukey');
        }

        function getMe2dayAuthPush() {
            return $this->get('me2day_autopush')=='Y'?true:false;
        }

        function getPhoneNumber(){
            return $this->get('phone_number');
        }
    }
?>
