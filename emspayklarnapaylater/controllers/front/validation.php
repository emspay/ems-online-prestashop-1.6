<?php

require_once(_PS_MODULE_DIR_.'/emspay/ems-php/vendor/autoload.php');

class emspayKlarnaPayLaterValidationModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        $apiKey = Configuration::get('EMS_PAY_APIKEY_TEST') ?: Configuration::get('EMS_PAY_APIKEY');
        $ginger = \GingerPayments\Payment\Ginger::createClient(
            $apiKey
        );
        if (Configuration::get('EMS_PAY_BUNDLE_CA')) {
            $ginger->useBundledCA();
        }
        $ginger_order_status = $ginger->getOrder(Tools::getValue('order_id'))->getStatus();
        $cart_id = Tools::getValue('id_cart');
        switch ($ginger_order_status) {
            case 'processing':
            case 'new':
            case 'completed':
                if (isset($cart_id)) {
                    Tools::redirect(
                        __PS_BASE_URI__.'index.php?controller=order-confirmation&id_cart='.$cart_id
                        .'&id_module='.$this->module->id
                        .'&id_order='.Order::getOrderByCartId(intval($cart_id))
                        .'&key='.$this->context->customer->secure_key
                    );
                }
                break;
            case 'cancelled':
            case 'expired':
            case 'error':
                $this->setTemplate('error.tpl');
                break;
            default:
                die("Should not happen");
        }
    }
}
