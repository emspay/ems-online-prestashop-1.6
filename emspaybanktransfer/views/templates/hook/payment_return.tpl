<h3>{l s='Your order at %s' sprintf=$shop_name mod='emspaybanktransfer'}</h3>

{if $status == 'pending'}
<p class="warning">{l s='The status of your order on %s could not be determined.' sprintf=$shop_name mod='emspaybanktransfer'}
</p>
{else if $status == 'ok'}
    <p>     
        {l s='Your order on %s is pending.' sprintf=$shop_name mod='emspaybanktransfer'}
	<br /><br />
	{l s='To complete your order, transfer the amount using the information below' mod='emspaybanktransfer'}
	<br /><br />- {l s='Amount:' mod='emspaybanktransfer'} <span class="price"><strong>{$total_to_pay}</strong></span>
	<br /><br />- {l s='Account holder: ' mod='emspaybanktransfer'}  <strong>EMS PAY</strong>
	<br /><br />- {l s='Account IBAN: ' mod='emspaybanktransfer'}  <strong>NL79ABNA0842577610</strong>
	<br /><br />- {l s='Do not forget to insert the reference %s in the subject of your banktransfer.' sprintf=$reference mod='emspaybanktransfer'}
	<br /><br />{l s='An email has been sent with this information.' mod='emspaybanktransfer'}
	<br /><br /> <strong>{l s='Your order will be sent as soon as we receive payment.' mod='emspaybanktransfer'}</strong>
	<br /><br />{l s='If you have questions, comments or concerns, please contact' mod='emspaybanktransfer'} <a href="{$link->getPageLink('contact', true)|escape:'html'}">{l s='us' mod='emspaybanktransfer'}</a>.
</p>
{else}
<p class="warning">
	{l s='We noticed a problem with your order. If you think this is an error, feel free to contact' mod='emspaybanktransfer'}
	<a href="{$link->getPageLink('contact', true)|escape:'html'}">{l s='us' mod='emspaybanktransfer'}</a>.
</p>
{/if}