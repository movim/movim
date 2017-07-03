<div class="placeholder icon">
</div>

<ul class="list flex middle active">
    {if="$top"}
        <li class="subheader block large">
            <p>{$c->__('chat.frequent')}</p>
        </li>
    {/if}
    {loop="$top"}
        <li class="block {if="$value->last > 60"} inactive{/if}"
            onclick="Chats_ajaxOpen('{$value->jid}'); Chat_ajaxGet('{$value->jid}');">
            {$url = $value->getPhoto('s')}
            {if="$url"}
                <span class="primary icon bubble
                    {if="$value->value"}
                        status {$presencestxt[$value->value]}
                    {/if}">
                    <img src="{$url}">
                </span>
            {else}
                <span class="primary icon bubble color {$value->jid|stringToColor}
                    {if="$value->value"}
                        status {$presencestxt[$value->value]}
                    {/if}">
                    <i class="zmdi zmdi-account"></i>
                </span>
            {/if}
            <p>{$value->getTrueName()}</p>
            <p>{$value->jid}</p>
        </li>
    {/loop}

    {if="$conferences"}
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
                    {$value->occupants} <i class="zmdi zmdi-accounts"></i>
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
