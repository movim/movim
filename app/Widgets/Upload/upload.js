var Upload = {
    xhr: null,
    initiated: [],
    attached: [],
    failed: [],
    progressed: [],
    get: null,
    prependName: null,
    name: null,
    file: null,
    canvas: null,
    thumbhash: null,
    uploadButton: null,

    init: function (appendDate) {
        Upload.launchInitiated();

        if (Upload.file) {
            let splited = Upload.name.split('.');
            let extension = splited.pop();
            let name = splited.join('.');

            if (Upload.prependName) {
                name = Upload.prependName + '_' + name;
            }

            if (name.length > 128) {
                name = name.substring(0, 32) + '_' + MovimUtils.hash(name);
            }

            if (appendDate) {
                let now = new Date();
                now = now.toISOString().replace(/[-:]/g, '_').replaceAll('_', '');
                now = now.substring(0, now.length - 5);

                name += '_' + now;
            }

            Upload.name = name + '.' + extension;

            Upload_ajaxPrepare({
                name: Upload.name,
                size: Upload.file.size,
                type: Upload.file.type
            });
        }
    },

    openFile: function () {
        Upload.clear();
        document.querySelector('input#file').click();
    },

    openImage: function () {
        Upload.clear();
        document.querySelector('input#image').click();
    },

    attach: function (func) {
        if (typeof (func) === "function") {
            this.attached.push(func);
        }
    },

    initiate: function (func) {
        if (typeof (func) === "function") {
            this.initiated.push(func);
        }
    },

    fail: function (func) {
        if (typeof (func) === "function") {
            this.failed.push(func);
        }
    },

    progress: function (func) {
        if (typeof (func) === "function") {
            this.progressed.push(func);
        }
    },

    launchInitiated: function () {
        for (var i = 0; i < Upload.initiated.length; i++) {
            Upload.initiated[i]();
        }
    },

    launchAttached: function () {
        for (var i = 0; i < Upload.attached.length; i++) {
            Upload.attached[i]({
                id: Upload.id,
                thumbhash: Upload.thumbhash ?? null,
                thumbhashWidth: Upload.canvas ? Upload.canvas.width : null,
                thumbhashHeight: Upload.canvas ? Upload.canvas.height : null
            });
        }
    },

    launchFailed: function () {
        for (var i = 0; i < Upload.failed.length; i++) {
            Upload.failed[i]();
        }
    },

    launchProgressed: function (percent) {
        for (var i = 0; i < Upload.progressed.length; i++) {
            Upload.progressed[i](percent);
        }
    },

    preview: function (file) {
        Upload.canvas = null;
        Upload.uploadButton = document.querySelector('#upload_button');

        var resolvedFile = file ? file : document.getElementById('file').files[0];
        var resolvedFile = resolvedFile ? resolvedFile : document.getElementById('image').files[0];

        Upload.name = resolvedFile.name;
        Upload.check(resolvedFile);
    },

    check: function (file) {
        if (!file.type.match(/image.*/)) {
            console.log("Not a picture !");
            Upload.prepare(file);
        } else {
            var reader = new FileReader();
            reader.readAsDataURL(file);

            reader.addEventListener('load', function (ev) {
                MovimUtils.getOrientation(file, function (orientation) {
                    Upload.compress(ev.target.result, file, orientation);
                });
            });
        };
    },

    compress: function (src, file, orientation) {
        Upload.setCompress(null);

        var image = new Image();
        image.addEventListener('load', function () {
            if (file.size > SMALL_PICTURE_LIMIT) {
                var limit = 1920;
                var width = image.naturalWidth;
                var height = image.naturalHeight;

                var ratio = Math.min(limit / width, limit / height);

                if (ratio < 1) {
                    width = Math.round(width * ratio);
                    height = Math.round(height * ratio);
                }

                Upload.canvas = document.createElement('canvas');

                if (orientation > 4 && orientation < 9) {
                    Upload.canvas.width = height;
                    Upload.canvas.height = width;
                } else {
                    Upload.canvas.width = width;
                    Upload.canvas.height = height;
                }

                ctx = Upload.canvas.getContext("2d");

                MovimUtils.applyOrientation(ctx, orientation, width, height);

                ctx.drawImage(image, 0, 0, width, height);

                if (typeof Upload.canvas.toBlob == 'function') {
                    if (file.type != 'image/jpeg') {
                        Upload.name += '.jpg';
                    }

                    Upload.setCompress('photo_size_select_small');

                    Upload.canvas.toBlob(
                        function (blob) {
                            Upload.prepare(blob);
                        },
                        'image/jpeg',
                        0.85
                    );
                } else {
                    Upload.prepare(file);
                }
            } else {
                // Also create the canvas for other usages like drawing
                Upload.canvas = document.createElement('canvas');
                Upload.canvas.width = image.naturalWidth;
                Upload.canvas.height = image.naturalHeight;
                ctx = Upload.canvas.getContext("2d");
                ctx.drawImage(image, 0, 0, image.naturalWidth, image.naturalHeight);

                Upload.prepare(file);
            }
        });
        image.src = src;
    },

    prepare: function (file) {
        Upload.file = file;

        var preview = document.querySelector('#upload img.preview_picture');
        var fileInfo = document.querySelector('#upload li.file');

        // If the preview system is there
        if (preview) {
            var toDraw = fileInfo.querySelector('span.primary');
            fileInfo.classList.remove('preview');
            fileInfo.querySelector('p.name').innerText = Upload.name;
            var type = file.type ? file.type + ' · ' : '';
            fileInfo.querySelector('p.desc').innerText = type + MovimUtils.humanFileSize(file.size);

            if (Upload.file.type.match(/image.*/)) {
                preview.src = URL.createObjectURL(Upload.file);

                // Thumbhash
                preview.addEventListener('load', e => {
                    const canvas = document.createElement('canvas');
                    const context = canvas.getContext('2d');
                    const scale = 100 / Math.max(Upload.canvas.width, Upload.canvas.height);

                    canvas.width = Math.round(Upload.canvas.width * scale);
                    canvas.height = Math.round(Upload.canvas.height * scale);

                    context.drawImage(preview, 0, 0, canvas.width, canvas.height);
                    const pixels = context.getImageData(0, 0, canvas.width, canvas.height);

                    Upload.thumbhash = MovimUtils.arrayBufferToBase64(rgbaToThumbHash(pixels.width, pixels.height, pixels.data));
                });

                toDraw.addEventListener('click', e => {
                    Draw.initCanvas = Upload.canvas ?? preview;
                    Draw_ajaxHttpGet();

                    Dialog_ajaxClear();
                    Upload.abort();
                });
                fileInfo.classList.add('preview');
            } else {
                preview.src = '';
            }
        }

        if (Upload.uploadButton) {
            if (!document.querySelector('#upload p.limit') || document.querySelector('#upload p.limit').dataset.limit >= file.size) {
                Upload.uploadButton.classList.remove('disabled');
            } else {
                Upload.uploadButton.classList.add('disabled');
            }
        }
    },

    request: function (route, id) {
        Upload.xhr = new XMLHttpRequest();
        Upload.id = id;

        if (Upload.uploadButton) {
            Upload.uploadButton.classList.add('disabled');
        }

        Upload.xhr.upload.addEventListener('progress', function (evt) {
            var percent = Math.floor(evt.loaded / evt.total * 100);

            Upload.launchProgressed(percent);
            Upload.setProgress('arrow_upload_progress', percent == 100 ? '' : percent + '%');
        }, false);

        Upload.xhr.onreadystatechange = function () {
            if (Upload.xhr.readyState == 4
                && (Upload.xhr.status >= 200 && Upload.xhr.status < 400)) {
                Dialog.clear();
                Upload.launchAttached();
                Upload.clear();

                if (Upload.uploadButton) {
                    Upload.uploadButton.classList.remove('disabled');
                }
            } else if (Upload.xhr.readyState == 4
                && (Upload.xhr.status >= 400 || Upload.xhr.status == 0)
                && Upload.file != null) {
                Upload.launchFailed();
                Upload_ajaxFailed();

                Upload.setProgress('error', '');

                if (Upload.uploadButton) {
                    Upload.uploadButton.classList.remove('disabled');
                }
            }
        }

        Upload.xhr.open("POST", route, true);

        if (Upload.file != null) {
            const formData = new FormData();
            formData.append('file', Upload.file, Upload.name);
            Upload.xhr.send(formData);

            Upload.setProgress('arrow_upload_ready', '');
        }
    },

    setCompress: function (icon) {
        if (document.querySelector('#upload_progress')) {
            document.querySelector('#upload_progress span.primary.compress i').innerText = icon;
        }
    },

    setProgress: function (icon, text) {
        if (document.querySelector('#upload_progress')) {
            document.querySelector('#upload_progress span.primary.upload i').innerText = icon;
            document.querySelector('#upload_progress li p').innerText = text;
        }
    },

    abort: function () {
        if (Upload.xhr) Upload.xhr.abort();
        Upload.clear();
    },

    clear: function () {
        if (document.getElementById('file')) {
            document.getElementById('file').value = null;
        }

        if (document.getElementById('image')) {
            document.getElementById('image').value = null;
        }

        Upload.name = null;
        Upload.prependName = null;
        Upload.file = null;
    },

    attachEvents: function () {
        if (Upload.file) {
            Upload.preview(Upload.file);
        }

        document.querySelector('#upload div.drop').addEventListener('drop', ev => {
            ev.preventDefault();

            if (ev.dataTransfer.items) {
                for (var i = 0; i < ev.dataTransfer.items.length; i++) {
                    if (ev.dataTransfer.items[i].kind === 'file') {
                        var file = ev.dataTransfer.items[i].getAsFile();
                        Upload.preview(file);
                    }
                }
            }
        });

        var dropArea = document.querySelector('#upload div.drop');

        dropArea.querySelector('img.preview_picture')
            .addEventListener('drop', ev => ev.preventDefault());

        dropArea.addEventListener('dragover', ev => {
            ev.preventDefault();
            dropArea.classList.add('dropped');
        }, false);

        dropArea.addEventListener('drop', ev => {
            ev.preventDefault();
            dropArea.classList.remove('dropped');
        }, false);

        dropArea.addEventListener('dragleave', ev => {
            ev.preventDefault();
            dropArea.classList.remove('dropped');
        }, false);
    }
}

/**
 * Handle the paste event
 */

MovimEvents.registerWindow('paste', 'upload', (e) => {
    if (Upload.file != null) return;

    const clipboardItems = e.clipboardData.items;
    const items = [].slice
        .call(clipboardItems)
        .filter(function (item) {
            return item.type.indexOf('image') !== -1;
        });

    if (items.length === 0) {
        return;
    }

    const item = items[0];
    Upload.file = item.getAsFile();
    Upload_ajaxGetPanel();
});

/**
 * Handle the global drop event
 */

MovimEvents.registerBody('dragover', 'upload', (ev) => {
    if (document.getElementById('upload')) return;
    ev.preventDefault();

    if (ev.dataTransfer.items[0].kind == 'file') {
        document.body.classList.add('dropped');
    }
});

MovimEvents.registerBody('drop', 'upload', (ev) => {
    if (document.getElementById('upload')) return;
    ev.preventDefault();

    if (ev.dataTransfer.items.length > 0
        && ev.dataTransfer.items[0].kind === 'file') {
        var file = ev.dataTransfer.items[0].getAsFile();

        Upload.file = file;
        Upload.name = file.name;

        Upload_ajaxGetPanel();
    }

    document.body.classList.remove('dropped');
});

MovimEvents.registerBody('dragleave', 'upload', (ev) => {
    if (document.getElementById('upload')) return;
    ev.preventDefault();
    document.body.classList.remove('dropped');
});
