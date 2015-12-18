<div>
    <ul class="list middle">
        <li>
            <span class="primary on_desktop icon"><i class="zmdi zmdi-comments"></i></span>
            <p>
                {$c->__('page.chats')}
            </p>
        </li>
    </ul>
</div>
<div>
    <ul class="list middle">
        <li id="chat_header">
            <span onclick="
            MovimTpl.hidePanel();
            Notification.current('chat');
            Header_ajaxReset('chat');
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
</div>
