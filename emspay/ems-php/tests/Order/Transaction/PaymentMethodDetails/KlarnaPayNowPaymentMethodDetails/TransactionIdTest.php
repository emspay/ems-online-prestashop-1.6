<?php

namespace GingerPayments\Payment\Tests\Order\Transaction\PaymentMethodDetails\KlarnaPayNowPaymentMethodDetails;

use GingerPayments\Payment\Order\Transaction\PaymentMethodDetails\KlarnaPayNowPaymentMethodDetails\TransactionId;

final class TransactionIdTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function itShouldInstantiateFromAValidString()
    {
        $this->assertInstanceOf(
            'GingerPayments\Payment\Order\Transaction\PaymentMethodDetails\KlarnaPayNowPaymentMethodDetails\TransactionId',
            TransactionId::fromString('DFGHDFGIFGJERGOWJ21')
        );
    }

    /**
     * @test
     */
    public function itShouldGuardAgainstEmptyValue()
    {
        $this->setExpectedException('Assert\InvalidArgumentException');
        TransactionId::fromString('');
    }
}
