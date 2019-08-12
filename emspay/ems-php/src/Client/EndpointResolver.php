<?php

namespace GingerPayments\Payment\Client;

use Dotenv\Dotenv;

class EndpointResolver {

    /**
     * API endpoint EMS
     */
    const ENDPOINT_EMS = 'https://api.onlinetest.emspay.eu/{version}/';

    public function __construct() {
        try {
            $dotenv = new Dotenv(__DIR__.'/../..');
            $dotenv->load();
        } catch (\Exception $e) {
            
        }
    }

    /**
     * @return string
     */
    public function getEndpointGinger() {
        return false !== getenv('ENDPOINT_GINGER') ? getenv('ENDPOINT_GINGER') : self::ENDPOINT_GINGER;
    }

    /**
     * @return string
     */
    public function getEndpointKassa() {
        return false !== getenv('ENDPOINT_KASSA') ? getenv('ENDPOINT_KASSA') : self::ENDPOINT_KASSA;
    }

    /**
     * @return string
     */
    public function getEndpointEms() {
        return false !== getenv('ENDPOINT_EMS') ? getenv('ENDPOINT_EMS') : self::ENDPOINT_EMS;
    }

    /**
     * @return string
     */
    public function getEndpointEpay() {
        return false !== getenv('ENDPOINT_EPAY') ? getenv('ENDPOINT_EPAY') : self::ENDPOINT_EPAY;
    }

}
