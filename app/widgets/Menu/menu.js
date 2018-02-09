var Menu = {
    init: function() {
        var parts = MovimUtils.urlParts();

        if(parts.hash != '') {
            switch(parts.hash) {
                case 'communities':
                    Menu_ajaxGetNews();
                    break;
                case 'contacts':
                    Menu_ajaxGetFeed();
                    break;
                case 'blog':
                    Menu_ajaxGetMe();
                    break;
            }
        } else {
            Menu_ajaxGetAll();
        }
    }
};

MovimWebsocket.attach(function() {
    Menu.init();
    Notification_ajaxClear('news');
    Notification.current('news');
});
