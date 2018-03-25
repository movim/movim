<li
    id="{$contact->jid|cleanupId}-chat-item"
    data-jid="{$contact->jid}"
    class="
        {if="isset($message)"}condensed{/if}
        {if="$roster && $roster->presence"}
            {if="$roster->presence->value > 4"}faded{/if}
            {if="$roster->presence->last > 60"} inactive{/if}
            {if="$roster->presence->capability && in_array($roster->presence->capability->type, array('handheld', 'phone', 'web'))"}
                action
            {/if}
        {/if}
        "
    title="{$contact->jid}{if="isset($message)"} – {$message->published|strtotime|prepareDate}{/if}">
    {$url = $contact->getPhoto('s')}
    {if="$url"}
        <span class="primary icon bubble {if="$roster && $roster->presence"}status {$roster->presence->presencekey}{/if}">
            <img src="{$url}">
        </span>
    {else}
        <span class="primary icon bubble color {$contact->jid|stringToColor} {if="$roster && $roster->presence"}status {$roster->presence->presencekey}{/if}">
            {$contact->truename|firstLetterCapitalize}
        </span>
    {/if}

    <span data-key="chat|{$contact->jid}" class="counter bottom"></span>
    <p class="normal line">
        {if="isset($message)"}
            <span class="info" title="{$message->published|strtotime|prepareDate}">
                {$message->published|strtotime|prepareDate:true,true}
            </span>
        {/if}
        {if="$roster"}
            {$roster->truename}
        {elseif="strpos($contact->jid, '/') != false"}
            {$contact->jid}
        {else}
            {$contact->truename}
        {/if}

        {if="$roster && $roster->presence && $roster->presence->capability"}
            <span class="second">
                <i class="zmdi {$roster->presence->capability->getDeviceIcon()}"></i>
            </span>
        {/if}
    </p>
    {if="$roster && $roster->presence && $roster && $roster->presence->status"}
        <p class="line">{$roster->presence->status}</p>
    {elseif="isset($message)"}
        {if="preg_match('#^\?OTR#', $message->body)"}
            <p><i class="zmdi zmdi-lock"></i> {$c->__('message.encrypted')}</p>
        {elseif="stripTags($message->body) != ''"}
            <p class="line">{$message->body|stripTags}</p>
        {/if}
    {/if}
</li>
