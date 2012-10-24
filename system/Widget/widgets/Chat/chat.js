function scrollAllTalks() {
    var mes = document.querySelectorAll('.messages');
    for (var i=0; i<mes.length; i++){
        // We add 200px to prevent smiley loading
        mes.item(i).scrollTop = mes.item(i).scrollHeight + 200;
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
    messages = document.getElementById(params);
    tabstyle = messages.parentNode.parentNode.querySelector('.tab').className = 'tab alert';
}

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

function closeTalk(n) {
    n.parentNode.parentNode.parentNode.parentNode.removeChild(n.parentNode.parentNode.parentNode);
}

function scrollTalk(params) {
    var messages = document.getElementById(params);
    messages.scrollTop = messages.scrollHeight;
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

function disableSound(){
	chatSoundNotif.volume = 0;
}

function setBackgroundColor(where, color)
{
    target = document.getElementById(where);
    if(target) {
        target.style.backgroundColor = color;
    }
}
