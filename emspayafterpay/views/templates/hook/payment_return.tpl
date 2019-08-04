<h1>
    {l s='Your order at %s' sprintf=$shop_name mod='emspayafterpay'}
</h1>
<h3>
    {l s='AfterPay Payment Success' mod='emspayafterpay'}
</h3>
<p>
    {l s='Your order is complete.' mod='emspayafterpay'}
    <br/><br/>
    <b>{l s='You have chosen the AfterPay payment method.' mod='emspayafterpay'}</b>
    <br/><br/>
    {l s='For any questions or for further information, please contact our' mod='emspayafterpay'}
    <a href="{$link->getPageLink('contact-form', true)|escape:'html'}">{l s='customer support' mod='emspayafterpay'}</a>.
</p>