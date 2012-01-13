function scrollAllTalks() {
    var mes = document.querySelectorAll('.messages');
    for (var i=0; i<mes.length; i++){
        mes.item(i).scrollTop = mes.item(i).scrollHeight;
    }
}

movim_add_onload(function()
{
    scrollAllTalks();
});

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

function newMessage() {
    if(document_focus == false) {
        movim_title_inc();
    }
}

function hideComposing(jid) {
    var composing = document.getElementById('composing' + jid);
    composing.style.display = 'none';
}

function sendMessage(n, jid)
{
    var text = n.value;
    var date = new Date();
    
    var h = date.getHours();
    if (h<10) {h = "0" + h}

    var m = date.getMinutes();
    if (m<10) {m = "0" + m}
    
    var box = document.getElementById('messages' + jid);
    box.innerHTML = box.innerHTML + '<div class="message me"><span class="date">' + h + ':' + m + '</span>' + 
    text + '</div>';
    
    n.value = "";
    
    n.focus();
    
    scrollTalk('messages' + jid);
    
    // We escape the text to prevent XML errors
    return encodeURIComponent(text);

}
