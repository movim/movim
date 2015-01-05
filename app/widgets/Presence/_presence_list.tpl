<section>
    <ul class="active thin">
        <li class="subheader">{$c->__('status.status')}</li>
        <li>
            <span class="icon"><i class="md md-mode-edit"></i></span>
            <form>
                <div>
                    <textarea 
                        class="status" 
                        spellcheck="false"
                        placeholder="{$c->__('status.here')}"
                        onload="movim_textarea_autoheight(this);"
                        onkeyup="movim_textarea_autoheight(this);">{if="isset($p)"}{$p->status}{/if}</textarea>
                    <label>{$c->__('status.here')}</label>
                </div>
            </form>        
        </li>
        <li onclick="{$callchat}">
            <span class="icon bubble color small green"></span>
            {$txt[1]}
        </li>
        <li onclick="{$callaway}">
            <span class="icon bubble color small orange"></span>
            {$txt[2]}
        </li>
        <li onclick="{$calldnd}">
            <span class="icon bubble color small red"></span>
            {$txt[3]}
        </li>
        <li onclick="{$callxa}" >
            <span class="icon bubble color small purple"></span>
            {$txt[4]}
        </li>
        <li class="subheader">{$c->__('status.disconnect')}</li>
        <li onclick="{$calllogout}">
            <span class="icon"><i class="md md-exit-to-app"></i></span>
            {$c->__('status.disconnect')}
        </li>
    </ul>
</section>
<div>
    <a onclick="Dialog.clear()" class="button flat">
        {$c->__('button.close')}
    </a>
</div>
