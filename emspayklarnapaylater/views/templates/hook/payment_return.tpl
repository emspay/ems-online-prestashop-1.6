<h1>
    {l s='Your order at %s' sprintf=$shop_name mod='emspayklarnapaylater'}
</h1>
<h3>
    {l s='Klarna Pay Later Payment Success' mod='emspayklarnapaylater'}
</h3>
<p>
    {l s='Your order is complete.' mod='emspayklarnapaylater'}
    <br/><br/>
    <b>{l s='You have chosen the Klarna payment method.' mod='emspayklarnapaylater'}</b>
    <br/><br/>
    {l s='For any questions or for further information, please contact our' mod='emspayklarnapaylater'}
    <a href="{$link->getPageLink('contact-form', true)|escape:'html'}">{l s='customer support' mod='emspayklarnapaylater'}</a>.
</p>