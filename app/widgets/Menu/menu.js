var Menu = {
    init: function() {
        let parts = MovimUtils.urlParts();
        let page = parts.params[0] !== undefined ? parseInt(parts.params[0]) : 0;

        if (parts.hash != '') {
            switch (parts.hash) {
                case 'communities':
                    Menu_ajaxHttpGetNews(page);
                    break;
                case 'contacts':
                    Menu_ajaxHttpGetFeed(page);
                    break;
            }
        } else {
            Menu_ajaxHttpGetAll(page);
        }
    },

    setLoad: function(tab) {
        tab.innerHTML = '<i class="material-icons spin">autorenew</i>';
    }
};

MovimWebsocket.attach(function() {
    Menu.init();
    Notification_ajaxClear('news');
    Notification.current('news');
});
