/**
 * Movim Websocket
 * 
 * This file define the websocket behaviour and handle its connection
 */ 

WebSocket.prototype.register = function() {
    this.send(JSON.stringify(
        {
            'func'      : 'register',
            'sid'       : localStorage.movimSession,
            'baseuri'   : BASE_URI
        }));
};
WebSocket.prototype.unregister = function() {
    this.send(JSON.stringify({'func' : 'unregister'}));
};

/**
 * @brief Definition of the MovimWebsocket object
 * @param string error 
 */
function MovimWebsocket() {
    var connection;
}

MovimWebsocket.prototype.init = function() {
    this.connection = new WebSocket('ws://' + BASE_HOST + ':8080');

    this.connection.onopen = function(e) {
        console.log("Connection established!");
        movim_onload();
    };

    this.connection.onmessage = function(e) {
        var obj = JSON.parse(e.data);

        if(obj.id) {
            localStorage.movimSession = obj.id;
            console.log('GNAP');
            document.cookie = 'MOVIM_SESSION_ID=' + obj.id;
            this.register();
        }

        if(obj.func == 'registered') {
            movim_onload();
        }

        if(obj.func == 'disconnected') {
            movim_disconnect();
        }

        websocket.handle(e.data);
    };
};

MovimWebsocket.prototype.send = function(widget, func, params) {
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
};

MovimWebsocket.prototype.handle = function(json) {
    var funcalls = JSON.parse(json);
    if(funcalls != null) {
        for(h = 0; h < funcalls.length; h++) {
            var funcall = funcalls[h];

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
}

MovimWebsocket.prototype.unregister = function() {
    this.connection.unregister();
}

function remoteUnregister()
{
    websocket.unregister();
}

// And we start it
var websocket = new MovimWebsocket;
websocket.init();
