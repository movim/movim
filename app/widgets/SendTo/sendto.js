var SendTo = {
    shareArticle : function(link) {
        SendTo_ajaxShareArticle(link, true /*typeof navigator.share == 'function'*/);
    },

    shareOs(object) {
        navigator.share(object);
    },
}