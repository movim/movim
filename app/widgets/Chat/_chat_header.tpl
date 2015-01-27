<div>
    <span class="on_desktop icon"><i class="md md-forum"></i></span>
    <h2>{$c->__('page.chats')}</h2>
</div>
<div>
    <ul class="active">
        <li onclick="Chats_ajaxClose('{$jid}'); MovimTpl.hidePanel();">
            <span class="icon">
                <i class="md md-close"></i>
            </span>
        </li>
    </ul>
    <h2 class="active r1" onclick="MovimTpl.hidePanel(); Chat_ajaxGet();">
        <span id="back" class="icon"><i class="md md-arrow-back"></i></span>
        {if="$contact != null"}
            {$contact->getTrueName()}
        {else}
            {$jid}
        {/if}
    </h2>
</div>
