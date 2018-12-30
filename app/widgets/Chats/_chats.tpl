{loop="$chats"}
    {if="$emptyItems"}
        {autoescape="off"}
            {$c->prepareEmptyChat($key)}
        {/autoescape}
    {else}
        {autoescape="off"}
            {$c->prepareChat($key, $contacts->get($key), $rosters->get($key))}
        {/autoescape}
    {/if}
{/loop}
