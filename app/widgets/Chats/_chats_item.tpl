<li
    id="{$contact->jid}"
    data-jid="{$contact->jid}"
    {if="isset($message)"}class="condensed"{/if}
    title="{$contact->jid}">
    <span data-key="chat|{$contact->jid}" class="counter bottom"></span>
    <span class="icon bubble {if="isset($presence)"}status {$presence}{/if}">
        <img src="{$contact->getPhoto('s')}">
    </span>
    <span>{$contact->getTrueName()}</span>
    {if="isset($message)"}
        <span class="info">{$message->delivered|strtotime|prepareDate}</span>
        {if="preg_match('#^\?OTR#', $message->body)"}
            <p><i class="md md-lock"></i> {$c->__('message.encrypted')}</p>
        {else}
            <p>{$message->body}</p>
        {/if}
    {/if}
</li>
