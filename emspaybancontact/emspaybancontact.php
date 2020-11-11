<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once(_PS_MODULE_DIR_.'/emspay/vendor/autoload.php');
require_once(_PS_MODULE_DIR_.'/emspay/emspay.php');
require_once(_PS_MODULE_DIR_.'/emspay/lib/emspayhelper.php');

class emspaybancontact extends PaymentModule
{
    public $extra_mail_vars;
    public $ginger;

    public function __construct()
    {
        $this->name = 'emspaybancontact';
	  $this->method_id = 'bancontact';
        $this->tab = 'payments_gateways';
        $this->version = '1.9.1';
        $this->author = 'Ginger Payments';
        $this->controllers = array('payment', 'validation');
        $this->is_eu_compatible = 1;
        $this->currencies = true;
        $this->currencies_mode = 'checkbox';
        $this->bootstrap = true;

        parent::__construct();

        if (Configuration::get('EMS_PAY_APIKEY')) {
            try {
		    $this->ginger = \Ginger\Ginger::createClient(
			    EmspayHelper::GINGER_ENDPOINT,
			    Configuration::get('EMS_PAY_APIKEY'),
			    (null !== \Configuration::get('EMS_PAY_BUNDLE_CA')) ?
				    [
					    CURLOPT_CAINFO => EmspayHelper::getCaCertPath()
				    ] : []
		    );
            } catch (\Assert\InvalidArgumentException $exception) {
                $this->warning = $exception->getMessage();
            }
        }

        $this->displayName = $this->l('EMS Online Bancontact');
        $this->description = $this->l('Accept payments for your products using Bancontact.');
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

    public function hookPayment($params)
    {
        if (!$this->active) {
            return;
        }
        if (!$this->checkCurrency($params['cart'])) {
            return;
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
            'cta_text' => $this->l('Pay by Bancontact'),
            'logo' => Media::getMediaPath(dirname(__FILE__).'/ems.png'),
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

    public function execPayment($cart, $locale)
    {
        $presta_customer = new Customer((int) $cart->id_customer);
        $presta_address = new Address((int) $cart->id_address_invoice);
        $presta_country = new Country((int) $presta_address->id_country);

        $customer = array(
            'address' => implode("\n", array_filter(array(
                $presta_address->company,
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
            'phone_numbers' => array_values(array_unique(array(
                (string) $presta_address->phone_mobile
            ))),
            'locale' => $locale
        );

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
		            'payment_method' => $this->method_id						// Payment method
		        ]
		    ],
		    'description' => $description,                                                  // Description
		    'merchant_order_id' => $this->currentOrder,                                     // Merchant Order Id
		    'return_url' => $returnURL,                                                     // Return URL
		    'customer' => $customer,                                                        // Customer information
		    'extra' => ['plugin' => EmspayHelper::getPluginVersionText($this->version)],   	// Extra information
		    'webhook_url' => $webhookUrl                                                    // Webhook URL
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
		            (`id_cart`, `ginger_order_id`, `key`, `payment_method`)
		        VALUES (
		            '".$cart->id."', 
		            '".$response['id']."', 
		            '".$this->context->customer->secure_key."', 
		            'emspaybancontact'
		        );
            ");
        }
    }

    /**
     * @param $cart
     * @return string
     */
    public function getReturnURL($cart)
    {
        if (version_compare(_PS_VERSION_, '1.5') <= 0) {
            $returnURL = (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://')
                .htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8')
                .__PS_BASE_URI__.'order-confirmation.php?id_cart='.$cart->id
                .'&id_module='.$this->id
                .'&id_order='.$this->currentOrder;
        } else {
            $returnURL = Context::getContext()->link->getModuleLink(
                'emspay',
                'validation',
                [
                    'id_cart' => $cart->id,
                    'id_module' => $this->id
                ]
            );
        }

        return $returnURL;
    }

    public function hookPaymentReturn($params)
    {
        if (!$this->active) {
            return;
        }

        return $this->display(__FILE__, 'payment_return.tpl');
    }

    /**
     * @return string
     */
    public function getPluginVersion() {
        return sprintf('Prestashop v%s', $this->version);
    }
}
