<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once(_PS_MODULE_DIR_.'/emspay/vendor/autoload.php');
require_once(_PS_MODULE_DIR_.'/emspay/emspay.php');
require_once(_PS_MODULE_DIR_.'/emspay/lib/emspayhelper.php');
require_once(_PS_MODULE_DIR_.'/emspay/lib/clientfactory.php');

class emspayKlarnaPayLater extends PaymentModule
{
    private $_html = '';
    public $extra_mail_vars;
    public $ginger;

    public function __construct()
    {
        $this->name = 'emspayklarnapaylater';
	  $this->method_id = 'klarna-pay-later';
        $this->tab = 'payments_gateways';
        $this->version = '1.9.1';
        $this->author = 'Ginger Payments';
        $this->controllers = array('payment', 'validation');
        $this->is_eu_compatible = 1;
        $this->currencies = true;
        $this->currencies_mode = 'checkbox';
        $this->bootstrap = true;

        parent::__construct();

        $apiKey = Configuration::get('EMS_PAY_APIKEY_TEST') ?: Configuration::get('EMS_PAY_APIKEY');

	  if ($apiKey) {
		try {
		    $this->ginger = ClientFactory::create(ClientFactory::KLARNA_TEST_API_KEY_ENABLED_CLIENT);
		} catch (\Assert\InvalidArgumentException $exception) {
		    $this->warning = $exception->getMessage();
		}
	  }
        $this->displayName = $this->l('EMS Online Klarna Pay Later');
        $this->description = $this->l('Accept payments for your products using EMS Online Klarna Pay Later');
        $this->confirmUninstall = $this->l('Are you sure about removing these details?');

        if (!count(Currency::checkPaymentCurrencies($this->id))) {
            $this->warning = $this->l('No currency has been set for this module.');
        }
    }

    public function install()
    {
        if (!parent::install()
            || !$this->registerHook('payment')
            || !$this->registerHook('displayPaymentEU')
            || !$this->registerHook('paymentReturn')
            || !$this->registerHook('updateOrderStatus')
            || !Configuration::get('EMS_PAY_APIKEY')
        ) {
            return false;
        }
        return true;
    }

    public function uninstall()
    {
        if (!parent::uninstall()) {
            return false;
        }
        return true;
    }

    private function _displayemspay()
    {
        return $this->display(__FILE__, 'infos.tpl');
    }

    public function getContent()
    {
        if (Tools::isSubmit('btnSubmit')) {
            $this->_postProcess();
        } else {
            $this->_html .= '<br />';
        }

        $this->_html .= $this->_displayemspay();
        $this->_html .= $this->renderForm();

        return $this->_html;
    }

    private function _postProcess()
    {
        if (Tools::isSubmit('btnSubmit')) {
            Configuration::updateValue('EMS_KLARNAPAYLATER_SHOW_FOR_IP', trim(Tools::getValue('EMS_KLARNAPAYLATER_SHOW_FOR_IP')));
        }
        $this->_html .= $this->displayConfirmation($this->l('Settings updated'));
    }

    
    public function hookUpdateOrderStatus($params) 
    { 
        $emspay = $this->getOrderDetails($params['cart']->id);
        if (isset($emspay['payment_method'])
                 && $emspay['payment_method'] == $this->name
                     && isset($params['newOrderStatus'])
                         && isset($params['newOrderStatus']->id)
                             && intval($params['newOrderStatus']->id) === intval(Configuration::get('PS_OS_SHIPPING'))
            ) {  
                try {
                     $this->ginger->setOrderCapturedStatus(
                             $this->ginger->getOrder($emspay['ginger_order_id'])
                             );
                     return true;
                } catch (\Exception $ex) {
                    return false;
                }
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

        // check if the EMS_KLARNAPAYLATER_SHOW_FOR_IP is set, if so, only display if user is from that IP
        $ems_klarna_show_for_ip = Configuration::get('EMS_KLARNAPAYLATER_SHOW_FOR_IP');
        if (strlen($ems_klarna_show_for_ip)) {
            $ip_whitelist = array_map('trim', explode(",", $ems_klarna_show_for_ip));
            if (!in_array(filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP), $ip_whitelist)) {
                return;
            }
        }

        $this->smarty->assign(array(
            'this_path' => $this->_path,
            'this_path_bw' => $this->_path,
            'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->name.'/'
        ));

        return $this->display(__FILE__, 'payment.tpl');
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
            'cta_text' => $this->l('Pay by Klarna Pay Later'),
            'logo' => Media::getMediaPath(dirname(__FILE__).'/emspay.png'),
            'action' => $this->context->link->getModuleLink($this->name, 'validation', array(), true)
        );
    }

    public function hookPaymentReturn($params)
    {
        if (!$this->active) {
            return;
        }

        $state = $params['objOrder']->getCurrentState();

        if (in_array($state, array(
            Configuration::get('PS_OS_PREPARATION'),
            Configuration::get('PS_OS_OUTOFSTOCK'),
            Configuration::get('PS_OS_OUTOFSTOCK_UNPAID')
        ))) {
            $this->smarty->assign(array(
                'total_to_pay' => Tools::displayPrice($params['total_to_pay'], $params['currencyObj'], false),
                'status' => 'ok',
            ));
        } else {
            $this->smarty->assign('status', 'failed');
        }
        return $this->display(__FILE__, 'payment_return.tpl');
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
                        'name' => 'EMS_KLARNAPAYLATER_SHOW_FOR_IP',
                        'required' => true,
                        'desc' => $this->l('You can specify specific IP addresses for which Klarna Pay Later is visible, for example if you want to test Klarna Pay Later you can type IP addresses as 128.0.0.1, 255.255.255.255. If you fill in nothing, then, Klarna Pay Later is visible to all IP addresses.'),
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
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules',
                false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
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
            'EMS_KLARNAPAYLATER_SHOW_FOR_IP' => Tools::getValue(
                'EMS_KLARNAPAYLATER_SHOW_FOR_IP',
                Configuration::get('EMS_KLARNAPAYLATER_SHOW_FOR_IP')
            ),
        );
    }

    public function execPayment($cart, $locale)
    {
        $customer = $this->getCustomerInformation($cart);
        $customer['locale'] = $locale;
        $orderLines = $this->getOrderLines($cart);
        $description = sprintf($this->l('Your order at')." %s", Configuration::get('PS_SHOP_NAME'));
        $totalInCents = EmspayHelper::getAmountInCents($cart->getOrderTotal(true));
        $currency = EmspayHelper::getPaymentCurrency();
        $webhookUrl = EmspayHelper::getWebHookUrl();
        $returnURL = $this->getReturnURL($cart);

        try {
            $response = $this->ginger->createOrder(array_filter([
			'amount' => $totalInCents,                                                      // Amount in cents
			'currency' => $currency,                                                        // Currency
			'transactions' => [
				[
					'payment_method' => $this->method_id					  // Payment method
				]
			],
			'description' => $description,                                                  // Description
			'merchant_order_id' => $this->currentOrder,                                     // Merchant Order Id
			'return_url' => $returnURL,                                                     // Return URL
			'customer' => $customer,                                                        // Customer information
			'extra' => ['plugin' => EmspayHelper::getPluginVersionText($this->version)],    // Extra information
			'webhook_url' => $webhookUrl,                                                   // Webhook URL
			'order_lines' => $orderLines                                                    // Order lines
		]));

        } catch (\Exception $exception) {
            return Tools::displayError($exception->getMessage());
        }

        if ($response['status'] == 'error') {
            return Tools::displayError($response['transactions'][0]['reason']);
        }

        if (!$response['id']) {
            return Tools::displayError("Error: Response did not include id!");
        }

	  $pay_url = array_key_exists(0, $response['transactions'])
		  ? $response['transactions'][0]['payment_url']
		  : null;

	  if (!$pay_url) {
		return Tools::displayError("Error: Response did not include payment url!");
	  }

        $this->validateOrder(
            $cart->id,
            Configuration::get('PS_OS_PREPARATION'),
            $cart->getOrderTotal(true),
            $this->displayName, null, array(), null, false,
            $this->context->customer->secure_key
        );

        $this->saveEMSOrderId($response, $cart);

        header('Location: '.$pay_url);
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
		            'emspayklarnapaylater', 
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
                'emspayklarnapaylater',
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
     * @param $cart
     * @return array
     */
    public function getCustomerInformation($cart)
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
            'phone_numbers' => array_values(
		    EmspayHelper::getArrayWithoutNullValues(
                    array_unique([
                        (string) $presta_address->phone,
                        (string) $presta_address->phone_mobile
                    ])
                )),
            'gender' => $gender,
            'birthdate' => $presta_customer->birthday,
            'ip_address' => Tools::getRemoteAddr()
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
     * @return string
     */
    public function getPluginVersion() {
        return sprintf('Prestashop v%s', $this->version);
    }
    
    
    /**
     * fetch emspay order by cart id
     * 
     * @param int $cartID
     * @return array
     */
    private function getOrderDetails($cartID) {
        return Db::getInstance()->getRow(
                sprintf(
                    'SELECT * FROM `%s` WHERE `%s` = \'%s\'',
                    _DB_PREFIX_.'emspay',
                    'id_cart',
                     $cartID
                )
            );
    }
}
