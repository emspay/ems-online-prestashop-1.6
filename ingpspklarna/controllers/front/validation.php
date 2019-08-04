<?php

require_once(_PS_MODULE_DIR_.'/ingpsp/ing-php/vendor/autoload.php');

class ingpspKlarnaValidationModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        $apiKey = Configuration::get('ING_PSP_APIKEY_TEST') ?: Configuration::get('ING_PSP_APIKEY');
        $ginger = \GingerPayments\Payment\Ginger::createClient(
            $apiKey,
            Configuration::get('ING_PSP_PRODUCT')
        );
        if (Configuration::get('ING_PSP_BUNDLE_CA')) {
            $ginger->useBundledCA();
        }
        $ginger_order_status = $ginger->getOrder(Tools::getValue('order_id'))->getStatus();
        $cart_id = Tools::getValue('id_cart');
        switch ($ginger_order_status) {
            case 'processing':
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
