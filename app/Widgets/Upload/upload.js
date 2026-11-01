var Upload = {
    id: null,
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
    checking: null,

    init: function (appendDate) {
        Upload.launchInitiated();

        if (Upload.file) {
            const dot = Upload.name.lastIndexOf('.');
            let name = dot > 0 ? Upload.name.slice(0, dot) : Upload.name;
            const extension = dot > 0 ? Upload.name.slice(dot + 1) : '';

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

            Upload.name = extension ? `${name}.${extension}` : name;

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

        const resolvedFile = file
            ?? document.getElementById('file')?.files[0]
            ?? document.getElementById('image')?.files[0];
        if (!resolvedFile) return;

        Upload.name = resolvedFile.name;
        Upload.check(resolvedFile);
    },

    check: async function (file) {
        if (!file.type.startsWith('image/')) {
            Upload.prepare(file);
            return;
        }

        const current = Upload.checking = file;
        let result;

        try {
            const image = await MovimUtils.loadImage(file);
            result = await Upload.compress(image, file);
        } catch (e) {
            result = { blob: file, canvas: null };
        }

        if (Upload.checking !== current) return;

        Upload.canvas = result.canvas;

        // Converted to JPEG: don't keep a misleading original extension
        if (result.blob.type === 'image/jpeg' && file.type !== 'image/jpeg') {
            Upload.name += '.jpg';
        }

        Upload.prepare(result.blob);
    },

    compress: async function (image, file) {
        Upload.setCompress(null);
        const resize = file.size > SMALL_PICTURE_LIMIT;

        let width = image.naturalWidth;
        let height = image.naturalHeight;

        if (resize) {
            const ratio = Math.min(1, 1920 / Math.max(width, height));
            width = Math.round(width * ratio);
            height = Math.round(height * ratio);
        }

        const canvas = document.createElement('canvas');
        canvas.width = width;
        canvas.height = height;

        const ctx = canvas.getContext('2d');

        if (resize) {
            ctx.fillStyle = '#fff';
            ctx.fillRect(0, 0, width, height);
        }

        ctx.drawImage(image, 0, 0, width, height);

        const [type, quality] = resize ? ['image/jpeg', 0.85] : [file.type, 0.92];
        const blob = await new Promise((resolve) => canvas.toBlob(resolve, type, quality));

        if (!blob) throw new Error('Canvas encoding failed');

        if (resize) Upload.setCompress('photo_size_select_small');

        return { blob, canvas };
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

            if (Upload.file.type.startsWith('image/')) {
                const url = URL.createObjectURL(Upload.file);
                preview.src = url;

                // Thumbhash
                preview.addEventListener('load', e => {
                    URL.revokeObjectURL(url);
                    const canvas = document.createElement('canvas');
                    const context = canvas.getContext('2d');
                    const scale = 100 / Math.max(preview.naturalWidth, preview.naturalHeight);

                    canvas.width = Math.round(preview.naturalWidth * scale);
                    canvas.height = Math.round(preview.naturalHeight * scale);

                    context.drawImage(preview, 0, 0, canvas.width, canvas.height);
                    const pixels = context.getImageData(0, 0, canvas.width, canvas.height);

                    Upload.thumbhash = MovimUtils.arrayBufferToBase64(rgbaToThumbHash(pixels.width, pixels.height, pixels.data));
                }, { once: true });

                toDraw.onclick = () => {
                    Draw.initCanvas = Upload.canvas ?? preview;
                    Draw_ajaxHttpGet();

                    Dialog_ajaxClear();
                    Upload.abort();
                };

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
            if (!evt.lengthComputable) return;
            const percent = Math.floor(evt.loaded / evt.total * 100);

            Upload.launchProgressed(percent);
            Upload.setProgress('arrow_upload_progress', percent == 100 ? '' : percent + '%');
        }, false);

        const fail = () => {
            Upload.launchFailed();
            Upload_ajaxFailed();
            Upload.setProgress('error', '');
            Upload.uploadButton?.classList.remove('disabled');
        };

        Upload.xhr.addEventListener('load', () => {
            if (Upload.xhr.status >= 200 && Upload.xhr.status < 400) {
                Dialog.clear();
                Upload.launchAttached();
                Upload.clear();
                Upload.uploadButton?.classList.remove('disabled');
            } else {
                fail();
            }
        });
        Upload.xhr.addEventListener('error', fail);

        Upload.xhr.open("POST", route, true);

        if (Upload.file != null) {
            const formData = new FormData();
            formData.append(Upload.id, Upload.file, Upload.name);
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

        Upload.checking = null;
        Upload.name = null;
        Upload.prependName = null;
        Upload.file = null;
    },

    attachEvents: function () {
        if (Upload.file) {
            Upload.preview(Upload.file);
        }

        if (typeof Chat != 'undefined' && Chat.isEncrypted()) {
            document.querySelector('#upload').classList.add('encrypted');
        }

        document.querySelector('#upload div.drop').addEventListener('drop', ev => {
            ev.preventDefault();

            const [file] = ev.dataTransfer.files;
            if (file) Upload.preview(file);
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

    const [file] = e.clipboardData?.files ?? [];
    if (!file) return;

    Upload.file = file;
    Upload.name = file.name;
    Upload_ajaxGetPanel();
});

/**
 * Handle the global drop event
 */

MovimEvents.registerBody('dragover', 'upload', (ev) => {
    if (document.getElementById('upload')) return;
    ev.preventDefault();

    if (ev.dataTransfer.items[0] && ev.dataTransfer.items[0].kind == 'file') {
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
