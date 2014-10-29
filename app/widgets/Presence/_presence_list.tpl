{if="isset($p)"}
    <div 
        id="tab" 
        class="{$txts[$p->value]}">
        <img class="avatar" src="{$contact->getPhoto('s')}"/>
        <span class="bubble"></span>
        <span class="arrow"><i class="fa fa-caret-down"></i></span>
        <span class="name">{$contact->getTrueName()}</span>
        <span class="status">{$p->status}</span>
    </div>
{else}
    <div 
        id="tab" 
        class="{$txts[1]}">
        <img class="avatar" src="{$contact->getPhoto('s')}"/>
        <span class="arrow"><i class="fa fa-caret-down"></i></span>
        <span class="name">{$contact->getTrueName()}</span>
    </div>
{/if}

<div id="list">
    <div class="tab">
        <img class="avatar" src="{$contact->getPhoto('s')}"/>
        <span class="arrow"><i class="fa fa-caret-up"></i></span>
        <span class="name">{$contact->getTrueName()}</span>
    </div>

    <textarea 
        class="status" 
        spellcheck="false"
        placeholder="{$c->__('status.here')}"
        onload="movim_textarea_autoheight(this);"
        onkeyup="movim_textarea_autoheight(this);">{$p->status}</textarea>
    
    <a onclick="{$callchat} movim_toggle_class('#logoutlist', 'show');" class="online"><span class="bubble"></span>{$txt[1]}</a>
    <a onclick="{$callaway} movim_toggle_class('#logoutlist', 'show');" class="away"><span class="bubble"></span>{$txt[2]}</a>
    <a onclick="{$calldnd}  movim_toggle_class('#logoutlist', 'show');" class="dnd"><span class="bubble"></span>{$txt[3]}</a>
    <a onclick="{$callxa} movim_toggle_class('#logoutlist', 'show');" class="xa"><span class="bubble"></span>{$txt[4]}</a>
    <a onclick="{$calllogout} movim_toggle_class('#logoutlist', 'show');" class="disconnect"><i class="fa fa-sign-out"></i>{$c->__('disconnect')}</a>
</div>
