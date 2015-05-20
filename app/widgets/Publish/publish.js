var Publish = {
    init: function() {
        if(localStorage.getItem('share_url')) {
            Publish_ajaxCreateBlog();
            MovimTpl.showPanel();
        }
    },

    setEmbed: function() {
        if(localStorage.getItem('share_url')) {
            var embed = document.querySelector('input[name=embed]');
            embed.value = localStorage.getItem('share_url');
            embed.onpaste();
            localStorage.removeItem('share_url');
        }
    },

    enableSend: function() {
        movim_remove_class('#button_send', 'disabled');
    },

    disableSend: function() {
        movim_add_class('#button_send', 'disabled');
    }
}

MovimWebsocket.attach(function() {
    Publish.init();
});
