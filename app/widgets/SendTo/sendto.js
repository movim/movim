var SendTo = {
    init: function () {
        document.querySelectorAll('#sendto_share_contacts > li[data-jid]').forEach(li => {
            var store = new ChatOmemoStorage();

            store.getContactState(li.dataset.jid).then(enabled => {
                if (enabled) li.classList.add('disabled');
            });
        });
    },

    shareArticle: function (link) {
        SendTo_ajaxShareArticle(link, typeof navigator.share == 'function');
    },

    shareOs(object) {
        navigator.share(object);
    },
}
