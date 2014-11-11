var AdminTest = {
    databaseOK : false,
    weksocketOK : false,
    apiOK : false,
    movimOK : true,
    
    toggleConfiguration : function() {
        if(this.databaseOK && this.websocketOK && this.movimOK) {
            movim_remove_class('li.admingen', 'disabled');
            movim_remove_class('li.api', 'disabled');
        } else {
            movim_add_class('li.admingen', 'disabled');
            movim_add_class('li.api', 'disabled');
        }
    },

    enableWebsocket : function() {
        this.websocketOK = true;
        this.toggleConfiguration();

        movim_remove_class('figure #browser-daemon', 'error');
        movim_add_class('figure #browser-daemon', 'success');

        movim_toggle_display('#websocket_error');
    },

    enableAPI : function() {
        this.apiOK = true;

        movim_remove_class('figure #movim-api', 'disabled');
        movim_add_class('figure #movim-api', 'success');
    },

    disableMovim : function() {
        this.movimOK = false;

        movim_add_class('figure #movim_block', 'error');
    }
}

MovimWebsocket.attach(function() {
    AdminTest.enableWebsocket();
});

movim_add_onload(function() {
    AdminTest.toggleConfiguration();
    movim_toggle_display('#websocket_error');
});
