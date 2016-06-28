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

            <span class="control icon active" onclick="Rooms_ajaxExit('{$room}'); MovimTpl.hidePanel(); {if="$anon"}Presence_ajaxLogout(){/if}">
                <i class="zmdi zmdi-close"></i>
            </span>

            <span class="control icon active" onclick="Rooms_ajaxList('{$jid|echapJS}')">
                <i class="zmdi zmdi-accounts"></i>
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
        {if="$presence != null && $presence->mucrole == 'moderator' && !$anon"}
            <li onclick="Chat_ajaxGetRoomConfig('{$room}')">
                <p class="normal">{$c->__('chatroom.administration')}</p>
            </li>
            <li class="divided" onclick="Chat_ajaxGetSubject('{$room}')">
                <p class="normal">{$c->__('chatroom.subject')}</p>
            </li>
        {/if}
        {if="!$anon"}
            <li onclick="Rooms_ajaxRemoveConfirm('{$room}')">
                <p class="normal">{$c->__('button.delete')}</p>
            </li>
        {/if}
        <li onclick="Rooms_ajaxEdit('{$room}');">
            <p class="normal">{$c->__('chatroom.config')}</p>
        </li>
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
            <p class="line">
                {$contact->getTrueName()}
            </p>
            <p class="line" id="{$jid}_state">{$contact->jid}</p>
        </li>
    </ul>
    {/if}
</header>

<div id="{$jid}_discussion" class="contained" data-muc="{$muc}">
    <section id="{$jid}_messages">
        <ul class="list {if="$muc"}thin simple{else}middle{/if}" id="{$jid}_conversation"></ul>
    </section>
</div>
<div class="chat_box">
    <ul class="list thin">
        <li>
            <span class="primary icon gray emojis_open" onclick="Stickers_ajaxShow('{$jid}')">
                <img alt=":smiley:" class="emoji large" src="{$c->getSmileyPath('1f603')}">
            </span>
            {if="$c->supported('upload')"}
                <span class="control icon" onclick="Upload_ajaxRequest()">
                    <i class="zmdi zmdi-attachment-alt"></i>
                </span>
            {/if}
            <span class="control icon gray {if="$c->supported('upload')"}hide{else}show{/if}"
                  data-jid="{$jid}"
                  onclick="Chat.sendMessage(this.dataset.jid, {if="$muc"}true{else}false{/if})">
                <i class="zmdi zmdi-mail-send"></i>
            </span>
            <form>
                <div>
                     <textarea
                        rows="1"
                        id="chat_textarea"
                        data-jid="{$jid}"
                        onkeydown="
                            if(event.keyCode == 38 && this.value == '') {
                                Chat_ajaxLast(this.dataset.jid);
                            } else if(event.keyCode == 40
                            && (this.value == '' || Chat.edit == true)) {
                                Chat.clearReplace();
                            }
                        "
                        onkeypress="
                            if(event.keyCode == 13) {
                                if(event.shiftKey) {
                                    return;
                                }
                                state = 0;
                                Chat.sendMessage(this.dataset.jid, {if="$muc"}true{else}false{/if});
                                return false;
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
                            {if="$c->supported('upload')"}
                                Chat.toggleAction(this.value.length);
                            {/if}
                            "
                        oninput="MovimUtils.textareaAutoheight(this);"
                        placeholder="{$c->__('chat.placeholder')}"
                    ></textarea>
                </div>
            </form>
        </li>
    </ul>
</div>

