var SendTo = {
    shareArticle : function(link) {
        SendTo_ajaxShareArticle(link, typeof navigator.share == 'function');
    },

    shareOs(object) {
        navigator.share(object);
    },
}