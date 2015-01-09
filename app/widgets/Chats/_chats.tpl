{if="$chats == null"}
    <li class="condensed">
        <span class="icon bubble color green"><i class="md md-chat"></i></span>
        <p>{$c->__('chats.empty')}</p>
    </li>
{/if}

{loop="$chats"}
    <li
        data-jid="{$value->jid}"
        {if="isset($messages[$value->jid])"}class="condensed"{/if}
        title="{$value->jid}">
        <span data-key="chat|{$value->jid}" class="counter bottom"></span>
        <span class="icon bubble">
            <img src="{$value->getPhoto('s')}">
        </span>
        <span>{$value->getTrueName()}</span>
        {if="isset($messages[$value->jid])"}
            <span class="info">{$messages[$value->jid]->delivered|strtotime|prepareDate}</span>
            {if="preg_match('#^\?OTR#', $messages[$value->jid]->body)"}
                <p><i class="md md-lock"></i> {$c->__('message.encrypted')}</p>
            {else}
                <p>{$messages[$value->jid]->body}</p>
            {/if}
        {/if}
    </li>
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
