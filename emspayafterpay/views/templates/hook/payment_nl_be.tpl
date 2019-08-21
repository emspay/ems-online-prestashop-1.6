{extends file='./payment.tpl'}

{block name="afterpay_text"}
    <form id="emspayafterpay_form" name="emspayafterpay_form" action="{$link->getModuleLink('emspayafterpay', 'payment')|escape:'html'}" method="post">
        {l s='Pay by AfterPay' mod='emspayafterpay'}
        &nbsp;&nbsp;
        <span>
            <a href="{$terms_and_condition_url}" target="_blank">
                {l s='Terms & Conditions' mod='emspayafterpay'}
            </a>
        </span>&nbsp;&nbsp;
        <button type="submit" value="Submit">{l s='Agree and Proceed' mod='emspayafterpay'}</button>
    </form>
{/block}