<h3>{l s='Your order at %s' sprintf=$shop_name mod='ingpspbanktransfer'}</h3>

{if $status == 'pending'}
<p class="warning">{l s='The status of your order on %s could not be determined.' sprintf=$shop_name mod='ingpspbanktransfer'}
</p>
{else if $status == 'ok'}
    <p>     
        {l s='Your order on %s is pending.' sprintf=$shop_name mod='ingpspbanktransfer'}
	<br /><br />
	{l s='To complete your order, transfer the amount using the information below' mod='ingpspbanktransfer'}
	<br /><br />- {l s='Amount:' mod='ingpspbanktransfer'} <span class="price"><strong>{$total_to_pay}</strong></span>
	<br /><br />- {l s='Account holder: ' mod='ingpspbanktransfer'}  <strong>ING PSP</strong>
	<br /><br />- {l s='Account IBAN: ' mod='ingpspbanktransfer'}  <strong>NL13INGB0005300060</strong>
	<br /><br />- {l s='Do not forget to insert the reference %s in the subject of your banktransfer.' sprintf=$reference mod='ingpspbanktransfer'}
	<br /><br />{l s='An email has been sent with this information.' mod='ingpspbanktransfer'}
	<br /><br /> <strong>{l s='Your order will be sent as soon as we receive payment.' mod='ingpspbanktransfer'}</strong>
	<br /><br />{l s='If you have questions, comments or concerns, please contact' mod='ingpspbanktransfer'} <a href="{$link->getPageLink('contact', true)|escape:'html'}">{l s='us' mod='ingpspbanktransfer'}</a>.
</p>
{else}
<p class="warning">
	{l s='We noticed a problem with your order. If you think this is an error, feel free to contact' mod='ingpspbanktransfer'} 
	<a href="{$link->getPageLink('contact', true)|escape:'html'}">{l s='us' mod='ingpspbanktransfer'}</a>.
</p>
{/if}