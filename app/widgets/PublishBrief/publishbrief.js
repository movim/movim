var Publish = {
    enableSend: function() {
        MovimUtils.removeClass('#button_send', 'disabled');
    },
    disableSend: function() {
        MovimUtils.addClass('#button_send', 'disabled');
    }
}

var PublishBrief = {
    togglePrivacy: function() {
        var checked = document.querySelector('#publishbrief form #open');

        var button = document.querySelector('#publishbrief span.privacy i');
        MovimUtils.removeClass(button, 'zmdi-lock-outline');
        MovimUtils.removeClass(button, 'zmdi-lock-globe');

        // Public
        if(checked.checked) {
            checked.checked = false;
            MovimUtils.addClass(button, 'zmdi-lock-outline');
        } else {
            checked.checked = true;
            MovimUtils.addClass(button, 'zmdi-globe');
        }

        PublishBrief_ajaxDisplayPrivacy(checked.checked);
    },
    addUrl: function() {
        var url = document.querySelector('#url');
        var embed = document.querySelector('#embed');
        embed.value = url.value;
        embed.onchange();
    },
    clearEmbed: function() {
        document.querySelector('input[name=embed]').value = '';
        PublishBrief_ajaxClearEmbed();
    }
}

MovimWebsocket.attach(function() {
    PublishBrief_ajaxClearEmbed();
});

Upload.attach(function() {
    var embed = document.querySelector('input[name=embed]');
    embed.value = Upload.get;
    embed.onchange();
});
