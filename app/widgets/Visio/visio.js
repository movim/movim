function notifyOpener() {    
    //console.log(self.opener.popupWin);
    document.querySelector('#connection').style.display = 'none';
	if(self.opener || !self.opener.popupWin) 
        self.opener.popupWin = self;
}

setInterval( notifyOpener, 200 );

self.focus();

/**
 * When an error occured
 */
window.onerror = function() {
	document.querySelector('#connection').style.display = 'block'; 
};

/**
 * When the popup is closed
 */
window.onunload = function() {
    //self.opener.Roster_ajaxToggleChat();
};

/*function scrollAllTalks() {
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

}*/


//setInterval( scrollAllTalks, 200 );

var Visio = {
    fullScreen: function() {
        var elem = document.getElementById("visio");
        if (!document.fullscreenElement &&    // alternative standard method
          !document.mozFullScreenElement && !document.webkitFullscreenElement) {  // current working methods
        if (document.documentElement.requestFullscreen) {
              document.documentElement.requestFullscreen();
            } else if (document.documentElement.mozRequestFullScreen) {
              document.documentElement.mozRequestFullScreen();
            } else if (document.documentElement.webkitRequestFullscreen) {
              document.documentElement.webkitRequestFullscreen(Element.ALLOW_KEYBOARD_INPUT);
            }
        } else {
            if (document.cancelFullScreen) {
              document.cancelFullScreen();
            } else if (document.mozCancelFullScreen) {
              document.mozCancelFullScreen();
            } else if (document.webkitCancelFullScreen) {
              document.webkitCancelFullScreen();
            }
        }
    },

    log: function(content) {
        var date = new Date();
        movim_prepend([
            "log",
            "<div>["
                + date.getHours() + ":"+date.getMinutes() + ":"+date.getSeconds() + "] "
                + content +
            "</div>"]);
    }
}

movim_add_onload(function()
{
    document.getElementById("toggle-screen").onclick = function() { Visio.fullScreen(); };
});
