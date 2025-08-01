<div class="placeholder">
    <i class="material-symbols fill">forum</i>
</div>

<div id="chat_frequent">
    <ul class="list flex fifth card shadow compact middle active">
        {if="$top->isNotEmpty()"}
            <li class="subheader">
                <div>
                    <p>{$c->__('chat.frequent')}</p>
                </div>
            </li>

            {loop="$top"}
                <li class="block {if="$value->last > 60"} inactive{/if}"
                    onclick="Chats_ajaxOpen('{$value->jid|echapJS}', true);">
                    <img class="main" src="{$value->getBanner(\Movim\ImageSize::L)}">
                    <span class="primary icon bubble
                        {if="$value->presence"}
                            status {$value->presence->presencekey}
                        {/if}">
                        <img src="{$value->getPicture()}">
                    </span>
                    <div>
                        <p class="line" title="{$value->truename}">
                            {$value->truename}

                            {if="$value->presence && $value->presence->capability"}
                                <span class="second" title="{$value->presence->capability->name}">
                                    <i class="material-symbols">{$value->presence->capability->getDeviceIcon()}</i>
                                </span>
                            {/if}
                        </p>

                        {if="$value->presence && $value->presence->seen"}
                            <p class="line" title="{$c->__('last.title')} {$value->presence->seen|prepareDate:true,true}">
                                {$c->__('last.title')} {$value->presence->seen|prepareDate:true,true}
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
</div>

<div id="chat_explore">
    {autoescape="off"}
        {$c->prepareExplore()}
    {/autoescape}
</div>

<br />
<br />
