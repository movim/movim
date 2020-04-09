<li class="subheader"><p>{$c->__('sendto.contact')}</p></li>
{loop="$contacts"}
    <li class="{if="$value->presence && $value->presence->value > 4"}faded{/if}"">
        {$url = $value->getPhoto('m')}
        {if="$url"}
            <span class="primary icon bubble
                {if="!$value->presence || $value->presence->value > 4"}
                    disabled
                {else}
                    status {$value->presence->presencekey}
                {/if}"
                style="background-image: url({$url});">
            </span>
        {else}
            <span class="primary icon bubble color {$value->jid|stringToColor}
                {if="!$value->presence || $value->presence->value > 4"}
                    disabled
                {else}
                    status {$value->presence->presencekey}
                {/if}"
            >
                <i class="material-icons">person</i>
            </span>
        {/if}
        <span class="control icon active gray" onclick="SendTo_ajaxSend('{$value->jid}', {'uri': '{$uri}'}, false, '{$openlink}')">
            <i class="material-icons">send</i>
        </span>
        <div>
            <p class="normal line">{$value->truename}</p>
            <p>
                {$value->jid}
                {if="$value->group"}
                    <span class="tag color {$value->group|stringToColor}">
                        {$value->group}
                    </span>
                {/if}
            </p>
        </div>
    </li>
{/loop}