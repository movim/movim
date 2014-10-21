/**
 * Movim Websocket
 * 
 * This file define the websocket behaviour and handle its connection
 */ 

WebSocket.prototype.link = function(body) {
    this.send(JSON.stringify({'func' : 'link'}));
};
WebSocket.prototype.register = function() {
    this.send(JSON.stringify({'func' : 'register', 'sid' : localStorage.movimSession}));
};

/**
 * @brief Definition of the MovimWebsocket object
 * @param string error 
 */
function MovimWebsocket() {
    var connection;
}

MovimWebsocket.prototype.init = function() {
    this.connection = new WebSocket('ws://localhost:8080');

    this.connection.onopen = function(e) {
        console.log("Connection established!");

        if(localStorage.movimSession === undefined) {
            this.send(JSON.stringify({'func' : 'ask'}));
        } else {
            this.register();
        }

        // And we launch the Javascript
        movim_onload();
    };

    this.connection.onmessage = function(e) {
        console.log(e.data);
        var obj = JSON.parse(e.data);

        if(obj.id) {
            localStorage.movimSession = obj.id;
            this.register();
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
    var funcalls = eval(json);
    
    if(funcalls != null) {
        for(h = 0; h < funcalls.length; h++) {
            var funcall = funcalls[h];

            if(funcall.func != null && eval("typeof " + funcall.func) == "function") {
                var funcs = funcall.func.split('.');
                
                try {
                    if(funcs.length == 1)
                        window[funcs[0]](funcall.params);
                    else if(funcs.length == 2)
                        window[funcs[0]][funcs[1]](funcall.params);
                }
                catch(err) {
                    console.log("Error caught: " + err.toString() + " - " +funcall.func);
                }
            }
        }
    }
}

// And we start it
var websocket = new MovimWebsocket;
websocket.init();
