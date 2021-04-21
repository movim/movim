{if="$users->isNotEmpty()"}
    <ul class="list" style="width: 100%;">
        <li class="subheader block large">
            <div>
                <p>{$c->__('explore.explore')}</p>
            </div>
            {if="$page > 0"}
                <span class="control icon active gray" onclick="Chat_ajaxHttpGetExplore({$page - 1})">
                    <i class="material-icons">chevron_left</i>
                </span>
            {/if}
            {if="$users->count() > $pagination"}
                <span class="control icon active gray" onclick="Chat_ajaxHttpGetExplore({$page + 1})">
                    <i class="material-icons">chevron_right</i>
                </span>
            {/if}
        </li>
    </ul>
    <ul class="list flex middle active highlighted">
        {if="$users->count() > $pagination"}
            {$user = $users->pop()}
        {/if}
        {loop="$users"}
            <li class="block" title="{$value->jid}"
                onclick="Chats_ajaxOpen('{$value->jid|echapJS}'); Chat.get('{$value->jid|echapJS}');">
                <span class="control icon gray">
                    <i class="material-icons">comment</i>
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