{if="isset($p)"}
    <div 
        id="logouttab" 
        class="{$txts[$p->value]}"
        onclick="movim_toggle_class('#logoutlist', 'show');">
        {$txt[$p->value]}
    </div>
{else}
    <div 
        id="logouttab" 
        class="{$txts[1]}"
        onclick="movim_toggle_class('#logoutlist', 'show');">
        {$txt[1]}
    </div>
{/if}

<div id="logoutlist">
    <a onclick="{$callchat} movim_toggle_class('#logoutlist', 'show');" class="online">{$txt[1]}</a>
    <a onclick="{$callaway} movim_toggle_class('#logoutlist', 'show');" class="away">{$txt[2]}</a>
    <a onclick="{$calldnd}  movim_toggle_class('#logoutlist', 'show');" class="dnd">{$txt[3]}</a>
    <a onclick="{$callxa} movim_toggle_class('#logoutlist', 'show');" class="xa">{$txt[4]}</a>
    <a onclick="{$calllogout} movim_toggle_class('#logoutlist', 'show');" class="disconnect">{$c->__('disconnect')}</a>
</div>
