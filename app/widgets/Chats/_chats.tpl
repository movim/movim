{if="$chats == null"}
    <li class="condensed">
        <span class="icon bubble color green"><i class="md md-chat"></i></span>
        <p>{$c->__('chats.empty')}</p>
    </li>
{/if}

{loop="$chats"}
    {$c->prepareChat($key)}
{/loop}
<li class="subheader">
    {$c->__('chatrooms.title')}
</li>
{loop="$conferences"}
    <li data-jid="{$value->conference}">
        <span class="icon bubble color {$value->name|stringToColor}">{$value->name|firstLetterCapitalize}</span>
        <span>{$value->name}</span>
    </li>
{/loop}
