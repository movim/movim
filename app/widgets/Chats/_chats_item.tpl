<li
    id="{$contact->jid}_chat_item"
    data-jid="{$contact->jid}"
    class="
        {if="isset($message)"}condensed{/if}
        {if="$contact->last > 60"} inactive{/if}
        {if="$caps && in_array($caps->type, array('handheld', 'phone', 'web'))"}
            action
        {/if}
        "
    title="{$contact->jid}">
    {$url = $contact->getPhoto('s')}
    {if="$url"}
        <span class="primary icon bubble {if="isset($presence)"}status {$presence}{/if}">
            <img src="{$url}">
        </span>
    {else}
        <span class="primary icon bubble color {$contact->jid|stringToColor} {if="isset($presence)"}status {$presence}{/if}">
            <i class="zmdi zmdi-account"></i>
        </span>
    {/if}

    {if="$caps && in_array($caps->type, array('handheld', 'phone'))"}
        <span class="control icon gray">
            <i class="zmdi zmdi-smartphone"></i>
        </span>
    {/if}
    {if="$caps && $caps->type == 'web'"}
        <span class="control icon gray">
            <i class="zmdi zmdi-globe-alt"></i>
        </span>
    {/if}
    <span data-key="chat|{$contact->jid}" class="counter bottom"></span>
    <p class="normal">
        <span class="info">{$message->published|strtotime|prepareDate}</span>
        {$contact->getTrueName()}
    </p>
    {if="isset($status)"}
        <p>{$status}</p>
    {else}
        {if="isset($message)"}
            {if="preg_match('#^\?OTR#', $message->body)"}
                <p><i class="zmdi zmdi-lock"></i> {$c->__('message.encrypted')}</p>
            {elseif="stripTags(prepareString($message->body)) != ''"}
                <p>{$message->body|prepareString|stripTags}</p>
            {/if}
        {/if}
    {/if}
</li>
