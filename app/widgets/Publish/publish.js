var Publish = {
    titleTimeout: null,
    contentTimeout: null,

    init: function() {
        let id = document.querySelector('#publish input[name=id]').value;

        Publish_ajaxTryResolveShareUrl(id);

        document.querySelector('#publish textarea[name=title]').addEventListener('keyup', function(event) {
            if (Publish.titleTimeout) clearTimeout(Publish.titleTimeout);
            document.querySelector('#publish textarea[name=title] + label span.save').classList.remove('saved');

            Publish.titleTimeout = setTimeout(function () {
                console.log('trigger');
                Publish_ajaxHttpSaveTitle(id, event.target.value);
            }, 1000);
        });

        document.querySelector('#publish textarea[name=content]').addEventListener('keyup', function(event) {
            if (Publish.contentTimeout) clearTimeout(Publish.contentTimeout);
            document.querySelector('#publish textarea[name=content] + label span.save').classList.remove('saved');

            Publish.contentTimeout = setTimeout(function () {
                Publish_ajaxHttpSaveContent(id, event.target.value);
            }, 1000);
        });
    },

    enableSend: function() {
        document.querySelector('.button.send').classList.remove('disabled');
        document.querySelector('.button.send i').classList.remove('spin');
        document.querySelector('.button.send i').innerText = 'send';
    },

    disableSend: function() {
        document.querySelector('.button.send').classList.add('disabled');
        document.querySelector('.button.send i').classList.add('spin');
        document.querySelector('.button.send i').innerText = 'autorenew';
    },

    preview: function() {
        let id = document.querySelector('#publish input[name=id]').value;
        Publish_ajaxPreview(id);
    },

    publish: function() {
        let id = document.querySelector('#publish input[name=id]').value;
        Publish_ajaxPublish(id);
    },

    clearReply: function() {
        let id = document.querySelector('#publish input[name=id]').value;
        Publish_ajaxClearReply(id);
    },

    addUrl: function() {
        let id = document.querySelector('#publish input[name=id]').value;
        var url = document.querySelector('#url').value;

        Publish_ajaxAddEmbed(id, url);
    }
}

MovimWebsocket.attach(function() {
    Publish.init();
    MovimUtils.applyAutoheight();
});

if (typeof Upload != 'undefined') {
    Upload.attach(function(file) {
        let id = document.querySelector('#publish input[name=id]').value;
        Publish_ajaxAddEmbed(id, file.uri);
    });
}
