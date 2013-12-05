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
    },

    /*
     * @brief Call a function in the main window
     * @param Array, array[0] is the name of the function, then the params
     */
    call: function(args) {
        if( self.opener && !self.opener.closed ) {
            // The popup is open so call it
            var func = args[0];
            args.shift();
            var params = args;
            self.opener[func].apply(null, params);
        } 
    }
}

movim_add_onload(function()
{
    document.getElementById("toggle-screen").onclick = function() { Visio.fullScreen(); };
});
