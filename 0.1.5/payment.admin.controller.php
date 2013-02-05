<?php
	/**
     * vi:set ts=4 sw=4 expandtab enc=utf8:
     * @class  Payment System Admin Controller
     * @author Suhan, Moon(lunyx@me.com) 
     * @brief  Payment Administrater Page Controller
     **/
    class paymentAdminController extends payment {
        /**
         * @brief 결제프로그램 설정XML
         **/
        function procPaymentAdminConfigXml() {
            $output = XmlParser::loadXmlFile(_XE_PATH_.ModuleHandler::getModulePath("payment")."system/system.xml");

            foreach($output->systemlist->system as $dat) {
                if($dat->id->body == Context::get('module_extra')) {
                    $this->add('getDescription',   $dat->desc->body);
                    $this->add('getParamArgs',     $dat->require_data->require_item);
                    $this->add('isTestModeUsable', $dat->testkey->body);
                    return;
                }
            }
        }

        function procPaymentAdminPaymentManage() {
            $module_srl = Context::get("module_srl");
            
            $oModuleModel = &getModel("module");
            $oModuleController = &getController("module");

            if(!$module_srl) {
                $module_args->mid = Context::get("payment_mid");
                $module_args->module = 'payment';
                $module_args->description = Context::get("description");

                $output = $oModuleController->insertModule($module_args);
                if(!$output->toBool()) return $output;
                
                $module_srl = $output->get('module_srl');
                $output = $this->insertPaymentSystem($module_srl);
                
                if(!$output->toBool()) return $output;

                $this->setMessage('success_registed');
                $this->add("module_srl", $module_srl);
            } else {
                $module_args = $oModuleModel->getModuleInfoByModuleSrl($module_srl);
                $module_args->description = Context::get("description");
				if( Context::get("payment_testmode") != "Y" )
				{
					$module_args->is_test = "N";
				}

                $oModuleController->updateModule($module_args);

                $output = $this->updatePaymentSystem($module_srl);
                
                if(!$output->toBool()) return $output;

                $this->setMessage('success_updated');
            }
        }

        function procPaymentAdminPaymentRemove() {
            $args->module_srl = Context::Get("module_srl");
            executeQueryArray('payment.deletePayment', $args);
            executeQueryArray('module.deleteModule', $args);
            
            $this->setMessage('success_deleted');
        }

        function insertPaymentSystem($module_srl) {
            $args->module_srl = $module_srl;
            $args->current_extra = Context::get("current_extra");
            $args->description = Context::get("payment_description");
            $args->extra_1 = Context::get("payment_extra_1");
            $args->extra_2 = Context::get("payment_extra_2");
            $args->extra_3 = Context::get("payment_extra_3");
            $args->is_test = Context::get("payment_testmode");
            $args->payment_srl = getNextSequence();
            $args->use_type = str_replace("|@|", "", Context::get("payment_usable"));
            $output = executeQuery('payment.insertPaymentInfo', $args);

            $this->setMessage('success_registed');

            return $output;
        }
        
        function updatePaymentSystem($module_srl) {
            $args->module_srl = $module_srl;
            $args->current_extra = Context::get("current_extra");
            $args->description = Context::get("payment_description");
            $args->extra_1 = Context::get("payment_extra_1");
            $args->extra_2 = Context::get("payment_extra_2");
            $args->extra_3 = Context::get("payment_extra_3");
            $args->is_test = Context::get("payment_testmode");
            $args->use_type = str_replace("|@|", "", Context::get("payment_usable"));
            $output = executeQuery('payment.updatePaymentInfo', $args);

            $this->setMessage('success_updated');

            return $output;
        }
    }
?>
