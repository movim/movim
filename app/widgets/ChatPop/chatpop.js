function notifyOpener() {    
    document.querySelector('#connection').style.display = 'none';
	if(self.opener || !self.opener.popupWin) 
        self.opener.popupWin = self;
}

setInterval( notifyOpener, 200 );

self.focus();

function doSomething() {
	alert("I'm doing something");
}

function handleError() { 
    document.querySelector('#connection').style.display = 'block'; 
}

window.onerror = handleError;

window.onunload = function() {
    self.opener.Roster_ajaxToggleChat();
};

function scrollAllTalks() {
    var mes = document.querySelectorAll('.content');
    for (var i=0; i<mes.length; i++){
        // We add 200px to prevent smiley loading
        mes.item(i).scrollTop = mes.item(i).scrollHeight + 200;
    }
}

function sendMessage(n, jid)
{
    var text = n.value;
    
    n.value = "";
    n.focus();
    
    // We escape the text to prevent XML errors
    return encodeURIComponent(text);

}


//setInterval( scrollAllTalks, 200 );
