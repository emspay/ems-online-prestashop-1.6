<?php

require_once(_PS_MODULE_DIR_.'/emspay/vendor/autoload.php');
require_once(_PS_MODULE_DIR_.'/emspay/lib/clientfactory.php');

class emspayAfterpayValidationModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        $gingerOrder = ClientFactory::create(ClientFactory::AFTERPAY_TEST_API_KEY_ENABLED_CLIENT)
                ->getOrder(Tools::getValue('order_id'));
	  $gingerOrderStatus = $gingerOrder['status'];
        switch ($gingerOrderStatus) {
            case 'completed':
                $this->processCompletedStatus(Tools::getValue('id_cart'));
                break;
            case 'cancelled':
                $this->processCacnelledStatus();
                break;
            case 'error':
                $this->processErrorStatus();
                break;
            default:
                die("Should not happen");
        }
    }
    
    private function processCacnelledStatus()
    {
        $this->context->smarty->assign(
            'checkout_url',
            $this->context->link->getPagelink('order').'?step=3'
        );
        $this->setTemplate('cancelled.tpl');
    }
    
    private function processErrorStatus()
    {
        $this->setTemplate('error.tpl');
    }

    /**
     *
     * @param int $cartId
     */
    private function processCompletedStatus($cartId)
    {
        if (isset($cartId)) {
            $this->validateOrder($cartId);
            $this->doRedirectToConfirmationPage($cartId);
        }
    }
    
    /**
     * Method validates Presta order
     *
     * @param int $cartId
     */
    private function validateOrder($cartId)
    {
        if (!$this->isOrderValidated($cartId)) {
            $cart = $this->context->cart;
            $customer = new \Customer($cart->id_customer);
            $total = (float) $cart->getOrderTotal(true, \Cart::BOTH);
            $this->module->validateOrder(
                    $cartId,
                    \Configuration::get('PS_OS_PAYMENT'),
                    $total,
                    $this->module->displayName,
                    null,
                    [],
                    (int) $this->context->currency->id,
                    false,
                    $customer->secure_key
            );
        }
    }
    
    /**
     * Method checks if the order has been validated already
     *
     * @param string $cartId
     * @return bool
     */
    public function isOrderValidated($cartId)
    {
        $orderHistory = OrderHistory::getLastOrderState(Order::getOrderByCartId(intval($cartId)));
        return is_object($orderHistory) && ((int) $orderHistory->id === (int) Configuration::get('PS_OS_PAYMENT'));
    }
    
    /**
     *
     * @param int $cartId
     */
    private function doRedirectToConfirmationPage($cartId)
    {
        Tools::redirect(
            __PS_BASE_URI__.'index.php?controller=order-confirmation&id_cart='.$cartId
            .'&id_module='.$this->module->id
            .'&id_order='.Order::getOrderByCartId(intval($cartId))
            .'&key='.$this->context->customer->secure_key
        );
    }
}
