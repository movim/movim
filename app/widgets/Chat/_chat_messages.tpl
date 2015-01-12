<ul class="middle">
    {loop="$messages"}
        {if="$value->body != ''"}
        <li {if="$value->jidfrom != $jid"}class="oppose"{/if}>
            <span class="icon bubble color {$value->resource|stringToColor}">
                {if="$value->jidfrom == $jid"}
                    {if="$contact->updated != null"}
                        <img src="{$contact->getPhoto('s')}">
                    {else}
                        {$value->resource|firstLetterCapitalize}
                    {/if}
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
        {/if}
    {/loop}
    {if="$status != false"}
        <li {if="$myself == false"}class="oppose"{/if}>
            <span class="icon bubble">
                {if="$myself == false"}
                    <img src="{$contact->getPhoto('s')}">
                {else}
                    <img src="{$me->getPhoto('s')}">
                {/if}
            </span>
            <div class="bubble">
                {if="$status == 'composing'"}
                    <i class="md md-mode-edit"></i> {$c->__('message.composing')}
                {else}
                    <i class="md md-mode-edit"></i> {$c->__('message.paused')}
                {/if}
            </div>
        </li>
    {/if}
</ul>
