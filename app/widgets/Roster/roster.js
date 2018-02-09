var Roster = {
    init : function() {
        var search      = document.querySelector('#rostersearch');
        if(search == null) return;

        var roster      = document.querySelector('#roster');
        var rosterlist  = document.querySelector('#rosterlist');

        search.oninput = function(event) {
            if(search.value.length > 0) {
                roster.classList.add('search');
            } else {
                roster.classList.remove('search');
            }

            document.querySelectorAll(
                '#rosterlist > li.found'
            ).forEach(item => item.classList.remove('found'));

            document.querySelectorAll(
                '#rosterlist > li[name*="' + MovimUtils.cleanupId(search.value).slice(3) + '"]'
            ).forEach(item => item.classList.add('found'));;
        };
    },
    setFound : function(jid) {
        document.querySelector('input[name=searchjid]').value = jid;
    }
};

MovimWebsocket.attach(function() {
    Notification.current('contacts');
});


movim_add_onload(function() {
    Roster.init();
});
