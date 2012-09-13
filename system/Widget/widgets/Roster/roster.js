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

    more = roster.querySelector('.more');
    roster.insertBefore(more, contacts.item(contacts.length));

    /*for(i = 0; i < 10; i++) {
        if(contacts.item(i) != null)
            contacts.item(i).style.display = 'block';
    }*/

    if(contacts.length < 9)
        more.style.display = 'none';

}

function showRoster(n) {
    roster = document.querySelector('#rosterlist');
    offline = roster.querySelectorAll('.offline');
    for(i = 0; i < offline.length; i++) {
        if(offline.item(i).style.display == 'list-item')
            offline.item(i).style.display = 'none';
        else
            offline.item(i).style.display = 'list-item';
    }
}

function incomingPresence(val) {
    target = document.getElementById('roster'+val[0]);
    if(target) {
        target.className = val[1];
    }
    sortRoster();
}

/*ROSTER SEARCH*/
function focusContact(){
	rosterlist = document.querySelector('#rosterlist');
	if(rosterlist.querySelector('.focused') == null){
		/*if(rosterlist.querySelector("li[style='display: list-item; ']")){
			rosterlist.querySelector("li[style='display: list-item; ']").className += " focused";
		}
		else*/
			rosterlist.querySelector("li:not([class='offline ']):not([style=''])").className += " focused";
	}
}

function rosterNext(c){
	if(c.className.lastIndexOf("focused")>-1){
		found = true;
	}
	if(c.nextSibling != null){
		if((c.nextSibling.style.display !='' || c.nextSibling.className.lastIndexOf("offline")<0))
			contact = c.nextSibling;
	}
	else{
		if(contact.parentNode.nextSibling!=null && contact.parentNode.nextSibling.nodeName!='#text'){
			contact = c.parentNode.nextSibling.querySelectorAll("li")[0];
		}
		else{
			lastContact = c;
		}
	}
}
function rosterPrevious(c){
	if(c.className.lastIndexOf("focused")>-1){
		found = true;
	}
	if(c.previousSibling == null || c.previousSibling.nodeName == "H1"){
		if(c.parentNode.previousSibling.nodeName!='#text' && c.parentNode.previousSibling!=null){
			last = c.parentNode.previousSibling.querySelectorAll("li").length - 1;
			contact = c.parentNode.previousSibling.querySelectorAll("li")[last];
			if((decallage = 2*c.offsetHeight) == 0)
				decallage = 2*c.previousSibling.offsetHeight;
		}
	}
	else{
		contact = c.previousSibling;
		if(0 == (decallage = c.offsetHeight))
		decallage = c.previousSibling.offsetHeight;
	}
}

function rosterSearch(e){
	rosterlist = document.querySelector('#rosterlist');
	parents = rosterlist.querySelectorAll('li');
	names = rosterlist.getElementsByTagName('span');
	request = document.querySelector('#request').value;
	focused = rosterlist.querySelector('.focused');

	if(e.keyCode==8 || (e.keyCode>47 && e.keyCode<91) || (e.keyCode>95 && e.keyCode<106) || e.keyCode==46){//key pressed is backspace, alphanumeric or delete
		focusflag = false;
		for(i = 0; i < parents.length; i++){
			if(names[i].innerHTML.toLowerCase().lastIndexOf(request.toLowerCase()) == -1){
				parents[i].style.display = "none";
			}
			else{
				parents[i].style.display = "list-item";
				if(rosterlist.querySelectorAll('.focused').length == 1 && !focusflag){
					rosterlist.querySelectorAll('.focused')[0].className = rosterlist.querySelectorAll('.focused')[0].className.split(' ')[0];
					parents[i].className += " focused";
					focusflag = true;
				}
			}
		}
		document.querySelector('#right').scrollTop = 0;
	}
	else{
		if(e.keyCode == 13){ //key pressed is enter; launch chat
			eval(focused.getElementsByTagName("div")[0].getAttribute("onclick"));
		}
		if(e.keyCode>36 && e.keyCode<41){ //key pressed is an arrow
			//lastContact = "";
			//contact is the first contact of the list which is shown (already sorted)
			contact = rosterlist.querySelectorAll("li[style='display: list-item; ']")[0];
			//otherwise it is the first contact of the list
			if(typeof contact === 'undefined'){
				contact = rosterlist.querySelectorAll("li:not([class='offline '])")[0];
			}
			found = false;
			if(0 == (decallage = contact.offsetHeight))
				decallage = contact.nextSibling.offsetHeight;

			switch(e.keyCode){
				//previous
				case e.keyCode = 37:
				case e.keyCode = 38:
					while(contact.className.lastIndexOf("focused") == -1){
						if(contact.nextSibling != null){
							contact = contact.nextSibling;
						}
						else{
							contact = contact.parentNode.nextSibling.querySelectorAll("li")[0];
						}
					}
					if(!(contact.previousSibling.nodeName == "H1" && contact.parentNode.previousSibling == null)){
						if(document.querySelector('.offline').style.display == ''){//before filtering, offline contacts are hidden
							while((contact.className.lastIndexOf("offline")>-1 || contact.className.lastIndexOf("error")>-1) || !found){//so they can't be focused
								rosterPrevious(contact);
							}
						}
						else{
							while(contact.getAttribute('style')=="" || !found){ //.lastIndexOf('list-item')<0
								rosterPrevious(contact);
							}
						}
						if(contact.offsetTop-document.querySelector('#right').scrollTop < decallage){
							document.querySelector('#right').scrollTop -= decallage -(contact.offsetTop-document.querySelector('#right').scrollTop) +10;
						}
					}
					else{
console.log(contact.parentNode.previousSibling);
						}
					break;
				//next
				case e.keyCode = 39:
				case e.keyCode = 40:
					//if(contact !== rosterlist.querySelectorAll("li:not([class='offline'])")[rosterlist.querySelectorAll("li:not([class='offline'])").length-1]){
						console.log(rosterlist.querySelectorAll("li:not([class='offline'])")[rosterlist.querySelectorAll("li:not([class='offline'])").length-1]);
						if(document.querySelector('.offline').style.display == '' ){
							while((contact.className.lastIndexOf("offline")>-1 || contact.className.lastIndexOf("error")>-1 || !found) ){
								rosterNext(contact);
							}
						}
						else{
							while((contact.getAttribute('style')=="" || !found) ){//.lastIndexOf('list-item')<0
								rosterNext(contact);
							}
						}
						if(contact.offsetTop+decallage-document.querySelector('#right').scrollTop >= document.querySelector('#rostermenu').offsetTop){
							document.querySelector('#right').scrollTop += contact.offsetTop+decallage-document.querySelector('#right').scrollTop - document.querySelector('#rostermenu').offsetTop;
						}
					//}
					break;
			}
			if(focused.className == (focused.className = focused.className.replace(" focused", "")))
				focused.className = focused.className.replace("focused", "");
			if(contact.className.lastIndexOf("focused")<0)
				contact.className += " focused";
		}
	}
}
