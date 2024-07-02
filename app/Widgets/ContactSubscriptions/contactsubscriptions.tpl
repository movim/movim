<ul id="{$jid|cleanupId}_contact_subscriptions" class="contact_subscriptions list card">
    {autoescape="off"}
        {$c->prepareSubscriptions($jid)}
    {/autoescape}
</ul>
