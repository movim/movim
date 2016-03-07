<header class="fixed">
    {if="$muc"}
    <ul class="list middle active">
        <li>
            <span id="back" class="primary icon active" {if="!$anon"}onclick="MovimTpl.hidePanel(); Chat_ajaxGet();"{/if}>
                {if="!$anon"}
                    <i class="zmdi zmdi-arrow-back"></i>
                {else}
                    <i class="zmdi zmdi-comment-text-alt"></i>
                {/if}
            </span>

            <span class="control icon show_context_menu active">
                <i class="zmdi zmdi-more-vert"></i>
            </span>

            {if="$c->supported('upload')"}
                <span class="control icon active" onclick="Upload_ajaxRequest()">
                    <i class="zmdi zmdi-attachment-alt"></i>
                </span>
            {/if}
            <span class="control icon active" onclick="Rooms_ajaxExit('{$room}'); MovimTpl.hidePanel(); {if="$anon"}Presence_ajaxLogout(){/if}">
                <i class="zmdi zmdi-close"></i>
            </span>

            {if="$conference != null && $conference->name"}
                <p class="line" title="{$room}">{$conference->name}</p>
            {else}
                <p class="line">{$room}</p>
            {/if}
            {if="$subject != null"}
                <p class="line" title="{$subject->subject}">{$subject->subject}</p>
            {/if}
        </li>
    </ul>

    <ul class="list context_menu active">
        <li onclick="Rooms_ajaxList('{$room}')">
            <p class="normal">{$c->__('chatroom.members')}</p>
        </li>
        {if="!$anon"}
            <li onclick="Rooms_ajaxRemoveConfirm('{$room}')">
                <p class="normal">{$c->__('button.delete')}</p>
            </li>
        {/if}
        {if="$presence != null && $presence->mucrole == 'moderator' && !$anon"}
            <li onclick="Chat_ajaxGetRoomConfig('{$room}')">
                <p class="normal">{$c->__('chatroom.config')}</p>
            </li>
            <li onclick="Chat_ajaxGetSubject('{$room}')">
                <p class="normal">{$c->__('chatroom.subject')}</p>
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
                <i class="zmdi zmdi-arrow-back"></i>
            </span>
            <span class="control icon active" onclick="Chats_ajaxClose('{$jid|echapJS}'); MovimTpl.hidePanel();">
                <i class="zmdi zmdi-close"></i>
            </span>
            {if="$c->supported('upload')"}
                <span class="control icon active" onclick="Upload_ajaxRequest()">
                    <i class="zmdi zmdi-attachment-alt"></i>
                </span>
            {/if}
            <p class="line">
                {if="$contact != null"}
                    {$contact->getTrueName()}
                {else}
                    {$jid|echapJS}
                {/if}
            </p>
            <p class="line" id="{$jid}_state">{$contact->jid}</p>
        </li>
    </ul>
    {/if}
</header>

<div id="{$jid}_discussion" class="contained" data-muc="{$muc}">
    <section id="{$jid}_messages">
        <ul class="list {if="$muc"}thin simple{else}thick{/if}" id="{$jid}_conversation"></ul>
    </section>
</div>
<div class="chat_box">
    <ul class="list thin">
        <li>
            <span class="primary icon gray emojis_open" onclick="Stickers_ajaxShow('{$jid}')">
                <img alt=":smiley:" class="emoji large" src="{$c->getSmileyPath('1f603')}">
            </span>
            <span class="control icon gray" data-jid="{$jid}" onclick="Chat.sendMessage(this.dataset.jid, {if="$muc"}true{else}false{/if})">
                <i class="zmdi zmdi-mail-send"></i>
            </span>
            <form>
                <div>
                     <textarea
                        rows="1"
                        id="chat_textarea"
                        data-jid="{$jid}"
                        onkeypress="
                            if(event.keyCode == 13) {
                                if(event.shiftKey) {
                                    return;
                                }
                                state = 0;
                                Chat.sendMessage(this.dataset.jid, {if="$muc"}true{else}false{/if});
                                return false;
                            } else if(event.keyCode == 38 && this.value == '') {
                                Chat_ajaxLast(this.dataset.jid);
                            } else if(event.keyCode == 40
                            && (this.value == '' || Chat.edit == true)) {
                                Chat.clearReplace();
                            } else {
                                {if="!$muc"}
                                if(state == 0 || state == 2) {
                                    state = 1;
                                    {$composing}
                                    since = new Date().getTime();
                                }
                                {/if}
                            }
                            "
                        onkeyup="
                            {if="!$muc"}
                            setTimeout(function()
                            {
                                if(state == 1 && since+5000 < new Date().getTime()) {
                                    state = 2;
                                    {$paused}
                                }
                            },5000);
                            {/if}
                            "
                        oninput="movim_textarea_autoheight(this);"
                        placeholder="{$c->__('chat.placeholder')}"
                    ></textarea>
                </div>
            </form>
        </li>
    </ul>
</div>
