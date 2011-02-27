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
    this.make_xmlhttp = MovimRPC_make_xmlhttp;
    this.commit = MovimRPC_commit;
    
    this.set_widget = MovimRPC_set_widget;
    this.set_func = MovimRPC_set_func;
    this.add_param = MovimRPC_add_param;

    this.set_call = MovimRPC_set_call;

    this.handle_rpc = MovimRPC_handle_rpc;

    this.generate_xml = MovimRPC_generate_xml;

    /* Properties */
    this.widget = '';
    this.func = '';
    this.params = [];
}

/**
 * Sets all movim call parameters at once.
 */
function MovimRPC_set_call(widget, func, params)
{
    this.set_widget(widget);
    this.set_func(func);
    this.params = params;
}

/**
 * What widget do we call?
 */
function MovimRPC_set_widget(widgetname)
{
    this.widget = widgetname;
}

/**
 * What function to call in Movim?
 */
function MovimRPC_set_func(funcname)
{
    this.func = funcname;
}

/**
 * Adds a parameter to the called function.
 */
function MovimRPC_add_param(param)
{
    this.params.push(param);
}

/**
 * Generates a new XMLHttpRequest object in a portable fashion.
 */
function MovimRPC_make_xmlhttp()
{
	if (window.XMLHttpRequest) {// code for real browsers
		return new XMLHttpRequest();
	} else {// code for IE6, IE5
		return new ActiveXObject("Microsoft.XMLHTTP");
	}
}

/**
 * Handles returns (xmlrpc)
 */
function MovimRPC_handle_rpc(xml)
{
    if(xml != null) {
        var funcalls = xml.getElementsByTagName("funcall");
        for(h = 0; h < funcalls.length; h++) {
            var func = funcalls[h];
            var funcname = func.attributes.getNamedItem("name").nodeValue;
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
}

/**
 * Generates the XML document corresponding to the provided parameters.
 */
function MovimRPC_generate_xml()
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
            var hash = this.params[i];
            hash.reset();
            params += "<array>\n";
            while(hash.iterate()) {
                params += '<arrayelt name="' + hash.key() + '">'
                    + hash.val()
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
}

/**
 * Sends data to the movim server through ajax.
 *
 * The provided mode determines what will become of the returned data. It
 * can either be processed by a callback function provided as modeopt or
 * it can append, prepend or fill the contents of the element which ID is
 * modeopt.
 */
function MovimRPC_commit()
{
    movim_xmlhttp = this.make_xmlhttp();
    
	movim_xmlhttp.open('POST', 'jajax.php', true);
    
   	movim_xmlhttp.onreadystatechange = function()
    {
        if(movim_xmlhttp.readyState == 4 && movim_xmlhttp.status == 200) {
//            log("Received data " + movim_xmlhttp.responseText);            
		    MovimRPC_handle_rpc(movim_xmlhttp.responseXML);
        }
    };

	movim_xmlhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    var data = this.generate_xml();
	movim_xmlhttp.send(data);
}

/**
 * Putting it all together.
 */
function movim_ajaxSend(widget, func, parameters)
{
    var rpc = new MovimRPC();
    rpc.set_call(widget, func, parameters);
    rpc.commit();
}
