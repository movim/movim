/**
 * @brief Definition of the MovimWebsocket object
 * @param string error 
 */
function Popup() {
    var stack;
    var win;
}

Popup.prototype.init = function() {
    this.stack = {};
}

Popup.prototype.open = function() {
    console.log('Popup already opened');
    var url = BASE_URI + PAGE_KEY_URI + 'visio';

    //this.setJid(jid);
    console.log(url);
    if( !this.win || this.win.closed ) {
        console.log('Opening the Popup');
        this.win = window.open( url, "win",  "height=480,width=640,directories=0,titlebar=0,toolbar=0,location=0,status=0, personalbar=0,menubar=0,resizable=0" );
    } else this.win.focus();
}

Popup.prototype.close = function() {
    if(this.win) { this.win.close(); }
}

Popup.prototype.focus = function() {
    if(this.win) { this.win.focus(); }
}

Popup.prototype.setJid = function(jid) {
    this.stack.jid = jid;
}

Popup.prototype.call = function(func, param) {
    if(!this.win || this.win.closed ) {
        /*if(this.stack == null) {
            this.stack = {};
        }*/
        this.stack[func] = param;
        this.open();
    }
}

function remoteCall(func, param) {
    console.log('HOP');
    console.log(popup);
    popup.call(func, param);
}

function remoteSetJid(jid) {
    popup.setJid(jid);
}

var popup = new Popup;
popup.init();
