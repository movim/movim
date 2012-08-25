
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

    target = document.getElementById(params[0]);
    if(target) {
        target.innerHTML += params[1];
    }
}
// movim_prepend(div, text)
function movim_prepend(params)
{
    if(params.length < 2) {
        return;
    }

    target = document.getElementById(params[0]);
    if(target) {
        target.innerHTML = params[1] + target.innerHTML;
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

	    movim_xmlhttp.open('POST', 'jajax.php', true);

        var handler = this.handle_rpc;

   	    movim_xmlhttp.onreadystatechange = function()
        {
            //if(movim_xmlhttp.readyState == 4 && movim_xmlhttp.status == 200) {
		        handler(movim_xmlhttp.responseXML);
            //}
            if(movim_xmlhttp.readyState == 4 && movim_xmlhttp.status == 500) {
                var url = window.location.href;
                var urlparts = url.split('/');
                var txt = urlparts[0]+'//';
                for(i = 2; i < urlparts.length-1; i++) {
                    txt = txt+urlparts[i]+'/'
                }
	            window.location.replace(txt+'index.php?q=disconnect&err=internal');
            }
        };

	    movim_xmlhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        var data = this.generate_xml();
	    movim_xmlhttp.send(data);
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
    this.handle_rpc = function(xml)
    {
        if(xml != null) {
            var funcalls = xml.getElementsByTagName("funcall");
            for(h = 0; h < funcalls.length; h++) {
                var func = funcalls[h];
                var funcname = func.attributes.getNamedItem("name").value;
                var f = window[funcname];
                var params = func.childNodes;

                var aparams = new Array();

                for(p = 0; p < params.length; p++) {
                    if(params[p].nodeName != "param")
                        continue;
                    aparams.push(params[p].textContent);
                }

                try {
                    f(aparams);
                }
                catch(err) {
                    log("Error caught: " + err.toString());
                }
            }
        }
    };

    /**
     * Generates the XML document corresponding to the provided parameters.
     */
    this.generate_xml = function()
    {
        var params = "";
        for(var i = 0; i < this.params.length; i++) {
            params += '<param>';

            // Argh! this is an array!
            if(this.params[i].constructor == Array) {
                var array = this.params[i]
                params += "<array>\n";
                for(var j = 0; j < array.length; j++) {
                    params += "<arrayelt>" + array[j] + "</arrayelt>\n";
                }
                params += "</array>\n";
            }
            else if(this.params[i].constructor == Hash) {
                var iter = this.params[i].iterate();
                iter.start();
                params += "<array>\n";
                while(iter.next()) {
                    params += '<arrayelt name="' + iter.key() + '">'
                        + iter.val()
                        + "</arrayelt>\n";
                }
                params += "</array>\n";
            }
            else {
                params += this.params[i];
            }

            params +="</param>\n";
        }

        var request =
            '<?xml version="1.0" encoding="UTF-8" ?>'
            + '<funcall widget="'+ this.widget + '" name="' + this.func + '">' + "\n"
            + params + "\n"
            + '</funcall>' + "\n";

        return request;
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
