<ul class="middle">
    {loop="$messages"}
        {if="$value->body != ''"}
        <li {if="$value->jidfrom != $jid"}class="oppose"{/if}>
            <span class="icon bubble {if="$contact->updated == null && !array_key_exists($value->resource, $contacts)"}color {$value->resource|stringToColor}{/if}">
                {if="$value->jidfrom == $jid"}
                    {if="$contact->updated != null"}
                        <img src="{$contact->getPhoto('s')}">
                    {elseif="array_key_exists($value->resource, $contacts)"}
                        <img src="{$contacts[$value->resource]->getPhoto('s')}">
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
                    {if="isset($value->html)"}
                        {$value->body}
                    {else}
                        {$value->body|htmlentities:ENT_COMPAT,'UTF-8'|prepareString}
                    {/if}
                {/if}
                <span class="info">{$value->delivered|strtotime|prepareDate}</span>
                {if="$value->type == 'groupchat'"}
                    <span class="info">{$value->resource} - </span>
                {/if}
            </div>
        </li>
        {/if}
    {/loop}
    {if="$status != false"}
        <li {if="$myself != false"}class="oppose"{/if}>
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
