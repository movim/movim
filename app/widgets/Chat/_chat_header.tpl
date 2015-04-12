<div>
    <ul class="active">
        <li onclick="Rooms_ajaxAdd()">
            <span class="icon">
                <i class="md md-group-add"></i>
            </span>
        </li>
    </ul>
    <span class="on_desktop icon"><i class="md md-forum"></i></span>
    <h2 class="r1">{$c->__('page.chats')}</h2>
</div>
<div>
    <ul class="active">
        <li onclick="Chats_ajaxClose('{$jid|echapJS}'); MovimTpl.hidePanel();">
            <span class="icon">
                <i class="md md-close"></i>
            </span>
        </li>
    </ul>
    <div id="chat_header" class="return active r1" onclick="MovimTpl.hidePanel(); Chat_ajaxGet();">
        <span id="back" class="icon"><i class="md md-arrow-back"></i></span>
        <h2>
            {if="$contact != null"}
                {$contact->getTrueName()}
            {else}
                {$jid|echapJS}
            {/if}
        </h2>
    </h2>
</div>
