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
    hash(string) {
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

        if (!form) {
            return false;
        }

        var json = {};

        for (var i = 0; i < form.elements.length; i++) {
            json_att = {};

            for (var j = 0; j < form.elements[i].attributes.length; j++) {
                if (form.elements[i].attributes[j].name != 'value') {
                    json_att[form.elements[i].attributes[j].name] = form.elements[i].attributes[j].value;
                }
            }

            if (form.elements[i].name.length != 0) {
                if (form.elements[i].type == 'checkbox') {
                    json[form.elements[i].name] = {
                        'value': form.elements[i].checked,
                        'attributes': json_att
                    };
                } else if (form.elements[i].type == 'radio' && form.elements[i].checked) {
                    json[form.elements[i].name] = {
                        'value': form.elements[i].value,
                        'attributes': json_att
                    };
                } else if (form.elements[i].type != 'radio') {
                    json[form.elements[i].name] = {
                        'value': form.elements[i].value,
                        'attributes': json_att
                    };
                }
            }
        }

        return json;
    },
    cleanTime: function (seconds) {
        var currentMinute = parseInt(seconds / 60) % 60,
            currentSecondsLong = seconds % 60,
            currentSeconds = currentSecondsLong.toFixed(),
            currentTime = (currentMinute < 10 ? "0" + currentMinute : currentMinute)
                + ":" + (currentSeconds < 10 ? "0" + currentSeconds : currentSeconds);

        return currentTime;
    },
    setTitle: function (title) {
        document.title = title;
    },
    pushState: function (url) {
        window.history.pushState(null, '', url);
    },
    pushSoftState: function (url) {
        if (window.location != url) {
            window.history.pushState({ soft: true }, '', url);
            MovimTpl.currentPage = window.location.pathname;
        }
    },
    redirect: function (url) {
        window.location.href = url;
    },
    openInNew: function (url) {
        window.open(url, '_blank');
    },
    reload: function (uri, noHistory) {
        requestUri = new URL(uri.replace(/^\/\//, 'https://'));
        requestUri.searchParams.append('soft', 'true');

        MovimTpl.loadingPage();

        MovimRPC.fetchWithTimeout(requestUri.toString(), {
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        }).then(reponse => {
            onloaders = [];
            onfocused = [];

            reponse.text().then(value => {
                MovimTpl.finishedPage();

                let page = JSON.parse(value);

                if (noHistory != true) {
                    MovimUtils.pushSoftState(uri);
                }

                if (typeof MovimWebsocket != 'undefined') {
                    MovimWebsocket.clear();
                }

                document.head.querySelectorAll('link[rel=stylesheet].widget').forEach(e => e.remove());
                document.head.querySelectorAll('script[type=\'text/javascript\'].widget').forEach(e => e.remove());
                document.head.querySelectorAll('script[type=\'text/javascript\'].inline').forEach(e => e.remove());
                document.querySelectorAll('#endcommon ~ *').forEach(e => e.remove());

                document.body.insertAdjacentHTML('beforeend', page.content);
                document.title = page.title;

                // CSS

                page.widgetsCSS.forEach(url => {
                    var css = document.createElement("link");
                    css.setAttribute('rel', 'stylesheet');
                    css.href = url;
                    css.classList.add('widget');
                    document.head.appendChild(css);
                });

                // Javascript

                const promises = [];
                page.widgetsScripts.forEach(script => {
                    promises.push(new Promise(function (resolve, reject) {
                        var js = document.createElement("script");
                        js.src = script;
                        js.setAttribute('type', 'text/javascript');
                        js.onload = resolve;
                        js.onerror = resolve;
                        js.classList.add('widget');
                        document.head.appendChild(js);
                    }));
                });

                var js = document.createElement("script");
                js.classList.add('inline');
                js.innerHTML = page.inlineScripts;
                document.head.appendChild(js);

                // Events
                Promise.all(promises).then(() => {
                    MovimEvents.triggerWindow('loaded', null);
                });
            });
        }).catch(error => {
            document.body.classList.add('finished');
        });
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
    setDataItem: function (element, key, value) {
        let el = document.querySelector(element);
        if (el) {
            if (value) {
                el.dataset[key] = value;
            } else {
                delete el.dataset[key];
            }
        }
    },
    decodeHTMLEntities(text) {
        var textarea = document.createElement('textarea');
        textarea.innerHTML = text;
        return textarea.value;
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
        var str = window.location.pathname.split('/');
        var page = str[1];

        str.shift();
        str.shift();

        str = str.map(param => decodeURIComponent(param));

        return { 'page': page, 'params': str, 'hash': window.location.hash.substring(1) };
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
    applyOrientation: function (ctx, orientation, width, height) {
        switch (orientation) {
            case 2: ctx.transform(-1, 0, 0, 1, width, 0); break;
            case 3: ctx.transform(-1, 0, 0, -1, width, height); break;
            case 4: ctx.transform(1, 0, 0, -1, 0, height); break;
            case 5: ctx.transform(0, 1, 1, 0, 0, 0); break;
            case 6: ctx.transform(0, 1, -1, 0, height, 0); break;
            case 7: ctx.transform(0, -1, -1, 0, height, width); break;
            case 8: ctx.transform(0, -1, 1, 0, 0, width); break;
            default: ctx.transform(1, 0, 0, 1, 0, 0);
        }
    },
    drawImageProp: function (ctx, img, x, y, w, h, offsetX, offsetY) {
        if (arguments.length === 2) {
            x = y = 0;
            w = ctx.canvas.width;
            h = ctx.canvas.height;
        }

        // default offset is center
        offsetX = typeof offsetX === "number" ? offsetX : 0.5;
        offsetY = typeof offsetY === "number" ? offsetY : 0.5;

        // keep bounds [0.0, 1.0]
        if (offsetX < 0) offsetX = 0;
        if (offsetY < 0) offsetY = 0;
        if (offsetX > 1) offsetX = 1;
        if (offsetY > 1) offsetY = 1;

        var iw = img.width,
            ih = img.height,
            r = Math.min(w / iw, h / ih),
            nw = iw * r,   // new prop. width
            nh = ih * r,   // new prop. height
            cx, cy, cw, ch, ar = 1;

        // decide which gap to fill
        if (nw < w) ar = w / nw;
        if (Math.abs(ar - 1) < 1e-14 && nh < h) ar = h / nh;  // updated
        nw *= ar;
        nh *= ar;

        // calc source rectangle
        cw = iw / (nw / w);
        ch = ih / (nh / h);

        cx = (iw - cw) * offsetX;
        cy = (ih - ch) * offsetY;

        // make sure source rectangle is valid
        if (cx < 0) cx = 0;
        if (cy < 0) cy = 0;
        if (cw > iw) cw = iw;
        if (ch > ih) ch = ih;

        // fill image in dest. rectangle
        ctx.drawImage(img, cx, cy, cw, ch, x, y, w, h);
    },
    imageToHex: function (img) {
        const context = document.createElement("canvas").getContext("2d");
        context.drawImage(img, 0, 0, 1, 1);
        const i = context.getImageData(0, 0, 1, 1).data;
        return "#" + ((1 << 24) + (i[0] << 16) + (i[1] << 8) + i[2]).toString(16).slice(1);
    },
    getEventLocation: function (e) {
        if (e.touches && e.touches.length == 1) {
            return { x: e.touches[0].clientX, y: e.touches[0].clientY }
        }
        else if (e.clientX && e.clientY) {
            return { x: e.clientX, y: e.clientY }
        }
    },
    base64ToBinary: function (base64) {
        return new Uint8Array(atob(base64).split('').map(x => x.charCodeAt(0)));
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
    linkify: function (inputText) {
        var replacedText, replacePattern1, replacePattern2;

        //URLs starting with http://, https://, or ftp://
        replacePattern1 = /(\b(https?|ftp):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/gim;
        replacedText = inputText.replace(replacePattern1, '<a href="$1" target="_blank" rel="noopener noreferrer">$1</a>');

        //Change email addresses to mailto:: links.
        replacePattern2 = /(([a-zA-Z0-9\-\_\.])+@[a-zA-Z\_]+?(\.[a-zA-Z]{2,6})+)/gim;
        replacedText = replacedText.replace(replacePattern2, '<a href="mailto:$1">$1</a>');

        return replacedText;
    },
    logError: function(error) {
        console.log(error.name + ': ' + error.message);
        console.error(error);
    }
};
