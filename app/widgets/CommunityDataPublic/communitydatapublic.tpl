<ul id="community_data_public" class="list card">
    <br />
    {autoescape="off"}
        {$c->prepareCard($info)}
    {/autoescape}

    {if="$subscriptions->isNotEmpty()"}
        {autoescape="off"}
            {$c->preparePublicSubscriptions($subscriptions)}
        {/autoescape}
    {/if}
</ul>
