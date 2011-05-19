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
    // Giving focus to input field.
    document.getElementById("chatInput").focus();
}

function incomingPresence(params) {
	var target = document.getElementById('status_'+params[0])
	if(target) {
		target.innerHTML = params[1];
	}
}
