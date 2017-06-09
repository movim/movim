var PublishBrief = {
    timeout: 0,
    togglePrivacy: function() {
        var checked = document.querySelector('#publishbrief form #open');

        var button = document.querySelector('#publishbrief span.privacy i');
        MovimUtils.removeClass(button, 'zmdi-lock-outline');
        MovimUtils.removeClass(button, 'zmdi-portable-wifi');

        // Public
        if(checked.checked) {
            checked.checked = false;
            MovimUtils.addClass(button, 'zmdi-lock-outline');
        } else {
            checked.checked = true;
            MovimUtils.addClass(button, 'zmdi-portable-wifi');
        }

        PublishBrief_ajaxDisplayPrivacy(checked.checked);
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
        PublishBrief_ajaxClearEmbed();
        PublishBrief.saveDraft();
    },
    checkEmbed: function() {
        var embed = document.querySelector('input[name=embed]');
        embed.onchange();

        document.querySelector('form[name=brief]').onkeyup = function() {
            if(PublishBrief.timeout) clearTimeout(PublishBrief.timeout);
            PublishBrief.timeout = setTimeout(function () {
                PublishBrief.saveDraft();
            }, 1000);
        };
    },
    saveDraft: function() {
        PublishBrief_ajaxSaveDraft(MovimUtils.formToJson('brief'));
    },
    enableSend: function() {
        MovimUtils.removeClass('#button_send', 'disabled');
    },
    disableSend: function() {
        MovimUtils.addClass('#button_send', 'disabled');
    }
}

MovimWebsocket.attach(function() {
    PublishBrief_ajaxGet();
});

if(typeof Upload != 'undefined') {
    Upload.attach(function(file) {
        document.querySelector('input[name=embed]').value = file.uri;
        PublishBrief.checkEmbed();
    });
}
