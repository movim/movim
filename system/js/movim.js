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

function movim_parse_form(formname) {
    var form = document.forms[formname];
    if(!form)
        return false;

	var data = H();
	for(var i = 0; i < form.elements.length; i++) {
        if(form.elements[i].type == 'checkbox') {
            data.set(form.elements[i].name,
                     form.elements[i].checked);
        } else {
            data.set(form.elements[i].name,
                     form.elements[i].value);
        }
	}
	return data;
}

function movim_reload(uri) {
    window.location.replace(uri);
}

/* A magical function to autoresize textarea when typing */
function movim_textarea_autoheight(textbox) {
    textbox.style.height = 0;
    textbox.style.height = textbox.scrollHeight+"px";
}
/**
 * Set a global var for widgets to see if document is focused
 */
var document_focus = true;
var document_title = document.title;
var messages_cpt = 1;
document.onblur = function() { document_focus = false; }
document.onfocus = function() { document_focus = true; document.title = document_title; messages_cpt = 1; }

function movim_title_inc() {
	document.title='[' + messages_cpt + '] ' + document_title ;
	messages_cpt++;
}

function movim_change_class(params) {
    var node = document.getElementById(params[0]);
    node.className = params[1];
}
