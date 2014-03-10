<div id="serverresult" class="paddedtop">
    <a class="button color purple oppose icon search" href="{$myserver}">{$c->t('Discover my server')}</a>
    <h2>{$c->t('Discussion Servers')}</h2>
    <ul class="list">
        {$servers}
    </ul>
</div>

<div class="clear"></div>

<div class="paddedtopbottom">
    <h2>{$c->t('Last registered')}</h2>

    <div id="contactsresult">
        {$contacts}
    </div>
</div>
