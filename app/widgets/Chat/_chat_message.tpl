{if="$message->body != ''"}
<li {if="$message->jidfrom != $jid"}class="oppose"{/if}>
    {if="$message->jidfrom == $jid"}
        {$url = $contact->getPhoto('s')}
        {if="$url"}
            <span class="icon bubble">
                <img src="{$url}">
            </span>
        {elseif="$message->type == 'groupchat'"}
            <span class="icon bubble color {$message->resource|stringToColor}">
                <i class="md md-person"></i>
            </span>        
        {else}
            <span class="icon bubble color {$contact->jid|stringToColor}">
                <i class="md md-person"></i>
            </span>
        {/if}
    {else}
        <span class="icon bubble">
            <img src="{$me->getPhoto('s')}">
        </span>
    {/if}

    {if="preg_match('#^\/me#', $message->body)"}
        {$message->body = '* '.substr($message->body, 3)}
        {$class = 'quote'}
    {else}
        {$class = ''}
    {/if}
    
    <div class="bubble {$class}">
        {if="preg_match('#^\?OTR#', $message->body)"}
            <i class="md md-lock"></i> {$c->__('message.encrypted')}
        {else}
            {if="isset($message->html)"}
                {$message->html|prepareString}
            {else}
                {$message->body|htmlentities:ENT_COMPAT,'UTF-8'|prepareString}
            {/if}
        {/if}
        <span class="info">{$message->delivered|strtotime|prepareDate}</span>
        {if="$message->type == 'groupchat'"}
            <span class="info">{$message->resource} - </span>
        {/if}
    </div>
</li>
{/if}
