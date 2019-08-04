<?php

class ingpspPaymentModuleFrontController extends ModuleFrontController
{
    public $ssl = true;
    public $display_column_left = false;

    /**
     *
     *
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        parent::initContent();

        $cart = $this->context->cart;

        $locale = $this->_getWebshopLocale();

        echo $this->module->execPayment($cart, $locale);
    }

    /**
     * @return string
     */
    protected function _getWebshopLocale()
    {
        if ($this->context->language) {
            // Current language
            $language = $this->context->language->iso_code;
        } else {
            // Default locale language
            $language = Configuration::get('PS_LOCALE_LANGUAGE');
        }
        return strtolower($language).'_'.strtoupper(Configuration::get('PS_LOCALE_COUNTRY'));
    }
}
