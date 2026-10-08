{$currentDirectory = null}
{loop="$space_rooms"}
    {if="$value->directory_id != $currentDirectory"}
        {loop="$directories"}
            <hr />
            <li {if="$edit"}draggable="true"{/if} class="subheader" data-id="{$value->id}">
                {if="$edit"}
                    <div class="controls">
                        <span class="control icon gray active edition" onclick="SpaceRooms_ajaxAskEditDirectory('{$value->server}', '{$value->node}', '{$value->id}')"
                            title="{$c->__('spaceinfo.edit_directory_title')}">
                            <i class="material-symbols">folder_managed</i>
                        </span>
                        <span class="control icon gray active edition" onclick="SpaceRooms_ajaxAskAdd('{$value->server}', '{$value->node}', '{$value->id}')"
                            title="{$c->__('spaceinfo.add_room_title')}">
                            <i class="material-symbols">add</i>
                        </span>
                    </div>
                {/if}
                <div>
                    <p class="line">
                        <span class="second">
                            <span class="material-symbols">folder</span>
                            {$value->title}
                        </span>
                    </p>
                </div>
            </li>
            {if="$directories->forget($key)"}{/if}
            {if="$value1->directory_id == $value->id"}
                {break}
            {/if}
        {/loop}
    {/if}

    {$currentDirectory = $value->directory_id}

    <li {if="$edit"}draggable="true"{/if} data-jid="{$value->conference|echapJS}" id="space{$value->conference|cleanupId}">
        <ul class="list thin">
            <li data-jid="{$value->conference}">
                <span class="primary icon gray"
                    id="{$value->conference|cleanupId}-rooms-primary">
                    {autoescape="off"}
                        {$c->prepareRoomCounter($value)}
                    {/autoescape}
                </span>

                {if="$edit"}
                    <div class="controls">
                        <span class="control icon gray active edition on_desktop"
                            onclick="event.stopPropagation(); SpaceRooms_ajaxAskEdit('{$value->conference}')"
                            title="{$c->__('button.edit')}">
                            <i class="material-symbols">edit</i>
                        </span>
                    </div>
                {/if}
                <div>
                    <p class="line">
                        {if="$value->sfuPresence && $value->sfuPresence->SFUStartedAt"}
                            <span class="info" title="{$c->prepareDate($value->sfuPresence->SFUStartedAt)}" data-started-at="{$value->sfuPresence->SFUStartedAt->toISOString()}"></span>
                        {/if}
                        <span title="{$value->conference}">
                            {if="$value->mujiPresences->isNotEmpty()"}
                                <i class="material-symbols call icon {if="$value->presence && $value->presence->hasMuji()"}green blink{else}blue{/if}" title="{$c->__('visio.in_call')}">call</i>
                            {elseif="$value->sfuPresence && $value->sfuPresence->SFUUsersCount > 0"}
                                <i class="material-symbols call icon {if="$c->currentCall()?->isJidInCall($value->sfuPresence->mucjid)"}green blink{else}blue{/if}" title="{$c->__('visio.in_call')}">video_call</i>
                            {/if}
                        </span>
                        {$value->title}
                    </p>
                </div>
            </li>
            {if="$value->isConferenceCall()"}
                {if="$value->sfuPresence"}
                    {loop="$value->sfuPresence->SFUUsers"}
                        <li>
                            <span class="primary icon r2 small">
                                <i class="material-symbols icon gray">
                                    line_curve
                                </i>
                            </span>
                            <span class="primary icon bubble small">
                                {if="array_key_exists('avatar', $value)"}
                                    <img loading="lazy" src="{$value.avatar}">
                                {else}
                                    <i class="material-symbols">phone</i>
                                {/if}
                            </span>
                            <div>
                                <p>
                                    {if="array_key_exists('name', $value)"}
                                        {$value.name}
                                    {else}
                                        {$value.jid}
                                    {/if}
                                </p>
                            </div>
                        </li>
                    {/loop}
                {/if}

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

{loop="$directories"}
    <li {if="$edit"}draggable="true"{/if} class="subheader" data-id="{$value->id}">
        {if="$edit"}
            <div class="controls">
                <span class="control icon gray active edition" onclick="SpaceRooms_ajaxAskEditDirectory('{$value->server}', '{$value->node}', '{$value->id}')"
                    title="{$c->__('spaceinfo.edit_directory_title')}">
                    <i class="material-symbols">folder_managed</i>
                </span>
                <span class="control icon gray active edition" onclick="SpaceRooms_ajaxAskAdd('{$value->server}', '{$value->node}', '{$value->id}')"
                    title="{$c->__('spaceinfo.add_room_title')}">
                    <i class="material-symbols">add</i>
                </span>
            </div>
        {/if}
        <div>
            <p class="line">
                <span class="second">
                    <span class="material-symbols">folder</span>
                    {$value->title}
                </span>
            </p>
        </div>
    </li>
{/loop}

{if="$space_rooms->isEmpty()"}
    <div class="placeholder">
        <i class="material-symbols fill">chat_dashed</i>
        <h1>{$c->__('chats.empty_title')}</h1>
        <h4>{autoescape="off"}{$addplaceholder}{/autoescape}</h4>
    </div>
{/if}
