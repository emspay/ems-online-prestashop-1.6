<h1>
    {l s='Your order at %s' sprintf=$shop_name mod='emspayklarna'}
</h1>
<h3>
    {l s='Klarna Payment Success' mod='emspayklarna'}
</h3>
<p>
    {l s='Your order is complete.' mod='emspayklarna'}
    <br/><br/>
    <b>{l s='You have chosen the Klarna payment method.' mod='emspayklarna'}</b>
    <br/><br/>
    {l s='For any questions or for further information, please contact our' mod='emspayklarna'}
    <a href="{$link->getPageLink('contact-form', true)|escape:'html'}">{l s='customer support' mod='emspayklarna'}</a>.
</p>