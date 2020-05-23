{if="$muc"}
    <ul class="list middle">
        <li>
            <span class="primary icon active"
                {if="!$anon"}
                    onclick="Chat_ajaxGet()"
                {/if}>

                {if="!$anon"}
                    <i class="material-icons">arrow_back</i>
                {else}
                    <i class="material-icons">comments</i>
                {/if}
            </span>

            {if="$conference"}
                {$curl = $conference->getPhoto()}
            {/if}

            {if="$curl"}
                <span class="primary icon bubble color active {$conference->name|stringToColor}
                    {if="!$conference->connected"}disabled{/if}"
                    style="background-image: url({$curl});"
                    onclick="RoomsUtils_ajaxShowSubject('{$jid|echapJS}')">
            {else}
                <span class="primary icon bubble color active {$conference->name|stringToColor}
                    {if="!$conference->connected"}disabled{/if}"
                    onclick="RoomsUtils_ajaxShowSubject('{$jid|echapJS}')">
                    {autoescape="off"}
                        {$conference->name|firstLetterCapitalize|addEmojis}
                    {/autoescape}
            {/if}
                {if="$conference->connected"}
                    {$count = $conference->presences()->count()}
                    <span class="counter alt">
                        {if="$count > 99"}99+{else}{$count}{/if}
                    </span>
                {/if}
                </span>

            {if="$conference && $conference->info && $conference->info->related"}
                {$related = $conference->info->related}
                <span
                    class="control icon active"
                    title="{$c->__('page.communities')} · {$related->name}"
                    onclick="MovimUtils.redirect('{$c->route('community', [$related->server, $related->node])}')">
                    <i class="material-icons">group_work</i>
                </span>
            {/if}

            <span class="control icon show_context_menu active {if="!$conference->connected"}disabled{/if}">
                <i class="material-icons">more_vert</i>
            </span>

            <div>
                {if="$conference && $conference->name"}
                    <p class="line active" title="{$jid|echapJS}" onclick="RoomsUtils_ajaxShowSubject('{$jid|echapJS}')">
                        {$conference->name}
                    </p>
                {else}
                    <p class="line active" onclick="RoomsUtils_ajaxShowSubject('{$jid|echapJS}')">
                        {$jid|echapJS}
                    </p>
                {/if}

                <p class="compose first line" id="{$jid|cleanupId}-state"></p>
                {if="$conference && !$conference->connected"}
                    <p>{$c->__('button.connecting')}…</p>
                {elseif="$conference && $conference->subject"}
                    <p class="line active" title="{$conference->subject}" onclick="RoomsUtils_ajaxShowSubject('{$jid|echapJS}')">
                        {if="$conference->info && $conference->info->mucpublic"}
                            <span title="{$c->__('room.public_muc_text')}">
                                {$c->__('room.public_muc')} <i class="material-icons">wifi_tethering</i>
                            </span>
                            ·
                        {/if}
                        {if="$conference->info && !$conference->info->mucsemianonymous"}
                            <span title="{$c->__('room.nonanonymous_muc_text')}">
                                {$c->__('room.nonanonymous_muc')} <i class="material-icons">face</i>
                            </span>
                            ·
                        {/if}
                        {$conference->subject}
                    </p>
                {else}
                    <p class="line active" id="{$jid|cleanupId}-state" onclick="RoomsUtils_ajaxShowSubject('{$jid|echapJS}')">
                        {if="$conference->info && $conference->info->mucpublic"}
                            <span title="{$c->__('room.public_muc_text')}">
                                {$c->__('room.public_muc')} <i class="material-icons">wifi_tethering</i>
                            </span>
                            ·
                        {/if}
                        {if="$conference->info && !$conference->info->mucsemianonymous"}
                            <span title="{$c->__('room.nonanonymous_muc_text')}">
                                {$c->__('room.nonanonymous_muc')} <i class="material-icons">face</i>
                            </span>
                            ·
                        {/if}
                        {$jid|echapJS}
                    </p>
                {/if}
            </div>
        </li>
    </ul>

    <ul class="list context_menu thin active">
        {if="$conference->presence && !$anon"}
            {if="$conference->presence->mucrole == 'moderator' || $conference->presence->mucaffiliation == 'owner'"}
                <li class="subheader">
                    <span class="control icon">
                        <i class="material-icons">settings</i>
                    </span>
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

        <li class="divided" onclick="RoomsUtils_ajaxAskInvite('{$jid|echapJS}');">
            <div>
                <p class="normal">{$c->__('room.invite')}</p>
            </div>
        </li>

        <li onclick="RoomsUtils_ajaxAdd('{$jid|echapJS}');">
            <div>
                <p class="normal">{$c->__('chatroom.config')}</p>
            </div>
        </li>
        {if="!$anon"}
            <li onclick="RoomsUtils_ajaxRemove('{$jid|echapJS}')">
                <div>
                    <p class="normal">{$c->__('button.delete')}</p>
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
            <span onclick="Chat_ajaxGet()" class="primary icon active">
                <i class="material-icons">arrow_back</i>
            </span>

            {$url = $contact->getPhoto()}
            {if="$url"}
                <span class="primary icon bubble active color
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

            {if="$roster && $roster->presences->count() > 0"}
                {loop="$roster->presences"}
                    {if="$value->capability && $value->capability->isJingle() && $value->jid"}
                        <span title="{$c->__('button.audio_call')}" class="control icon active on_desktop"
                            onclick="VisioLink.openVisio('{$value->jid}');">
                            <i class="material-icons">phone</i>
                        </span>
                        <span title="{$c->__('button.video_call')}" class="control icon active on_desktop"
                            onclick="VisioLink.openVisio('{$value->jid}', '', true);">
                            <i class="material-icons">videocam</i>
                        </span>
                        {break}
                    {/if}
                {/loop}
            {/if}

            <span
                title="{$c->__('button.close')}"
                class="control icon active"
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
                    {else}
                        {$contact->truename}
                    {/if}
                </p>
                <p class="compose first line active" id="{$jid|cleanupId}-state" onclick="ChatActions_ajaxGetContact('{$contact->jid|echapJS}')"></p>
                <p class="line active" onclick="ChatActions_ajaxGetContact('{$contact->jid|echapJS}')">{$contact->jid}</p>
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
    </ul>
{/if}