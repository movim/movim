<li
    id="{$contact->jid|cleanupId}-chat-item"
    data-jid="{$contact->jid}"
    class="
        {if="isset($message)"}condensed{/if}
        {if="isset($contact->value) && $contact->value > 4"}faded{/if}
        {if="isset($contact->last) && $contact->last > 60"} inactive{/if}
        {if="$caps && in_array($caps->type, array('handheld', 'phone', 'web'))"}
            action
        {/if}
        "
    title="{$contact->jid}{if="isset($message)"} – {$message->published|strtotime|prepareDate}{/if}">
    {$url = $contact->getPhoto('s')}
    {if="$url"}
        <span class="primary icon bubble {if="isset($presence)"}status {$presence}{/if}">
            <img src="{$url}">
        </span>
    {else}
        <span class="primary icon bubble color {$contact->jid|stringToColor} {if="isset($presence)"}status {$presence}{/if}">
            {$contact->getTrueName()|firstLetterCapitalize}
        </span>
    {/if}

    <span data-key="chat|{$contact->jid}" class="counter bottom"></span>
    <p class="normal line">
        {if="isset($message)"}
            <span class="info" title="{$message->published|strtotime|prepareDate}">
                {$message->published|strtotime|prepareDate:true,true}
            </span>
        {/if}
        {if="strpos($contact->jid, '/') != false"}
            {$contact->jid}
        {else}
            {$contact->getTrueName()}
        {/if}

        {if="$caps"}
            <span class="second"><i class="zmdi
            {if="in_array($caps->type, ['handheld', 'phone'])"}
                zmdi-smartphone
            {elseif="$caps->type == 'bot'"}
                zmdi-memory
            {elseif="$caps->type == 'web'"}
                {if="$caps->name == 'Movim'"}
                    zmdi-cloud-outline
                {else}
                    zmdi-globe-alt
                {/if}
            {/if}
            "></i></span>
        {/if}
    </p>
    {if="isset($status)"}
        <p>{$status}</p>
    {elseif="isset($message)"}
        {if="preg_match('#^\?OTR#', $message->body)"}
            <p><i class="zmdi zmdi-lock"></i> {$c->__('message.encrypted')}</p>
        {elseif="stripTags($message->body) != ''"}
            <p class="line">{$message->body|stripTags}</p>
        {/if}
    {/if}
</li>
