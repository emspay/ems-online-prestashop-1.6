<style>
a.emspaywechat::after {
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
a.emspaywechat{
      padding-left: 0 !important;
}
      span.amexlogo{
            margin-left: 15px;
      }
      span.amexlogo img{
            width: 64px;
            height: auto;
      }
      span.amextitle{
            padding-left: 20px;
      }
</style>
<div class="row">
      <div class="col-xs-12">
            <p class="payment_module">

                  <a class="emspaywechat" href="{$link->getModuleLink('emspaywechat', 'payment')|escape:'html'}" title="{l s='Pay by WeChat' mod='emspaywechat'}">
                        <span class="amexlogo"><img src={$base_dir}modules/emspaywechat/logo_bestelling.png></span>
                        <span class="amextitle">{l s='Pay by WeChat' mod='emspaywechat'}<span>
                  </a>
            </p>
      </div>
</div>