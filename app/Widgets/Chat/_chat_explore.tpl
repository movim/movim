{if="$users->isNotEmpty()"}
    <ul class="list">
        <li class="subheader">
            <div>
                <p>{$c->__('explore.explore')}</p>
            </div>
            {if="$page > 0"}
                <span class="control icon active gray" onclick="Chat_ajaxHttpGetExplore({$page - 1})">
                    <i class="material-symbols">chevron_left</i>
                </span>
            {/if}
            {if="$users->count() > $pagination"}
                <span class="control icon active gray" onclick="Chat_ajaxHttpGetExplore({$page + 1})">
                    <i class="material-symbols">chevron_right</i>
                </span>
            {/if}
        </li>
    </ul>
    <ul class="list card shadow flex fourth compact middle active">
        {if="$users->count() > $pagination"}
            {$user = $users->pop()}
        {/if}
        {loop="$users"}
            <li class="block" title="{$value->jid}"
                onclick="Chats_ajaxOpen('{$value->jid|echapJS}', true);">
                <img class="main" src="{$value->getBanner(\Movim\ImageSize::L)}">
                <span class="primary icon bubble {if="$value->value"}status {$presencestxt[$value->value]}{/if}">
                    <img src="{$value->getPicture(\Movim\ImageSize::M)}">
                </span>
                <div>
                    <p class="normal line">
                        {$value->truename}
                    </p>
                    {if="!empty($value->status)"}
                        <p class="line" title="{$value->status|strip_tags}">
                            {$value->status|strip_tags|truncate:80}
                        </p>
                    {elseif="!empty($value->description)"}
                        <p class="line" title="{$value->description|strip_tags}">
                            {$value->description|strip_tags|truncate:80}
                        </p>
                    {/if}
                </div>
            </li>
        {/loop}
    </ul>
{/if}
