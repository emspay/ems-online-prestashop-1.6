<?php

class ingpspProcessingModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();
        
        if (Tools::getValue('processing')) {
            $this->checkStatusAjax();
        }
        
        $this->context->smarty->assign(
                [
                    'fallback_url' => $this->getPendingUrl(),
                    'validation_url' => $this->getValidationUrl()
                ]
        );
        $this->setTemplate('processing.tpl');
    }
    
    /**
     * @return string
     */
    public function getPendingUrl()
    {
        if (version_compare(_PS_VERSION_, '1.5') <= 0) {
            return (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://')
                .htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8')
                .__PS_BASE_URI__.'index.php?fc=module&module=ingpsp&controller=pending';
        } else {
            return $this->context->link->getModuleLink('ingpsp', 'pending');
        }
    }
    
    /**
     * @return string
     */
    public function getValidationUrl()
    {
        if (version_compare(_PS_VERSION_, '1.5') <= 0) {
            return (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://')
                .htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8')
                .__PS_BASE_URI__.'order-confirmation.php?id_cart='.Tools::getValue('id_cart')
                .'&id_module='.$this->module->id
                .'&order_id='.Tools::getValue('order_id');
        } else {
            return $this->context->link->getModuleLink(
                'ingpsp',
                'validation',
                [
                    'id_cart'   => Tools::getValue('id_cart'),
                    'id_module' => $this->module->id,
                    'order_id'  => Tools::getValue('order_id')
                ]
            );
        }
    }
    
    /**
     * Method prepares Ajax response for processing page
     */
    public function checkStatusAjax()
    {
        $orderStatus = $this->checkOrderStatus();

        if ($orderStatus == 'processing') {
            $response = [
                'status' => $orderStatus,
                'redirect' => false
            ];
        } else {
            $response = [
                'status' => $orderStatus,
                'redirect' => true
            ];
        }

        die(json_encode($response));
    }
    
    /**
     * @param string $orderId
     * @return null|string
     */
    public function checkOrderStatus()
    {
        $ginger = \GingerPayments\Payment\Ginger::createClient(
            Configuration::get('ING_PSP_APIKEY'),
            Configuration::get('ING_PSP_PRODUCT')
        );
        if (Configuration::get('ING_PSP_BUNDLE_CA')) {
            $ginger->useBundledCA();
        }

        return $ginger->getOrder(Tools::getValue('order_id'))->getStatus();
    }
}
