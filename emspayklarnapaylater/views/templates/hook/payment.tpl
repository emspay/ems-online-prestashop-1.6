<style>
    a.emspayklarnapaylater::after {
        display: block;
        content: "\f054";
        position: absolute;
        right: 15px;
        margin-top: -11px;
        top: 50%;
        font-family: "FontAwesome";
        font-size: 25px;
        height: 22px;
        width: 14px;
        color: #777;
    }
    a.emspayklarnapaylater {
        background: url({$base_dir}modules/emspayklarnapaylater/logo_bestelling.png) 15px 12px no-repeat;
    }
</style>

<div class="row">
    <div class="col-xs-12">
        <p class="payment_module">
            <a class="emspayklarnapaylater" href="{$link->getModuleLink('emspayklarnapaylater', 'payment')|escape:'html'}"
               title="{l s='Pay by Klarna Pay Later' mod='emspayklarnapaylater'}">
                {l s='Pay by Klarna Pay Later' mod='emspayklarnapaylater'}
            </a>
        </p>
    </div>
</div>
