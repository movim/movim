{loop="$subscription->spaceRooms"}
    <li onclick="Chat.getRoom('{$value->conference}')" id="space{$value->conference|cleanupId}">
        <span class="primary icon gray">
            <i class="material-symbols">tag</i>
                <span data-key="{$value->notifKey}" class="counter"></span>
        </span>
        {if="$edit"}
            <span class="control icon gray active" onclick="SpaceRooms_ajaxAskEdit('{$value->space_server}', '{$value->space_node}', '{$value->conference}')">
                <i class="material-symbols">edit</i>
            </span>
            <span class="control icon gray active" onclick="SpaceRooms_ajaxAskDestroy('{$value->space_server}', '{$value->space_node}', '{$value->conference}')">
                <i class="material-symbols">delete</i>
            </span>
        {/if}
        <div>
            <p class="line">
                {if="$value->pinned"}
                    <span class="info">
                        <i class="material-symbols fill" title="{$c->__('room.pinned')}">push_pin</i>
                    </span>
                {/if}
                {$value->name}
            </p>
        </div>
    </li>
{/loop}

{if="$subscription->spaceRooms->isEmpty()"}
    <div class="placeholder">
        <i class="material-symbols fill">chat_dashed</i>
        <h1>{$c->__('chats.empty_title')}</h1>
    </div>
{/if}