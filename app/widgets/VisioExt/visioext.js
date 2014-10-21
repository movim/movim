var Popup = {
    win : null,
    jid : null,

    setJid: function(jid) {
        this.jid = jid;
    },
    
    open: function(jid) {
        console.log('Popup already opened');
        var url = BASE_URI + PAGE_KEY_URI + "visio&f="+jid

        this.setJid(jid);
        
        if( !this.win || this.win.closed ) {
            console.log('Opening the Popup');
            this.win = window.open( url, "win",  "height=480,width=640,directories=0,titlebar=0,toolbar=0,location=0,status=0, personalbar=0,menubar=0,resizable=0" );
        } else this.win.focus();
    },
    
    close: function() {
        if(this.win)
            this.win.close();
    },
    
    focus: function() {
        this.win.focus();
    },

    send: function(args) {
        var func = args[0];
        args.shift();
        var params = args;
        
        console.log('Calling the Popup');
        this.win[func](params);
    },
    
    hangUp: function(args) {
        console.log('Your friend just hung up');
    },
    
    call: function(args) {
        if( this.win && !this.win.closed ) {
            // The popup is open so call it
            Popup.send(args);
        } else if(this.jid) {
            // The popup is closed so open it
            console.log('We open the Popup');
            this.open(this.jid);

            console.log('We wait a little');
            this.win.addEventListener('load', function() { Popup.send(args); }, false);
        }
    }
}
