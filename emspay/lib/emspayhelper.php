<?php

/**
 * Emspay helper class
 */
class EmspayHelper
{

    /**
     * GINGER_ENDPOINT used for create Ginger client
     */
    const GINGER_ENDPOINT = 'https://api.online.emspay.eu';

    const PHYSICAL = 'physical';
    const SHIPPING_FEE = 'shipping_fee';

    /**
     * @param string $amount
     * @return int
     */
    public static function getAmountInCents($amount)
    {
        return (int) round($amount * 100);
    }
    
    /**
     * @return null|string
     */
    public static function getWebHookUrl()
    {
        return Configuration::get('EMS_PAY_USE_WEBHOOK')
            ? _PS_BASE_URL_.__PS_BASE_URI__.'modules/emspay/webhook.php'
            : null;
    }
    
    /**
     * @param string $version
     * @return string
     */
    public static function getPluginVersionText($version)
    {
        return sprintf('Prestashop v%s', $version);
    }
    
    /**
     * @param array $array
     * @return array
     */
    public static function getArrayWithoutNullValues($array)
    {
	  static $fn = __FUNCTION__;

	  foreach (array_unique($array) as $key => $value) {
		if (is_array($value)) {
		    $array[$key] = self::$fn($array[$key]);
		}

		if (empty($array[$key]) && $array[$key] !== '0' && $array[$key] !== 0) {
		    unset($array[$key]);
		}
	  }
	  return array_values($array);
    }

    /**
     * @return string
     */
    public static function getPaymentCurrency()
    {
	  return 'EUR';
    }

    /**
     * Get CA certificate path
     *
     * @return bool|string
     */
    public static function getCaCertPath(){
	  return realpath(_PS_MODULE_DIR_ . '/emspay/assets/cacert.pem');
    }
}
