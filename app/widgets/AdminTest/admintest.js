var AdminTest = {
    databaseOK : false,
    websocketOK : false,
    apiOK : false,
    movimOK : true,

    toggleConfiguration : function() {
        if(this.databaseOK && this.websocketOK && this.movimOK) {
            MovimUtils.removeClass('li.admingen', 'disabled');
            MovimUtils.removeClass('li.api', 'disabled');
        } else {
            MovimUtils.addClass('li.admingen', 'disabled');
            MovimUtils.addClass('li.api', 'disabled');
        }
    },

    enableWebsocket : function() {
        this.websocketOK = true;
        this.toggleConfiguration();

        MovimUtils.removeClass('figure #browser-daemon', 'error');
        MovimUtils.addClass('figure #browser-daemon', 'success');
        MovimUtils.addClass('div #xmpp-daemon', 'success');

        MovimUtils.hideElement(MovimUtils.getNode('#websocket_error'));
    },

    enableAPI : function() {
        this.apiOK = true;

        MovimUtils.removeClass('figure #movim-api', 'disabled');
        MovimUtils.addClass('figure #movim-api', 'success');
    },

    disableMovim : function() {
        this.movimOK = false;

        MovimUtils.addClass('figure #movim_block', 'error');
    }
};

MovimWebsocket.attach(function() {
    AdminTest.enableWebsocket();
    AdminTest.toggleConfiguration();
});

movim_add_onload(function() {
    AdminTest.toggleConfiguration();
});
