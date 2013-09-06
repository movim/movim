
/**
 * These are the default callback functions that users may (or may not) use.
 *
 * Note that all of them take only one parameter. Don't be fooled by this, the
 * expected parameter is actually an array containing the real parameters. These
 * are checked before use.
 *
 * Look at the comments for help.
 */

// movim_append(div, text)
function movim_append(params)
{
    if(params.length < 2) {
        return;
    }
    
    var wrapper= document.createElement('div');
    wrapper.innerHTML = params[1];
    var nodes = wrapper.childNodes;

    target = document.getElementById(params[0]);
    if(target) {
        for(i = 0; i < nodes.length; i++) {
            target.appendChild(nodes[i]);
        }
    }
}
// movim_prepend(div, text)
function movim_prepend(params)
{
    if(params.length < 2) {
        return;
    }

    var wrapper= document.createElement('div');
    wrapper.innerHTML = params[1];
    var nodes = wrapper.childNodes;

    target = document.getElementById(params[0]);
    if(target) {
        for(i = 0; i < nodes.length; i++) {
            target.insertBefore(nodes[i],target.childNodes[0]);
        }
    }
}
// movim_fill(div, text)
function movim_fill(params)
{
    if(params.length < 2) {
        return;
    }

    target = document.getElementById(params[0]);
    if(target) {
        target.innerHTML = params[1];
    }
}
// movim_delete(div)
function movim_delete(params)
{
    target = document.getElementById(params[0]);
    target.parentNode.removeChild(target);
}
// movim_drop()
function movim_drop(params)
{
    // log('movim_drop called.');
}

function movim_disconnect(error)
{
    window.location.replace(ERROR_URI + error);
}

var movim_xmlhttp;

/***********************************************************************
 * MOVIM RPC class.
 *
 * Implements an abstraction to access MOVIM's RPC system. This includes
 * facilities to simply call functions.
 *
 * This also includes functions to make arrays etc.
 */
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

        var handler = this.handle_rpc_json;

	movim_xmlhttp.onreadystatechange = function()
        {
            if(movim_xmlhttp.readyState == 4 && movim_xmlhttp.status == 500)
                movim_disconnect('internal');
	    else if(movim_xmlhttp.readyState == 4)
		handler(movim_xmlhttp.response);
        };

	movim_xmlhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded; charset=utf-8");

        var json = this.generate_json();
	movim_xmlhttp.send(json);
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
                
                try {
                    window[funcall.func](funcall.params);
                }
                catch(err) {
                    log("Error caught: " + err.toString());
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
