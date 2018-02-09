var PublishBrief = {
    timeout: 0,
    togglePrivacy: function() {
        var checked = document.querySelector('#publishbrief form #open');

        var button = document.querySelector('#publishbrief span.privacy i');
        button.classList.remove('zmdi-lock-outline', 'zmdi-portable-wifi');

        // Public
        if (checked.checked) {
            checked.checked = false;
            button.classList.add('zmdi-lock-outline');
        } else {
            checked.checked = true;
            button.classList.add('zmdi-portable-wifi');
        }

        PublishBrief_ajaxDisplayPrivacy(checked.checked);
    },
    setTitle: function(value) {
        let title = document.querySelector('textarea[name=title]');
        if (title.value == '') {
            title.value = value;
        }
    },
    addUrl: function() {
        var url = document.querySelector('#url');
        var embed = document.querySelector('#embed');
        embed.value = url.value;
        embed.onchange();
        PublishBrief.saveDraft();
    },
    clearEmbed: function() {
        document.querySelector('input[name=embed]').value = '';
        document.querySelector('input[name=imagenumber]').value = 0;
        PublishBrief_ajaxClearEmbed();
        PublishBrief.saveDraft();
    },
    checkEmbed: function() {
        MovimUtils.textareaAutoheight(document.querySelector('textarea[name=content]'));

        var embed = document.querySelector('input[name=embed]');
        embed.onchange();

        document.querySelector('form[name=brief]').onkeyup = function() {
            if (PublishBrief.timeout) clearTimeout(PublishBrief.timeout);
            PublishBrief.timeout = setTimeout(function () {
                PublishBrief.saveDraft();
            }, 1000);
        };
    },
    setEmbedImage: function(imagenumber) {
        Drawer.clear();
        document.querySelector('input[name=imagenumber]').value = imagenumber;
        document.querySelector('input[name=embed]').onchange();
    },
    saveDraft: function() {
        PublishBrief_ajaxSaveDraft(MovimUtils.formToJson('brief'));
    },
    enableSend: function() {
        document.querySelector('#button_send').classList.remove('disabled');
    },
    disableSend: function() {
        document.querySelector('#button_send').classList.add('disabled');
    }
}

MovimWebsocket.attach(function() {
    var parts = MovimUtils.urlParts();

    if (parts.params.length > 3 && parts.params[3] == 'share') {
        PublishBrief_ajaxGet(parts.params[0], parts.params[1], parts.params[2], true, true);
    } else if (parts.params.length > 2) {
        PublishBrief_ajaxGet(parts.params[0], parts.params[1], parts.params[2], false, true);
    } else if (parts.params.length > 0) {
        PublishBrief_ajaxGet(parts.params[0], parts.params[1], false, false, true);
    } else if (parts.page == 'publish') {
        PublishBrief_ajaxGet(false, false, false, false, true);
    } else {
        PublishBrief_ajaxGet();
    }
});

if(typeof Upload != 'undefined') {
    Upload.attach(function(file) {
        document.querySelector('input[name=embed]').value = file.uri;
        PublishBrief.checkEmbed();
    });
}
