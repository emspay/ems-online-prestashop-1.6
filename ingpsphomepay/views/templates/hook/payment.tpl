<style>
a.ingpsphomepay::after {
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
a.ingpsphomepay {
      background: url({$base_dir}modules/ingpsphomepay/logo_bestelling.png) 15px 12px no-repeat
}
</style>
<div class="row">
      <div class="col-xs-12">
            <p class="payment_module">
                  <a class="ingpsphomepay" href="{$link->getModuleLink('ingpsphomepay', 'payment')|escape:'html'}" title="{l s='Pay by Home\'Pay' mod='ingpsphomepay'}">
                        {l s='Pay by Home\'Pay' mod='ingpsphomepay'}</span>
                  </a>
            </p>
      </div>
</div>