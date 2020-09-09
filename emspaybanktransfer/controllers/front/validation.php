<?php

require_once(_PS_MODULE_DIR_.'/emspay/vendor/autoload.php');
require_once(_PS_MODULE_DIR_ . '/emspay/lib/emspayhelper.php');

class emspayBanktransferValidationModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
	  $ginger = \Ginger\Ginger::createClient(
		  EmspayHelper::GINGER_ENDPOINT,
		  Configuration::get('EMS_PAY_APIKEY'),
		  (null !== \Configuration::get('EMS_PAY_BUNDLE_CA')) ?
			  [
				  CURLOPT_CAINFO => EmspayHelper::getCaCertPath()
			  ] : []
	  );
	  $ginger_order = $ginger->getOrder(Tools::getValue('order_id'));

	  $ginger_order_status = $ginger_order['status'];
        $cart_id = Tools::getValue('id_cart');

        switch ($ginger_order_status) {
            case 'completed':
                if (isset($cart_id)) {
                    Tools::redirect(__PS_BASE_URI__.'index.php?controller=order-confirmation&id_cart='.$cart_id.'&id_module='.$this->module->id.'&id_order='.Order::getOrderByCartId(intval($cart_id)).'&key='.$this->context->customer->secure_key);
                }
                break;
            case 'processing':
                if (isset($cart_id)) {
                    Tools::redirect(__PS_BASE_URI__.'index.php?controller=order-confirmation&id_cart='.$cart_id.'&id_module='.$this->module->id.'&id_order='.Order::getOrderByCartId(intval($cart_id)).'&key='.$this->context->customer->secure_key);
                }
                break;
            case 'cancelled':
            case 'expired':
            case 'error':
                Tools::redirect('order&step=3');
                break;
            default:
                die("Should not happen");
        }

        Tools::redirect('index.php?controller=order-confirmation&id_cart='.Tools::getValue('id_cart').'&id_module='.Tools::getValue('id_module'));
    }
}
