<h3>{l s='Your order at %s' sprintf=$shop_name mod='ingpspcashondelivery'}</h3>
{if $status == 'ok'}
<p>{l s='Your order on %s is complete.' sprintf=$shop_name mod='ingpspcashondelivery'}
	<br /><br />
	{l s='You have chosen the cash on delivery method.' mod='ingpspcashondelivery'}
	<br /><br /><span class="bold">{l s='Your order will be sent very soon.' mod='ingpspcashondelivery'}</span>
	<br /><br />{l s='For any questions or for further information, please contact our' mod='ingpspcashondelivery'} <a href="{$link->getPageLink('contact-form', true)|escape:'html'}">{l s='customer support' mod='ingpspcashondelivery'}</a>.
</p>
{else}
<p class="warning">
	{l s='We noticed a problem with your order. If you think this is an error, feel free to contact' mod='ingpspcashondelivery'} 
	<a href="{$link->getPageLink('contact', true)|escape:'html'}">{l s='us' mod='ingpspcashondelivery'}</a>.
</p>
{/if}