<li class="subheader">
    <div>
        <p>{$c->__('sendto.contact')}</p>
    </div>
</li>
{loop="$contacts"}
    <li class="{if="$value->presence && $value->presence->value > 4"}faded{/if}">
        <span class="primary icon bubble small
            {if="!$value->presence || $value->presence->value > 4"}
                disabled
            {else}
                status {$value->presence->presencekey}
            {/if}">
            <img src="{$value->getPhoto('m')}">
        </span>
        <span class="control icon active gray" onclick="SendTo_ajaxSend('{$value->jid|echapJS}', {'uri': '{$uri}'}, false, '{$openlink}')">
            <i class="material-icons">send</i>
        </span>
        <div>
            <p class="normal line">
                {$value->truename}
                {if="$value->group"}
                    <span class="tag color {$value->group|stringToColor}">
                        {$value->group}
                    </span>
                {/if}

                <span class="second">{$value->jid}</span>
            </p>
        </div>
    </li>
{/loop}