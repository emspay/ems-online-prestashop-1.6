{include file="$tpl_dir/errors.tpl"}

<h1>{l s='Your order at %s' sprintf=$shop_name mod='emspayafterpay'}</h1>

<h3>{l s='There was an error processing your order' mod='emspayafterpay'}</h3>

<div class="error">
    <p><strong>{$error_message}</strong></p>
    <p>{l s='Please choose another payment option to complete your order. We apologize for the inconvenience.' mod='emspayafterpay'}</p>
</div>

<a href="{$checkout_url}" title="{l s='Please click here to try again.' mod='emspayafterpay'}" class="button-exclusive btn btn-default">
    <i class="icon-chevron-left"></i>
    {l s='Go back to the checkout page' mod='emspayafterpay'}
</a>
