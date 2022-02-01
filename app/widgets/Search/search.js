var Search = {
    timer : null,
    rosterLimit: false,

    init : function() {
        if (window.matchMedia("(min-width: 1025px)").matches) {
            document.querySelector('input[name=keyword]').focus();
        }

        Search.rosterLimit = 7;

        Search_ajaxHttpInitRoster();
    },

    roster : function(key) {
        var selector = '#search > #roster > li';
        var subheader = document.querySelector(selector + '.subheader');
        var showall = document.querySelector(selector + '.showall');

        document.querySelectorAll(selector)
            .forEach(item => item.classList.remove('found'));

        if (key == '') {
            document.querySelectorAll(selector + ':nth-child(-n+' + (Search.rosterLimit+1) + ')').forEach(item => item.classList.add('found'));
            if (document.querySelector(selector + ':last-child')) {
                document.querySelector(selector + ':last-child').classList.add('found');
            }
        };

        var founds = document.querySelectorAll(
            selector + '[name*="' + MovimUtils.cleanupId(key).slice(3) + '"]'
        );

        founds = [].slice.call(founds).slice(0, Search.rosterLimit);

        if (founds.length > 0) {
            if (subheader) {
                subheader.classList.add('found');
            }

            showall.classList.add('found');
            founds.forEach(item => item.classList.add('found'));
        } else if (key != '') {
            if (subheader) {
                subheader.classList.remove('found');
            }

            if (showall) {
                showall.classList.remove('found');
            }
        }
    },

    showCompleteRoster : function(li)
    {
        li.style.display = 'none';
        Search.rosterLimit = document.querySelectorAll('#search > #roster > li:not(.showall)').length;
        Search.searchCurrent();
    },

    chat : function(jid) {
        if (MovimUtils.urlParts().page === 'chat') {
            Drawer_ajaxClear();
            Chats_ajaxOpen(jid);
            Chat.get(jid);
        } else {
            Search_ajaxChat(jid);
        }
    },

    searchCurrent() {
        Search.searchSomething(document.querySelector('#searchbar input[name=keyword').value);
    },

    searchSomething : function(value) {
        clearTimeout(Search.timer);

        if (value !== '') {
            document.querySelector('#searchbar span.primary i').innerText = 'autorenew';
            document.querySelector('#searchbar span.primary').classList.add('spin');
        }

        Search.timer = setTimeout(() => {
            Search_ajaxSearch(value);
        }, 700);

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
