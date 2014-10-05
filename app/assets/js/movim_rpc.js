/**
 * Movim RPC class.
 *
 * Implements an abstraction to access MOVIM's RPC system. This includes
 * facilities to simply call functions.
 *
 * This also includes functions to make arrays etc.
 */
var movim_xmlhttp;
var movim_xmlhttp_hash = {};
 
function MovimRPC()
{
    /* Methods */
    /**
     * Generates a new XMLHttpRequest object in a portable fashion.
     */
    this.make_xmlhttp = function()
    {
        if (window.XMLHttpRequest) {// code for real browsers
            return new XMLHttpRequest();
        } else {// code for IE6, IE5
            return new ActiveXObject("Microsoft.XMLHTTP");
        }
    };

    this.startRequest = function(key)
    {
        movim_xmlhttp_hash[this.key] = 1;
    }

    this.endRequest = function(key)
    {
        delete movim_xmlhttp_hash[this.key];
    }

    /**
     * Sends data to the movim server through ajax.
     *
     * The provided mode determines what will become of the returned data. It
     * can either be processed by a callback function provided as modeopt or
     * it can append, prepend or fill the contents of the element which ID is
     * modeopt.
     */
    this.commit = function()
    {
        movim_xmlhttp = this.make_xmlhttp();

        if(FAIL_SAFE)
            var fail_safe = '?fail_safe=1';
        else
            var fail_safe = '';

        movim_xmlhttp.open('POST', BASE_URI+'jajax.php'+fail_safe, true);

        movim_xmlhttp = this.set_key(movim_xmlhttp);

        movim_xmlhttp.addEventListener('loadstart', this.startRequest, false);
        movim_xmlhttp.addEventListener('loadend', this.endRequest, false);

        var handler = this.handle_rpc_json;

        movim_xmlhttp.onreadystatechange = function() {
            if(movim_xmlhttp.readyState == 4 && movim_xmlhttp.status == 500)
                movim_disconnect('internal');
            else if(movim_xmlhttp.readyState == 4) {
                handler(movim_xmlhttp.response);
            }
        };

        movim_xmlhttp.setRequestHeader("Content-Type", "application/json; charset=utf-8");

        var json = this.generate_json();
        movim_xmlhttp.send(json);
    };

    /**
     * Attach a key to the object
     */
    this.set_key = function(movim_xmlhttp)
    {
        movim_xmlhttp.key = Math.random().toString(36).replace(/[^a-z]+/g, '').substr(0, 5);
        return movim_xmlhttp;
    };

    /**
     * What widget do we call?
     */
    this.set_widget = function(widgetname)
    {
        this.widget = widgetname;
    };

    /**
     * What function to call in Movim?
     */
    this.set_func = function(funcname)
    {
        this.func = funcname;
    };

    /**
     * Adds a parameter to the called function.
     */
    this.add_param = function(param)
    {
        this.params.push(param);
    };
    
    /**
     * Sets all movim call parameters at once.
     */
    this.set_call = function(widget, func, params)
    {
        this.set_widget(widget);
        this.set_func(func);
        this.params = params;
    };

    /**
     * Handles returns (xmlrpc)
     */
    this.handle_rpc_json = function(json)
    {
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

    /**
     * Generates the JSON document corresponding to the provided parameters.
     */
    this.generate_json = function()
    {
        return JSON.stringify(this);
    };

    /* Properties */
    this.widget = '';
    this.func = '';
    this.params = [];
}

/**
 * Putting it all together.
 */
function movim_ajaxSend(widget, func, parameters)
{
    rpc.set_call(widget, func, parameters);
    rpc.commit();
}

var rpc = new MovimRPC(); // Initialising global rpc handler.
