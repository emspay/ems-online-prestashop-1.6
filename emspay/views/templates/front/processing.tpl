{include file="$tpl_dir./errors.tpl"}

<h3>
    {l s='Your order at %s' sprintf=$shop_name mod='emspay'}
</h3>

<p>
    {l s='Please wait while your order status is being checked...' mod='emspay'}
</p>

<div><img src="{$modules_dir}emspay/ajax-loader.gif"/></div>

<script language="JavaScript">
    {literal}
    var fallback_url = '{/literal}{$fallback_url}{literal}';
    var validation_url = '{/literal}{$validation_url}{literal}';
    {/literal}
</script>

<script type="text/javascript" src="{$modules_dir}emspay/processing.js"></script>