var Upload = {
    xhr : null,
    attached : new Array(),
    get : null,
    name : null,
    file : null,

    init : function() {
        document.getElementById('file').addEventListener('change', function(){
            var file = this.files[0];

            Upload.name = file.name;

            Upload.preview(file);
        });
    },

    attach : function(func) {
        if(typeof(func) === "function") {
            this.attached.push(func);
        }
    },

    launchAttached : function() {
        for(var i = 0; i < Upload.attached.length; i++) {
            Upload.attached[i]();
        }
    },

    preview : function(file) {
        if (!file.type.match(/image.*/)) {
            console.log("Not a picture !");
            Upload.initiate(file);
        } else {
            var reader = new FileReader();
            reader.readAsDataURL(file);

            reader.onload = function (ev) {
                Upload.compress(ev.target.result, file);
            };
        };
    },

    compress : function(src, file) {
        var image = new Image();
        image.onload = function()
        {
            var limit = 1600;

            var width = image.naturalWidth;
            var height = image.naturalHeight;

            var ratio = (limit*limit)/(width*height);

            if(ratio < 1 || file.size > SMALL_PICTURE_LIMIT) {
                if(ratio < 1) {
                    width = Math.round(width*ratio);
                    height = Math.round(height*ratio);
                }

                var canvas = document.createElement('canvas');
                canvas.width = width;
                canvas.height = height;

                canvas.getContext("2d").drawImage(image, 0, 0, width, height);

                if(typeof canvas.toBlob == 'function') {
                    if(file.type != 'image/jpeg') {
                        Upload.name += '.jpg';
                    }

                    canvas.toBlob(
                        function (blob) {
                            Upload.initiate(blob);
                        },
                        'image/jpeg',
                        0.85
                    );
                } else {
                    Upload.initiate(file);
                }
            } else {
                Upload.initiate(file);
            }
        }
        image.src = src;
    },

    initiate : function(file) {
        Upload.file = file;

        Upload_ajaxSend({
            name: Upload.name,
            size: file.size,
            type: file.type
        });
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
            && Upload.xhr.status >= 400) {
                Upload_ajaxFailed();
            }
        }

        Upload.xhr.open("PUT", put, true);

        Upload.xhr.setRequestHeader('Content-Type', 'text/plain');
        Upload.xhr.send(Upload.file);
    },

    abort : function() {
        if(Upload.xhr) Upload.xhr.abort();
    }
}

