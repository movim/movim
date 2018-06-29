<li class="subheader">
    <p class="normal">
        {$c->__('page.chats')}
    </p>
</li>

{loop="$chats"}
    {if="$emptyItems"}
        {$c->prepareEmptyChat($key)}
    {else}
        {$c->prepareChat($key)}
    {/if}
{/loop}
