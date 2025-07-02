{if="$muc"}
    <ul class="list middle">
        <li>
            <span class="primary icon active on_mobile_after" id="chatheadercounter" onclick="Chat.get()"
                {if="$counter > 0"}data-counter="{$counter}"{/if}>
                <i class="material-symbols">arrow_back</i>
            </span>

            {if="$conference"}
                <span class="primary icon bubble active
                    {if="!$conference->connected"}disabled{/if}"
                    onclick="RoomsUtils_ajaxGetDrawer('{$jid|echapJS}')">
                    <img src="{$conference->getPicture()}">
            {else}
                <span class="primary icon bubble active"
                    onclick="RoomsUtils_ajaxGetDrawer('{$jid|echapJS}')">
                    <img src="{$jid|avatarPlaceholder}">
            {/if}
            {if="$conference"}
                {if="$conference->isGroupChat()"}
                    {$count = $conference->activeMembers()->count()}
                    <span class="counter alt" data-mucreceipts="true">
                        {if="$count > 99"}99+{else}{$count}{/if}
                    </span>
                {elseif="$conference->connected"}
                    {$count = $conference->presences()->count()}
                    <span class="counter alt" data-mucreceipts="{if="$conference->presences()->count() < 10"}true{/if}">
                        {if="$count > 99"}99+{else}{$count}{/if}
                    </span>
                {/if}
            {/if}
                </span>

            {if="$conference && $conference->isGroupChat()"}
                {if="$conference && $conference->info && $conference->info->related"}
                    {$related = $conference->info->related}
                    <span
                        title="{$c->__('page.communities')} • {$related->name}"
                        onclick="MovimUtils.reload('{$c->route('community', [$related->server, $related->node])}')"
                        class="control icon bubble active small">
                        <img src="{$related->getPicture(\Movim\ImageSize::M)}"/>
                    </span>
                {/if}
            {/if}

            {if="$conference && $conference->mujiCalls->isEmpty() && $conference->isGroupChat()"}
                <span class="control icon active {if="$incall"}disabled{/if}" onclick="Visio_ajaxGetMujiLobby('{$conference->conference}', true, true);">
                    <i class="material-symbols">videocam</i>
                </span>
                <span class="control icon active {if="$incall"}disabled{/if}" onclick="Visio_ajaxGetMujiLobby('{$conference->conference}', true, false);">
                    <i class="material-symbols">call</i>
                </span>
            {/if}

            <span
                class="control icon show_context_menu active {if="$conference && !$conference->connected"}disabled{/if}"
                onclick="MovimTpl.showContextMenu()">
                <i class="material-symbols">more_vert</i>
            </span>

            <div>
                {if="$conference && $conference->mujiCalls->isNotEmpty()"}
                    {if="$muji = $conference->currentMuji()"}
                        <button class="button oppose color red"
                                onclick="Visio_ajaxLeaveMuji('{$muji->id}')">
                            <i class="material-symbols">{$muji->icon}</i>
                        </button>
                    {else}
                        {if="$conference->mujiCalls->count() > 1"}
                            <button class="button oppose color blue {if="$incall"}disabled{/if}"
                                    onclick="Visio_ajaxChooseMuji('{$conference->conference}')">
                                {$conference->mujiCalls->count()}
                                <i class="material-symbols blink">call</i>
                            </button>
                        {else}
                            {$muji = $conference->mujiCalls->first()}
                            <button class="button oppose color blue {if="$incall"}disabled{/if}"
                                    onclick="Visio_ajaxJoinMuji('{$muji->id}', {if="$muji->video"}true{else}false{/if});">
                                <i class="material-symbols {if="$muji->joined"}blink{/if}">
                                    {$muji->icon}
                                </i>
                            </button>
                        {/if}
                    {/if}
                {/if}
                <p class="line active" title="{$jid|echapJS}" onclick="RoomsUtils_ajaxGetDrawer('{$jid|echapJS}')">
                    {if="$conference && $conference->title"}
                        {$conference->title}
                        {if="$conference->notify == 0"}
                            <span class="second" title="{$c->__('room.notify_never')}">
                                <i class="material-symbols">notifications_off</i>
                            </span>
                        {elseif="$conference->notify == 2"}
                            <span class="second" title="{$c->__('room.notify_always')}">
                                <i class="material-symbols">notifications_active</i>
                            </span>
                        {/if}
                    {else}
                        {$jid|echapJS}
                    {/if}

                    <span class="second">
                        {if="$conference && $conference->isGroupChat()"}
                            <i class="material-symbols">people_alt</i>
                        {else}
                            <i class="material-symbols">wifi_tethering</i>
                        {/if}
                    </span>

                    {if="$conference && $conference->info && $conference->info->name"}
                        <span class="second" title="{$conference->info->name}">
                            {$conference->info->name}
                        </span>
                    {/if}

                    {if="$conference && $conference->info && $conference->isGroupChat() && $conference->subject && $conference->info->name"}
                        <span class="second">•</span>
                    {/if}

                    {if="$conference && $conference->isGroupChat() && $subject = $conference->subject"}
                        <span class="second" title="{$subject}">
                            {$subject}
                        </span>
                    {/if}
                </p>

                <p class="compose first line" id="{$jid|cleanupId}-state"></p>
                <p class="line active">
                    {if="$conference"}
                        {if="$muji = $conference->currentMuji()"}
                            <i class="material-symbols icon green blink">{$muji->icon}</i>
                            {$c->__('visio.joined_call')}
                            <span class="second">
                                {$muji->created_at|prepareDate:true,true}
                                •
                                {$c->__('visio.by', $muji->inviter->name)}
                                •
                                {$muji->presences->count()}
                                <i class="material-symbols">people</i>
                            </span>
                            •
                        {elseif="$conference->mujiCalls->count() > 0"}
                            {if="$conference->mujiCalls->count() == 1"}
                                {$muji = $conference->mujiCalls->first()}
                                <i class="material-symbols icon blue blink">{$muji->icon}</i>
                                {$c->__('visio.in_call')}
                                <span class="second">
                                    {$muji->created_at|prepareDate:true,true}
                                    •
                                    {$c->__('visio.by', $muji->inviter->name)}
                                    •
                                    {$muji->participants->count()}
                                    <i class="material-symbols">people</i>
                                </span>
                            {else}
                                <i class="material-symbols icon blue blink">call</i>
                                {$c->__('visio.in_call')}
                            {/if}
                            •
                        {/if}
                        {if="!$conference->connected"}
                            {$c->__('button.connecting')}…
                        {elseif="$conference->connected && $conference->isGroupChat()"}
                            {$connected = $conference->otherPresences()->take(25)->get()}
                            {loop="$connected"}
                                <span onclick="Chat.quoteMUC('{$value->resource}', true);" class="icon bubble tiny">
                                    <img src="{$value->conferencePicture}">
                                </span><span onclick="Chat.quoteMUC('{$value->resource}', true);">{$value->resource}</span>
                                {if="$key < $connected->count() -1"}
                                {/if}
                            {/loop}
                        {elseif="!empty($conference->subject)"}
                            <span onclick="RoomsUtils_ajaxGetDrawer('{$jid|echapJS}')">{$conference->subject}</span>
                        {/if}
                    {/if}
                </p>
            </div>
        </li>
    </ul>

    <ul class="list context_menu thin active">
        {if="$conference && $conference->presence && !$anon"}
            {if="$conference->presence->mucrole == 'moderator' || $conference->presence->mucaffiliation == 'owner'"}
                <li class="subheader">
                    <div>
                        <p class="line">{$c->__('chatroom.administration')}</p>
                    </div>
                </li>
            {/if}
            {if="$conference->presence->mucrole == 'moderator'"}
                <li onclick="RoomsUtils_ajaxGetAvatar('{$jid|echapJS}')">
                    <span class="primary icon gray">
                        <i class="material-symbols">image</i>
                    </span>
                    <div>
                        <p class="normal">{$c->__('page.avatar')}</p>
                    </div>
                </li>
                <li onclick="RoomsUtils_ajaxGetSubject('{$jid|echapJS}')">
                    <span class="primary icon gray">
                        <i class="material-symbols">short_text</i>
                    </span>
                    <div>
                        <p class="normal">{$c->__('chatroom.subject')}</p>
                    </div>
                </li>
            {/if}
            {if="$conference->presence->mucaffiliation == 'owner'"}
                <li onclick="Chat_ajaxGetRoomConfig('{$jid|echapJS}')">
                    <span class="primary icon gray">
                        <i class="material-symbols">settings</i>
                    </span>
                    <div>
                        <p class="normal">{$c->__('chatroom.config')}</p>
                    </div>
                </li>
                <li class="divided" onclick="RoomsUtils_ajaxAskDestroy('{$jid|echapJS}')">
                    <span class="primary icon gray">
                        <i class="material-symbols">delete_forever</i>
                    </span>
                    <div>
                        <p class="normal">{$c->__('button.destroy')}</p>
                    </div>
                </li>
            {/if}
        {/if}

        {if="!empty($info->abuseaddresses)"}
            {$parsed = parse_url($info->abuseaddresses[0])}
            {if="$parsed['scheme'] == 'xmpp'"}
                {if="isset($parsed['query']) && $parsed['query'] == 'join'"}
                <li onclick="MovimUtils.reload('{$c->route('chat', [$parsed['path'], 'room'])}')">
                {else}
                <li onclick="MovimUtils.reload('{$c->route('chat', $parsed['path'])}')">
                {/if}
            {else}
                <li onclick="MovimUtils.reload('{$info->abuseaddresses[0]}')">
            {/if}
                <span class="primary icon gray">
                    <i class="material-symbols">report</i>
                </span>
                <div>
                    <p class="normal">{$c->__('chat.report_abuse')}</p>
                </div>
            </li>
        {/if}

        <li onclick="Rooms_ajaxExit('{$jid|echapJS}'); {if="$anon"}Presence_ajaxLogout(){/if}">
            <span class="primary icon gray">
                <i class="material-symbols">logout</i>
            </span>
            <div>
                <p class="normal">{$c->__('status.disconnect')}</p>
            </div>
        </li>
    </ul>
{else}
    <ul class="list middle">
        <li id="chat_header">
            <span class="primary icon active on_mobile_after" id="chatheadercounter" onclick="Chat.get()"
                {if="$counter > 0"}data-counter="{$counter}"{/if}>
                <i class="material-symbols">arrow_back</i>
            </span>

            <span class="primary icon bubble active
                {if="$roster"}
                    {if="$roster->presence"}status {$roster->presence->presencekey}{/if}
                    {if="$roster->stories->count()"}stories {if="$roster->storiesSeen"}seen{/if}{/if}
                {/if}
            "
            {if="$roster && $roster->firstUnseenStory"}
                onclick="StoriesViewer_ajaxHttpGet({$roster->firstUnseenStory->id})"
            {else}
                onclick="ChatActions_ajaxGetContact('{$contact->jid|echapJS}')"
            {/if}>
                <img src="{if="$roster"}{$roster->getPicture()}{else}{$contact->getPicture()}{/if}">
            </span>

            {$call = false}

            {if="!$incall"}
                {if="$roster && $roster->presences->count() > 0"}
                    {loop="$roster->presences"}
                        {if="$value->capability && $value->capability->isJingleAudio() && $value->jid"}
                            {$call = true}
                            <span title="{$c->__('button.audio_call')}" class="control icon active on_desktop"
                                onclick="Visio_ajaxGetLobby('{$value->jid|echapJS}', true);">
                                <i class="material-symbols">phone</i>
                            </span>
                        {/if}
                        {if="$value->capability && $value->capability->isJingleVideo() && $value->jid"}
                            {$call = true}
                            <span title="{$c->__('button.video_call')}" class="control icon active on_desktop"
                                onclick="Visio_ajaxGetLobby('{$value->jid|echapJS}', true, true);">
                                <i class="material-symbols">videocam</i>
                            </span>
                            {break}
                        {/if}
                    {/loop}
                {/if}
            {/if}

            <span class="control icon show_context_menu active"
                onclick="MovimTpl.showContextMenu()">
                <i class="material-symbols">more_vert</i>
            </span>

            <div>
                <p class="line active" onclick="ChatActions_ajaxGetContact('{$contact->jid|echapJS}')">
                    {if="$roster"}
                        {$roster->truename}
                    {elseif="strpos($contact->jid, '/') != false"}
                        {$exploded = explodeJid($contact->jid)}
                        {$exploded.resource}
                    {else}
                        {$contact->truename}
                    {/if}
                </p>
                <p class="compose first line active" id="{$jid|cleanupId}-state" onclick="ChatActions_ajaxGetContact('{$contact->jid|echapJS}')"></p>
                <p class="line active" onclick="ChatActions_ajaxGetContact('{$contact->jid|echapJS}')">
                    {if="$contactincall"}
                        <i class="material-symbols icon green blink">phone_in_talk</i>
                        {$c->__('visio.in_call')} •
                    {/if}
                    {if="$contact->locationDistance != null"}
                        <i class="material-symbols">place</i>
                        {$contact->locationDistance|humanDistance} •
                    {/if}
                    {if="$roster && $roster->presence && $roster->presence->seen"}
                        {$c->__('last.title')} {$roster->presence->seen|prepareDate:true,true}
                    {elseif="$roster && $roster->presence && !empty($roster->presence->status)"}
                        {$roster->presence->status}
                    {elseif="$roster && $roster->presence"}
                        {$roster->presence->presencetext}
                    {else}
                        {$contact->jid}
                    {/if}
                </p>
            </div>
        </li>
    </ul>
    <ul class="list context_menu active">
        {if="!$contact->isFromMuc()"}
            <li onclick="MovimUtils.reload('{$c->route('contact', $contact->jid)}')">
                <span class="primary icon gray">
                    <i class="material-symbols">person</i>
                </span>
                <div>
                    <p class="normal line">{$c->__('chat.profile')}</p>
                </div>
            </li>
        {/if}
        <li onclick="Chat_ajaxClearHistory('{$contact->jid|echapJS}')">
            <span class="primary icon gray">
                <i class="material-symbols">clear_all</i>
            </span>
            <div>
                <p class="normal line">{$c->__('chat.clear')}</p>
            </div>
        </li>
        {if="!empty($info->abuseaddresses)"}
            {$parsed = parse_url($info->abuseaddresses[0])}
            {if="$parsed['scheme'] == 'xmpp'"}
                {if="isset($parsed['query']) && $parsed['query'] == 'join'"}
                <li onclick="MovimUtils.reload('{$c->route('chat', [$parsed['path'], 'room'])}')">
                {else}
                <li onclick="MovimUtils.reload('{$c->route('chat', $parsed['path'])}')">
                {/if}
            {else}
                <li onclick="MovimUtils.reload('{$info->abuseaddresses[0]}')">
            {/if}
                <span class="primary icon gray">
                    <i class="material-symbols">report</i>
                </span>
                <div>
                    <p class="normal line">{$c->__('chat.report_abuse')}</p>
                </div>
            </li>
        {/if}
        <hr />
        {if="$contact->isBlocked()"}
            <li onclick="ChatActions_ajaxUnblock('{$contact->jid|echapJS}')">
                <span class="primary icon gray">
                    <i class="material-symbols">check_circle</i>
                </span>
                <div>
                    <p class="normal line">{$c->__('blocked.unblock_account')}</p>
                </div>
            </li>
        {else}
            <li onclick="ChatActions_ajaxBlock('{$contact->jid|echapJS}'); Notifications_ajaxRefuse('{$contact->jid|echapJS}');">
                <span class="primary icon gray">
                    <i class="material-symbols">block</i>
                </span>
                <div>
                    <p class="normal line">{$c->__('blocked.block_account')}</p>
                </div>
            </li>
        {/if}

        <li onclick="Chats_ajaxClose('{$jid|echapJS}', true);">
            <span class="primary icon gray">
                <i class="material-symbols">close</i>
            </span>
            <div>
                <p class="normal line">{$c->__('button.close')}</p>
            </div>
        </li>
    </ul>
{/if}
