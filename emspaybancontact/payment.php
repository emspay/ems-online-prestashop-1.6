<?php

include dirname(__FILE__).'/../../config/config.inc.php';
include dirname(__FILE__).'/../../init.php';

class Payment extends PaymentModule
{
    public function execPayment()
    {
        include_once(_PS_MODULE_DIR_.'emspaybancontact/emspaybancontact.php');
        $emspaybc = new emspaybancontact();
        $emspaybc->execPayment($this->context->cart);
    }
}

$paymentclass = new Payment();
$paymentclass->execPayment();
