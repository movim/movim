var PublishBrief = {
    enableSend: function() {
        MovimUtils.removeClass('#button_send', 'disabled');
    },
    disableSend: function() {
        MovimUtils.addClass('#button_send', 'disabled');
    },
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
    }
}

