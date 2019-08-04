<?php

/**
 * Simple Ginger client facorty
 */
class ClientFactory
{
    const STANDARD_CLIENT = 'standard';
    const KLARNA_TEST_API_KEY_ENABLED_CLIENT = 'klarna_test_api_key_enabled';
    const AFTERPAY_TEST_API_KEY_ENABLED_CLIENT = 'afterpay_test_api_key_enabled';

    /**
     * Method creats an instance of Ginger Client
     * 
     * @param string $type
     * @return \GingerPayments\Payment\Client
     * @since v1.6.0
     */
    public static function create($type)
    {
        switch ($type) {
            case self::KLARNA_TEST_API_KEY_ENABLED_CLIENT:
                $apiKey  = Configuration::get('EMS_PAY_APIKEY_TEST') ? : Configuration::get('EMS_PAY_APIKEY');
                break;
            case self::AFTERPAY_TEST_API_KEY_ENABLED_CLIENT:
                $apiKey  = Configuration::get('EMS_PAY_AFTERPAY_APIKEY_TEST') ? : Configuration::get('EMS_PAY_APIKEY');
                break;
            case self::STANDARD_CLIENT:
            default:
                $apiKey = Configuration::get('EMS_PAY_APIKEY_TEST');
                break;
        }
        
        $ginger = \GingerPayments\Payment\Ginger::createClient(
                $apiKey,
                Configuration::get('EMS_PAY_PRODUCT')
        );
        if (Configuration::get('EMS_PAY_BUNDLE_CA')) {
            $ginger->useBundledCA();
        }
        return $ginger;
    }
}
