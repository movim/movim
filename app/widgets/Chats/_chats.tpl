{if="$chats == null"}
    <li class="condensed">
        <span class="icon bubble color green"><i class="md md-chat"></i></span>
        <p>{$c->__('chats.empty')}</p>
    </li>
{/if}

{loop="$chats"}
    {$c->prepareChat($key)}
{/loop}
