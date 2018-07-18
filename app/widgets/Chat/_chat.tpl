<header class="fixed">
    {if="$muc"}
    <ul class="list middle">
        <li>
            <span id="back" class="primary icon active"
                {if="!$anon"}
                    onclick="
                        MovimTpl.hidePanel();
                        Notification.current('chat');
                        Chat_ajaxGet();"
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
                    onclick="Rooms_ajaxList('{$jid|echapJS}')">
                </span>
            {else}
                <span class="primary icon bubble color active {$conference->name|stringToColor}
                    {if="!$conference->connected"}disabled{/if}"
                    onclick="Rooms_ajaxList('{$jid|echapJS}')">
                    {$conference->name|firstLetterCapitalize}
                </span>
            {/if}

            <span class="control icon show_context_menu active {if="!$conference->connected"}disabled{/if}">
                <i class="material-icons">more_vert</i>
            </span>

            <span
                title="{$c->__('button.close')}"
                class="control icon active"
                onclick="Rooms_ajaxExit('{$room}'); MovimTpl.hidePanel(); {if="$anon"}Presence_ajaxLogout(){/if}">
                <i class="material-icons">close</i>
            </span>

            {if="$conference && $conference->info && $conference->info->related"}
                {$related = $conference->info->related}
                <span
                    class="control icon active"
                    title="{$c->__('page.communities')} – {$related->name}"
                    onclick="MovimUtils.redirect('{$c->route('community', [$related->server, $related->node])}')">
                    <i class="material-icons">group_work</i>
                </span>
            {/if}

            {if="$conference && $conference->name"}
                <p class="line" title="{$room}">{$conference->name}</p>
            {else}
                <p class="line">{$room}</p>
            {/if}

            {if="$conference && !$conference->connected"}
                <p>{$c->__('button.connecting')}…</p>
            {elseif="$conference && $conference->subject"}
                <p class="line" title="{$conference->subject}">{$conference->subject|addUrls}</p>
            {else}
                <p class="line">{$room}</p>
            {/if}
        </li>
    </ul>

    <ul class="list context_menu active">
        {if="$conference->presence && $conference->presence->mucrole == 'moderator' && !$anon"}
            <li onclick="Chat_ajaxGetRoomConfig('{$room}')">
                <p class="normal">{$c->__('chatroom.administration')}</p>
            </li>
            <li onclick="Rooms_ajaxGetAvatar('{$room}')">
                <p class="normal">{$c->__('page.avatar')}</p>
            </li>
            <li class="divided" onclick="Rooms_ajaxGetSubject('{$room}')">
                <p class="normal">{$c->__('chatroom.subject')}</p>
            </li>
        {/if}
        {if="!$anon"}
            <li onclick="Rooms_ajaxRemoveConfirm('{$room}')">
                <p class="normal">{$c->__('button.delete')}</p>
            </li>
        {/if}
        <li onclick="Rooms_ajaxAskInvite('{$room}');">
            <p class="normal">{$c->__('room.invite')}</p>
        </li>
        <li onclick="Rooms_ajaxAdd('{$room}');">
            <p class="normal">{$c->__('chatroom.config')}</p>
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
    {else}
    <ul class="list middle">
        <li id="chat_header">
            <span onclick="
            MovimTpl.hidePanel();
            Notification.current('chat');
            Chat_ajaxGet();"
            id="back" class="primary icon active">
                <i class="material-icons">arrow_back</i>
            </span>

            {$url = $contact->getPhoto()}
            {if="$url"}
                <span class="primary icon bubble active {if="$roster->presence"}status {$roster->presence->presencekey}{/if}"
                    onclick="Chat_ajaxGetContact('{$contact->jid}')">
                    <img src="{$url}">
                </span>
            {else}
                <span class="primary icon bubble active color {$contact->jid|stringToColor} {if="$roster->presence"}status {$roster->presence->presencekey}{/if}"
                    onclick="Chat_ajaxGetContact('{$contact->jid}')">
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
                onclick="Chats_ajaxClose('{$jid|echapJS}'); MovimTpl.hidePanel();">
                <i class="material-icons">close</i>
            </span>
            <p class="line">
                {if="$roster"}
                    {$roster->truename}
                {else}
                    {$contact->truename}
                {/if}
            </p>
            <p class="line" id="{$jid|cleanupId}-state">{$contact->jid}</p>
        </li>
    </ul>
    <ul class="list context_menu active">
        <li onclick="MovimUtils.reload('{$c->route('contact', $contact->jid)}')">
            <p class="normal">{$c->__('chat.profile')}</p>
        </li>
        <li class="on_mobile" onclick="Chat.editPrevious()">
            <p class="normal">{$c->__('chat.edit_previous')}</p>
        </li>
        <li onclick="Chat_ajaxClearHistory('{$contact->jid}')">
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
        </div>
    </section>
</div>
<div class="chat_box">
    <ul class="list">
        <li class="{if="$muc && !$conference->connected"}disabled{/if}">
            {if="!$muc"}
            <span class="primary icon gray emojis_open" onclick="Stickers_ajaxShow('{$jid}')">
                <img alt="☺" class="emoji large" src="{$c->getSmileyPath('1f603')}">
            </span>
            {/if}
            {if="$c->getUser()->hasUpload()"}
                <span class="upload control icon"
                    title="{$c->__('publishbrief.attach')}"
                    onclick="Upload_ajaxRequest()">
                    <i class="material-icons">attach_file</i>
                </span>
            {/if}
            <span title="{$c->__('button.submit')}"
                class="send control icon gray {if="$c->getUser()->hasUpload()"}hide{else}show{/if}"
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
                        {if="rand(0, 4) == 4 && !$muc"}
                            placeholder="{$c->__('message.edit_help')}"
                        {else}
                            placeholder="{$c->__('chat.placeholder')}"
                        {/if}
                    ></textarea>
                </div>
            </form>
        </li>
    </ul>
</div>
