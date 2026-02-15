var BottomNavigation = {
    chatsCounter: 0,
    spacesCounters: {},
    setChatNotification: function (keys) {
        let counter = document.querySelector('#bottomchatcounter');

        if (counter) {
            counter.classList.remove('notifications');

            Object.keys(keys).forEach(key => {
                if (key == 'chat') {
                    BottomNavigation.chatsCounter = keys[key];
                }

                if (key.substring(0, 5) == 'space' && !key.includes('|')) {
                    BottomNavigation.spacesCounters[key] = keys[key];
                }
            });

            if (BottomNavigation.chatsCounter
                + Object.values(BottomNavigation.spacesCounters).reduce((sum, counter) => sum + counter, 0)) {
                counter.classList.add('notifications');
            }
        }
    }
}

MovimWebsocket.initiate(() => {
    if (MovimUtils.isMobile()) {
        BottomNavigation_ajaxHttpRefresh()
    }
});
