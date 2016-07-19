/**
 * Movim Utils
 *
 * This file include some useful functions used quite everywhere in Movim
 */

/**
 * @brief Set object in localStorage
 * @param key string
 * @param value the object
 */
Storage.prototype.setObject = function(key, value) {
    this.setItem(key, JSON.stringify(value));
};

/**
 * @brief Get object in localStorage
 * @param key
 */
Storage.prototype.getObject = function(key) {
    return JSON.parse(this.getItem(key));
};



var MovimUtils = {
    accentsTidy: function(s){
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
    },
    addClass: function(element, classname) {
        if(!MovimUtils.hasClass(element, classname)) {
            element = MovimUtils.getNode(element);
            element.className += " " + classname;
        }
    },
    base64Decode: function(data) {
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
    },
    buttonReset: function(element) {
        var elt = document.querySelector(element);
        elt.className = elt.dataset.oldclassname;
    },
    buttonSave: function(element) {
        var elt = document.querySelector(element);
        elt.dataset.oldclassname = elt.className;
    },
    checkString: function(str) {
        if (typeof str == 'object') {
            return str instanceof String;
        } else {
            return typeof str == 'string';
        }
    },
    disconnect: function() {
        window.location.replace(ERROR_URI);
    },
    formToJson: function(formname) {
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
                    json[form.elements[i].name] = {
                        'value' : form.elements[i].checked,
                        'attributes' : json_att
                    };
                else if(form.elements[i].type == 'radio' && form.elements[i].checked )
                    json[form.elements[i].name] = {
                        'value' : form.elements[i].value,
                        'attributes' : json_att
                    };
                else if(form.elements[i].type != 'radio')
                    json[form.elements[i].name] = {
                        'value' : form.elements[i].value,
                        'attributes' : json_att
                    };
            }
        }

        return json;
    },
    getNode: function(str) {
        if(MovimUtils.checkString(str))
            return document.querySelector(str);
        else
            return str;
    },
    hasClass: function(element, classname) {
        var node = element;
        if(typeof node == "string")
            node = MovimUtils.getNode(node);
        if(!node) return false;
        return node.className.split(" ").indexOf(classname) == -1? false : true;
    },
    showElement: function(element) {
        if(!MovimUtils.hasClass(element, "show"))
            MovimUtils.addClass(element, "show");
        if(MovimUtils.hasClass(element, "hide"))
            MovimUtils.removeClass(element, "hide");
    },
    hideElement: function(element) {
        if(!MovimUtils.hasClass(element, "hide"))
            MovimUtils.addClass(element, "hide");
        if(MovimUtils.hasClass(element, "show"))
            MovimUtils.removeClass(element, "show");
    },
    parseForm: function(formname) {
        var form = document.forms[formname];
        if(!form)
            return false;

        var data = H();
        for(var i = 0; i < form.elements.length; i++) {
            if(form.elements[i].type == 'checkbox') {
                data.set(
                    form.elements[i].name,
                    form.elements[i].checked
                );
            } else if(form.elements[i].type == 'radio' && form.elements[i].checked ) {
                data.set(
                    form.elements[i].name,
                    form.elements[i].value
                );
            } else if(form.elements[i].type != 'radio'){
                data.set(
                    form.elements[i].name,
                    form.elements[i].value
                );
            }
        }
        return data;
    },
    pushState: function(url) {
        window.history.pushState(null, "", url);
    },
    redirect: function(url) {
        window.location.href = url;
    },
    reload: function(uri) {
        window.location.replace(uri);
    },
    reloadThis: function() {
        window.location.reload();
    },
    removeClass: function(element,classname) {
        if (MovimUtils.hasClass(element, classname)) {
            var reg = new RegExp('(\\s|^)' + classname + '(\\s|$)');
            element = MovimUtils.getNode(element);
            element.className = element.className.replace(reg,' ');
        }
    },
    removeClassInList: function(myclass, list) {
        for(i = 0; i < list.length; i++) {
            MovimUtils.removeClass(list[i], myclass);
        }
    },
    textareaAutoheight: function(textbox) {
        if(textbox != null) {
            var val = textbox.value.replace(/\n/g, '<br>');
            var hidden = document.querySelector('#hiddendiv');
            hidden.innerHTML = val + '<br/>';

            textboxStyle = window.getComputedStyle(textbox);

            hidden.style.paddingTop = textboxStyle.paddingTop;
            hidden.style.paddingBottom = textboxStyle.paddingBottom;
            hidden.style.width = textboxStyle.width;
            hidden.style.fontSize = textboxStyle.fontSize;

            textbox.style.height = hidden.scrollHeight + "px";
        }
    },
    toggleClass: function(element, classname) {
        if(MovimUtils.hasClass(element, classname))
            MovimUtils.removeClass(element,classname);
        else
            MovimUtils.addClass(element, classname);
    },
    toggleDisplay: function(element) {
        var node = MovimUtils.getNode(element);

        if(node != null) {
            if(node.style.display == 'block')
                MovimUtils.hideElement(node);
            else
                MovimUtils.showElement(node);
        }
    }
};
