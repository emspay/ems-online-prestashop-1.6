<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once(_PS_MODULE_DIR_.'/emspay/ems-php/vendor/autoload.php');

class emspay extends PaymentModule
{
    private $_html = '';
    private $_postErrors = array();
    public $extra_mail_vars;

    private $ems_modules = [
        'ideal',
        'banktransfer',
        'creditcard',
        'bancontact',
        'applepay',
    ];

    public function __construct()
    {
        $this->name = 'emspay';
        $this->tab = 'payments_gateways';
        $this->version = '1.7.1';
        $this->author = 'Ginger Payments';
        $this->controllers = array('payment', 'validation');
        $this->is_eu_compatible = 1;
        $this->currencies = true;
        $this->currencies_mode = 'checkbox';
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('EMS Online');
        $this->description = $this->l('Accept payments for your products using EMS Online. Install this module first');
        $this->confirmUninstall = $this->l('Are you sure about removing these details?');

        if (!count(Currency::checkPaymentCurrencies($this->id))) {
            $this->warning = $this->l('No currency has been set for this module.');
        }
    }

    /**
     * Method retrieves allowed products from EMS API and saves them to config
     */
    protected function initiateAllowedProducts()
    {
        if (Configuration::get('EMS_PAY_APIKEY')) {
            $this->ginger = \GingerPayments\Payment\Ginger::createClient(
                Configuration::get('EMS_PAY_APIKEY')
            );
            if (Configuration::get('EMS_PAY_BUNDLE_CA')) {
                $this->ginger->useBundledCA();
            }            

            $allowedProducts = $this->ginger->getAllowedProducts();

            Configuration::updateValue('PAY_ENABLED_MODULES', json_encode($allowedProducts));
        }
    }

    public function install()
    {
        require_once _PS_MODULE_DIR_.'/emspay/install.php';

        $emspay_install = new emspayInstall();

        if (!parent::install() || !$emspay_install->createTables() || !$emspay_install->createOrderState()) {
            return false;
        }

        return true;
    }

    public function uninstall()
    {
        if (!Configuration::deleteByName('EMS_PAY_APIKEY')
            || !parent::uninstall()
        ) {
            return false;
        }
        return true;
    }

    private function _postValidation()
    {
        if (Tools::isSubmit('btnSubmit')) {
            if (!Tools::getValue('EMS_PAY_APIKEY')) {
                $this->_postErrors[] = $this->l('API key should be set.');
            }
        }
    }

    private function _postProcess()
    {
        if (Tools::isSubmit('btnSubmit')) {
            Configuration::updateValue('EMS_PAY_APIKEY', trim(Tools::getValue('EMS_PAY_APIKEY')));
            Configuration::updateValue('EMS_PAY_APIKEY_TEST', trim(Tools::getValue('EMS_PAY_APIKEY_TEST')));
            Configuration::updateValue('EMS_PAY_AFTERPAY_APIKEY_TEST', trim(Tools::getValue('EMS_PAY_AFTERPAY_APIKEY_TEST')));
            Configuration::updateValue('EMS_PAY_USE_WEBHOOK', Tools::getValue('EMS_PAY_USE_WEBHOOK'));
            Configuration::updateValue('EMS_PAY_BUNDLE_CA', Tools::getValue('EMS_PAY_BUNDLE_CA'));
        }
        $this->_html .= $this->displayConfirmation($this->l('Settings updated'));
    }

    private function _displayemspay()
    {
        return $this->display(__FILE__, 'infos.tpl');
    }

    public function getContent()
    {
        if (Tools::isSubmit('btnSubmit')) {
            $this->_postValidation();
            if (!count($this->_postErrors)) {
                $this->_postProcess();
            } else {
                foreach ($this->_postErrors as $err) {
                    $this->_html .= $this->displayError($err);
                }
            }
        } else {
            $this->_html .= '<br />';
        }

        $this->_html .= $this->_displayemspay();
        $this->_html .= $this->renderForm();

        return $this->_html;
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
                        'type' => 'checkbox',
                        'name' => 'EMS_PAY',
                        'desc' => $this->l('Resolves issue when curl.cacert path is not set in PHP.ini'),
                        'values' => array(
                            'query' => array(
                                array(
                                    'id' => 'BUNDLE_CA',
                                    'name' => $this->l('Use cURL CA bundle'),
                                    'val' => '1'
                                ),
                            ),
                            'id' => 'id',
                            'name' => 'name'
                        )
                    ),
                    array(
                        'type' => 'checkbox',
                        'name' => 'EMS_PAY',
                        'desc' => $this->l('Automatically provide webhook URL to the API'),
                        'values' => array(
                            'query' => array(
                                array(
                                    'id' => 'USE_WEBHOOK',
                                    'name' => $this->l('Include Webhook URL with every order'),
                                    'val' => '1'
                                ),
                            ),
                            'id' => 'id',
                            'name' => 'name'
                        )
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('API Key'),
                        'name' => 'EMS_PAY_APIKEY',
                        'required' => true
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Klarna Test API Key'),
                        'name' => 'EMS_PAY_APIKEY_TEST',
                        'required' => false,
                        'desc' => $this->l('The Test API Key is Applicable only for Klarna. Remove when not used.')
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Afterpay Test API Key'),
                        'name' => 'EMS_PAY_AFTERPAY_APIKEY_TEST',
                        'required' => false,
                        'desc' => $this->l('The Test API Key is Applicable only for Afterpay. Remove when not used.')
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
            'EMS_PAY_APIKEY' => Tools::getValue('EMS_PAY_APIKEY', Configuration::get('EMS_PAY_APIKEY')),
            'EMS_PAY_APIKEY_TEST' => Tools::getValue('EMS_PAY_APIKEY_TEST', Configuration::get('EMS_PAY_APIKEY_TEST')),
            'EMS_PAY_AFTERPAY_APIKEY_TEST' => Tools::getValue('EMS_PAY_AFTERPAY_APIKEY_TEST', Configuration::get('EMS_PAY_AFTERPAY_APIKEY_TEST')),
            'EMS_PAY_BUNDLE_CA' => Tools::getValue('EMS_PAY_BUNDLE_CA', Configuration::get('EMS_PAY_BUNDLE_CA')),
            'EMS_PAY_USE_WEBHOOK' => Tools::getValue('EMS_PAY_USE_WEBHOOK', (Configuration::hasKey('EMS_PAY_USE_WEBHOOK')) ? Configuration::get('EMS_PAY_USE_WEBHOOK') : true)
        );
    }

    public static function moduleIsEnabled($module)
    {
        $modules = json_decode(Configuration::get('PAY_ENABLED_MODULES'));

        return (is_array($modules) && in_array(str_replace('emspay', '', $module), $modules));
    }
}
