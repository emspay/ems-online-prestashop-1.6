<h1>{l s='Your order at %s' sprintf=$shop_name mod='ingpspafterpay'}</h1>

<div class="error">
    <p><b>{l s='Unfortunately, we can not currently accept your purchase with AfterPay. Please choose another payment option to complete your order. We apologize for the inconvenience.' mod='ingpspafterpay'}</b></p>
    <p><a href="{$checkout_url}">{l s='Please click here to try again.' mod='ingpspafterpay'}</a></p>
</div>