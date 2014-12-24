<ul class="middle">
    {loop="$messages"}
        <li {if="$value->jidfrom == $jid"}class="oppose"{/if}>
            <span class="icon bubble">
                {if="$value->jidfrom == $jid"}
                    <img src="{$contact->getPhoto('s')}">
                {else}
                    <img src="{$me->getPhoto('s')}">
                {/if}
            </span>
            <div class="bubble">
                {if="preg_match('#^\?OTR#', $value->body)"}
                    <i class="md md-lock"></i> {$c->__('message.encrypted')}
                {else}
                    {$value->body|prepareString}
                {/if}
            <span class="info">{$value->delivered|strtotime|prepareDate}</span>
            </div>
        </li>
    {/loop}
</ul>
