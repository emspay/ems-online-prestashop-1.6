<?php

require_once(_PS_MODULE_DIR_.'/emspay/ginger-php/vendor/autoload.php');
require_once(_PS_MODULE_DIR_ . '/emspay/lib/emspayhelper.php');

class emspayValidationModuleFrontController extends ModuleFrontController
{
    /**
     * Method called after payment processing is finished
     */
    public function postProcess()
    {
        $cart_id = Tools::getValue('id_cart');
        $orderStatus = $this->checkOrderStatus(Tools::getValue('order_id'));

        if (Tools::getValue('processing')) {
            $this->checkStatusAjax();
        }

        switch ($orderStatus) {
            case 'completed':
            case 'accepted':
                if (isset($cart_id)) {
                    Tools::redirect(
                        __PS_BASE_URI__.'index.php?controller=order-confirmation&id_cart='.$cart_id
                        .'&id_module='.$this->module->id.'&id_order='.Order::getOrderByCartId(intval($cart_id))
                        .'&key='.$this->context->customer->secure_key
                    );
                }
                break;
            case 'processing':
                if (isset($cart_id)) {
                    Tools::redirect($this->getProcessingUrl());
                }
                break;
            case 'new':
            case 'cancelled':
            case 'expired':
            case 'error':
                $this->context->smarty->assign(
                    'checkout_url',
                    $this->context->link->getPagelink('order').'?step=3'
                );
                $this->setTemplate('errors-messages.tpl');
                break;
            default:
                die("Should not happen");
        }
    }

    /**
     * @return string
     */
    public function getProcessingUrl()
    {
        if (version_compare(_PS_VERSION_, '1.5') <= 0) {
            return (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://')
                .htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8')
                .__PS_BASE_URI__.'index.php?fc=module&module=emspay&controller=processing&order_id='.Tools::getValue('order_id').'&id_cart='.Tools::getValue('id_cart');
        } else {
            return $this->context->link->getModuleLink(
                'emspay',
                'processing',
                [
                    'order_id' => Tools::getValue('order_id'),
                    'id_cart'  => Tools::getValue('id_cart')
                ]
            );
        }
    }
    
    /**
     * @param string $orderId
     * @return null|string
     */
    public function checkOrderStatus($orderId)
    {
	  $ginger = \Ginger\Ginger::createClient(
		  EmspayHelper::GINGER_ENDPOINT,
		  Configuration::get('EMS_PAY_APIKEY'),
		  (null !== \Configuration::get('EMS_PAY_BUNDLE_CA')) ?
			  [
				  CURLOPT_CAINFO => EmspayHelper::getCaCertPath()
			  ] : []
	  );
        $ginger_order = $ginger->getOrder($orderId);

        return $ginger_order['status'];
    }
}
