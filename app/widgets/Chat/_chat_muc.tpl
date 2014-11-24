<div class="chat muc" 
     onclick="this.querySelector('textarea').focus()"
     id="chat{$jid}">
    <div class="panel" {$panelstyle}>
        <div class="head" >
            <a class="name">
                {$conference->name} - {$jid}
            </a>
            <span class="chatbutton cross" onclick="{$exitmuc}"></span>
            <span 
                class="chatbutton arrow" 
                onclick="{$toggle} hideTalk(this); scrollAllTalks()">
            </span>
        </div>
        <div class="list" id="list{$jid}">
            {$muclist}
        </div>
        <div class="messages" id="messages{$jid}">
            {$messageshtml}
        </div>

        <div class="text">
             <textarea 
                rows="1"
                id="textarea{$jid}"
                onkeyup="
                    movim_textarea_autoheight(this);
                    "
                onkeypress="
                    if(event.keyCode == 13) {
                        {$sendmessage}
                        return false;
                    }"
            ></textarea>
        </div>
    </div>
    <div 
        class="tab" 
        {$tabstyle} 
        onclick="{$toggle} showTalk(this); scrollAllTalks();">
        <div class="name">
            {$conference->name}
        </div>
    </div>


</div>
