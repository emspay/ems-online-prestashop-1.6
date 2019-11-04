<style>
a.emspayapplepay::after {
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
a.emspayapplepay{
      padding-left: 0px !important;
}
      span.applelogo{
            margin-left: 15px;
      }
      span.applelogo img{
            width: 64px;
            height: auto;
      }
      span.appletitle{
            padding-left: 20px;
      }
</style>
<div class="row">
      <div class="col-xs-12">
            <p class="payment_module">

                  <a class="emspayapplepay" href="{$link->getModuleLink('emspayapplepay', 'payment')|escape:'html'}" title="{l s='Pay by Apple Pay' mod='emspayapplepay'}">
                        <span class="applelogo"><img src={$base_dir}modules/emspayapplepay/logo_bestelling.png></span>
                        <span class="appletitle">{l s='Pay by Apple Pay' mod='emspayapplepay'}<span>
                  </a>
            </p>
      </div>
</div>