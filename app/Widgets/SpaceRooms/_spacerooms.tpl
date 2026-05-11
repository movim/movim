{loop="$subscription->spaceRooms"}
    {if="$value->voice_room"}
        <li class="voice-room" id="space{$value->conference|cleanupId}"
            data-jid="{$value->conference}">
            <ul class="list thin">
                <li onclick="SpaceRooms_ajaxJoinVoiceRoom('{$value->conference|echapJS}')">
                    <span class="primary icon gray">
                        <i class="material-symbols {if="$value->mujiPresences->isNotEmpty()"}green{/if}">volume_up</i>
                    </span>

                    {if="$edit"}
                        <span class="control icon gray active"
                            onclick="event.stopPropagation(); SpaceRooms_ajaxAskDestroy('{$value->space_server}', '{$value->space_node}', '{$value->conference}')"
                            title="{$c->__('button.delete')}">
                            <i class="material-symbols">delete</i>
                        </span>
                    {/if}

                    <div>
                        <p class="line">
                            {$value->name}
                            {if="$value->mujiPresences->isNotEmpty()"}
                                <span class="info">
                                    {$value->mujiPresences->count()} <i class="material-symbols">people</i>
                                </span>
                            {/if}
                        </p>
                    </div>
                </li>

                <ul id="voice-room-participants-{$value->conference|cleanupId}" class="list thin">
                    {loop="$value->mujiPresences"}
                        <li class="voice-participant">
                            <span class="primary icon r2 small">
                                <i class="material-symbols icon gray">line_curve</i>
                            </span>
                            <span class="primary icon bubble small">
                                <img loading="lazy" src="{$value->conferencePicture}">
                            </span>
                            <div>
                                <p>{$value->resource}</p>
                            </div>
                        </li>
                    {/loop}
                </ul>
            </ul>
        </li>
    {else}
        <li onclick="Chat.getRoom('{$value->conference}')" id="space{$value->conference|cleanupId}"
            data-jid="{$value->conference}">
            <span class="primary icon gray"
                id="{$value->conference|cleanupId}-rooms-primary">
                {autoescape="off"}
                    {$c->prepareRoomCounter($value)}
                {/autoescape}
            </span>

            {if="$edit"}
                <span class="control icon gray active" onclick="SpaceRooms_ajaxAskEdit('{$value->space_server}', '{$value->space_node}', '{$value->conference}')"
                    title="{$c->__('button.edit')}">
                    <i class="material-symbols">edit</i>
                </span>
                <span class="control icon gray active" onclick="SpaceRooms_ajaxAskDestroy('{$value->space_server}', '{$value->space_node}', '{$value->conference}')"
                    title="{$c->__('button.delete')}">
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
    {/if}
{/loop}

{if="$subscription->spaceRooms->isEmpty()"}
    <div class="placeholder">
        <i class="material-symbols fill">chat_dashed</i>
        <h1>{$c->__('chats.empty_title')}</h1>
        <h4>{autoescape="off"}{$addplaceholder}{/autoescape}</h4>
    </div>
{/if}
