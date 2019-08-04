<h1>
    {l s='Your order at %s' sprintf=$shop_name mod='ingpspklarna'}
</h1>
<h3>
    {l s='Klarna Payment Success' mod='ingpspklarna'}
</h3>
<p>
    {l s='Your order is complete.' mod='ingpspklarna'}
    <br/><br/>
    <b>{l s='You have chosen the Klarna payment method.' mod='ingpspklarna'}</b>
    <br/><br/>
    {l s='For any questions or for further information, please contact our' mod='ingpspklarna'}
    <a href="{$link->getPageLink('contact-form', true)|escape:'html'}">{l s='customer support' mod='ingpspklarna'}</a>.
</p>