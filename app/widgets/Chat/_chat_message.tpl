{if="$message->body != ''"}
<li {if="$message->jidfrom != $jid"}class="oppose"{/if}>
    <span class="icon bubble {if="empty($contact) && !array_key_exists($message->resource, $contacts)"}color {$message->resource|stringToColor}{/if}">
        {if="$message->jidfrom == $jid"}
            {if="!empty($contacts) && array_key_exists($message->resource, $contacts)"}
                <img src="{$contacts[$message->resource]->getPhoto('s')}">
            {elseif="isset($contact)"}
                <img src="{$contact->getPhoto('s')}">
            {else}
                {$message->resource|firstLetterCapitalize}
            {/if}
        {else}
            <img src="{$me->getPhoto('s')}">
        {/if}
    </span>
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
