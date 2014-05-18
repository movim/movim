/**
 * Movim Utils
 * 
 * This file include some useful functions used quite everywhere in Movim
 */ 
 
function movim_check_string(str) {
    if (typeof str == 'object') {
        return str instanceof String;
    } else {
        return typeof str == 'string';
    }
}

function movim_get_node(str) {
    if(movim_check_string(str))
        return document.querySelector(str);
    else
        return str;
}

/**
 * @brief Force Movim to go back to the login page
 * @param string error 
 */
function movim_disconnect(error)
{
    window.location.replace(ERROR_URI + error);
}

/**
 * @brief Force Movim to reload the page
 * @param string uri  
 */
function movim_reload(uri) {
    window.location.replace(uri);
}

/**
 * @brief Force Movim to reload the current page
 * @param string error 
 */
function movim_reload_this() {
    window.location.reload();
}

/**
 * @brief Force Movim to go to an url
 * @param string url
 */
function movim_redirect(url) {
    window.location.href = url;
}

/**
 * @brief Return a hash (key->value) version of a form 
 * @param string the name of the form
 * @return hash
 */
function movim_parse_form(formname) {
    var form = document.forms[formname];
    if(!form)
        return false;

	var data = H();
	for(var i = 0; i < form.elements.length; i++) {
        if(form.elements[i].type == 'checkbox') {
            data.set(form.elements[i].name,
                     form.elements[i].checked);
        } else if(form.elements[i].type == 'radio'
               && form.elements[i].checked ) {
            data.set(form.elements[i].name,
                     form.elements[i].value);
        } else if(form.elements[i].type != 'radio'){
            data.set(form.elements[i].name,
                     form.elements[i].value);
        }
	}
	return data;
}

/**
 * @brief Return a JSON version of a form 
 * @param string the name of the form
 * @return JSON
 */
function movim_form_to_json(formname) {
    var form = document.forms[formname];
    if(!form)
        return false;  
        
    var json = {};
        
    for(var i = 0; i < form.elements.length; i++) {
        json_att = {};
        
        for(var j = 0; j < form.elements[i].attributes.length; j++) {
            json_att[form.elements[i].attributes[j].name] = form.elements[i].attributes[j].value;
        }
         
        if(form.elements[i].name.length != 0) {
            if(form.elements[i].type == 'checkbox')
                json[form.elements[i].name] = {'value' : form.elements[i].checked, 'attributes' : json_att};
            else if(form.elements[i].type == 'radio'
                   && form.elements[i].checked ) 
                json[form.elements[i].name] = {'value' : form.elements[i].value, 'attributes' : json_att};
            else if(form.elements[i].type != 'radio')
                json[form.elements[i].name] = {'value' : form.elements[i].value, 'attributes' : json_att};
        }
	}
    
    return json;
}

/**
 * @brief A magical function to autoresize textarea when typing
 * @param DOMElement textbox 
 */
function movim_textarea_autoheight(textbox) {
    if(textbox != null ) {
        textbox.style.height = 0;
        textbox.style.height = textbox.scrollHeight +"px";
    }
}

/**
 * Class manipulation
 */

/**
 * @brief Check if the element own the class
 * @param string the selector of the element (e.g '#myid', '.theclass')
 * @param string the class to check 
 */
function movim_has_class(element,classname) {
    var element = movim_get_node(element);
    return element.className.match(new RegExp('(\\s|^)'+classname+'(\\s|$)'));
}

/**
 * @brief Add a class of an element
 * @param string the selector of the element
 * @param string the class to add
 */
function movim_add_class(element,classname) {
    if(!movim_has_class(element,classname)) {
        var element = movim_get_node(element);
        element.className += " "+classname;
    }
}

/**
 * @brief Remove a class of an element
 * @param string the selector of the element
 * @param string the class to remove
 */
function movim_remove_class(element,classname) {
  if (movim_has_class(element,classname)) {
      var reg = new RegExp('(\\s|^)'+classname+'(\\s|$)');
      var element = movim_get_node(element);
      element.className=element.className.replace(reg,' ');
  }
}

/**
 * @brief Toggle the class of an element
 * @param string the selector of the element
 * @param string the class to toggle
 */
function movim_toggle_class(element, classname) {
    if(movim_has_class(element, classname))
        movim_remove_class(element,classname);
    else
        movim_add_class(element, classname);
}

/**
 * @brief Save the current button class
 * @param string the selector of the element
 */
function movim_button_save(element) {
    var elt = document.querySelector(element);
    elt.dataset.oldclassname = elt.className;
}

/**
 * @brief Reset the button
 * @param string the selector of the element
 */
function movim_button_reset(element) {
    var elt = document.querySelector(element);
    elt.className = elt.dataset.oldclassname;
}

/**
 * @brief Toggle the visibility of an element
 * @param string the selector of the element
 */
function movim_toggle_display(element) {
    if (element.constructor === Array)
        var node = movim_get_node(element[0]);
    else
        var node = movim_get_node(element);

    if(node != null) {
        if(node.style.display == 'block')
            node.style.display = 'none';
        else
            node.style.display = 'block';
    }

}

window.addEventListener('load', function () {
  Notification.requestPermission(function (status) {
    // This allows to use Notification.permission with Chrome/Safari
    if (Notification.permission !== status) {
      Notification.permission = status;
    }
  });
});
