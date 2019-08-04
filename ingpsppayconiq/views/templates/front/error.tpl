{include file="$tpl_dir/errors.tpl"}

<h1>{l s='Your order at %s' sprintf=$shop_name mod='ingpsppayconiq'}</h1>

<h3>{l s='There was an error processing your order' mod='ingpsppayconiq'}</h3>

<div class="error">
    <p><strong>{$error_message}</strong></p>
    <p>
        <a href="{$checkout_url}">
            {l s='Please click here to choose another payment method.' mod='ingpsppayconiq'}
        </a>
    </p>
</div>

<a href="{$checkout_url}" title="{l s='Please click here to try again.' mod='ingpsppayconiq'}" class="button-exclusive btn btn-default">
    <i class="icon-chevron-left"></i>
    {l s='Go back to the checkout page' mod='ingpsppayconiq'}
</a>
