{if="$muc"}
    {$curl = false}
    {if="$conference"}
        {$curl = $conference->getPhoto()}
    {/if}

    <ul class="list middle">
        <li>
            <span class="primary icon active" id="chatheadercounter" onclick="Chat.get()">
                {autoescape="off"}
                    {$counter}
                {/autoescape}
            </span>

            {if="$curl"}
                <span class="primary icon bubble active
                    {if="!$conference->connected"}disabled{/if}"
                    style="background-image: url({$curl});"
                    onclick="RoomsUtils_ajaxShowSubject('{$jid|echapJS}')">
            {elseif="$conference"}
                <span class="primary icon bubble color active {$conference->name|stringToColor}
                    {if="!$conference->connected"}disabled{/if}"
                    onclick="RoomsUtils_ajaxShowSubject('{$jid|echapJS}')">
                    {autoescape="off"}
                        {$conference->name|firstLetterCapitalize|addEmojis}
                    {/autoescape}
            {else}
                <span class="primary icon bubble color active {$jid|stringToColor}"
                    onclick="RoomsUtils_ajaxShowSubject('{$jid|echapJS}')">
                    {$jid|firstLetterCapitalize}
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

            {if="$conference && $conference->info && $conference->info->related"}
                {$related = $conference->info->related}

                {$url = $related->getPhoto('m')}

                {if="$url"}
                    <span
                        title="{$c->__('page.communities')} · {$related->name}"
                        onclick="MovimUtils.redirect('{$c->route('community', [$related->server, $related->node])}')"
                        class="control icon bubble active small">
                        <img src="{$url}"/>
                    </span>
                {else}
                    <span
                        title="{$c->__('page.communities')} · {if="$related->name"}{$related->name}{else}{$related->node}{/if}"
                        onclick="MovimUtils.redirect('{$c->route('community', [$related->server, $related->node])}')"
                        class="control icon bubble active small color {$related->node|stringToColor}">
                        {$related->node|firstLetterCapitalize}
                    </span>
                {/if}
            {/if}

            <span class="control icon show_context_menu active {if="$conference && !$conference->connected"}disabled{/if}">
                <i class="material-icons">more_vert</i>
            </span>

            <div>
                <p class="line active" title="{$jid|echapJS}" onclick="RoomsUtils_ajaxShowSubject('{$jid|echapJS}')">
                    {if="$conference && $conference->title"}
                        {$conference->title}
                        {if="$conference->notify == 0"}
                            <span class="second" title="{$c->__('room.notify_never')}">
                                <i class="material-icons">notifications_off</i>
                            </span>
                        {elseif="$conference->notify == 2"}
                            <span class="second" title="{$c->__('room.notify_always')}">
                                <i class="material-icons">notifications_active</i>
                            </span>
                        {/if}
                    {else}
                        {$jid|echapJS}
                    {/if}

                    <span class="second">
                        {if="$conference && $conference->isGroupChat()"}
                            <i class="material-icons">people_alt</i> {$c->__('room.group_chat')}
                        {else}
                            <i class="material-icons">wifi_tethering</i> {$c->__('room.channel')}
                        {/if}
                    </span>

                    {if="$conference && $conference->isGroupChat() && $subject = $conference->subject"}
                        ·<span class="second" title="{$subject}">
                            {$subject}
                        </span>
                    {/if}
                </p>

                <p class="compose first line" id="{$jid|cleanupId}-state"></p>
                <p class="line active">
                    {if="$conference"}
                        {if="!$conference->connected"}
                            {$c->__('button.connecting')}…
                        {elseif="$conference->connected && $conference->isGroupChat()"}
                            {$connected = $conference->presences()->take(25)->get()}
                            {loop="$connected"}
                                <span onclick="Chat.quoteMUC('{$value->resource}', true);">
                                    {$value->resource}
                                </span>{if="$key < $connected->count() -1"}, {/if}
                            {/loop}
                        {elseif="!empty($conference->subject)"}
                            {$conference->subject}
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
                    <div>
                        <p class="normal">{$c->__('page.avatar')}</p>
                    </div>
                </li>
                <li onclick="RoomsUtils_ajaxGetSubject('{$jid|echapJS}')">
                    <div>
                        <p class="normal">{$c->__('chatroom.subject')}</p>
                    </div>
                </li>
            {/if}
            {if="$conference->presence->mucaffiliation == 'owner'"}
                <li onclick="Chat_ajaxGetRoomConfig('{$jid|echapJS}')">
                    <div>
                        <p class="normal">{$c->__('chatroom.administration')}</p>
                    </div>
                </li>
                <li class="divided" onclick="RoomsUtils_ajaxAskDestroy('{$jid|echapJS}')">
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
                <div>
                    <p class="normal">{$c->__('chat.report_abuse')}</p>
                </div>
            </li>
        {/if}

        {if="$conference && $conference->isGroupChat()"}
            <li class="on_mobile" onclick="Chat.editPrevious()">
                <div>
                    <p class="normal">{$c->__('chat.edit_previous')}</p>
                </div>
            </li>
        {/if}

        <li onclick="Rooms_ajaxExit('{$jid|echapJS}'); {if="$anon"}Presence_ajaxLogout(){/if}">
            <div>
                <p class="normal">{$c->__('status.disconnect')}</p>
            </div>
        </li>
    </ul>
{else}
    <ul class="list middle fill">
        <li id="chat_header">
            <span class="primary icon active" id="chatheadercounter" onclick="Chat.get()">
                {autoescape="off"}
                    {$counter}
                {/autoescape}
            </span>

            {$url = $contact->getPhoto()}
            {if="$url"}
                <span class="primary icon bubble active
                    {if="$roster && $roster->presence"}status {$roster->presence->presencekey}{/if}"
                    onclick="ChatActions_ajaxGetContact('{$contact->jid|echapJS}')">
                    <img src="{$url}">
                </span>
            {else}
                <span class="primary icon bubble active color
                    {$contact->jid|stringToColor}
                    {if="$roster && $roster->presence"}status {$roster->presence->presencekey}{/if}"
                    onclick="ChatActions_ajaxGetContact('{$contact->jid|echapJS}')">
                    {if="$roster"}
                        {$roster->truename|firstLetterCapitalize}
                    {else}
                        {$contact->truename|firstLetterCapitalize}
                    {/if}
                </span>
            {/if}

            {$call = false}

            {if="$roster && $roster->presences->count() > 0"}
                {loop="$roster->presences"}
                    {if="$value->capability && $value->capability->isJingleAudio() && $value->jid"}
                        {$call = true}
                        <span title="{$c->__('button.audio_call')}" class="control icon active on_desktop"
                            onclick="VisioLink.openVisio('{$value->jid|echapJS}');">
                            <i class="material-icons">phone</i>
                        </span>
                    {/if}
                    {if="$value->capability && $value->capability->isJingleVideo() && $value->jid"}
                        {$call = true}
                        <span title="{$c->__('button.video_call')}" class="control icon active on_desktop"
                            onclick="VisioLink.openVisio('{$value->jid|echapJS}', '', true);">
                            <i class="material-icons">videocam</i>
                        </span>
                        {break}
                    {/if}
                {/loop}
            {/if}

            <span
                title="{$c->__('button.close')}"
                class="control icon active {if="$call"}divided{/if}"
                onclick="Chats_ajaxClose('{$jid|echapJS}', true);">
                <i class="material-icons">close</i>
            </span>

            <span class="control icon show_context_menu active">
                <i class="material-icons">more_vert</i>
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
                    {if="$contact->locationDistance != null"}
                        <i class="material-icons">place</i>
                        {$contact->locationDistance|humanDistance} •
                    {/if}
                    {if="$roster && $roster->presence && $roster->presence->seen"}
                        {$c->__('last.title')} {$roster->presence->seen|strtotime|prepareDate:true,true}
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
                <div>
                    <p class="normal">{$c->__('chat.profile')}</p>
                </div>
            </li>
        {/if}
        <li class="on_mobile" onclick="Chat.editPrevious()">
            <div>
                <p class="normal">{$c->__('chat.edit_previous')}</p>
            </div>
        </li>
        <li onclick="Chat_ajaxClearHistory('{$contact->jid|echapJS}')">
            <div>
                <p class="normal">{$c->__('chat.clear')}</p>
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
                <div>
                    <p class="normal">{$c->__('chat.report_abuse')}</p>
                </div>
            </li>
        {/if}
        <hr />
        {if="$contact->isBlocked()"}
            <li onclick="ChatActions_ajaxUnblockContact('{$contact->jid|echapJS}')">
                <div>
                    <p class="normal">{$c->__('blocked.unblock_account')}</p>
                </div>
            </li>
        {else}
            <li onclick="ChatActions_ajaxBlockContact('{$contact->jid|echapJS}')">
                <div>
                    <p class="normal">{$c->__('blocked.block_account')}</p>
                </div>
            </li>
        {/if}
    </ul>
{/if}