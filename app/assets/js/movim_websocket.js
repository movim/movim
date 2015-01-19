/**
 * Movim Websocket
 * 
 * This file define the websocket behaviour and handle its connection
 */ 

WebSocket.prototype.unregister = function() {
    this.send(JSON.stringify({'func' : 'unregister'}));
};

WebSocket.prototype.admin = function(key) {
    this.send(JSON.stringify({'func' : 'admin', 'key' : key}));
};

/**
 * @brief Definition of the MovimWebsocket object
 * @param string error 
 */

var MovimWebsocket = {
    connection: null,
    attached: new Array(),
    unregistered: false,
    
    launchAttached : function() {
        for(var i = 0; i < MovimWebsocket.attached.length; i++) {
            MovimWebsocket.attached[i]();
        }
    },

    init : function() {
        this.connection = new WebSocket('ws://' + BASE_HOST + ':8080');
        
        this.connection.onopen = function(e) {
            console.log("Connection established!");
            MovimWebsocket.launchAttached();
        };

        this.connection.onmessage = function(e) {
            data = pako.ungzip(base64_decode(e.data), { to: 'string' });

            var obj = JSON.parse(data);

            if(obj != null) {
                if(obj.func == 'registered') {
                    MovimWebsocket.launchAttached();
                }

                if(obj.func == 'disconnected') {
                    movim_disconnect();
                }
            }

            MovimWebsocket.handle(data);
        };

        this.connection.onclose = function(e) {
            console.log("Connection closed by the server or session closed");
            if(e.code == 1006 || e.code == 1000) {
                if(MovimWebsocket.unregistered == false) {
                    movim_disconnect();
                } else {
                    MovimWebsocket.unregistered = false;
                    MovimWebsocket.init();
                }
            }
        };

        this.connection.onerror = function(e) {
            console.log("Connection error!");
            // We prevent the onclose launch
            this.onclose = null;
        };
    },

    send : function(widget, func, params) {
        this.connection.send(
            JSON.stringify(
                {'func' : 'message', 'body' :
                    {
                        'widget' : widget,
                        'func' : func,
                        'params' : params
                    }
                }
            )
        );
    },

    attach : function(func) {
        if(typeof(func) === "function") {
            this.attached.push(func);
        }
    },

    handle : function(json) {
        var funcalls = JSON.parse(json);
        if(funcalls != null) {
            for(h = 0; h < funcalls.length; h++) {
                var funcall = funcalls[h];
                //console.log(funcall);
                if(funcall.func != null && (typeof window[funcall.func] == 'function')) {
                    try {
                        window[funcall.func].apply(null, funcall.params);
                    } catch(err) {
                        console.log("Error caught: " + err.toString() + " - " + funcall.func + ":" + JSON.stringify(funcall.params));
                    }
                } else if(funcall.func != null) {
                    var funcs  = funcall.func.split('.');
                    var called = funcs[0];
                    if(typeof window[called] == 'object') {
                        window[funcs[0]][funcs[1]].apply(null, funcall.params);
                    }
                }
            }
        }
    },

    unregister : function(reload) {
        if(reload == false) this.unregistered = true;
        this.connection.unregister();
    }
}

function remoteUnregister()
{
    MovimWebsocket.unregister(false);
}

function remoteUnregisterReload()
{
    MovimWebsocket.unregister(true);
}

window.onbeforeunload = function() {
    MovimWebsocket.connection.onclose = function () {}; // disable onclose handler first
    MovimWebsocket.connection.close()
};

movim_add_onload(function() {
    // And we start it
    MovimWebsocket.init();
});
