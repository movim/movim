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

function rosterToggleClass(myClass){
	c = roster.querySelectorAll('.'+myClass);
    for(i = 0; i < c.length; i++) {
        if(c.item(i).style.display == 'list-item')
            c.item(i).style.display = 'none';
        else
            c.item(i).style.display = 'list-item';
    }
}

function showRoster(n) {
    roster = document.querySelector('#rosterlist');
    rosterToggleClass("offline");
    rosterToggleClass("server_error");
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
		//offline are shown
		if(rosterlist.querySelector(".offline").style.display!=""){
			rosterlist.querySelector("li").className += " focused";
		}
		//offline are hidden
		else{
			rosterlist.querySelector("li:not(.offline)").className += " focused";
		}
		document.querySelector('#right').scrollTop = 0;
	}
	else{
		document.querySelector('#right').scrollTop = rosterlist.querySelector('.focused').offsetTop-document.querySelector('#nav').offsetHeight;
	}
}

function rosterNextGroup(cgp){
	thisGp = gp[0];
	while(cgp != thisGp && thisGp.nextSibling!= null){
		thisGp = thisGp.nextSibling;
	}
	if(thisGp.nextSibling!= null){
		//test "ob"
		console.log(thisGp.querySelectorAll("li")[0]);
        thisGp = thisGp.nextSibling;
        //if(rosterlist.querySelector(".offline").style.display != ""){//offline are shown
            while(thisGp.querySelectorAll("li:not([style='display: none;'])").length == 0){
                thisGp = thisGp.nextSibling;
            }
        /*}
        else{
            while(thisGp.querySelectorAll("li:not([style='display: none;'])").length == 0){
                thisGp = thisGp.nextSibling;
            }
        }*/
		cgp = thisGp;
		currContact = cgp.querySelectorAll("li")[0];
	}
}

function rosterNext(){
	currFocus = rosterlist.querySelector(".focused");
	currGp = currFocus.parentNode;
	end = false;
	gp = rosterlist.querySelectorAll("div:not([class='chat on'])");
	currContact = currGp.querySelectorAll("li")[0];
    //Define contact end limit
    if(rosterlist.querySelector(".offline").style.display != "")//offline are shown
        last = rosterlist.querySelectorAll("li:not([style='display: none;'])")[rosterlist.querySelectorAll("li:not([style='display: none;'])").length -1];
    else
        last = rosterlist.querySelectorAll("li:not(.offline), li:not([style='display: none;'])")[rosterlist.querySelectorAll("li:not(.offline), li:not([style='display: none;'])").length -1];

    if(currFocus != last){
        while(currContact.className.lastIndexOf("focused") < 0 && currContact != null){
            currContact = currContact.nextSibling;
        }
        currContact = currContact.nextSibling;
        //Change groupe
        if(currContact == null ){
            if( currGp !== gp[gp.length-1]){
                rosterNextGroup(currGp);
            }
            else{
                end = true;
            }
        }
        if(currContact !== null && !end){
            //offline are shown
            if(rosterlist.querySelector(".offline").style.display != ""){
                while(currContact.style.display.lastIndexOf("none") > (-1) && !end){
                    if(currContact.nextSibling != null){
                        currContact = currContact.nextSibling;
                    }
                    else{
                        if(currGp != gp[gp.length-1])
                            rosterNextGroup(currGp);
                        else{
                            end = true;
                        }
                    }
                }
            }
            //offline are hidden
            else{
                while((currContact.className.lastIndexOf("offline") > -1 || currContact.className.lastIndexOf("server") > -1) && !end){
                    if(currContact.nextSibling != null){
                        currContact = currContact.nextSibling;
                    }
                    else{
                        if(currGp != gp[gp.length-1])
                            rosterNextGroup(currGp);
                        else{
                            end = true;
                        }
                    }
                }
            }
            if(end)
                contact = currFocus;
            else
                contact = currContact;
        }
        else{
            if(end)
                contact = currFocus;
        }
    }
    else contact = currFocus;
}

function rosterPreviousGroup(cgp){
	thisGp = gp[0];
	if(cgp != thisGp){ //not first group
		while(thisGp.nextSibling != cgp){
			thisGp = thisGp.nextSibling;//change group
		}
		console.log(thisGp.querySelectorAll("li:not([style='display: none;'])")[0]);
			//thisGp = thisGp.previousSibling;
			while(thisGp.querySelectorAll("li:not([style='display: none;'])").length == 0 && thisGp != firstGroup){
				thisGp = thisGp.previousSibling;
			}
			currGp = thisGp;
			first = currGp.querySelector("li");
			currContact = thisGp.querySelectorAll("li")[thisGp.querySelectorAll("li").length-1];
			console.log(currContact);
	}
}

function rosterPrevious(){
	currFocus = rosterlist.querySelector(".focused");
	currGp = currFocus.parentNode;
	gp = rosterlist.querySelectorAll("div:not([class='chat on'])");
	firstGroup = gp[0];
	end = false;
	firstContact = currGp.querySelector("li");
	currContact = currGp.querySelectorAll("li")[currGp.querySelectorAll("li").length -1];
    if(rosterlist.querySelector(".offline").style.display != "")//offline are shown
        first = rosterlist.querySelector("li:not([style='display: none;'])");
    else
        first = rosterlist.querySelector("li:not(.offline), li:not([style='display: none;'])");
    if(currFocus != first){
        while(currContact.className.lastIndexOf("focused") < 0 && currContact != firstContact){
            currContact = currContact.previousSibling;
        }
        currContact = currContact.previousSibling;
        if(currGp.querySelector("li") == currFocus){ //first contact of the group
            rosterPreviousGroup(currGp);
        }
        if(currGp.querySelector("li") != currFocus && !end){
            //offline are shown
            if(rosterlist.querySelector(".offline").style.display != ""){
                while(currContact.style.display.lastIndexOf("none") > (-1)){
                    if(currContact != firstContact){
                        currContact = currContact.previousSibling;
                    }
                    else{
                        rosterPreviousGroup(currGp);
                    }
                }
                if(end)
                    contact = currFocus;
                else
                    contact = currContact;
            }
            //offline are hidden
            else{
                while((currContact.className.lastIndexOf("offline") > -1 || currContact.className.lastIndexOf("server") > -1) && !end){
                    if(currContact != firstContact){
                        currContact = currContact.previousSibling;
                    }
                    else{
                        rosterPreviousGroup(currGp);
                    }
                }
                if(end)
                    contact = currFocus;
                else
                    contact = currContact;
            }
        }
    }
    else{
        contact = currFocus;
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
			//contact is the first contact of the list which is shown (already sorted)
			contact = rosterlist.querySelectorAll("li[style='display: list-item; ']")[0];
			//otherwise it is the first contact of the list
			if(typeof contact === 'undefined'){
				contact = rosterlist.querySelectorAll("li:not(.offline)")[0];
			}
			found = false;
			if(0 == (decallage = contact.offsetHeight))
				decallage = contact.nextSibling.offsetHeight;

			switch(e.keyCode){
				//previous
				case e.keyCode = 38:
					rosterPrevious();
					if(contact.offsetTop-document.querySelector('#right').scrollTop < decallage){
						document.querySelector('#right').scrollTop = currContact.offsetTop-document.querySelector('#nav').offsetHeight;
					}
					break;
				//next
				case e.keyCode = 40:
					rosterNext();
					if(contact.offsetTop+decallage-document.querySelector('#right').scrollTop >= document.querySelector('#rostermenu').offsetTop){
						document.querySelector('#right').scrollTop += contact.offsetTop+decallage-document.querySelector('#right').scrollTop - document.querySelector('#rostermenu').offsetTop;
					}
					break;
			}
			if(focused.className == (focused.className = focused.className.replace(" focused", "")))
				focused.className = focused.className.replace("focused", "");
			if(contact.className.lastIndexOf("focused")<0)
				contact.className += " focused";
		}
	}
}

function addJid(n) {
    document.querySelector('#addcontact').style.display = "block";
}

function cancelAddJid() {
    document.querySelector('#addcontact').style.display = "none";
}

function getAlias() {
    return document.querySelector('#notifsalias').value;
}

function getAddJid() {
    return document.querySelector('#addjid').value;
}

function getAddAlias() {
    return document.querySelector('#addalias').value;
}
