<div class="placeholder">
    <i class="material-icons">forum</i>
</div>

<ul class="list flex middle active">
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

{if="$users->isNotEmpty()"}
    <ul class="list" style="width: 100%;">
        <li class="subheader block large">
            <div>
                <p>{$c->__('explore.explore')}</p>
            </div>
        </li>
    </ul>
    <ul class="list flex middle active highlighted">
        {loop="$users"}
            <li class="block" title="{$value->jid}" onclick="MovimUtils.reload('{$c->route('contact', $value->jid)}')">
                <span class="control icon gray">
                    <i class="material-icons">chevron_right</i>
                </span>
                {$url = $value->getPhoto('m')}
                {if="$url"}
                    <span class="primary icon bubble
                    {if="$value->value"}
                        status {$presencestxt[$value->value]}
                    {/if}
                    " style="background-image: url({$url});">
                    </span>
                {else}
                    <span class="primary icon bubble color {$value->jid|stringToColor}
                    {if="$value->value"}
                        status {$presencestxt[$value->value]}
                    {/if}
                    ">
                        <i class="material-icons">person</i>
                    </span>
                {/if}

                <div>
                    <p class="normal line">
                        {$value->truename}
                    </p>
                    {if="!empty($value->description)"}
                        <p class="line" title="{$value->description|strip_tags}">
                            {$value->description|strip_tags|truncate:80}
                        </p>
                    {/if}
                </div>
            </li>
        {/loop}
    </ul>
{/if}

<br />
<br />
