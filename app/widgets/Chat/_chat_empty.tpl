<div class="placeholder">
    <i class="material-icons">forum</i>
</div>

<ul class="list flex fifth card shadow compact middle active">
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
                    <p class="line" title="{$value->truename}">
                        {$value->truename}

                        {if="$value->presence && $value->presence->capability"}
                            <span class="second" title="{$value->presence->capability->name}">
                                <i class="material-icons">{$value->presence->capability->getDeviceIcon()}</i>
                            </span>
                        {/if}
                    </p>

                    {if="$value->presence && $value->presence->seen"}
                        <p class="line" title="{$c->__('last.title')} {$value->presence->seen|strtotime|prepareDate:true,true}">
                            {$c->__('last.title')} {$value->presence->seen|strtotime|prepareDate:true,true}
                        </p>
                    {elseif="$value->presence"}
                        <p class="line">{$value->presence->presencetext}</p>
                    {else}
                        <p></p>
                    {/if}
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
