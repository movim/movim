var Search = {
    timer : null,

    init : function() {
        if (window.matchMedia("(min-width: 1025px)").matches) {
            document.querySelector('input[name=keyword]').focus();
        }

        Search_ajaxInitRoster();
    },

    roster : function(key) {
        var selector = '#search > #roster > li';
        var subheader = document.querySelector(selector + '.subheader')

        document.querySelectorAll(selector)
            .forEach(item => item.classList.remove('found'));

        if (key == '') {
            document.querySelectorAll(selector).forEach(item => item.classList.add('found'));
        };

        var founds = document.querySelectorAll(
            selector + '[name*="' + MovimUtils.cleanupId(key).slice(3) + '"]'
        )

        if (founds.length > 0) {
            subheader.classList.add('found');
            founds.forEach(item => item.classList.add('found'));
        } else if (key != '') {
            subheader.classList.remove('found');
        }
    },

    chat : function(jid) {
        if (MovimUtils.urlParts().page === 'chat') {
            Drawer_ajaxClear();
            Chats_ajaxOpen(jid);
            Chat_ajaxGet(jid);
        } else {
            Search_ajaxChat(jid);
        }
    },

    searchSomething : function(value) {
        clearTimeout(Search.timer);

        if (value !== '') {
            document.querySelector('#searchbar span.primary i').innerText = 'autorenew';
            document.querySelector('#searchbar span.primary').classList.add('spin');
        }

        Search.timer = setTimeout(() => {
            Search_ajaxSearch(value);
        },
        700);

        Search.roster(value);
    },

    searchClear : function() {
        document.querySelector('#searchbar span.primary i').innerText = 'search';
        document.querySelector('#searchbar span.primary').classList.remove('spin');
    }
}

document.addEventListener('keydown', function(e) {
    if (e.keyCode == 77 && e.ctrlKey) {
        e.preventDefault();
        Search_ajaxRequest();
    }
});
