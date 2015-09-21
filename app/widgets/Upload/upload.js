var Upload = {
    xhr : null,
    attached : new Array(),
    get : null,

    init : function() {
        document.getElementById('file').addEventListener('change', function(){
            var file = this.files[0];
            Upload_ajaxSend({
                name: file.name,
                size: file.size,
                type: file.type
            });
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

    request : function(get, put) {
        Upload.get = get;

        var file = document.getElementById('file').files[0];

        Upload.xhr = new XMLHttpRequest();

        Upload.xhr.upload.addEventListener('progress', function(evt) {
            var percent = Math.floor(evt.loaded/evt.total*100);
            document.querySelector('#dialog ul li p').innerHTML = percent + '%';
        }, false);

        Upload.xhr.onreadystatechange = function() {
            if(Upload.xhr.readyState == 4 ) {
                Dialog.clear();
                Upload.launchAttached();
            }
        }

        Upload.xhr.open("PUT", put, true);

        Upload.xhr.setRequestHeader('Content-Type', 'text/plain');
        Upload.xhr.send(file);
    }
}

MovimWebsocket.attach(function()
{
});
