var Post = {
    init: function() {
        if(localStorage.getItem('share_url')) {
            Post_ajaxCreate();
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
    }
}

MovimWebsocket.attach(function() {
    Post.init();
});
