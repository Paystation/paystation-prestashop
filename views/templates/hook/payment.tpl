<p class="payment_module">
    <a href="{$link->getModuleLink('paystation', 'payment', [], true)|escape:'html'}"
       title="{l s='Pay by Paystation Payment Gateway.' mod='paystation'}">
        <img src="{$this_path_cheque}logo.png" alt="{l s='Pay by Paystation Payment Gateway.' mod='paystation'}"/>
        --- <br> {l s='You will be redirected to Paystation Payment Gateway to complete your payment.' mod='paystation'}
    </a>
</p>
