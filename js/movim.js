var movimAjax;

// Ajax modes.
var DROP = 0;
var CALLBACK = 1;
var APPEND = 2;
var FILL = 3;
var PREPEND = 4;

var movimPollHandlers = new Array();
var onloaders = new Array();

/**
 * Adds a function to the onload event.
 */
function movim_add_onload(func)
{
    onloaders.push(func);
}

/**
 * Function that is run once the page is loaded.
 */
function movim_onload()
{
    for(var i = 0; i < onloaders.length; i++) {
        onloaders[i]();
    }
}

function makeXMLHttpRequest()
{
	if (window.XMLHttpRequest) {// code for real browsers
		return new XMLHttpRequest();
	} else {// code for IE6, IE5
		return new ActiveXObject("Microsoft.XMLHTTP");
	}
}

function xmlToString(xml){
	var xmlString;

	if(xml.xml) { // IE
		xmlString = xml.xml;
	} else { // Real browsers
		xmlString = (new XMLSerializer).serializeToString(xml);
	}
	
	return xmlString;
}

movimAjax = makeXMLHttpRequest();

function movimPack(data)
{
	var outBuffer = "";
	
	// The given array must be "associative".
	if(data.length % 2 != 0) {
		return "";
	}
	
	for(var i = 0; i < data.length; i += 2) {
		outBuffer += '<param name="' + data[i]
			+ '" value="' + data[i + 1] + '" />' + "\n";
	}
	
	return outBuffer;
}

function log(text)
{
    if(typeof text !== 'undefined') {
        text = text.toString();
        text = text.replace(/</g, '&lt;').replace(/>/g, '&gt;');
	    document.getElementById('log').innerHTML
		    = "$ " + text + "<br /> "
		    + document.getElementById('log').innerHTML;
    }
}

/**
 * Sends data to the movim server through ajax.
 *
 * The provided mode determines what will become of the returned data. It
 * can either be processed by a callback function provided as modeopt or
 * it can append, prepend or fill the contents of the element which ID is
 * modeopt.
 */
function movim_ajaxSend(widget, func, mode, modeopt, parameters)
{
	// Regenerating the client everytime (necessary for IE)
	movimAjax = makeXMLHttpRequest();
	
	var request = '<funcall widget="'+ widget
		+ '" name="' + func + '">' + "\n"
		+ parameters + '</funcall>' + "\n";

	movimAjax.open('POST', 'jajax.php', true);

	if(mode == CALLBACK) { // considers the given modeopt as a callback function.
		movimAjax.onreadystatechange = modeopt;
	}
	else if(mode == APPEND) {
		movimAjax.onreadystatechange = function()
		{
			if(movimAjax.readyState == 4 && movimAjax.status == 200) {
				document.getElementById(modeopt).innerHTML += movimAjax.responseText;
			}
		};
	}
	else if(mode == FILL) {
		movimAjax.onreadystatechange = function()
		{
			if(movimAjax.readyState == 4 && movimAjax.status == 200) {
				document.getElementById(modeopt).innerHTML = movimAjax.responseText;
			}
		};
	}
	else if(mode == PREPEND) {
		movimAjax.onreadystatechange = function()
		{
			if(movimAjax.readyState == 4 && movimAjax.status == 200) {
				var elt = document.getElementById(modeopt);
				elt.innerHTML = movimAjax.responseText + elt.innerHTML;
			}
		};
	}
    else { // DROP and unknown
        movimAjax.onreadystatechange = function()
		{
			if(movimAjax.readyState == 4 && movimAjax.status == 200) {
		        // Do nothing.
			}
		};
    }

	movimAjax.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	movimAjax.send("<?xml version='1.0' encoding='UTF-8'?>" + request);
}

function myFocus(element) {
 if (element.value == element.defaultValue) {
   element.value = '';
 }
}
function myBlur(element) {
 if (element.value == '') {
   element.value = element.defaultValue;
 }
}
