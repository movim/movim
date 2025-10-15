var Publish = {
    titleTimeout: null,
    contentTimeout: null,

    get: function(type) {
        let parts = MovimUtils.urlParts();

        Publish_ajaxHttpGet(
            type ?? null,
            parts.params[0],
            parts.params[1],
            parts.params[2],
            parts.params[3],
            parts.params[4],
            parts.params[5],
        )
    },

    init: function () {
        let id = document.querySelector('#publish input[name=id]').value;

        Publish_ajaxTryResolveShareUrl(id);

        document.querySelector('#publish textarea[name=title]').addEventListener('keyup', function (event) {
            if (Publish.titleTimeout) clearTimeout(Publish.titleTimeout);
            document.querySelector('#publish textarea[name=title] + label span.save').classList.remove('saved');

            Publish.titleTimeout = setTimeout(function () {
                Publish.saveTitle();
                Publish_ajaxOpenlinkPreview(id);
            }, 1000);
        });

        document.querySelector('#publish textarea[name=content]').addEventListener('keyup', function (event) {
            if (Publish.contentTimeout) clearTimeout(Publish.contentTimeout);
            document.querySelector('#publish textarea[name=content] + label span.save').classList.remove('saved');

            Publish.contentTimeout = setTimeout(function () {
                Publish.saveContent();
            }, 1000);
        });

        MovimUtils.applyAutoheight();
        Publish_ajaxOpenlinkPreview(id);
        Publish_ajaxCheckPrivacy(id);
    },

    saveTitle: function () {
        let id = document.querySelector('#publish input[name=id]').value;
        return Publish_ajaxHttpSaveTitle(id, document.querySelector('#publish textarea[name=title]').value);
    },

    saveContent: function () {
        let id = document.querySelector('#publish input[name=id]').value;
        return Publish_ajaxHttpSaveContent(id, document.querySelector('#publish textarea[name=content]').value);
    },

    enableSend: function () {
        document.querySelector('.button.send').classList.remove('disabled');
        document.querySelector('.button.send i').classList.remove('spin');
        document.querySelector('.button.send i').innerText = 'send';
    },

    disableSend: function () {
        document.querySelector('.button.send').classList.add('disabled');
        document.querySelector('.button.send i').classList.add('spin');
        document.querySelector('.button.send i').innerText = 'autorenew';
    },

    preview: function () {
        Publish.saveTitle().then(e => {
            Publish.saveContent().then(e => {
                let id = document.querySelector('#publish input[name=id]').value;
                Publish_ajaxPreview(id);
            });
        });
    },

    publish: function () {
        Publish.saveTitle().then(e => {
            Publish.saveContent().then(e => {
                let id = document.querySelector('#publish input[name=id]').value;
                Publish_ajaxPublish(id);
            });
        });
    },

    clearReply: function () {
        let id = document.querySelector('#publish input[name=id]').value;
        Publish_ajaxClearReply(id);
    },

    addUrl: function () {
        let id = document.querySelector('#publish input[name=id]').value;
        var url = document.querySelector('#url').value;

        Publish_ajaxAddUrl(id, url);
    }
}

MovimWebsocket.attach(() => {
    Publish.get('brief');
    MovimUtils.applyAutoheight();
});

MovimEvents.registerWindow('loaded', 'publish', () => {
    if (typeof Upload != 'undefined') {
        Upload.attach((file) => {
            if (MovimUtils.urlParts().page == 'publish') {
                Publish_ajaxAddUpload(document.querySelector('#publish input[name=id]').value, file.id);
            }
        });

        Upload.initiate((file) => {
            if (MovimUtils.urlParts().page == 'publish') {
                Upload.prependName = 'post';
            }
        });
    }
});
