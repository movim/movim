<section>
    <ul class="active">
        <li class="subheader">Status **Fixme**</li>
        <li>
            <span class="icon"><i class="md md-thumbs-up-down"></i></span>
            <form>
                <div>
                    <textarea 
                        class="status" 
                        spellcheck="false"
                        placeholder="{$c->__('status.here')}"
                        onload="movim_textarea_autoheight(this);"
                        onkeyup="movim_textarea_autoheight(this);">{if="isset($p)"}{$p->status}{/if}</textarea>
                </div>
            </form>        
        </li>
        <li class="subheader">Presence **Fixme**</li>
        <li>
            <a onclick="{$callchat} movim_toggle_class('#logoutlist', 'show');" class="online">
                <span class="icon bubble color small green"></span>
                {$txt[1]}
            </a>
        </li>
        <li>
            <a onclick="{$callaway} movim_toggle_class('#logoutlist', 'show');" class="away">
                <span class="icon bubble color small orange"></span>
                {$txt[2]}
            </a>
        </li>
        <li>
            <a onclick="{$calldnd}  movim_toggle_class('#logoutlist', 'show');" class="dnd">
                <span class="icon bubble color small red"></span>
                {$txt[3]}
            </a>
        </li>
        <li>
            <a onclick="{$callxa} movim_toggle_class('#logoutlist', 'show');" class="xa">
                <span class="icon bubble color small purple"></span>
                {$txt[4]}
            </a>
        </li>
    </ul>
</section>
<div class="actions">
    <a onclick="Dialog.clear()" class="button flat">
        {$c->__('button.close')}
    </a>
    <a onclick="{$calllogout} movim_toggle_class('#logoutlist', 'show');" class="button flat">
        {$c->__('disconnect')}
    </a>
</div>
