<div class="chat" 
     onclick="this.querySelector('textarea').focus()"
     id="chat{$contact->jid}">
    <div class="panel" {$panelstyle}>
        <div class="head" >
            <span class="chatbutton cross" onclick="{$closetalk}"></span>
            <span class="chatbutton arrow" onclick="{$hidetalk} hideTalk(this)"></span>
            <a class="name" href="{$c->route('friend',$contact->jid)}">
                {$contact->getTrueName()}
            </a>
        </div>
        <div class="messages" id="messages{$contact->jid}">
            {$messageshtml}
            <div style="display: none;" class="message composing" id="composing{$contact->jid}">{$c->t('Composing…')}</div>
            <div style="display: none;" class="message composing" id="paused{$contact->jid}">{$c->t('Paused…')}</div>                        
        </div>
        
        <div class="text">
             <textarea 
                rows="1"
                id="textarea{$contact->jid}"
                onkeypress="
                    if(event.keyCode == 13) {
                        state = 0;
                        {$send}
                        return false;
                    } else {
                        if(state == 0 || state == 2) {
                            state = 1;
                            {$composing}
                            since = new Date().getTime();
                        }
                    }
                    "
                onkeyup="
                    movim_textarea_autoheight(this);
                    setTimeout(function()
                    {
                        if(state == 1 && since+5000 < new Date().getTime()) {
                            state = 2;
                            {$paused}
                        }
                    },5000); 
                    "
            ></textarea>
        </div>
    </div>
    
    <div class="tab" {$tabstyle} onclick="{$hidetalk} showTalk(this);">
        <div class="name">
            <img class="avatar"  src="{$contact->getPhoto('xs')}" />{$contact->getTrueName()}
        </div>
    </div>
</div>
