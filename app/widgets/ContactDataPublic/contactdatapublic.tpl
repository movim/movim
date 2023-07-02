<ul id="contact_public_data" class="contact_data list card">
    <br />
    {if="$contact->isPublic()"}
        {autoescape="off"}
            {$c->prepareCard($contact)}
        {/autoescape}

        {autoescape="off"}
            {$c->prepareSubscriptions($jid)}
        {/autoescape}
    {/if}
</ul>
