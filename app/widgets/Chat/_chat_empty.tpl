<div class="placeholder">
    <i class="material-icons">forum</i>
</div>

<ul class="list flex middle active">
    {if="$top->isNotEmpty()"}
        <li class="subheader block large">
            <p>{$c->__('chat.frequent')}</p>
        </li>
    {/if}
    {loop="$top"}
        <li class="block {if="$value->last > 60"} inactive{/if}"
            onclick="Chats_ajaxOpen('{$value->jid|echapJS}'); Chat_ajaxGet('{$value->jid|echapJS}');">
            {$url = $value->getPhoto('m')}
            {if="$url"}
                <span class="primary icon bubble
                    {if="$value->presence"}
                        status {$value->presence->presencekey}
                    {/if}">
                    <img src="{$url}">
                </span>
            {else}
                <span class="primary icon bubble color {$value->jid|stringToColor}
                    {if="$value->presence"}
                        status {$value->presence->presencekey}
                    {/if}">
                    <i class="material-icons">person</i>
                </span>
            {/if}
            <p class="line">
                {$value->truename}

                {if="$value->presence && $value->presence->capability"}
                    <span class="second" title="{$value->presence->capability->name}">
                        <i class="material-icons">{$value->presence->capability->getDeviceIcon()}</i>
                    </span>
                {/if}
            </p>
            <p class="line">{$value->jid}</p>
        </li>
    {/loop}

    {if="$conferences->isNotEmpty()"}
        <li class="subheader block large">
            <p>{$c->__('chatrooms.title')}</p>
        </li>
    {/if}
    {loop="$conferences"}
        <li class="block"
            onclick="Rooms_ajaxAdd('{$value->server}')"
            title="{$value->server}">
            <span class="primary icon bubble color {$value->name|stringToColor}">
                {$value->name|firstLetterCapitalize}
            </span>

            <p class="line">{$value->name}
                <span class="second">{$value->server}</span>
            </p>
            <p class="line" title="{$value->description}">
                {if="$value->occupants > 0"}
                    <span title="{$c->__('communitydata.sub', $value->occupants)}">
                        {$value->occupants} <i class="material-icons">people</i>
                    </span>
                {/if}
                {if="$value->occupants > 0 && !empty($value->description)"}  – {/if}
                {$value->description}
            </p>
        </li>
    {/loop}
</ul>
<br />
<br />
