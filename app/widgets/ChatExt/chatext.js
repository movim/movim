// Initialise popup pointer
var popupWin = null;

function openPopup() {
	var url = BASE_URI + PAGE_KEY_URI + "chatpop"
	if( !popupWin || popupWin.closed ) {
		popupWin = window.open( url, "popupWin",  "height=400,width=600,directories=0,titlebar=0,toolbar=0,location=0,status=0,menubar=0,resizable=no" );
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
}
