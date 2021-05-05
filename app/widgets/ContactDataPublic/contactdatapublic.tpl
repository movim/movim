<ul id="contact_public_data" class="contact_data list card">
    <br />
    {autoescape="off"}
        {$c->prepareCard($contact)}
    {/autoescape}

    {autoescape="off"}
        {$c->prepareSubscriptions($subscriptions)}
    {/autoescape}
</ul>
