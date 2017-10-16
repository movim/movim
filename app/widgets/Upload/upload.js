var Upload = {
    xhr : null,
    attached : [],
    get : null,
    name : null,
    file : null,

    init : function() {
        if(Upload.file) {
            Upload_ajaxSend({
                name: Upload.name,
                size: Upload.file.size,
                type: Upload.file.type
            });
        }
    },

    attach : function(func) {
        if(typeof(func) === "function") {
            this.attached.push(func);
        }
    },

    launchAttached : function() {
        for(var i = 0; i < Upload.attached.length; i++) {
            Upload.attached[i]({
                name: Upload.name,
                size: Upload.file.size,
                type: Upload.file.type,
                uri:  Upload.get
            });
        }
    },

    preview : function() {
        var file = document.getElementById('file').files[0];
        Upload.name = file.name;
        Upload.check(file);
    },

    check : function(file) {
        if (!file.type.match(/image.*/)) {
            console.log("Not a picture !");
            Upload.prepare(file);
        } else {
            var reader = new FileReader();
            reader.readAsDataURL(file);

            reader.onload = function (ev) {
                MovimUtils.getOrientation(file, function(orientation) {
                    Upload.compress(ev.target.result, file, orientation);
                });
            };
        };
    },

    compress : function(src, file, orientation) {
        var image = new Image();
        image.onload = function()
        {
            var limit = 1600;

            var width = image.naturalWidth;
            var height = image.naturalHeight;

            var ratio = Math.min(limit / width, limit / height);

            if(ratio < 1 || file.size > SMALL_PICTURE_LIMIT) {
                if(ratio < 1) {
                    width = Math.round(width*ratio);
                    height = Math.round(height*ratio);
                }

                var canvas = document.createElement('canvas');

                if ([5,6,7,8].indexOf(orientation) > -1) {
                  canvas.width = height;
                  canvas.height = width;
                } else {
                  canvas.width = width;
                  canvas.height = height;
                }

                ctx = canvas.getContext("2d");

                switch (orientation) {
                    case 2: ctx.transform(-1, 0, 0, 1, width, 0); break;
                    case 3: ctx.transform(-1, 0, 0, -1, width, height ); break;
                    case 4: ctx.transform(1, 0, 0, -1, 0, height ); break;
                    case 5: ctx.transform(0, 1, 1, 0, 0, 0); break;
                    case 6: ctx.transform(0, 1, -1, 0, height , 0); break;
                    case 7: ctx.transform(0, -1, -1, 0, height , width); break;
                    case 8: ctx.transform(0, -1, 1, 0, 0, width); break;
                    default: ctx.transform(1, 0, 0, 1, 0, 0);
                }

                ctx.drawImage(image, 0, 0, width, height);

                if(typeof canvas.toBlob == 'function') {
                    if(file.type != 'image/jpeg') {
                        Upload.name += '.jpg';
                    }

                    canvas.toBlob(
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
                Upload.prepare(file);
            }

        }
        image.src = src;
    },

    prepare : function(file) {
        Upload.file = file;

        var preview = document.querySelector('#upload img.preview_picture');
        if (Upload.file.type.match(/image.*/)) {
            preview.src = URL.createObjectURL(Upload.file);
        } else {
            preview.src = '';
        }
    },

    request : function(get, put) {
        Upload.get = get;

        Upload.xhr = new XMLHttpRequest();

        Upload.xhr.upload.addEventListener('progress', function(evt) {
            var percent = Math.floor(evt.loaded/evt.total*100);
            var progress = document.querySelector('#dialog ul li p');
            if(progress) progress.innerHTML = percent + '%';
        }, false);

        Upload.xhr.onreadystatechange = function() {
            if(Upload.xhr.readyState == 4
            && (Upload.xhr.status >= 200 && Upload.xhr.status < 400)) {
                Dialog.clear();
                Upload.launchAttached();
            }

            if(Upload.xhr.readyState == 4
            && (Upload.xhr.status >= 400 || Upload.xhr.status == 0)
            && Upload.file != null) {
                Upload_ajaxFailed();
            }
        }

        Upload.xhr.open("PUT", put, true);

        Upload.xhr.setRequestHeader('Content-Type', 'text/plain');

        if(Upload.file != null) {
            Upload.xhr.send(Upload.file);
        }
    },

    abort : function() {
        if(Upload.xhr) Upload.xhr.abort();
    }
}

