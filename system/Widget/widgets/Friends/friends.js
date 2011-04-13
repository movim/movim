function incomingOnline(jid) {
    target = document.getElementById(jid);
    if(target) {
        target.className = "online";
    }
}

function incomingOffline(jid) {
    target = document.getElementById(jid);
    if(target) {
        target.className = "offline";
    }
}

function incomingDND(jid) {
    target = document.getElementById(jid);
    if(target) {
        target.className = "dnd";
    }
}

function incomingAway(jid) {
    target = document.getElementById(jid);
    if(target) {
        target.className = "away";
    }
}

function setChatUser(user) {
    var target = document.getElementById('chatTo');
    	target.value = user;
}
