function sortRoster() {
    roster = document.querySelector('#rosterlist');
    contacts = roster.querySelectorAll('li');

    online = roster.querySelectorAll('.online');
    for(i = 0; i < online.length; i++) {
        roster.insertBefore(online.item(i), contacts.item(contacts.length))
    }
    away = roster.querySelectorAll('.away');
    for(i = 0; i < away.length; i++) {
        roster.insertBefore(away.item(i), contacts.item(contacts.length))
    }
    dnd = roster.querySelectorAll('.dnd');
    for(i = 0; i < dnd.length; i++) {
        roster.insertBefore(dnd.item(i), contacts.item(contacts.length))
    }
    xa = roster.querySelectorAll('.xa');
    for(i = 0; i < xa.length; i++) {
        roster.insertBefore(xa.item(i), contacts.item(contacts.length))
    }
    
    offline = roster.querySelectorAll('.offline');
    for(i = 0; i < offline.length; i++) {
        roster.insertBefore(offline.item(i), contacts.item(contacts.length))
    }
    
    more = roster.querySelector('.more');
    roster.insertBefore(more, contacts.item(contacts.length));
        
    for(i = 0; i < 10; i++) {
        if(contacts.item(i) != null)
            contacts.item(i).style.display = 'block';
    }
    
    if(contacts.length < 9)
        more.style.display = 'none';
    
}

function showRoster(n) {
    roster = document.querySelector('#rosterlist');
    offline = roster.querySelectorAll('.offline');
    for(i = 0; i < offline.length; i++) {
        offline.item(i).style.display = 'block';
    }
    
    n.style.display = 'none';
}

function incomingPresence(val) {
    target = document.getElementById('roster'+val[0]);
    if(target) {
        target.className = val[1];
    }
    sortRoster();
}
