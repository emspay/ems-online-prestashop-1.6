<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once(_PS_MODULE_DIR_.'/emspay/ginger-php/vendor/autoload.php');
require_once(_PS_MODULE_DIR_.'/emspay/emspay.php');
require_once(_PS_MODULE_DIR_.'/emspay/lib/emspayhelper.php');
require_once(_PS_MODULE_DIR_.'/emspay/lib/clientfactory.php');

class emspayAfterpay extends PaymentModule
{
    const TERMS_CONDITION_URL_NL = 'https://www.afterpay.nl/nl/algemeen/betalen-met-afterpay/betalingsvoorwaarden';
    const TERMS_CONDITION_URL_BE = 'https://www.afterpay.be/be/footer/betalen-met-afterpay/betalingsvoorwaarden';
    const BE_ISO_CODE = 'BE';
    
    public $extra_mail_vars;
    public $ginger;
    protected $allowedLocales = ['NL', 'BE'];

    public function __construct()
    {
        $this->name = 'emspayafterpay';
	    $this->method_id = 'afterpay';
        $this->tab = 'payments_gateways';
        $this->version = '1.8.1';
        $this->author = 'Ginger Payments';
        $this->controllers = array('payment', 'validation');
        $this->is_eu_compatible = 1;
        $this->currencies = true;
        $this->currencies_mode = 'checkbox';
        $this->bootstrap = true;

        parent::__construct();
        
        $apiKey = Configuration::get('EMS_PAY_AFTERPAY_APIKEY_TEST') ?: Configuration::get('EMS_PAY_APIKEY');
        
        if ($apiKey) {
            try {
                $this->ginger = ClientFactory::create(ClientFactory::AFTERPAY_TEST_API_KEY_ENABLED_CLIENT);
            } catch (\Assert\InvalidArgumentException $exception) {
                $this->warning = $exception->getMessage();
            }
        }
        
        $this->displayName = $this->l('EMS Online AfterPay');
        $this->description = $this->l('Accept payments for your products using EMS Online AfterPay');
        $this->confirmUninstall = $this->l('Are you sure about removing these details?');

        if (!count(Currency::checkPaymentCurrencies($this->id))) {
            $this->warning = $this->l('No currency has been set for this module.');
        }
    }
    
    public function install()
    {
        Configuration::updateValue('EMS_AFTERPAY_COUNTRY_ACCESS', trim('NL, BE'));

        if (!parent::install()
            || !$this->registerHook('payment')
            || !$this->registerHook('displayPaymentEU')
            || !$this->registerHook('paymentReturn')
            || !$this->registerHook('actionOrderStatusUpdate')
            || !Configuration::get('EMS_PAY_APIKEY')
        ) {
            return false;
        }

        return true;
    }

    public function getContent()
    {
        $html = '<br />';
        if (Tools::isSubmit('btnSubmit')) {
            $html = $this->postProcess();
        }

        $html .= $this->displayemspay();
        $html .= $this->renderForm();

        return $html;
    }
    
    private function postProcess()
    {
        if (Tools::isSubmit('btnSubmit')) {
            Configuration::updateValue('EMS_AFTERPAY_SHOW_FOR_IP', trim(Tools::getValue('EMS_AFTERPAY_SHOW_FOR_IP')));
            Configuration::updateValue('EMS_AFTERPAY_COUNTRY_ACCESS', trim(Tools::getValue('EMS_AFTERPAY_COUNTRY_ACCESS')));

        }
        return $this->displayConfirmation($this->l('Settings updated'));
    }
    
    private function displayemspay()
    {
        return $this->display(__FILE__, 'infos.tpl');
    }
    
    public function renderForm()
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('EMS Online Settings'),
                    'icon' => 'icon-envelope'
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->l('IP address(es) for testing.'),
                        'name' => 'EMS_AFTERPAY_SHOW_FOR_IP',
                        'required' => true,
                        'desc' => $this->l('You can specify specific IP addresses for which AfterPay is visible, for example if you want to test AfterPay you can type IP addresses as 128.0.0.1, 255.255.255.255. If you fill in nothing, then, AfterPay is visible to all IP addresses.'),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Countries available for AfterPay.'),
                        'name' => 'EMS_AFTERPAY_COUNTRY_ACCESS',
                        'required' => true,
                        'desc' => $this->l('To allow AfterPay to be used for any other country just add its country code (in ISO 2 standard) to the "Countries available for AfterPay" field. Example: BE, NL, FR'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                )
            ),
        );

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $this->fields_form = array();
        $helper->id = (int) Tools::getValue('id_carrier');
        $helper->identifier = $this->identifier;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'btnSubmit';
        $helper->currentIndex = $this->context->link->getAdminLink(
            'AdminModules',
                false
        ).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );

        return $helper->generateForm(array($fields_form));
    }
    
    public function getConfigFieldsValues()
    {
        return array(
            'EMS_AFTERPAY_SHOW_FOR_IP' => Tools::getValue(
                'EMS_AFTERPAY_SHOW_FOR_IP',
                Configuration::get('EMS_AFTERPAY_SHOW_FOR_IP')
            ),
            'EMS_AFTERPAY_COUNTRY_ACCESS' => Tools::getValue(
                'EMS_AFTERPAY_COUNTRY_ACCESS',
                Configuration::get('EMS_AFTERPAY_COUNTRY_ACCESS')
            ),
        );
    }

    public function uninstall()
    {
        Configuration::deleteByName('EMS_AFTERPAY_SHOW_FOR_IP');
        Configuration::deleteByName('EMS_AFTERPAY_COUNTRY_ACCESS');

        if (!parent::uninstall()) {
            return false;
        }
        return true;
    }

    public function hookPayment($params)
    {
        if (!$this->active) {
            return;
        }
        if (!$this->checkCurrency($params['cart'])) {
            return;
        }
       
        if ($this->isSetShowForIpFilter()) {
            return;
        }
        
        $this->smarty->assign(array(
            'this_path' => $this->_path,
            'this_path_bw' => $this->_path,
            'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->name.'/'
        ));

        $userCountry = $this->getUserCountryFromAddressId($params['cart']->id_address_invoice);

        if (!$this->countryAccess($params['cart']->id_address_invoice)){
            return;
        }
        if ($this->isValidCountry($userCountry)) {
            $this->smarty->assign('terms_and_condition_url', $this->getTermsAndConditionUrlByCountryIsoLocale($userCountry));
            return $this->display(__FILE__, 'payment_nl_be.tpl');
        }
        
        return $this->display(__FILE__, 'payment_not_available.tpl');
    }
    
    /**
     * Method checks is afterpay pm available for the user locale
     *
     * @param type $isoLocale
     * @return type
     */
    protected function isValidCountry($isoLocaleCode)
    {
        return (bool) in_array($isoLocaleCode, $this->allowedLocales);
    }

    /**
     * 
     * @param string $idAddress
     * @return string
     */
    protected function getUserCountryFromAddressId($idAddress)
    {
        $presta_address = new Address((int) $idAddress);
        $country = new Country(intval($presta_address->id_country));
        return strtoupper($country->iso_code);
    }

    /**
     * Get Terms&Condition url based on the country iso code
     * If the customer is from BE use the BE url, otherwise use default NL url
     *
     * @param string $isoLocaleCode
     * @return string
     */
    protected function getTermsAndConditionUrlByCountryIsoLocale($isoLocaleCode)
    {
        if (strtoupper($isoLocaleCode) === self::BE_ISO_CODE) {
            return self::TERMS_CONDITION_URL_BE;
        }
        return self::TERMS_CONDITION_URL_NL;
    }

    /**
     * check if the EMS_AFTERPAY_SHOW_FOR_IP is set,
     * if so, only display if user is from that IP
     *
     * @return boolean
     */
    protected function isSetShowForIpFilter()
    {
        $ems_afterpay_show_for_ip = Configuration::get('EMS_AFTERPAY_SHOW_FOR_IP');
        if (strlen($ems_afterpay_show_for_ip)) {
            $ip_whitelist = array_map('trim', explode(",", $ems_afterpay_show_for_ip));
            if (!in_array(filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP), $ip_whitelist)) {
                return true;
            }
        }
        return false;
    }

    /**
     * check if the EMS_AFTERPAY_COUNTY_ACCESS is set,
     * if so, only display if user is from that counties
     *
     * @return boolean
     */
    protected function countryAccess($idusercountry)
    {
        $ems_afterpay_country_access = Configuration::get('EMS_AFTERPAY_COUNTRY_ACCESS');
        if (empty($ems_afterpay_country_access)) {
            return true;
        } else {
            $countrylist =  array_map("trim", explode(',',$ems_afterpay_country_access));
            return in_array($this->getUserCountryFromAddressId($idusercountry), $countrylist);
        }
    }

    public function hookDisplayPaymentEU($params)
    {
        if (!$this->active) {
            return;
        }

        if (!$this->checkCurrency($params['cart'])) {
            return;
        }

        return array(
            'cta_text' => $this->l('Pay by AfterPay'),
            'logo' => Media::getMediaPath(dirname(__FILE__).'/emspayafterpay.png'),
            'action' => $this->context->link->getModuleLink($this->name, 'validation', array(), true)
        );
    }

    public function checkCurrency($cart)
    {
        $currency_order = new Currency($cart->id_currency);
        $currencies_module = $this->getCurrency($cart->id_currency);

        if (is_array($currencies_module)) {
            foreach ($currencies_module as $currency_module) {
                if ($currency_order->id == $currency_module['id_currency']) {
                    return true;
                }
            }
        }
        return false;
    }

    public function execPayment($cart, $locale = '')
    {
        $customer = $this->getCustomerInformation($cart, $locale);
        $orderLines = $this->getOrderLines($cart);
        $description = $this->getOrderDescription();
        $totalInCents = EmspayHelper::getAmountInCents($cart->getOrderTotal(true));
        $currency = EmspayHelper::getPaymentCurrency();
        $webhookUrl = EmspayHelper::getWebHookUrl();
 
        try {
            $response = $this->ginger->createOrder([
		    'amount' => $totalInCents,                                                      // Amount in cents
		    'currency' => $currency,                                                        // Currency
		    'transactions' => [
		        [
		            'payment_method' => $this->method_id						// Payment method
		        ]
		    ],
		    'description' => $description,                                                  // Description
		    'merchant_order_id' => $this->currentOrder,                                     // Merchant Order Id
		    'return_url' => $this->getReturnURL($cart),                                            // Return URL
		    'customer' => $customer,                                                        // Customer information
		    'extra' => ['plugin' => EmspayHelper::getPluginVersionText($this->version)],   	// Extra information
		    'webhook_url' => $webhookUrl,                                                   // Webhook URL
		    'order_lines' => $orderLines                                                    // Order lines
            ]);
        } catch (\Exception $exception) {
            return Tools::displayError($exception->getMessage());
        }

        if ($response['status'] == 'error') {
            return Tools::displayError($response['transactions'][0]['reason']);
        }
 
        if (!$response['id']) {
            return Tools::displayError("Error: Response did not include id!");
        }
        
        $this->saveEMSOrderId($response, $cart);
        $orderData = $this->ginger->getOrder($response['id']);
        $this->ginger->updateOrder($response['id'], $orderData);

        header('Location: '.$this->getReturnURL($cart, $response));
    }
    
    /**
     * @param $cart
     * @return array
     */
    public function getCustomerInformation($cart, $locale = '')
    {
        $presta_customer = new Customer((int) $cart->id_customer);
        $presta_address = new Address((int) $cart->id_address_invoice);
        $presta_country = new Country((int) $presta_address->id_country);
        $gender = ($presta_customer->id_gender == '1') ? 'male' : 'female';

        return [
            'address' => implode("\n", array_filter(array(
                $presta_address->address1,
                $presta_address->address2,
                $presta_address->postcode." ".$presta_address->city,
            ))),
            'address_type' => 'customer',
            'country' => $presta_country->iso_code,
            'email_address' => $presta_customer->email,
            'first_name' => $presta_customer->firstname,
            'last_name' => $presta_customer->lastname,
            'merchant_customer_id' => $cart->id_customer,
            'phone_numbers' => EmspayHelper::getArrayWithoutNullValues([
                        (string) $presta_address->phone,
                        (string) $presta_address->phone_mobile
                    ]),
            'gender' => $gender,
            'birthdate' => $presta_customer->birthday,
            'ip_address' => Tools::getRemoteAddr(),
            'locale' => $locale
        ];
    }
    
    /**
     * @param $cart
     * @return array
     */
    public function getOrderLines($cart)
    {
        $orderLines = [];

        foreach ($cart->getProducts() as $key => $product) {
            $orderLines[] = array_filter([
                'ean' => $this->getProductEAN($product),
                'url' => $this->getProductURL($product),
                'name' => $product['name'],
                'type' => EmspayHelper::PHYSICAL,
                'amount' => EmspayHelper::getAmountInCents(Tools::ps_round($product['price_wt'], 2)),
                'currency' => EmspayHelper::getPaymentCurrency(),
                'quantity' => (int)$product['cart_quantity'],
                'image_url' => $this->getProductCoverImage($product),
                'vat_percentage' => ((int) $product['rate'] * 100),
                'merchant_order_line_id' => $product['unique_id']
            ], function ($var) {
                return !is_null($var);
            });
        }

        $shippingFee = $cart->getOrderTotal(true, Cart::ONLY_SHIPPING);

        if ($shippingFee > 0) {
            $orderLines[] = $this->getShippingOrderLine($cart, $shippingFee);
        }

        return count($orderLines) > 0 ? $orderLines : null;
    }
    
    /**
     * @param $cart
     * @param $shippingFee
     * @return array
     */
    public function getShippingOrderLine($cart, $shippingFee)
    {
        return [
            'name' => $this->l("Shipping Fee"),
            'type' => EmspayHelper::SHIPPING_FEE,
            'amount' => EmspayHelper::getAmountInCents($shippingFee),
            'currency' => EmspayHelper::getPaymentCurrency(),
            'vat_percentage' => EmspayHelper::getAmountInCents($this->getShippingTaxRate($cart)),
            'quantity' => 1,
            'merchant_order_line_id' => count($cart->getProducts()) + 1
        ];
    }
    
    /**
     * @param $product
     * @return string|null
     */
    public function getProductEAN($product)
    {
        return (key_exists('ean13', $product) && strlen($product['ean13']) > 0) ? $product['ean13'] : null;
    }

    /**
     * @param $product
     * @return string|null
     */
    public function getProductURL($product)
    {
        $productURL = $this->context->link->getProductLink($product);

        return strlen($productURL) > 0 ? $productURL : null;
    }
    
    /**
     * @param $product
     * @return mixed
     */
    public function getProductCoverImage($product)
    {
        $productCover = Product::getCover($product['id_product']);

        if ($productCover) {
            return $this->context->link->getImageLink($product['link_rewrite'], $productCover['id_image']);
        }
    }

    /**
     * @param $cart
     * @return mixed
     */
    public function getShippingTaxRate($cart)
    {
        $carrier = new Carrier((int) $cart->id_carrier, (int) $this->context->cart->id_lang);

        return $carrier->getTaxesRate(
            new Address((int) $this->context->cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')})
        );
    }
    
    /**
     * set order status to 'Captured'
     *
     * @param array $params
     * @return boolean
     */
    public function hookActionOrderStatusUpdate($params)
    {
        $emspay = $this->getOrderDetails($params['cart']->id);
        if ($this->isNewOrderStatusIsShipping($params, $emspay)) {
            try {
                $this->ginger->setOrderCapturedStatus(
                         $this->ginger->getOrder($emspay['ginger_order_id'])
                         );
                return true;
            } catch (\Exception $ex) {
                $this->warning = $ex->getMessage();
                return false;
            }
        }
        return true;
    }

    /**
    * fetch emspay order by cart id
    *
    * @param int $cartID
    * @return array
    */
    private function getOrderDetails($cartID)
    {
        return Db::getInstance()->getRow(
                sprintf(
                    'SELECT * FROM `%s` WHERE `%s` = \'%s\'',
                    _DB_PREFIX_.'emspay',
                    'id_cart',
                     $cartID
                )
            );
    }
    
    /**
     * @param array $params
     * @return boolean
     */
    protected function isNewOrderStatusIsShipping($params, $emspay)
    {
        return (bool)  (
            isset($emspay['payment_method']) &&
            $emspay['payment_method'] == $this->name &&
            isset($params['newOrderStatus']) &&
            isset($params['newOrderStatus']->id) &&
            intval($params['newOrderStatus']->id) === intval(Configuration::get('PS_OS_SHIPPING'))
        );
    }

    /**
     * @param $response
     * @param $cart
     */
    public function saveEMSOrderId($response, $cart)
    {
        if ($response['id']) {
            $db = Db::getInstance();
            $db->Execute("DELETE FROM `"._DB_PREFIX_."emspay` WHERE `id_cart` = ".$cart->id);
            $db->Execute("
                        INSERT INTO `"._DB_PREFIX_."emspay`
		            (`id_cart`, `ginger_order_id`, `key`, `payment_method`, `id_order`)
		        VALUES (
		            '".$cart->id."', 
		            '".$response['id']."', 
		            '".$this->context->customer->secure_key."', 
		            '".$this->name."', 
		            '".$this->currentOrder."'
		        );
            ");
        }
    }

    /**
     * @param $cart
     * @param $response
     * @return string
     */
    public function getReturnURL($cart, $response=[])
    {
	  if (version_compare(_PS_VERSION_, '1.5') <= 0) {
		$returnURL = (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://')
			.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__
			.'order-confirmation.php?id_cart='.$cart->id
			.'&id_module='.$this->id
			.'&id_order='.$this->currentOrder;
		if(!empty($response)) $returnURL.='&order_id='.$response['id'];
	  } else {
		$returnURL = Context::getContext()->link->getModuleLink(
			'emspaybanktransfer',
			'validation',
			[
				'id_cart' => $cart->id,
				'id_module' => $this->id,
				'order_id' => !empty($response)?$response['id']:''
			]
		);
	  }

	  return $returnURL;
    }
    
    /**
     * @param array $params
     * @return string
     */
    public function hookPaymentReturn($params)
    {
        if (!$this->active) {
            return;
        }
        return $this->display(__FILE__, 'payment_return.tpl');
    }
    
    /*
     * @return string
     */
    protected function getOrderDescription()
    {
        return sprintf($this->l('Your order at')." %s", Configuration::get('PS_SHOP_NAME'));
    }
}
