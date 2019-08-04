<style>

    .ingpspafterpay {
        background: url({$base_dir}modules/ingpspafterpay/logo_bestelling.png) 15px 40px no-repeat
    }

    #ingpspafterpay_form, #ingpspafterpay_empty {
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

    .ingpspafterpay::after {
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

</style>

<div class="row">
    <div class="col-xs-12">
        <p class="payment_module">
            <div class='ingpspafterpay'>{block name="afterpay_text"}{/block}</div>    
        </p>
    </div>
</div>
