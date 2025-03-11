var Menu = {
    init: function() {
        let parts = MovimUtils.urlParts();
        let page = parts.params[0] !== undefined ? parseInt(parts.params[0]) : 0;

        if (parts.hash != '') {
            switch (parts.hash) {
                case 'communities':
                    Menu_ajaxHttpGetCommunities(page);
                    break;
                case 'contacts':
                    Menu_ajaxHttpGetContacts(page);
                    break;
            }
        } else {
            Menu_ajaxHttpGetAll(page);
        }
    },

    refresh: function() {
        document.querySelector('#menu_refresh li').classList.add('refreshing');
        Menu_ajaxHttpGetAll();
    },

    setLoad: function(tab) {
        tab.innerHTML = '<i class="material-symbols spin">autorenew</i>';
    }
};

MovimWebsocket.attach(function() {
    Menu.init();
    Notif_ajaxClear('news');
    Notif.current('news');
});
