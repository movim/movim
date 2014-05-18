<div id="serverresult" class="paddedtop">
    <a class="button color purple oppose icon search" href="{$myserver}">{$c->__('discover_my_server')}</a>
    <h2>{$c->__('discussion_servers')}</h2>
    <ul class="list">
        {$servers}
    </ul>
</div>

<div class="clear"></div>

<div class="paddedtopbottom">
    <h2>{$c->__('last_registered')}</h2>

    <div id="contactsresult">
        {$contacts}
    </div>
</div>
