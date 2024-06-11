<style>
    .emspayideal::after {
        display: block;
        content: "\f054";
        position: absolute;
        right: 30px;
        margin-top: -11px;
        top: 50%;
        font-family: "FontAwesome";
        font-size: 25px;
        height: 22px;
        width: 14px;
        color: #777;
    }
    .emspayideal {
        background: url({$base_dir}modules/emspayideal/logo_bestelling.png) 15px 12px no-repeat
    }
</style>
<div class="row">
    <div class="col-xs-12">
        <p class="payment_module">
            <a class="emspayideal" href="{$link->getModuleLink('emspayideal', 'payment')|escape:'html'}" title="{l s='Pay by iDEAL' mod='emspayideal'}">
                {l s='Pay by iDEAL' mod='emspayideal'}</span>
            </a>
        </p>
    </div>
</div>