{loop="$chats"}
    {if="$emptyItems"}
        {$c->prepareEmptyChat($key)}
    {else}
        {$c->prepareChat($key)}
    {/if}
{/loop}
