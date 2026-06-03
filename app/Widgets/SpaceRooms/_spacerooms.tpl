{loop="$subscription->spaceRooms"}
    <li onclick="Chat.getRoom('{$value->conference}')" id="space{$value->conference|cleanupId}"
            data-jid="{$value->conference}">
        <ul class="list thin">
            <li>
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
                        <span title="{$value->conference}">
                            {if="$value->mujiPresences->isNotEmpty()"}
                                <i class="material-symbols call icon {if="$value->presence && $value->presence->hasMuji()"}green blink{else}blue{/if}" title="{$c->__('visio.in_call')}">call</i>
                            {/if}
                        </span>
                        {$value->name}
                    </p>
                </div>
            </li>
            {if="$value->isConferenceCall()"}
                {loop="$value->mujiPresences"}
                    <li>
                        <span class="primary icon r2 small">
                            <i class="material-symbols icon gray">
                                line_curve
                            </i>
                        </span>
                        <span class="primary icon bubble small">
                            <img loading="lazy" src="{$value->conferencePicture}">
                        </span>
                        <div>
                            <p>
                                {if="$value->hasMujiScreenSharing()"}
                                    <span class="info live"><i class="material-symbols">screen_share</i></span>
                                {/if}
                                {if="$value->hasVideoMuji()"}
                                    <span class="info"><i class="material-symbols">videocam</i></span>
                                {/if}
                                {$value->resource}
                            </p>
                        </div>
                    </li>
                {/loop}
            {/if}
        </ul>
    </li>
{/loop}

{if="$subscription->spaceRooms->isEmpty()"}
    <div class="placeholder">
        <i class="material-symbols fill">chat_dashed</i>
        <h1>{$c->__('chats.empty_title')}</h1>
        <h4>{autoescape="off"}{$addplaceholder}{/autoescape}</h4>
    </div>
{/if}
