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
    cleanupId: function(string) {
        return "id-" + string.replace(/([^a-z0-9]+)/gi, '-').toLowerCase();
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
        return node.classList.contains(classname);
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
    addClass: function(element, classname) {
        if(!MovimUtils.hasClass(element, classname)) {
            element = MovimUtils.getNode(element);
            element.classList.add(classname);
        }
    },
    removeClass: function(element, classname) {
        if (MovimUtils.hasClass(element, classname)) {
            element = MovimUtils.getNode(element);
            element.classList.remove(classname);
        }
    },
    textareaAutoheight: function(textbox) {
        if(textbox != null) {
            var val = MovimUtils.htmlEscape(textbox.value).replace(/\n/g, '<br>');
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
    htmlEscape: function(string) {
        return String(string)
                .replace(/&/g, '&amp;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;');
    },
    enhanceArticlesContent: function() {
        document.querySelectorAll('article section content video')
            .forEach(item => item.setAttribute('controls', 'controls'));

        document.querySelectorAll('article section content a:not(.innertag)')
            .forEach(link => link.setAttribute('target', '_blank'));
    },
    urlParts : function() {
        var str = window.location.search.split('/');
        var page = str[0].substr(1);
        str.shift();

        var str = str.map(param => decodeURIComponent(param));

        return {'page': page, 'params': str, 'hash': window.location.hash.substr(1)};
    },
    getOrientation : function(file, callback) {
        var reader = new FileReader();

        reader.onload = function(e) {
            var view = new DataView(e.target.result);
            if (view.getUint16(0, false) != 0xFFD8) return callback(-2);
            var length = view.byteLength, offset = 2;

            while (offset < length) {
                var marker = view.getUint16(offset, false);
                offset += 2;
                if (marker == 0xFFE1) {
                    if (view.getUint32(offset += 2, false) != 0x45786966) return callback(-1);

                    var little = view.getUint16(offset += 6, false) == 0x4949;
                    offset += view.getUint32(offset + 4, little);

                    var tags = view.getUint16(offset, little);
                    offset += 2;

                    for (var i = 0; i < tags; i++)
                        if (view.getUint16(offset + (i * 12), little) == 0x0112)

                    return callback(view.getUint16(offset + (i * 12) + 8, little));
                }
                else if ((marker & 0xFF00) != 0xFF00) break;
                else offset += view.getUint16(offset, false);
            }

            return callback(-1);
        };

        reader.readAsArrayBuffer(file);
    }
};
