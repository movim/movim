<li class="subheader">
    <div>
        <p>{$c->__('sendto.contact')}</p>
    </div>
</li>
{loop="$contacts"}
    <li data-jid="{$value->jid}">
        <span class="primary icon bubble small {if="$value->presence"}status {$value->presence->presencekey}{/if}">
            <img src="{$value->getPicture(\Movim\ImageSize::M)}">
        </span>
        <span class="control icon active gray" onclick="SendTo_ajaxSend('{$value->jid|echapJS}', false, '{$uri}')">
            <i class="material-symbols">send</i>
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
