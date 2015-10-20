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
 */
function movim_disconnect()
{
    window.location.replace(ERROR_URI);
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
    if(textbox != null) {
        var val = textbox.value;
        val = val.replace(/\n/g, '<br>');
        var hidden = document.querySelector('#hiddendiv');
        hidden.innerHTML = val + '<br/>';

        textboxStyle = window.getComputedStyle(textbox);

        hidden.style.paddingTop = textboxStyle.paddingTop;
        hidden.style.paddingBottom = textboxStyle.paddingBottom;
        hidden.style.width = textboxStyle.width;
        hidden.style.fontSize = textboxStyle.fontSize;

        textbox.style.height = hidden.scrollHeight+"px";
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
    var node = movim_get_node(element);
    if(!node) console.log('Node ' + element + ' not found');
    return node.className.match(new RegExp('(\\s|^)'+classname+'(\\s|$)'));
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
    var node = movim_get_node(element);

    if(node != null) {
        if(node.style.display == 'block')
            node.style.display = 'none';
        else
            node.style.display = 'block';
    }

}

/**
 * @brief Set object in localStorage
 * @param key string
 * @param value the object
 */
Storage.prototype.setObject = function(key, value) {
    this.setItem(key, JSON.stringify(value));
}

/**
 * @brief Get object in localStorage
 * @param key
 */
Storage.prototype.getObject = function(key) {
    return JSON.parse(this.getItem(key));
}

function base64_decode(data) {
    //  discuss at: http://phpjs.org/functions/base64_decode/
    // original by: Tyler Akins (http://rumkin.com)
    // improved by: Thunder.m
    // improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    //    input by: Aman Gupta
    //    input by: Brett Zamir (http://brett-zamir.me)
    // bugfixed by: Onno Marsman
    // bugfixed by: Pellentesque Malesuada
    // bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    //   example 1: base64_decode('S2V2aW4gdmFuIFpvbm5ldmVsZA==');
    //   returns 1: 'Kevin van Zonneveld'
    //   example 2: base64_decode('YQ===');
    //   returns 2: 'a'

    var b64 = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=';
    var o1, o2, o3, h1, h2, h3, h4, bits, i = 0,
    ac = 0,
    dec = '',
    tmp_arr = [];

    if (!data) {
        return data;
    }

    data += '';

    do { // unpack four hexets into three octets using index points in b64
        h1 = b64.indexOf(data.charAt(i++));
        h2 = b64.indexOf(data.charAt(i++));
        h3 = b64.indexOf(data.charAt(i++));
        h4 = b64.indexOf(data.charAt(i++));

        bits = h1 << 18 | h2 << 12 | h3 << 6 | h4;

        o1 = bits >> 16 & 0xff;
        o2 = bits >> 8 & 0xff;
        o3 = bits & 0xff;

        if (h3 == 64) {
            tmp_arr[ac++] = String.fromCharCode(o1);
        } else if (h4 == 64) {
            tmp_arr[ac++] = String.fromCharCode(o1, o2);
        } else {
            tmp_arr[ac++] = String.fromCharCode(o1, o2, o3);
        }
    } while (i < data.length);

    dec = tmp_arr.join('');

    return dec.replace(/\0+$/, '');
}

/**
 * @brief Sanitize string for easy search
 * @param string
 */
function accentsTidy(s){
    //Ian Elliott in http://stackoverflow.com/questions/990904/javascript-remove-accents-diacritics-in-strings
    var r = s.toLowerCase();
    r = r.replace(new RegExp("\\s", 'g'),"");
    r = r.replace(new RegExp("[àáâãäå]", 'g'),"a");
    r = r.replace(new RegExp("æ", 'g'),"ae");
    r = r.replace(new RegExp("ç", 'g'),"c");
    r = r.replace(new RegExp("[èéêë]", 'g'),"e");
    r = r.replace(new RegExp("[ìíîï]", 'g'),"i");
    r = r.replace(new RegExp("ñ", 'g'),"n");
    r = r.replace(new RegExp("[òóôõö]", 'g'),"o");
    r = r.replace(new RegExp("œ", 'g'),"oe");
    r = r.replace(new RegExp("[ùúûü]", 'g'),"u");
    r = r.replace(new RegExp("[ýÿ]", 'g'),"y");
    r = r.replace(new RegExp("\\W", 'g'),"");
    return r;
};
