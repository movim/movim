// Initialise popup pointer
/*var popupWin = null;

function openPopup(jid) {
	var url = BASE_URI + PAGE_KEY_URI + "visio&f="+jid
	if( !popupWin || popupWin.closed ) {
		popupWin = window.open( url, "popupWin",  "height=480,width=640,directories=0,titlebar=0,toolbar=0,location=0,status=0, personalbar=0,menubar=0,resizable=0" );
	} else popupWin.focus();
}

function closePopup() {
    popupWin.close();
}

function focusPopup() {
    popupWin.focus();
}

function popUpEvent(args) {
	if( popupWin && !popupWin.closed ) {
		// The popup is open so call it
        var func = args[0];
        args.shift();
		var params = args;
        
        window['popupWin'][func](params);
	} else {
		// The popup is closed so open it
		openPopup();
	}
}*/

var Popup = {
    win : null,
    open: function(jid) {
        console.log('Opening the Popup');
        var url = BASE_URI + PAGE_KEY_URI + "visio&f="+jid
        
        if( !this.win || this.win.closed ) {
            this.win = window.open( url, "win",  "height=480,width=640,directories=0,titlebar=0,toolbar=0,location=0,status=0, personalbar=0,menubar=0,resizable=0" );
        } else this.win.focus();
    },
    
    close: function() {
        this.win.close();
    },
    
    focus: function() {
        this.win.focus();
    },
    
    call: function(args) {
        if( this.win && !this.win.closed ) {
            // The popup is open so call it
            var func = args[0];
            args.shift();
            var params = args;
            
            console.log('Calling the Popup');
            console.log(args);
            
            this.win[func](params);
        } else {
            // The popup is closed so open it
            
        }
    }
}
