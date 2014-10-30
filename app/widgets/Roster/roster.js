function sortRoster() {

    roster = document.querySelector('#rosterlist');
    contacts = roster.querySelectorAll('li');

    online = roster.querySelectorAll('.online');
    for(i = 0; i < online.length; i++) {
        online.item(i).parentNode.insertBefore(online.item(i), contacts.item(contacts.length))
    }
    away = roster.querySelectorAll('.away');
    for(i = 0; i < away.length; i++) {
        away.item(i).parentNode.insertBefore(away.item(i), contacts.item(contacts.length))
    }
    dnd = roster.querySelectorAll('.dnd');
    for(i = 0; i < dnd.length; i++) {
        dnd.item(i).parentNode.insertBefore(dnd.item(i), contacts.item(contacts.length))
    }
    xa = roster.querySelectorAll('.xa');
    for(i = 0; i < xa.length; i++) {
        xa.item(i).parentNode.insertBefore(xa.item(i), contacts.item(contacts.length))
    }
    offline = roster.querySelectorAll('.offline');
    for(i = 0; i < offline.length; i++) {
        offline.item(i).parentNode.insertBefore(offline.item(i), contacts.item(contacts.length))
    }

    server_error = roster.querySelectorAll('.server_error');
    for(i = 0; i < server_error.length; i++) {
        server_error.item(i).parentNode.insertBefore(server_error.item(i), contacts.item(contacts.length))
    }
}

function showRoster(boolOffline) {
    if(boolOffline == '1')
        document.querySelector('ul#rosterlist').className = 'offlineshown';
    else
        document.querySelector('ul#rosterlist').className = '';
}

function showHideRoster(hide) {
    if(hide == '1')
        document.querySelector('#roster').className = 'hide';
    else
        document.querySelector('#roster').className = '';        
}

movim_add_onload(function()
{
    var search      = document.querySelector('#rostersearch');
    var roster      = document.querySelector('#roster');
    var rosterlist  = document.querySelector('#rosterlist');
    
    var roster_classback      = document.querySelector('#roster').className;
    var rosterlist_classback  = document.querySelector('#rosterlist').className;   

    roster.onblur  = function() {
        roster.className = roster_classback;
        rosterlist.className = rosterlist_classback;
    };
    search.onkeyup = function(event) {
        if(search.value.length > 0) {
            roster.className = 'search';
            rosterlist.className = 'offlineshown';
        } else {
            roster.className = roster_classback;
            rosterlist.className = rosterlist_classback;
        }

        // We clear the old search
        var selector_clear = '#rosterlist div > li';
        var li = document.querySelectorAll(selector_clear);

        for(i = 0; i < li.length; i++) {
            li.item(i).className = '';
        }

        // We select the interesting li
        var selector = '#rosterlist div > li[title*=\'' + search.value + '\']';
        var li = document.querySelectorAll(selector);

        for(i = 0; i < li.length; i++) {
            li.item(i).className = 'found';
        }
    };
});

function rosterToggleGroup(id){
    group = document.getElementById(id);
    
    if(group.className == '')
        group.className = 'groupshown';
    else
        group.className = '';
}

