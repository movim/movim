function scrollAllTalks() {
    var mes = document.querySelectorAll('.messages');
    for (var i=0; i<mes.length; i++){
        mes.item(i).scrollTop = mes.item(i).scrollHeight;
    }
}
//Loads the Notification sound.
var chatSoundNotif = document.createElement('audio');
chatSoundNotif.setAttribute('src', './system/Widget/widgets/Chat/sound/notif.ogg');
chatSoundNotif.load();
chatSoundNotif.volume = 1;

movim_add_onload(function()
{
    scrollAllTalks();
});

function colorTalk(params) {
    chat = document.getElementById(params);
    chat.parentNode.style.backgroundColor = '#DD951F';
}

function hideTalk(n) {
    n.parentNode.style.backgroundColor = '#444';
    childs = n.parentNode.childNodes;
    messages = childs[0];
    text = childs[1];
    if(messages.style.display == 'none') {
        messages.style.display = 'block';
        text.style.display = 'block';
    }
    else {
        messages.style.display = 'none';
        text.style.display = 'none';   
    }
}

function closeTalk(n) {
    n.parentNode.parentNode.removeChild(n.parentNode);
}

function scrollTalk(params) {
    var messages = document.getElementById(params);
    messages.scrollTop = messages.scrollHeight;
}

function showComposing(jid) {
    var box = document.getElementById('messages' + jid);
    var composing = document.getElementById('composing' + jid);
    
    box.appendChild(composing);
    
    composing.style.display = 'block';
}

function notify() {
    if(document_focus == false) {
        movim_title_inc();
        //play the notif sound
        chatSoundNotif.pause();
        chatSoundNotif.currentTime= 0;
        chatSoundNotif.play();
    }

}

function hideComposing(jid) {
    var composing = document.getElementById('composing' + jid);
    composing.style.display = 'none';
}

function sendMessage(n, jid)
{
    var text = n.value;
    
    n.value = "";
    
    n.focus();
    
    // We escape the text to prevent XML errors
    return encodeURIComponent(text);

}

function disableSound(){
	chatSoundNotif.volume = 0;
}
