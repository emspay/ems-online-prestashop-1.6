<h1>
    {l s='Your order at %s' sprintf=$shop_name mod='ingpspafterpay'}
</h1>
<h3>
    {l s='AfterPay Payment Success' mod='ingpspafterpay'}
</h3>
<p>
    {l s='Your order is complete.' mod='ingpspafterpay'}
    <br/><br/>
    <b>{l s='You have chosen the AfterPay payment method.' mod='ingpspafterpay'}</b>
    <br/><br/>
    {l s='For any questions or for further information, please contact our' mod='ingpspafterpay'}
    <a href="{$link->getPageLink('contact-form', true)|escape:'html'}">{l s='customer support' mod='ingpspafterpay'}</a>.
</p>