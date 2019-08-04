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
#emspayideal_form {
    display: block;
    border: 1px solid #d6d4d4;
    -moz-border-radius: 4px;
    -webkit-border-radius: 4px;
    border-radius: 4px;
    font-size: 17px;
    line-height: 23px;
    color: #333;
    font-weight: bold;
    padding: 33px 40px 34px 99px;
    letter-spacing: -1px;
    position: relative; 
}
</style>
<div class="row">
	<div class="col-xs-12">
		<p class="payment_module">
                  <div class='emspayideal'>
                  <form id="emspayideal_form" name="emspayideal_form" action="{$link->getModuleLink('emspayideal', 'payment')|escape:'html'}" method="post">
                  {l s='Pay by iDEAL' mod='emspayideal'}<br />
                  {l s='Choose your bank' mod='emspayideal'}
                  &nbsp;&nbsp;
                  <select name="issuerid" id="issuerid">
                        <option value="">{l s='Choose your bank' mod='emspayideal'}</option>
                  
                        {foreach from=$issuers item=issuer}
                              <option value="{$issuer.id}">{$issuer.name}</option>
                        {/foreach}  
                  </select>            
                  </form>
                  </div>
		</p>
	</div>
</div>
<script type="text/javascript">
      var mess_emspay__error = "{l s='Choose your bank' mod='emspayideal' js=1}";
      {literal}
            $(document).ready(function(){

                  $('#issuerid').change(function()
                        {
                        if ($('#issuerid').val() == '')
                        {
                              alert(mess_emspay__error);
                        }
                        else
                        {
                              $('#emspayideal_form').submit();
                        }
                        return false;
                  });
            });
      {/literal}
</script>