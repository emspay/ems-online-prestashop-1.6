{extends file='./payment.tpl'}

{block name="afterpay_text"}
    <form id="ingpspafterpay_form" name="ingpspafterpay_form" action="{$link->getModuleLink('ingpspafterpay', 'payment')|escape:'html'}" method="post">
        {l s='Pay by AfterPay' mod='ingpspafterpay'}
        &nbsp;&nbsp;
        <span>
            <a href="{$terms_and_condition_url}" target="_blank">
                {l s='Terms & Conditions' mod='ingpspafterpay'} 
            </a>
        </span>&nbsp;&nbsp;
        <button type="submit" value="Submit">{l s='Agree and Proceed' mod='ingpspafterpay'}</button>
    </form>
{/block}