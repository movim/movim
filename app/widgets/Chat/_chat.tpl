<header class="fixed">
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
                    onclick="Rooms_ajaxShowSubject('{$room|echapJS}')">
            {else}
                <span class="primary icon bubble color active {$conference->name|stringToColor}
                    {if="!$conference->connected"}disabled{/if}"
                    onclick="Rooms_ajaxShowSubject('{$room|echapJS}')">
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

            <span class="control icon show_context_menu active {if="!$conference->connected"}disabled{/if}">
                <i class="material-icons">more_vert</i>
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

            {if="$conference && $conference->name"}
                <p class="line active" title="{$room|echapJS}" onclick="Rooms_ajaxShowSubject('{$room|echapJS}')">
                    {$conference->name}
                </p>
            {else}
                <p class="line active" onclick="Rooms_ajaxShowSubject('{$room|echapJS}')">
                    {$room|echapJS}
                </p>
            {/if}

            <p class="compose line" id="{$jid|cleanupId}-state"></p>
            {if="$conference && !$conference->connected"}
                <p>{$c->__('button.connecting')}…</p>
            {elseif="$conference && $conference->subject"}
                <p class="line active" title="{$conference->subject}" onclick="Rooms_ajaxShowSubject('{$room|echapJS}')">
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
                <p class="line active" id="{$jid|cleanupId}-state" onclick="Rooms_ajaxShowSubject('{$room|echapJS}')">
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
                    {$room|echapJS}
                </p>
            {/if}
        </li>
    </ul>

    <ul class="list context_menu thin active">
        {if="$conference->presence && !$anon"}
            {if="$conference->presence->mucrole == 'moderator' || $conference->presence->mucaffiliation == 'owner'"}
                <li class="subheader">
                    <span class="control icon">
                        <i class="material-icons">settings</i>
                    </span>
                    <p class="line">{$c->__('chatroom.administration')}</p>
                </li>
            {/if}
            {if="$conference->presence->mucrole == 'moderator'"}
                <li onclick="Rooms_ajaxGetAvatar('{$room|echapJS}')">
                    <p class="normal">{$c->__('page.avatar')}</p>
                </li>
                <li onclick="Rooms_ajaxGetSubject('{$room|echapJS}')">
                    <p class="normal">{$c->__('chatroom.subject')}</p>
                </li>
            {/if}
            {if="$conference->presence->mucaffiliation == 'owner'"}
                <li onclick="Chat_ajaxGetRoomConfig('{$room|echapJS}')">
                    <p class="normal">{$c->__('chatroom.administration')}</p>
                </li>
                <li class="divided" onclick="Rooms_ajaxAskDestroy('{$room|echapJS}')">
                    <p class="normal">{$c->__('button.destroy')}</p>
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
                <p class="normal">{$c->__('chat.report_abuse')}</p>
            </li>
        {/if}

        <li class="divided" onclick="Rooms_ajaxAskInvite('{$room|echapJS}');">
            <p class="normal">{$c->__('room.invite')}</p>
        </li>

        <li onclick="Rooms_ajaxAdd('{$room|echapJS}');">
            <p class="normal">{$c->__('chatroom.config')}</p>
        </li>
        {if="!$anon"}
            <li onclick="Rooms_ajaxRemoveConfirm('{$room|echapJS}')">
                <p class="normal">{$c->__('button.delete')}</p>
            </li>
        {/if}

        <li onclick="Rooms_ajaxExit('{$room|echapJS}'); {if="$anon"}Presence_ajaxLogout(){/if}">
            <p class="normal">{$c->__('status.disconnect')}</p>
        </li>
    </span>
    </ul>
    {else}
    <ul class="list middle">
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

            <span class="control icon show_context_menu active">
                <i class="material-icons">more_vert</i>
            </span>

            <span
                title="{$c->__('button.close')}"
                class="control icon active"
                onclick="Chats_ajaxClose('{$jid|echapJS}', true);">
                <i class="material-icons">close</i>
            </span>

            <p class="line active" onclick="ChatActions_ajaxGetContact('{$contact->jid|echapJS}')">
                {if="$roster"}
                    {$roster->truename}
                {else}
                    {$contact->truename}
                {/if}
            </p>
            <p class="compose line active" id="{$jid|cleanupId}-state" onclick="ChatActions_ajaxGetContact('{$contact->jid|echapJS}')"></p>
            <p class="line active" onclick="ChatActions_ajaxGetContact('{$contact->jid|echapJS}')">{$contact->jid}</p>
        </li>
    </ul>
    <ul class="list context_menu active">
        {if="!$contact->isFromMuc()"}
            <li onclick="MovimUtils.reload('{$c->route('contact', $contact->jid)}')">
                <p class="normal">{$c->__('chat.profile')}</p>
            </li>
        {/if}
        <li class="on_mobile" onclick="Chat.editPrevious()">
            <p class="normal">{$c->__('chat.edit_previous')}</p>
        </li>
        <li onclick="Chat_ajaxClearHistory('{$contact->jid|echapJS}')">
            <p class="normal">{$c->__('chat.clear')}</p>
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
                <p class="normal">{$c->__('chat.report_abuse')}</p>
            </li>
        {/if}
    </ul>
    {/if}
</header>

<div id="{$jid|cleanupId}-discussion" class="contained {if="$muc"}muc{/if}" data-muc="{$muc}">
    <section id="{$jid|cleanupId}-messages">
        <ul class="list middle spin" id="{$jid|cleanupId}-conversation"></ul>
        <div class="placeholder">
            <i class="material-icons">chat</i>
            <h1>{$c->__('chat.new_title')}</h1>
            <h4>{$c->___('chat.new_text')}</h4>
            <h4>{$c->___('message.edit_help')}</h4>
            <h4>{$c->___('message.emoji_help')}</h4>
        </div>
    </section>
</div>
<div class="chat_box">
    <ul class="list">
        <li class="emojis"></li>
        <li class="{if="$muc && !$conference->connected"}disabled{/if}">
            <span class="primary icon gray primary_action" onclick="Stickers_ajaxShow('{$jid}')">
                <i class="material-icons">mood</i>
            </span>
            {if="$c->getUser()->hasUpload()"}
                <span class="attach control icon" onclick="Chat.toggleAttach()">
                    <i class="material-icons">add_circle</i>
                </span>
                <ul class="list actions">
                    <li onclick="Chat.toggleAttach(); Snap.init()">
                        <span class="button action control icon bubble color blue">
                            <i class="material-icons">camera_alt</i>
                        </span>
                        <p class="normal line">Snap</p>
                    </li>
                    <li onclick="Chat.toggleAttach(); Draw.init()">
                        <span class="button action control icon middle bubble color green">
                            <i class="material-icons">gesture</i>
                        </span>
                        <p class="normal line">{$c->__('draw.title')}</p>
                    </li>
                    <li onclick="Chat.toggleAttach(); Upload_ajaxRequest()">
                        <span class="button action control icon bubble color purple">
                            <i class="material-icons">attach_file</i>
                        </span>
                        <p class="normal line">{$c->__('upload.title')}</p>
                    </li>
                </ul>
            {/if}
            <span title="{$c->__('button.submit')}"
                class="send control icon gray"
                  onclick="Chat.sendMessage()">
                <i class="material-icons">send</i>
            </span>
            <form>
                <div>
                     <textarea
                        rows="1"
                        id="chat_textarea"
                        data-jid="{$jid}"
                        data-muc="{if="$muc"}true{/if}"
                        {$rand = rand(0, 4)};
                        {if="$rand == 4 && !$muc"}
                            placeholder="{$c->__('message.edit_help')}"
                        {elseif="$rand == 3"}
                            placeholder="{$c->__('message.emoji_help')}"
                        {else}
                            placeholder="{$c->__('chat.placeholder')}"
                        {/if}
                    ></textarea>
                </div>
            </form>
        </li>
    </ul>
</div>