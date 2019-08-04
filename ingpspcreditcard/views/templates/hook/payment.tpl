<style>
a.ingpspcreditcard::after {
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
a.ingpspcreditcard {
      background: url({$base_dir}modules/ingpspcreditcard/logo_bestelling.png) 15px 12px no-repeat      
}
</style>
<div class="row">
      <div class="col-xs-12">
            <p class="payment_module">
                  <a class="ingpspcreditcard" href="{$link->getModuleLink('ingpspcreditcard', 'payment')|escape:'html'}" title="{l s='Pay by Mastercard, VISA, Maestro or V PAY' mod='ingpspcreditcard'}">
                        {l s='Pay by Mastercard, VISA, Maestro or V PAY' mod='ingpspcreditcard'}</span>
                  </a>
            </p>
      </div>
</div>