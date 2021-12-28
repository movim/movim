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
Storage.prototype.setObject = function (key, value) {
    this.setItem(key, JSON.stringify(value));
};

/**
 * @brief Get object in localStorage
 * @param key
 */
Storage.prototype.getObject = function (key) {
    return JSON.parse(this.getItem(key));
};

var MovimUtils = {
    cleanupId: function (string) {
        return 'id-' + string.replace(/([^a-z0-9]+)/gi, '-').toLowerCase();
    },
    hash (string) {
        var hash = 0;

        if (string.length == 0) return hash;

        for (i = 0; i < string.length; i++) {
            char = string.charCodeAt(i);
            hash = ((hash << 5) - hash) + char;
            hash = hash & hash;
        }

        return hash;
    },
    isMobile: function () {
        return window.matchMedia('(max-width: 1024px)').matches;
    },
    disconnect: function () {
        window.location.replace(ERROR_URI);
    },
    formToJson: function (formname) {
        var form = document.forms[formname];
        if (!form)
            return false;

        var json = {};

        for (var i = 0; i < form.elements.length; i++) {
            json_att = {};

            for (var j = 0; j < form.elements[i].attributes.length; j++) {
                json_att[form.elements[i].attributes[j].name] = form.elements[i].attributes[j].value;
            }

            if (form.elements[i].name.length != 0) {
                if (form.elements[i].type == 'checkbox')
                    json[form.elements[i].name] = {
                        'value': form.elements[i].checked,
                        'attributes': json_att
                    };
                else if (form.elements[i].type == 'radio' && form.elements[i].checked)
                    json[form.elements[i].name] = {
                        'value': form.elements[i].value,
                        'attributes': json_att
                    };
                else if (form.elements[i].type != 'radio')
                    json[form.elements[i].name] = {
                        'value': form.elements[i].value,
                        'attributes': json_att
                    };
            }
        }

        return json;
    },
    setTitle: function (title) {
        document.title = title;
    },
    pushState: function (url) {
        window.history.pushState(null, '', url);
    },
    redirect: function (url) {
        window.location.href = url;
    },
    softRedirect: function (url) {
        var location = window.location.href;

        if (location.substring(0, location.indexOf('#')) !== url) {
            window.location.href = url;
        }
    },
    openInNew: function (url) {
        window.open(url, '_blank');
    },
    reload: function (uri) {
        window.location.replace(uri);
    },
    reloadThis: function () {
        window.location.reload();
    },
    addClass: function (element, classname) {
        let el = document.querySelector(element);
        if (el) el.classList.add(classname);
    },
    removeClass: function (element, classname) {
        let el = document.querySelector(element);
        if (el) el.classList.remove(classname);
    },
    textareaAutoheight: function (textbox) {
        if (textbox != null) {
            var val = MovimUtils.htmlEscape(textbox.value).replace(/\n/g, '<br>');
            var hidden = document.querySelector('#hiddendiv');
            hidden.innerHTML = val + '<br/>';

            textboxStyle = window.getComputedStyle(textbox);

            hidden.style.paddingTop = textboxStyle.paddingTop;
            hidden.style.paddingBottom = textboxStyle.paddingBottom;
            hidden.style.width = textboxStyle.width;
            hidden.style.fontSize = textboxStyle.fontSize;

            textbox.style.height = hidden.scrollHeight + 'px';
        }
    },
    copyToClipboard: function (text) {
        var input = document.body.appendChild(document.createElement('input'));
        input.value = text;
        input.focus();
        input.select();
        document.execCommand('copy');
        input.parentNode.removeChild(input);
    },
    applyAutoheight: function () {
        var textareas = document.querySelectorAll('textarea[data-autoheight=true]')

        for (var i = 0; i < textareas.length; i++) {
            MovimUtils.textareaAutoheight(textareas[i]);
            textareas[i].addEventListener('keyup', e => MovimUtils.textareaAutoheight(e.target));
        };
    },
    htmlEscape: function (string) {
        return String(string)
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    },
    enhanceArticlesContent: function () {
        document.querySelectorAll('article section > div video')
            .forEach(item => item.setAttribute('controls', 'controls'));

        document.querySelectorAll('article section > div a:not(.innertag)')
            .forEach(link => link.setAttribute('target', '_blank'));

        document.querySelectorAll('article section > div img')
            .forEach(img => {
                if (img.parentNode.localName != 'a') {
                    var div = document.createElement('div');
                    if (!img.parentNode.classList.contains('previewable')) {
                        img.parentNode.insertBefore(div, img);
                        div.classList.add('previewable');
                        img.classList.add('active');
                        img.addEventListener('click', () => Preview_ajaxHttpShow(img.src))

                        div.appendChild(img);
                    }
                }
            });
    },
    urlParts: function () {
        var str = window.location.search.split('/');
        var page = str[0].substr(1);
        str.shift();

        var str = str.map(param => decodeURIComponent(param));

        return { 'page': page, 'params': str, 'hash': window.location.hash.substr(1) };
    },
    humanFileSize: function (bytes, si) {
        var thresh = si ? 1000 : 1024;
        if (Math.abs(bytes) < thresh) {
            return bytes + ' B';
        }
        var units = si
            ? ['kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB']
            : ['KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB'];
        var u = -1;
        do {
            bytes /= thresh;
            ++u;
        } while (Math.abs(bytes) >= thresh && u < units.length - 1);
        return bytes.toFixed(1) + ' ' + units[u];
    },
    getOrientation: function (file, callback) {
        var testImageURL =
            'data:image/jpeg;base64,/9j/4QAiRXhpZgAATU0AKgAAAAgAAQESAAMAAAABAAYAAAA' +
            'AAAD/2wCEAAEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBA' +
            'QEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQE' +
            'BAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAf/AABEIAAIAAwMBEQACEQEDEQH/x' +
            'ABRAAEAAAAAAAAAAAAAAAAAAAAKEAEBAQADAQEAAAAAAAAAAAAGBQQDCAkCBwEBAAAAAAA' +
            'AAAAAAAAAAAAAABEBAAAAAAAAAAAAAAAAAAAAAP/aAAwDAQACEQMRAD8AG8T9NfSMEVMhQ' +
            'voP3fFiRZ+MTHDifa/95OFSZU5OzRzxkyejv8ciEfhSceSXGjS8eSdLnZc2HDm4M3BxcXw' +
            'H/9k='

        var img = document.createElement('img');
        img.onload = function () {
            // Check if the browser supports automatic image orientation:
            let orientation = img.width === 2 && img.height === 3;

            if (orientation) {
                return callback(-1);
            } else {
                var reader = new FileReader();

                reader.onload = function (e) {
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
        }
        img.src = testImageURL
    },
    arrayBufferToBase64: function (ab) {
        return btoa((new Uint8Array(ab)).reduce((data, byte) => data + String.fromCharCode(byte), ''));
    },
    base64ToArrayBuffer: function (base64) {
        var binary_string = window.atob(base64);
        var len = binary_string.length;
        var bytes = new Uint8Array(len);
        for (var i = 0; i < len; i++) {
            bytes[i] = binary_string.charCodeAt(i);
        }
        return bytes.buffer;
    },
    stringToArrayBuffer: function (string) {
        const bytes = new TextEncoder("utf-8").encode(string);
        return bytes.buffer;
    },
    arrayBufferToString: function (ab) {
        return new TextDecoder().decode(ab);
    },
    hexToArrayBuffer: function (hex) {
        const typedArray = new Uint8Array(hex.match(/[\da-f]{2}/gi).map(h => parseInt(h, 16)));
        return typedArray.buffer;
    },
    appendArrayBuffer: function (buffer1, buffer2) {
        const tmp = new Uint8Array(buffer1.byteLength + buffer2.byteLength);
        tmp.set(new Uint8Array(buffer1), 0);
        tmp.set(new Uint8Array(buffer2), buffer1.byteLength);
        return tmp.buffer;
    },
    range: function (start, end) {
        return Array.from({ length: end - start + 1 }, (_, i) => i)
    },
    linkify: function(inputText) {
        var replacedText, replacePattern1, replacePattern2, replacePattern3;

        //URLs starting with http://, https://, or ftp://
        replacePattern1 = /(\b(https?|ftp):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/gim;
        replacedText = inputText.replace(replacePattern1, '<a href="$1" target="_blank" rel="noopener">$1</a>');

        //URLs starting with "www." (without // before it, or it'd re-link the ones done above).
        replacePattern2 = /(^|[^\/])(www\.[\S]+(\b|$))/gim;
        replacedText = replacedText.replace(replacePattern2, '$1<a href="http://$2" target="_blank" rel="noopener">$2</a>');

        //Change email addresses to mailto:: links.
        replacePattern3 = /(([a-zA-Z0-9\-\_\.])+@[a-zA-Z\_]+?(\.[a-zA-Z]{2,6})+)/gim;
        replacedText = replacedText.replace(replacePattern3, '<a href="mailto:$1">$1</a>');

        return replacedText;
    }
};
