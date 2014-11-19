//Loads the Notification sound.
/*var chatSoundNotif = document.createElement('audio');
chatSoundNotif.setAttribute('src', './system/Widget/widgets/Chat/sound/notif.ogg');
chatSoundNotif.load();
chatSoundNotif.volume = 1;
this.querySelector('textarea').focus()

function disableSound(){
	chatSoundNotif.volume = 0;
}

* */

var Chats = {
    message: function(jid, html) {
        movim_append('messages' + jid, html);
        Chats.scroll(jid);
    },
    scroll: function(jid) {
        var messages = document.getElementById('messages' + jid);
        if(messages != null) messages.scrollTop = messages.scrollHeight;
    },

    scrollAll: function() {
        var mes = document.querySelectorAll('.messages');
        for (var i = 0; i<mes.length; i++){
            mes.item(i).scrollTop = mes.item(i).scrollHeight + 200;
        }
    },
    unread: function(jid) {
        chat = document.getElementById('chat' + jid);
        chat.querySelector('.tab').className = 'tab alert';
    },
}

movim_add_onload(function()
{
    Chats.scrollAll();
});

function showTalk(n) {
    panel = n.parentNode.querySelector('.panel');
    
    panel.style.display = 'block';
    n.style.display = 'none'; 
    
    n.className = 'tab';
}

function hideTalk(n) {
    panel = n.parentNode.parentNode.parentNode.querySelector('.panel');
    tab = n.parentNode.parentNode.parentNode.querySelector('.tab');
    
    panel.style.display = 'none';
    tab.style.display = 'block';
}

function showComposing(jid) {
    var box = document.getElementById('messages' + jid);
    var composing = document.getElementById('composing' + jid);
    
    hidePaused(jid);
    box.appendChild(composing);
    
    composing.style.display = 'block';
}

function showPaused(jid) {
    var box = document.getElementById('messages' + jid);
    var paused = document.getElementById('paused' + jid);
    
    hideComposing(jid);
    box.appendChild(paused);
    
    paused.style.display = 'block';
}

function notify(title, body, image) {
    if(document_focus == false) {
        movim_title_inc();
        movim_desktop_notification(title, body, image);
    }
}

function hideComposing(jid) {
    var composing = document.getElementById('composing' + jid);
    composing.style.display = 'none';
}

function hidePaused(jid) {
    var paused = document.getElementById('paused' + jid);
    paused.style.display = 'none';
}

function sendMessage(n, jid)
{
    var text = n.value;
    n.value = "";
    n.focus();
    // We escape the text to prevent XML errors
    return encodeURIComponent(text);

}
