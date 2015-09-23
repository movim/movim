<div>
    <ul class="active">
        <li onclick="Rooms_ajaxAdd()">
            <span class="icon">
                <i class="zmdi zmdi-group-add"></i>
            </span>
        </li>
    </ul>
    <span class="on_desktop icon"><i class="zmdi zmdi-comments"></i></span>
    <h2 class="r1">
        {$c->__('page.chats')}
    </h2>
</div>
<div>
    <ul class="active">
        {if="$c->supported('upload')"}
        <li onclick="Upload_ajaxRequest()">
            <span class="icon">
                <i class="zmdi zmdi-attachment-alt"></i>
            </span>
        </li>
        {/if}
        <li onclick="Chats_ajaxClose('{$jid|echapJS}'); MovimTpl.hidePanel();">
            <span class="icon">
                <i class="zmdi zmdi-close"></i>
            </span>
        </li>
    </ul>
    <div
        id="chat_header"
        class="return active {if="$c->supported('upload')"}r2{else}r1{/if} condensed"
        onclick="
            MovimTpl.hidePanel();
            Notification.current('chat');
            Chat_ajaxGet();">
        <span id="back" class="icon"><i class="zmdi zmdi-arrow-back"></i></span>
        <h2>
            {if="$contact != null"}
                {$contact->getTrueName()}
            {else}
                {$jid|echapJS}
            {/if}
        </h2>
        <h4 id="{$jid}_state">{$contact->jid}</h4>
    </h2>
</div>
