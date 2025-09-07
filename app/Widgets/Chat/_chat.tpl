<header id="{$jid|cleanupId}-header">
    {autoescape="off"}
        {$c->prepareHeader($jid, $muc)}
    {/autoescape}
</header>

<div id="{$jid|cleanupId}-discussion" class="contained {if="$muc"}muc{/if}">
    <section id="{$jid|cleanupId}-messages">
        <div class="placeholder first_messages">
            <i class="material-symbols fill">waving_hand</i>
            <h1>{$c->__('chat.first_messages_title')}</h1>
            {if="!$muc"}
                <h4 style="margin-bottom: 1rem;">{$c->__('chat.first_messages_text')}</h4>
                <h4>
                    <button class="button color" onclick="Notifications_ajaxAddAsk('{$jid|echapJS}')">
                        <i class="material-symbols">add</i> {$c->__('chat.first_messages_add')}
                    </button>
                    <button class="button flat" onclick="ChatActions_ajaxBlock('{$jid|echapJS}'); Notifications_ajaxRefuse('{$jid|echapJS}');">
                        {$c->__('chat.first_messages_block')}
                    </button>
                </h4>
            {/if}
        </div>
        <ul class="list spin conversation" id="{$jid|cleanupId}-conversation"></ul>
        <div class="placeholder empty">
            <i class="material-symbols fill">chat</i>
            <h1>{$c->__('chat.new_title')}</h1>
            <h4>{$c->__('chat.new_text')}</h4>
            <h4>{$c->__('message.edit_help')}</h4>
            <h4>{$c->__('message.emoji_help')}</h4>
        </div>
    </section>
</div>
<div class="chat_box {if="isset($conference) && $conference->presence && $conference->presence->mucrole == 'visitor'"}disabled{/if}">
    <a id="scroll_now" class="button action color small" onclick="Chat_ajaxClearAndGetMessages('{$jid}', {if="$muc"}true{/if})">
        <i class="material-symbols">clock_arrow_down</i>
    </a>
    <a id="scroll_down" class="button action color transparent small" onclick="Chat.scrollTotally()">
        <i class="material-symbols">expand_more</i>
    </a>
    <ul class="list fill">
        <div id="reply"></div>
        <ul class="list middle" id="embed"></ul>
        <div id="dictaphone"></div>
        <li class="emojis"></li>
        <li class="main">
            <span class="primary icon gray active"
                  title="{$c->__('sticker.title')}"
                  onclick="Stickers_ajaxShow('{$jid}')">
                <i class="material-symbols flip-vert">note_stack</i>
            </span>
            {if="$c->getUser()->hasUpload()"}
                <span class="emojis control icon gray active"
                        id="dictaphone_toggle"
                        title="{$c->__('dictaphone.name')}"
                        onclick="Dictaphone.toggle()">
                    <i class="material-symbols">mic</i>
                </span>
            {/if}
            <span class="emojis control icon gray active on_desktop"
                    title="{$c->__('emojisconfig.title')}"
                    onclick="Stickers_ajaxReaction(null)">
                <i class="material-symbols">emoji_emotions</i>
            </span>
            {if="$c->getUser()->hasUpload()"}
                <span class="attach control icon" onclick="Chat.toggleAttach()">
                    <i class="material-symbols">add_circle</i>
                </span>
                <ul class="list active actions">
                    <li onclick="Chat.toggleAttach(); Snap.init()">
                        <span class="control icon gray">
                            <i class="material-symbols">camera_alt</i>
                        </span>
                        <div>
                            <p class="normal line">Snap</p>
                        </div>
                    </li>
                    <li onclick="Chat.toggleAttach(); Draw_ajaxHttpGet()">
                        <span class="control icon gray">
                            <i class="material-symbols">gesture</i>
                        </span>
                        <div>
                            <p class="normal line">{$c->__('draw.title')}</p>
                        </div>
                    </li>
                    <li onclick="Chat.toggleAttach(); Upload_ajaxGetPanel()">
                        <span class="control icon gray">
                            <i class="material-symbols">attach_file</i>
                        </span>
                        <div>
                            <p class="normal line">{$c->__('upload.title')}</p>
                        </div>
                    </li>
                </ul>
            {/if}
            <span title="{$c->__('button.submit')}"
                class="send control icon gray"
                  onclick="Chat.sendMessage()">
                <i class="material-symbols">send</i>
            </span>
            <form>
                <div>
                     <textarea
                        dir="auto"
                        rows="1"
                        id="chat_textarea"
                        data-jid="{$jid}"
                        data-muc="{if="$muc"}true{/if}"
                        data-muc-group="{if="isset($conference) && $conference->isGroupChat()"}true{/if}"
                        {$rand = rand(0, 1)}
                        {if="isset($conference) && $conference->presence && $conference->presence->mucrole == 'visitor'"}
                            placeholder="{$c->__('message.visitor_help')}"
                        {elseif="$rand == 1"}
                            placeholder="{$c->__('message.emoji_help')}"
                        {else}
                            placeholder="{$c->__('chat.placeholder')}"
                        {/if}
                        {if="isset($conference) && $conference->presence && $conference->presence->mucrole == 'visitor'"}disabled{/if}
                    ></textarea>
                    <span class="control icon encrypted" title="{$c->__('omemo.encrypted')}"
                        onclick="ChatOmemo.disableContactState('{$jid}', {if="$muc"}true{else}false{/if})">
                        <i class="material-symbols fill">lock</i>
                    </span>
                    <span class="control icon encrypted_disabled" title="{$c->__('omemo.encrypted_disabled')}"
                        onclick="ChatOmemo.enableContactState('{$jid}', {if="$muc"}true{else}false{/if})">
                        <i class="material-symbols">no_encryption</i>
                    </span>
                    <span class="control icon encrypted_loading" title="{$c->__('omemo.encrypted_loading')}"
                        onclick="ChatOmemo.disableContactState('{$jid}', {if="$muc"}true{else}false{/if})">
                        <i class="material-symbols">lock_clock</i>
                    </span>
                </div>
            </form>
        </li>
    </ul>
</div>
