<h1>{l s='Paystation payment unsuccessful.' mod='paystation'}</h1>
<p class="warning">
    Error message: <b>{$error_message}</b><br/><br/>
    {l s=' If you think this message in error, you can contact our' mod='paystation'}
    <a href="{$link->getPageLink('contact', true)|escape:'html'}">{l s='customer service department.' mod='paystation'}</a>
<p>
<p>
    <a href="{$retry_link}">Click here to try again</a>
</p>