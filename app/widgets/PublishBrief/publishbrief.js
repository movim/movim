var Publish = {
    enableSend: function() {
        MovimUtils.removeClass('#button_send', 'disabled');
    },

    disableSend: function() {
        MovimUtils.addClass('#button_send', 'disabled');
    }
}

