/**
 * Movim Websocket
 *
 * This file define the websocket behaviour and handle its connection
 */

WebSocket.prototype.unregister = function() {
    this.send(JSON.stringify({'func' : 'unregister'}));
};

WebSocket.prototype.register = function(host) {
    this.send(JSON.stringify({'func' : 'register', 'host' : host}));
};

/**
 * Short functions
 */
function MWSs(widget, func, params) {
    MovimWebsocket.send(widget, func, params);
}

/**
 * @brief Definition of the MovimWebsocket object
 * @param string error
 */

var MovimWebsocket = {
    connection: null,
    initiated: [], // Launched when the socket is connecting or reconnecting
    attached: [], // Launched when the socket is connected to the daemon
    started: [], // Launched when the linker is started
    registered: [], // Launched when the linker is connected to XMPP
    attempts: 1,
    pong: false,
    closed: false,
    statusBar: null,

    launchAttached : function() {
        // We hide the Websocket error
        MovimWebsocket.statusBar.classList.add('hide');

        for(var i = 0; i < MovimWebsocket.attached.length; i++) {
            MovimWebsocket.attached[i]();
        }
    },

    launchRegistered : function() {
        for(var i = 0; i < MovimWebsocket.registered.length; i++) {
            MovimWebsocket.registered[i]();
        }
    },

    launchStarted : function() {
        for(var i = 0; i < MovimWebsocket.started.length; i++) {
            MovimWebsocket.started[i]();
        }
    },

    launchInitiated : function() {
        for(var i = 0; i < MovimWebsocket.initiated.length; i++) {
            MovimWebsocket.initiated[i]();
        }
    },

    init : function() {
        if (window.location.protocol === "https:") {
            var uri = 'wss:' + BASE_URI + 'ws/';
        } else {
            var uri = 'ws:' + BASE_URI + 'ws/';
        }

        MovimWebsocket.launchInitiated();

        if (this.connection !== null) {
            this.connection.onclose = null;
            this.connection.close();
        }

        var date = new Date();

        this.connection = new WebSocket(uri +
            '?path=' + MovimUtils.urlParts().page +
            '&offset=' + date.getTimezoneOffset());

        this.connection.onopen = function(e) {
            console.log("Connection established!");
            MovimWebsocket.attempts = 1;
            MovimWebsocket.launchAttached();
            setTimeout(function() {
                MovimWebsocket.ping();
            }, 10000);
        };

        this.connection.onmessage = function(e) {
            var obj = JSON.parse(e.data);

            if (obj != null) {
                if (obj.func == 'registered') {
                    MovimWebsocket.launchRegistered();
                }

                if (obj.func == 'started') {
                    // If the linker was started but we're not on the login page
                    if (!['login', 'account', 'accountnext', 'tag', 'about'].includes(MovimUtils.urlParts().page)) {
                        MovimUtils.disconnect();
                    } else {
                        MovimWebsocket.launchStarted();
                    }
                }

                if (obj.func == 'disconnected') {
                    MovimUtils.disconnect();
                }

                if (obj.func == 'pong') {
                    MovimWebsocket.pong = true;
                }

                MovimRPC.handle(obj);
            }
        };

        this.connection.onclose = function(e) {
            console.log("Connection closed by the server or session closed");

            if (e.code == 1008) {
                // The server closed the connection and asked to keep it this way
                this.closed = true;
                MovimWebsocket.statusBar.classList.remove('hide', 'connect');
                MovimWebsocket.connection.close();
            } if (e.code == 1006) {
                MovimWebsocket.reconnect();
            } else if (e.code == 1000) {
                MovimUtils.disconnect();
            }
        };

        this.connection.onerror = function(e) {
            console.log("Connection error!");

            // We show the Websocket error
            MovimWebsocket.statusBar.classList.remove('hide', 'connect');
            MovimWebsocket.reconnect();

            // We prevent the onclose launch
            this.onclose = null;
        };
    },

    send : function(widget, func, params) {
        if (this.connection.readyState == 1) {
            var body = {
                'w' : widget,
                'f' : func
            };

            if (params) body.p = params;
            this.connection.send(JSON.stringify(
                {'func' : 'message', 'b' : body}
            ));
        }
    },

    // A ping/pong system to handle socket errors for buggy browser (Chrome on Linux…)
    ping : function() {
        if (this.connection.readyState == 1 && !this.closed) {
            this.connection.send(
                JSON.stringify(
                    {'func' : 'ping'}
                )
            );

            setTimeout(function() {
                if (MovimWebsocket.pong == false) {
                    MovimWebsocket.connection.onerror();
                } else {
                    MovimWebsocket.pong = false;
                    MovimWebsocket.ping();
                }
            }, 10000);
        }
    },

    attach : function(func) {
        if (typeof(func) === "function") {
            this.attached.push(func);
        }
    },

    register : function(func) {
        if (typeof(func) === "function") {
            this.registered.push(func);
        }
    },

    start : function(func) {
        if (typeof(func) === "function") {
            this.started.push(func);
        }
    },

    initiate : function(func) {
        if (typeof(func) === "function") {
            this.initiated.push(func);
        }
    },

    clearAttached : function() {
        this.attached = [];
    },

    unregister : function() {
        this.connection.unregister();
    },

    reconnect : function() {
        var interval = MovimWebsocket.generateInterval();
        console.log("Try to reconnect");

        setTimeout(function () {
            // We've tried to reconnect so increment the attempts by 1
            MovimWebsocket.attempts++;

            // Show the reconnect state
            MovimWebsocket.statusBar.classList.remove('hide');
            MovimWebsocket.statusBar.classList.add('connect');

            // Connection has closed so try to reconnect every 10 seconds.
            MovimWebsocket.init();
        }, interval);
    },

    generateInterval :function() {
        var maxInterval = (Math.pow(2, MovimWebsocket.attempts) - 1) * 1000;

        if (maxInterval > 30*1000) {
            maxInterval = 30*1000; // If the generated interval is more than 30 seconds, truncate it down to 30 seconds.
        }

        // generate the interval to a random number between 0 and the maxInterval determined from above
        return Math.random() * maxInterval;
    }
}

window.onbeforeunload = function() {
    if (MovimWebsocket.connection !== null) {
        MovimWebsocket.connection.onclose = function () {}; // disable onclose handler first
        MovimWebsocket.connection.close()
    }
};

// If the Websocket was closed after some innactivity, we try to reconnect
window.addEventListener('focus', function() {
    if (MovimWebsocket.connection !== null
     && MovimWebsocket.connection.readyState > 1) {
         // Show the reconnect state
        MovimWebsocket.statusBar.classList.remove('hide');
        MovimWebsocket.statusBar.classList.add('connect');

        MovimWebsocket.init();
    }
});

document.addEventListener("DOMContentLoaded", function(event) {
    MovimWebsocket.statusBar = document.getElementById('status_websocket');

    movimAddFocus(function() {
        if (!MovimWebsocket.statusBar.classList.contains('hide')) {
            MovimWebsocket.reconnect();
        }
    });

    // And we start it
    MovimWebsocket.init();
});