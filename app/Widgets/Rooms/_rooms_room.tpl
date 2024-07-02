<li id="{$conference->conference|cleanupId}"
    data-jid="{$conference->conference}"
    {if="$conference->nick != null"} data-nick="{$conference->nick}" {/if}
    class="room
        {if="$conference->connected"}connected{/if}
        {if="$conference->pinned"}pinned{/if}
        {if="$conference->isGroupChat()"}groupchat{/if}
        {if="$conference->unreads_count > 0 || $conference->quoted_count > 0"}unread{/if}
    ">
    <span class="primary icon bubble small"
        id="{$conference->conference|cleanupId}-rooms-primary"
        style="background-image: url({$conference->getPicture()});">
        {autoescape="off"}
            {$c->prepareRoomCounter($conference, true)}
        {/autoescape}
    </span>

    {$info = $conference->info}

    <div>
        <p class="normal line">
            {if="!$conference->isGroupChat() && $conference->connected"}
                {$count = $conference->presences()->count()}
                <span title="{$c->__('communitydata.sub', $count)}"
                    class="info
                        {if="$conference->connected && $conference->presence->mucrole == 'moderator'"}
                            moderator
                        {/if}
                    ">
                    {$count} <i class="material-symbols">people</i>
                </span>
            {elseif="!$conference->isGroupChat() && isset($info) && $info->occupants > 0"}
                <span title="{$c->__('communitydata.sub', $info->occupants)}"
                    class="info
                        {if="$conference->connected && $conference->presence->mucrole == 'moderator'"}
                            moderator
                        {/if}
                    ">
                    {$info->occupants} <i class="material-symbols">people</i>
                </span>
            {/if}

            <span title="{$conference->conference}">{$conference->title}</span>
            <span class="second">
                {if="$conference->pinned"}
                    <i class="material-symbols" title="{$c->__('room.pinned')}">push_pin</i>
                {/if}
                {if="$conference->notify == 0"}
                    <i class="material-symbols" title="{$c->__('room.notify_never')}">notifications_off</i>
                {elseif="$conference->notify == 2"}
                    <i class="material-symbols" title="{$c->__('room.notify_always')}">notifications_active</i>
                {/if}
                {if="isset($info) && $info->name && $conference->title != $info->name"}
                    {$info->name}
                {elseif="isset($info) && $info->description"}
                    {$info->description}
                {else}
                    {$conference->conference}
                {/if}
            </span>
        </p>
    </div>
    <span class="control icon active gray" onclick="event.stopPropagation(); RoomsUtils_ajaxRemove('{$conference->conference|echapJS}');">
        <i class="material-symbols">delete</i>
    </span>
    <span class="control icon active gray" onclick="event.stopPropagation(); RoomsUtils_ajaxAdd('{$conference->conference|echapJS}');">
        <i class="material-symbols">edit</i>
    </span>
</li>
