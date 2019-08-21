<?php

/**
 * Emspay helper class
 */
class EmspayHelper
{
    
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
     * @param type $array
     * @return array
     */
    public static function getArrayWithoutNullValues($array)
    {
        return array_values(
                \GingerPayments\Payment\Common\ArrayFunctions::withoutNullValues(
                    array_unique($array)
                )
            );
    }
}
