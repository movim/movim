<div class="placeholder">
    <i class="material-icons">forum</i>
</div>

<ul class="list flex quarter card shadow compact middle active">
    {if="$top->isNotEmpty()"}
        <li class="subheader block large">
            <div>
                <p>{$c->__('chat.frequent')}</p>
            </div>
        </li>

        {loop="$top"}
            <li class="block {if="$value->last > 60"} inactive{/if}"
                onclick="Chats_ajaxOpen('{$value->jid|echapJS}'); Chat.get('{$value->jid|echapJS}');">
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
                <div>
                    <p class="line">
                        {$value->truename}

                        {if="$value->presence && $value->presence->capability"}
                            <span class="second" title="{$value->presence->capability->name}">
                                <i class="material-icons">{$value->presence->capability->getDeviceIcon()}</i>
                            </span>
                        {/if}
                    </p>
                    <p class="line">{$value->jid}</p>
                </div>
            </li>
        {/loop}
    {/if}
</ul>

<div id="chat_explore">
    {autoescape="off"}
        {$c->prepareExplore()}
    {/autoescape}
</div>

<br />
<br />
