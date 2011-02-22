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
        if(typeof(onloaders[i]) === "function")
    	    onloaders[i]();
    }
}

function log(text)
{
    if(typeof text !== 'undefined') {
        text = text.toString();
        text = text.replace(/</g, '&lt;').replace(/>/g, '&gt;');
	    document.getElementById('log_content').innerHTML
		    = "$ " + text + "<br /> "
		    + document.getElementById('log_content').innerHTML;
    }
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

function movim_parse_form(form) {
	var data = new Array();
	//data.push(form.elements[0].name);
	for (var i=0; i<form.elements.length;i++) {
		data.push(form.elements[i].name +":"+ form.elements[i].value);
	}
	return data;
}
